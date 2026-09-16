const { default: makeWASocket, useMultiFileAuthState, DisconnectReason, downloadMediaMessage } = require('@whiskeysockets/baileys');
const QRCode = require('qrcode');
const express = require('express');
const cors = require('cors');
const path = require('path');
const fs = require('fs');

const app = express();
app.use(cors({ origin: false })); // no browser CORS — internal only
app.use(express.json({ limit: '10mb' })); // reduced from 50mb

// Load .env file automatically if present
try {
    const envPath = path.join(__dirname, '.env');
    if (fs.existsSync(envPath)) {
        const lines = fs.readFileSync(envPath, 'utf8').split('\n');
        for (const line of lines) {
            const trimmed = line.trim();
            if (trimmed && !trimmed.startsWith('#') && trimmed.includes('=')) {
                const idx = trimmed.indexOf('=');
                const key = trimmed.substring(0, idx).trim();
                const val = trimmed.substring(idx + 1).trim().replace(/^["']|["']$/g, '');
                if (key && !process.env[key]) {
                    process.env[key] = val;
                }
            }
        }
    }
} catch (e) {}

// Require internal token on every request — only Laravel proxy should call this
const INTERNAL_SECRET = process.env.WA_INTERNAL_SECRET || 'change-me-in-env';
app.use((req, res, next) => {
    if (req.headers['x-internal-token'] !== INTERNAL_SECRET) {
        return res.status(403).json({ error: 'Forbidden' });
    }
    next();
});

const PORT = 3000;

// ─── Global Templates (Admin-created, shared across all users) ────────────────
const GLOBAL_TEMPLATES_FILE = path.join(__dirname, 'global_templates.json');
let globalTemplatesStore = [];
try {
    if (fs.existsSync(GLOBAL_TEMPLATES_FILE)) {
        const raw = JSON.parse(fs.readFileSync(GLOBAL_TEMPLATES_FILE, 'utf-8'));
        if (Array.isArray(raw)) globalTemplatesStore = raw;
    }
} catch(e) {}

function saveGlobalTemplates() {
    try { fs.writeFileSync(GLOBAL_TEMPLATES_FILE, JSON.stringify(globalTemplatesStore, null, 2), 'utf-8'); } catch(e) {}
}

// ─── Per-User Session ────────────────────────────────────────────────────────

function createSessionData(userId) {
    return {
        userId,
        waSock: null,
        connectionStatus: 'disconnected',
        userProfile: { name: '', phone: '' },
        currentQRDataURL: null,
        allChatsMap: new Map(),
        messagesStore: new Map(),
        profilePicCache: new Map(),
        contactsStore: {},
        savedListsStore: [],
        savedTemplatesStore: [],
        scheduledBroadcasts: [],   // { id, name, message, imageBase64, jids, scheduledAt, createdAt }
        broadcastHistory: [],      // { id, name, message, jids, sentCount, failCount, timestamp }
        storeFilePath: path.join(__dirname, `baileys_store_${userId}.json`),
        authFolder: path.join(__dirname, `auth_info_${userId}`),
        _saveDiskTimer: null,
        _picFetchTimer: null,
    };
}

/** sessions Map: userId (string) -> sessionData */
const sessions = new Map();

function getSession(userId) {
    if (!sessions.has(userId)) {
        const s = createSessionData(userId);
        sessions.set(userId, s);
        loadStoreFromDisk(s);
        // Auto-connect when session is first created
        connectToWhatsApp(s);
    }
    return sessions.get(userId);
}

function getSessionFromReq(req) {
    const userId = String(req.headers['x-wa-user'] || 'default');
    return getSession(userId);
}

// ─── Disk Storage ─────────────────────────────────────────────────────────────

function loadStoreFromDisk(s) {
    if (!fs.existsSync(s.storeFilePath)) return;
    try {
        const data = JSON.parse(fs.readFileSync(s.storeFilePath, 'utf-8'));
        if (data.contacts) s.contactsStore = data.contacts;
        if (Array.isArray(data.savedLists)) s.savedListsStore = data.savedLists;
        if (Array.isArray(data.savedTemplates)) s.savedTemplatesStore = data.savedTemplates;
        if (Array.isArray(data.scheduledBroadcasts)) s.scheduledBroadcasts = data.scheduledBroadcasts;
        if (Array.isArray(data.broadcastHistory)) s.broadcastHistory = data.broadcastHistory;
        if (Array.isArray(data.chats)) {
            data.chats.forEach(c => {
                if (c.id && isAllowedJid(c.id)) s.allChatsMap.set(c.id, c);
            });
        }
        if (data.messages) {
            Object.entries(data.messages).forEach(([jid, msgs]) => s.messagesStore.set(jid, msgs));
        }
        console.log(`📁 [User:${s.userId}] Loaded ${Object.keys(s.contactsStore).length} contacts, ${s.allChatsMap.size} chats`);
    } catch (e) {
        console.error(`[User:${s.userId}] Error loading store:`, e.message);
    }
}

function saveStoreToDisk(s, immediate = false) {
    if (immediate) {
        _flushStore(s);
    } else {
        if (!s._saveDiskTimer) {
            s._saveDiskTimer = setTimeout(() => {
                s._saveDiskTimer = null;
                _flushStore(s);
            }, 3000);
        }
    }
}

function _flushStore(s) {
    try {
        const chats = Array.from(s.allChatsMap.values());
        const messages = {};
        s.messagesStore.forEach((msgs, jid) => { messages[jid] = msgs.slice(-50); });
        fs.writeFileSync(s.storeFilePath, JSON.stringify({
            savedLists: s.savedListsStore,
            savedTemplates: s.savedTemplatesStore,
            scheduledBroadcasts: s.scheduledBroadcasts,
            broadcastHistory: s.broadcastHistory.slice(-200),
            contacts: s.contactsStore,
            chats,
            messages
        }, null, 2), 'utf-8');
    } catch (e) {
        console.error(`[User:${s.userId}] Error saving store:`, e.message);
    }
}

// ─── WhatsApp Connection ──────────────────────────────────────────────────────

function isAllowedJid(jid) {
    if (!jid) return false;
    return jid.endsWith('@s.whatsapp.net') || jid.endsWith('@g.us');
}

async function connectToWhatsApp(s) {
    if (s._connecting) return;
    s._connecting = true;

    try {
        const { state, saveCreds } = await useMultiFileAuthState(s.authFolder);

        s.waSock = makeWASocket({
            auth: state,
            printQRInTerminal: false,
            browser: ['ISP Tickets Hub', 'Chrome', '120.0.0'],
            syncFullHistory: false,
            generateHighQualityLinkPreview: false,
            getMessage: async (key) => {
                const msgs = s.messagesStore.get(key.remoteJid) || [];
                const found = msgs.find(m => m.id === key.id);
                return found ? { conversation: found.text } : { conversation: '' };
            }
        });

        s.waSock.ev.on('creds.update', saveCreds);

        s.waSock.ev.on('connection.update', async (update) => {
            const { connection, lastDisconnect, qr } = update;

            if (qr) {
                s.currentQRDataURL = await QRCode.toDataURL(qr);
                s.connectionStatus = 'qr_ready';
                console.log(`🔄 [User:${s.userId}] New QR generated`);
            }

            if (connection === 'open') {
                s._connecting = false;
                s.connectionStatus = 'connected';
                s.currentQRDataURL = null;

                const rawId = s.waSock.user.id || '';
                const phone = rawId.split(':')[0].split('@')[0];
                s.userProfile = { name: s.waSock.user.name || ('+' + phone), phone: '+' + phone, profilePic: null };
                console.log(`✅ [User:${s.userId}] Connected:`, s.userProfile);

                try {
                    const ownJid = rawId.includes('@') ? rawId : (phone + '@s.whatsapp.net');
                    const pic = await s.waSock.profilePictureUrl(ownJid, 'image').catch(() => null);
                    s.userProfile.profilePic = pic || null;
                    s.profilePicCache.set(ownJid, pic || null);
                } catch (e) {}

                setTimeout(() => syncChatsAndGroups(s), 2000);
            setTimeout(() => fetchProfilePics(s), 8000);
            }

            if (connection === 'close') {
                s._connecting = false;
                s.connectionStatus = 'disconnected';
                const statusCode = lastDisconnect?.error?.output?.statusCode;
                const shouldReconnect = statusCode !== DisconnectReason.loggedOut;
                console.log(`[User:${s.userId}] Connection closed. Code:${statusCode} Reconnect:${shouldReconnect}`);

                if (shouldReconnect) {
                    setTimeout(() => connectToWhatsApp(s), 3000);
                } else {
                    // Logged out — clean and restart for new QR
                    if (fs.existsSync(s.authFolder)) {
                        try { fs.rmSync(s.authFolder, { recursive: true, force: true }); } catch (e) {}
                    }
                    if (fs.existsSync(s.storeFilePath)) {
                        try { fs.unlinkSync(s.storeFilePath); } catch (e) {}
                    }
                    // Reset session data but keep it in sessions map
                    s.contactsStore = {};
                    s.allChatsMap.clear();
                    s.messagesStore.clear();
                    s.profilePicCache.clear();
                    s.savedListsStore = [];
                    s.savedTemplatesStore = [];
                    s.scheduledBroadcasts = [];
                    setTimeout(() => connectToWhatsApp(s), 2000);
                }
            }
        });

        // History Sync
        s.waSock.ev.on('messaging-history.set', ({ chats, contacts, messages }) => {
            if (contacts) {
                contacts.forEach(c => {
                    if (!c.id) return;
                    s.contactsStore[c.id] = { id: c.id, name: c.name || c.notify || c.pushName || null, notify: c.notify || null, pushName: c.pushName || null };
                    updateChatName(s, c.id, c.name || c.notify || c.pushName);
                });
            }
            if (chats) chats.forEach(c => addOrUpdateChat(s, c));
            if (messages) messages.forEach(msg => processMessage(s, msg));
            saveStoreToDisk(s);
        });

        s.waSock.ev.on('contacts.set', ({ contacts }) => {
            contacts.forEach(c => {
                if (!c.id) return;
                s.contactsStore[c.id] = { id: c.id, name: c.name || c.notify || c.pushName || null, notify: c.notify || null, pushName: c.pushName || null };
                updateChatName(s, c.id, c.name || c.notify || c.pushName);
            });
            saveStoreToDisk(s);
        });

        s.waSock.ev.on('contacts.upsert', (contacts) => {
            contacts.forEach(c => {
                if (!c.id) return;
                s.contactsStore[c.id] = {
                    id: c.id,
                    name: c.name || s.contactsStore[c.id]?.name || null,
                    notify: c.notify || s.contactsStore[c.id]?.notify || null,
                    pushName: c.pushName || s.contactsStore[c.id]?.pushName || null,
                };
                updateChatName(s, c.id, c.name || c.notify || c.pushName);
            });
            saveStoreToDisk(s);
        });

        s.waSock.ev.on('contacts.update', (updates) => {
            updates.forEach(c => {
                if (!c.id) return;
                if (!s.contactsStore[c.id]) s.contactsStore[c.id] = { id: c.id };
                if (c.name) s.contactsStore[c.id].name = c.name;
                if (c.notify) s.contactsStore[c.id].notify = c.notify;
                if (c.pushName) s.contactsStore[c.id].pushName = c.pushName;
                updateChatName(s, c.id, c.name || c.notify || c.pushName);
            });
            saveStoreToDisk(s);
        });

        s.waSock.ev.on('chats.set', ({ chats }) => {
            chats.forEach(c => addOrUpdateChat(s, c));
            saveStoreToDisk(s);
        });

        s.waSock.ev.on('chats.upsert', (chats) => {
            chats.forEach(c => addOrUpdateChat(s, c));
            saveStoreToDisk(s);
        });

        s.waSock.ev.on('messages.upsert', async (m) => {
            if (m.type === 'notify' || m.type === 'append') {
                for (const msg of m.messages) await processMessage(s, msg, true);
                saveStoreToDisk(s);
            }
        });

    } catch (err) {
        s._connecting = false;
        console.error(`[User:${s.userId}] Connect error:`, err.message);
        setTimeout(() => connectToWhatsApp(s), 5000);
    }
}

function updateChatName(s, jid, name) {
    if (!jid || !name) return;
    if (s.allChatsMap.has(jid)) {
        const chat = s.allChatsMap.get(jid);
        if (!chat.name || chat.name.startsWith('+') || chat.name === jid) chat.name = name;
    }
}

function addOrUpdateChat(s, c) {
    if (!c.id || !isAllowedJid(c.id)) return;
    const isGroup = c.id.endsWith('@g.us');
    const ci = s.contactsStore[c.id] || {};
    const name = c.name || c.subject || ci.name || ci.notify || ci.pushName || (isGroup ? 'WhatsApp Group' : ('+' + c.id.replace('@s.whatsapp.net', '')));
    const existing = s.allChatsMap.get(c.id) || {
        id: c.id, name, isGroup, unreadCount: c.unreadCount || 0,
        lastMessage: '', timestamp: '', lastMessageTimestamp: (c.conversationTimestamp ? c.conversationTimestamp * 1000 : 0)
    };
    if (name && (existing.name.startsWith('+') || existing.name === 'WhatsApp Group')) existing.name = name;
    if (c.unreadCount !== undefined) existing.unreadCount = c.unreadCount;
    s.allChatsMap.set(c.id, existing);
}

async function processMessage(s, msg, downloadImage = false) {
    if (!msg.message) return;
    const jid = msg.key.remoteJid;
    if (!jid || !isAllowedJid(jid)) return;
    const fromMe = msg.key.fromMe;
    const mMsg = msg.message?.ephemeralMessage?.message || msg.message?.viewOnceMessage?.message || msg.message;
    const hasImage = !!(mMsg.imageMessage);
    const text = mMsg.conversation || mMsg.extendedTextMessage?.text ||
        (mMsg.imageMessage ? (mMsg.imageMessage.caption || '') : '') ||
        (mMsg.videoMessage ? (mMsg.videoMessage.caption || '📹 Video') : '') ||
        (mMsg.documentMessage ? ('📄 ' + (mMsg.documentMessage.fileName || 'Document')) : '') ||
        (mMsg.audioMessage ? '🎵 Voice Message' : '') ||
        (mMsg.stickerMessage ? '🎨 Sticker' : '') ||
        (mMsg.reactionMessage ? (mMsg.reactionMessage.text || '❤️') : '') ||
        (mMsg.locationMessage ? '📍 Location' : '') ||
        (mMsg.contactMessage ? ('👤 ' + (mMsg.contactMessage.displayName || 'Contact')) : '') || '';
    const senderName = msg.pushName || (fromMe ? 'You' : jid.split('@')[0]);

    if (!fromMe && msg.pushName && jid.endsWith('@s.whatsapp.net')) {
        if (!s.contactsStore[jid]) s.contactsStore[jid] = { id: jid };
        if (!s.contactsStore[jid].name) s.contactsStore[jid].name = msg.pushName;
        s.contactsStore[jid].pushName = msg.pushName;
    }

    const msgTimeMs = msg.messageTimestamp ? msg.messageTimestamp * 1000 : Date.now();
    const timestamp = new Date(msgTimeMs).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

    if (!s.messagesStore.has(jid)) s.messagesStore.set(jid, []);
    const history = s.messagesStore.get(jid);
    if (!history.some(m => m.id === msg.key.id)) {
        let imageBase64 = null;
        if (hasImage && downloadImage && s.waSock) {
            try {
                const buf = await downloadMediaMessage(msg, 'buffer', {}, { reuploadRequest: s.waSock.updateMediaMessage });
                const mime = mMsg.imageMessage.mimetype || 'image/jpeg';
                imageBase64 = `data:${mime};base64,${buf.toString('base64')}`;
            } catch(e) {}
        }
        const msgObj = { id: msg.key.id, fromMe, senderName, text, timestamp, hasImage };
        if (imageBase64) msgObj.imageBase64 = imageBase64;
        history.push(msgObj);
    }

    const isGroup = jid.endsWith('@g.us');
    const ci = s.contactsStore[jid] || {};
    const chatName = isGroup ? (s.allChatsMap.get(jid)?.name || 'Group Chat') : (ci.name || ci.notify || ci.pushName || senderName || ('+' + jid.split('@')[0]));
    const existingChat = s.allChatsMap.get(jid) || { id: jid, name: chatName, isGroup, unreadCount: 0 };
    existingChat.lastMessage = text;
    existingChat.lastSender = fromMe ? 'You' : (isGroup ? senderName : null);
    existingChat.timestamp = timestamp;
    existingChat.lastMessageTimestamp = msgTimeMs;
    existingChat.fromMe = fromMe;
    if (!fromMe) existingChat.unreadCount = (existingChat.unreadCount || 0) + 1;
    s.allChatsMap.set(jid, existingChat);
}

async function syncChatsAndGroups(s) {
    if (!s.waSock || s.connectionStatus !== 'connected') return;
    try {
        const groups = await s.waSock.groupFetchAllParticipating();
        Object.values(groups).forEach(g => {
            s.allChatsMap.set(g.id, {
                id: g.id, name: g.subject || g.id, isGroup: true,
                unreadCount: s.allChatsMap.get(g.id)?.unreadCount || 0,
                lastMessage: `${g.participants?.length || 0} members`,
                timestamp: 'Group', participants: g.participants?.length || 0
            });
        });
        console.log(`📋 [User:${s.userId}] Synced ${Object.keys(groups).length} groups`);
    } catch (err) {
        console.log(`[User:${s.userId}] Group sync:`, err.message);
    }

    try {
        let pc = 0;
        Object.values(s.contactsStore).forEach(contact => {
            const jid = contact.id || '';
            if (!jid || !jid.endsWith('@s.whatsapp.net') || jid === s.waSock.user?.id) return;
            const name = contact.name || contact.notify || contact.pushName || ('+' + jid.replace('@s.whatsapp.net', ''));
            if (!s.allChatsMap.has(jid)) {
                s.allChatsMap.set(jid, { id: jid, name, isGroup: false, unreadCount: 0, lastMessage: 'Personal Contact', timestamp: '' });
                pc++;
            } else {
                const ex = s.allChatsMap.get(jid);
                if (name && (!ex.name || ex.name.startsWith('+'))) ex.name = name;
            }
        });
        console.log(`👤 [User:${s.userId}] Synced ${pc} personal contacts`);
    } catch (err) {
        console.error(`[User:${s.userId}] Contact sync:`, err.message);
    }

    saveStoreToDisk(s);
}

// Profile pic helpers
function scheduleProfilePicFetch(s) {
    if (s._picFetchTimer) return;
    s._picFetchTimer = setTimeout(() => {
        s._picFetchTimer = null;
        fetchProfilePics(s);
    }, 60000);
}

async function fetchProfilePics(s) {
    if (!s.waSock || s.connectionStatus !== 'connected') return;
    const jids = Array.from(s.allChatsMap.keys()).slice(0, 40);
    for (const jid of jids) {
        if (!s.profilePicCache.has(jid)) {
            try {
                const url = await s.waSock.profilePictureUrl(jid, 'image');
                s.profilePicCache.set(jid, url || null);
                if (s.allChatsMap.has(jid)) s.allChatsMap.get(jid).profilePic = url || null;
            } catch (e) {
                s.profilePicCache.set(jid, null);
            }
        }
    }
}

// ─── Scheduled Broadcast Cron ─────────────────────────────────────────────────

async function runScheduledBroadcasts() {
    const now = Date.now();
    for (const [, s] of sessions) {
        if (s.connectionStatus !== 'connected' || !s.waSock) continue;
        const due = s.scheduledBroadcasts.filter(b => b.scheduledAt <= now && b.status === 'pending');
        for (const b of due) {
            b.status = 'sending';
            console.log(`📅 [User:${s.userId}] Sending scheduled broadcast "${b.name}" to ${b.jids.length} recipients`);
            let sentCount = 0, failCount = 0;
            for (const jid of b.jids) {
                try {
                    if (b.imageBase64) {
                        const buf = Buffer.from(b.imageBase64.replace(/^data:image\/\w+;base64,/, ''), 'base64');
                        await s.waSock.sendMessage(jid, { image: buf, caption: b.message || '' });
                    } else {
                        await s.waSock.sendMessage(jid, { text: b.message || '' });
                    }
                    sentCount++;
                } catch (e) {
                    failCount++;
                }
                await new Promise(r => setTimeout(r, 800));
            }
            b.status = 'done';
            s.broadcastHistory.unshift({
                id: 'hist-' + Date.now() + '-' + Math.random().toString(36).slice(2),
                name: b.name,
                message: b.message,
                jids: b.jids,
                sentCount,
                failCount,
                scheduledAt: b.scheduledAt,
                timestamp: new Date().toLocaleString(),
                type: 'scheduled'
            });
            saveStoreToDisk(s, true);
        }
        // Clean up done scheduled broadcasts older than 7 days
        s.scheduledBroadcasts = s.scheduledBroadcasts.filter(b => b.status !== 'done' || (now - b.scheduledAt) < 7 * 86400000);
    }
}

setInterval(runScheduledBroadcasts, 60000); // check every minute

// ─── API Routes ───────────────────────────────────────────────────────────────

// Sessions list (for super admin)
app.get('/api/whatsapp/sessions', (req, res) => {
    const list = [];
    for (const [userId, s] of sessions) {
        list.push({
            userId,
            status: s.connectionStatus,
            user: s.userProfile,
            hasQR: !!s.currentQRDataURL,
            chatCount: s.allChatsMap.size
        });
    }
    res.json({ sessions: list });
});

app.get('/api/whatsapp/status', (req, res) => {
    const s = getSessionFromReq(req);
    res.json({ status: s.connectionStatus, user: s.userProfile, hasQR: !!s.currentQRDataURL, chatCount: s.allChatsMap.size });
});

app.get('/api/whatsapp/qr', (req, res) => {
    const s = getSessionFromReq(req);
    res.json({ status: s.connectionStatus, qrDataURL: s.currentQRDataURL });
});

app.get('/api/whatsapp/chats', (req, res) => {
    const s = getSessionFromReq(req);
    for (const jid of s.allChatsMap.keys()) {
        if (!isAllowedJid(jid)) s.allChatsMap.delete(jid);
    }
    const chats = Array.from(s.allChatsMap.values());
    chats.sort((a, b) => (b.lastMessageTimestamp || 0) - (a.lastMessageTimestamp || 0));
    chats.forEach(c => { if (s.profilePicCache.has(c.id)) c.profilePic = s.profilePicCache.get(c.id); });
    res.json({ status: s.connectionStatus, chats });
    if (s.connectionStatus === 'connected') scheduleProfilePicFetch(s);
});

app.get('/api/whatsapp/contacts', (req, res) => {
    const s = getSessionFromReq(req);
    const contacts = Object.values(s.contactsStore).filter(c => c.id && c.id.endsWith('@s.whatsapp.net'));
    res.json({ contacts });
});

app.get('/api/whatsapp/messages', (req, res) => {
    const s = getSessionFromReq(req);
    const jid = req.query.jid;
    if (!jid) return res.json({ messages: [] });
    res.json({ messages: s.messagesStore.get(jid) || [] });
});

app.get('/api/whatsapp/search', (req, res) => {
    const s = getSessionFromReq(req);
    const q = (req.query.q || '').toLowerCase().trim();
    if (!q || q.length < 2) return res.json({ results: [] });

    const results = [];
    for (const [jid, messages] of s.messagesStore.entries()) {
        const chat = s.chatsStore.get(jid);
        const chatName = chat?.name || jid.split('@')[0];
        for (const msg of messages) {
            if (msg.text && msg.text.toLowerCase().includes(q)) {
                results.push({
                    jid,
                    chatName,
                    messageId: msg.id,
                    text: msg.text,
                    fromMe: msg.fromMe,
                    senderName: msg.senderName,
                    timestamp: msg.timestamp,
                });
                if (results.length >= 50) break;
            }
        }
        if (results.length >= 50) break;
    }
    results.sort((a, b) => b.timestamp - a.timestamp);
    res.json({ results });
});

app.get('/api/whatsapp/profile-pic', async (req, res) => {
    const s = getSessionFromReq(req);
    const jid = req.query.jid;
    if (!jid) return res.json({ url: null });
    if (s.profilePicCache.has(jid)) return res.json({ url: s.profilePicCache.get(jid) });
    if (!s.waSock || s.connectionStatus !== 'connected') return res.json({ url: null });
    try {
        const url = await s.waSock.profilePictureUrl(jid, 'image');
        s.profilePicCache.set(jid, url || null);
        res.json({ url: url || null });
    } catch (e) {
        s.profilePicCache.set(jid, null);
        res.json({ url: null });
    }
});

app.post('/api/whatsapp/profile-pics', async (req, res) => {
    const s = getSessionFromReq(req);
    const { jids } = req.body;
    if (!Array.isArray(jids)) return res.json({ pics: {} });
    const pics = {};
    for (const jid of jids.slice(0, 50)) {
        if (s.profilePicCache.has(jid)) { pics[jid] = s.profilePicCache.get(jid); continue; }
        if (!s.waSock || s.connectionStatus !== 'connected') { pics[jid] = null; continue; }
        try {
            const url = await s.waSock.profilePictureUrl(jid, 'image');
            s.profilePicCache.set(jid, url || null);
            pics[jid] = url || null;
        } catch (e) {
            s.profilePicCache.set(jid, null);
            pics[jid] = null;
        }
    }
    res.json({ pics });
});

app.post('/api/whatsapp/send', async (req, res) => {
    const s = getSessionFromReq(req);
    let { targetJid, text, imageBase64 } = req.body;
    if (!targetJid) return res.status(400).json({ success: false, message: 'targetJid is required' });
    if (!s.waSock || s.connectionStatus !== 'connected') return res.status(400).json({ success: false, message: 'WhatsApp not connected' });

    const parts = targetJid.split('@');
    let number = parts[0].replace(/[^0-9]/g, '');
    const domain = parts[1] || 's.whatsapp.net';
    if (!number.startsWith('880') && number.startsWith('01')) number = '88' + number;
    targetJid = `${number}@${domain}`;

    for (let attempt = 1; attempt <= 2; attempt++) {
        try {
            let sendResult;
            if (imageBase64) {
                const buf = Buffer.from(imageBase64.replace(/^data:image\/\w+;base64,/, ''), 'base64');
                sendResult = await s.waSock.sendMessage(targetJid, { image: buf, caption: text || '' });
            } else {
                sendResult = await s.waSock.sendMessage(targetJid, { text: text || '' });
            }
            const timestamp = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            if (!s.messagesStore.has(targetJid)) s.messagesStore.set(targetJid, []);
            s.messagesStore.get(targetJid).push({ id: sendResult?.key?.id || ('msg-' + Date.now()), fromMe: true, senderName: 'You', text: text || '', imageBase64: imageBase64 || null, timestamp });
            const ec = s.allChatsMap.get(targetJid) || { id: targetJid, name: '+' + targetJid.replace('@s.whatsapp.net', ''), isGroup: targetJid.includes('@g.us'), unreadCount: 0 };
            ec.lastMessage = text || (imageBase64 ? '🖼️ Image' : '');
            ec.lastSender = 'You'; ec.timestamp = timestamp; ec.lastMessageTimestamp = Date.now();
            s.allChatsMap.set(targetJid, ec);
            saveStoreToDisk(s);
            return res.json({ success: true, message: 'Message sent', sendResult });
        } catch (err) {
            if (attempt === 2) return res.status(500).json({ success: false, message: err.message });
            await new Promise(r => setTimeout(r, 1500));
        }
    }
});

// Bulk broadcast (sends to many, records history)
app.post('/api/whatsapp/broadcast', async (req, res) => {
    const s = getSessionFromReq(req);
    const { name, jids, message, imageBase64 } = req.body;
    if (!Array.isArray(jids) || jids.length === 0) return res.status(400).json({ success: false, message: 'jids required' });
    if (!s.waSock || s.connectionStatus !== 'connected') return res.status(400).json({ success: false, message: 'WhatsApp not connected' });

    let sentCount = 0, failCount = 0;
    for (const rawJid of jids) {
        let jid = rawJid;
        const parts = jid.split('@');
        let number = parts[0].replace(/[^0-9]/g, '');
        const domain = parts[1] || 's.whatsapp.net';
        if (!number.startsWith('880') && number.startsWith('01')) number = '88' + number;
        jid = `${number}@${domain}`;
        try {
            if (imageBase64) {
                const buf = Buffer.from(imageBase64.replace(/^data:image\/\w+;base64,/, ''), 'base64');
                await s.waSock.sendMessage(jid, { image: buf, caption: message || '' });
            } else {
                await s.waSock.sendMessage(jid, { text: message || '' });
            }
            sentCount++;
        } catch (e) {
            failCount++;
        }
        await new Promise(r => setTimeout(r, 600));
    }

    const histEntry = {
        id: 'hist-' + Date.now(),
        name: name || 'Broadcast',
        message,
        jids,
        sentCount,
        failCount,
        timestamp: new Date().toLocaleString(),
        type: 'immediate'
    };
    s.broadcastHistory.unshift(histEntry);
    saveStoreToDisk(s, true);

    res.json({ success: true, sentCount, failCount, histEntry });
});

app.post('/api/whatsapp/add-contact', async (req, res) => {
    const s = getSessionFromReq(req);
    let { phone, name } = req.body;
    if (!phone) return res.status(400).json({ success: false, message: 'Phone required' });
    let cleaned = phone.replace(/[^0-9]/g, '');
    if (!cleaned.startsWith('880') && cleaned.startsWith('01')) cleaned = '88' + cleaned;
    const jid = cleaned + '@s.whatsapp.net';
    try {
        if (s.waSock) {
            try {
                const [result] = await s.waSock.onWhatsApp(cleaned);
                if (!result?.exists) return res.json({ success: false, message: 'Not registered on WhatsApp' });
            } catch (e) {}
        }
        const contactName = name || ('+' + cleaned);
        const newContact = { id: jid, name: contactName, isGroup: false, unreadCount: 0, lastMessage: 'Tap to start', timestamp: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) };
        s.contactsStore[jid] = { id: jid, name: contactName };
        s.allChatsMap.set(jid, newContact);
        saveStoreToDisk(s);
        res.json({ success: true, contact: newContact });
    } catch (err) {
        res.status(500).json({ success: false, message: err.message });
    }
});

// Saved Lists
app.get('/api/whatsapp/saved-lists', (req, res) => {
    const s = getSessionFromReq(req);
    const isAdmin = req.headers['x-wa-admin'] === '1';
    let publicLists = [];
    if (isAdmin) {
        // Collect public lists from all other sessions
        for (const [uid, sess] of sessions) {
            if (uid === s.userId) continue;
            for (const l of sess.savedListsStore) {
                if (l.isPublic) publicLists.push({ ...l, _ownerUserId: uid });
            }
        }
    }
    res.json({ lists: s.savedListsStore, publicLists });
});

app.post('/api/whatsapp/saved-lists', (req, res) => {
    const s = getSessionFromReq(req);
    const { name, jids } = req.body;
    if (!name || !Array.isArray(jids) || jids.length === 0) return res.status(400).json({ success: false, message: 'Name and jids required' });
    const newList = { id: 'list-' + Date.now(), name: name.trim(), jids, targetCount: jids.length, createdAt: new Date().toLocaleDateString(), isPublic: false };
    s.savedListsStore.unshift(newList);
    saveStoreToDisk(s, true);
    const isAdmin = req.headers['x-wa-admin'] === '1';
    let publicLists = [];
    if (isAdmin) {
        for (const [uid, sess] of sessions) {
            if (uid === s.userId) continue;
            for (const l of sess.savedListsStore) {
                if (l.isPublic) publicLists.push({ ...l, _ownerUserId: uid });
            }
        }
    }
    res.json({ success: true, list: newList, lists: s.savedListsStore, publicLists });
});

app.post('/api/whatsapp/saved-lists/:id/rename', (req, res) => {
    const s = getSessionFromReq(req);
    const list = s.savedListsStore.find(l => l.id === req.params.id);
    if (!list) return res.status(404).json({ success: false, message: 'Not found' });
    if (!req.body.name?.trim()) return res.status(400).json({ success: false, message: 'Name required' });
    list.name = req.body.name.trim();
    saveStoreToDisk(s, true);
    res.json({ success: true, lists: s.savedListsStore });
});

app.post('/api/whatsapp/saved-lists/:id/toggle-public', (req, res) => {
    const s = getSessionFromReq(req);
    const list = s.savedListsStore.find(l => l.id === req.params.id);
    if (!list) return res.status(404).json({ success: false, message: 'Not found' });
    list.isPublic = !list.isPublic;
    saveStoreToDisk(s, true);
    res.json({ success: true, isPublic: list.isPublic, lists: s.savedListsStore });
});

app.delete('/api/whatsapp/saved-lists', (req, res) => {
    const s = getSessionFromReq(req);
    if (!req.query.id) return res.status(400).json({ success: false, message: 'id required' });
    s.savedListsStore = s.savedListsStore.filter(l => l.id !== req.query.id);
    saveStoreToDisk(s, true);
    res.json({ success: true, lists: s.savedListsStore });
});

// Templates — global (admin, shared) + personal (per-user)
app.get('/api/whatsapp/templates', (req, res) => {
    const s = getSessionFromReq(req);
    res.json({ globalTemplates: globalTemplatesStore, personalTemplates: s.savedTemplatesStore });
});

app.post('/api/whatsapp/templates', (req, res) => {
    const s = getSessionFromReq(req);
    const { name, message, scope } = req.body;
    const isAdmin = req.headers['x-wa-admin'] === '1';
    if (!name || !message) return res.status(400).json({ success: false, message: 'Name and message required' });
    const tmpl = { id: 'tmpl-' + Date.now(), name: name.trim(), message: message.trim(), createdAt: new Date().toLocaleDateString() };
    if (scope === 'global' && isAdmin) {
        globalTemplatesStore.unshift(tmpl);
        saveGlobalTemplates();
    } else {
        s.savedTemplatesStore.unshift(tmpl);
        saveStoreToDisk(s, true);
    }
    res.json({ success: true, globalTemplates: globalTemplatesStore, personalTemplates: s.savedTemplatesStore });
});

app.post('/api/whatsapp/templates/:id/rename', (req, res) => {
    const s = getSessionFromReq(req);
    const isAdmin = req.headers['x-wa-admin'] === '1';
    let tmpl = isAdmin ? globalTemplatesStore.find(t => t.id === req.params.id) : null;
    const isGlobal = !!tmpl;
    if (!tmpl) tmpl = s.savedTemplatesStore.find(t => t.id === req.params.id);
    if (!tmpl) return res.status(404).json({ success: false });
    if (req.body.name) tmpl.name = req.body.name.trim();
    if (req.body.message !== undefined) tmpl.message = req.body.message;
    if (isGlobal) saveGlobalTemplates(); else saveStoreToDisk(s);
    res.json({ success: true, globalTemplates: globalTemplatesStore, personalTemplates: s.savedTemplatesStore });
});

app.delete('/api/whatsapp/templates', (req, res) => {
    const s = getSessionFromReq(req);
    const isAdmin = req.headers['x-wa-admin'] === '1';
    const id = req.query.id;
    const scope = req.query.scope;
    if (scope === 'global' && isAdmin) {
        globalTemplatesStore = globalTemplatesStore.filter(t => t.id !== id);
        saveGlobalTemplates();
    } else {
        s.savedTemplatesStore = s.savedTemplatesStore.filter(t => t.id !== id);
        saveStoreToDisk(s, true);
    }
    res.json({ success: true, globalTemplates: globalTemplatesStore, personalTemplates: s.savedTemplatesStore });
});

// Scheduled Broadcasts
app.get('/api/whatsapp/scheduled-broadcasts', (req, res) => {
    const s = getSessionFromReq(req);
    res.json({ scheduled: s.scheduledBroadcasts.filter(b => b.status === 'pending') });
});

app.post('/api/whatsapp/scheduled-broadcasts', (req, res) => {
    const s = getSessionFromReq(req);
    const { name, message, imageBase64, jids, scheduledAt } = req.body;
    if (!Array.isArray(jids) || jids.length === 0) return res.status(400).json({ success: false, message: 'jids required' });
    if (!scheduledAt) return res.status(400).json({ success: false, message: 'scheduledAt required' });
    const ts = new Date(scheduledAt).getTime();
    if (isNaN(ts) || ts <= Date.now()) return res.status(400).json({ success: false, message: 'scheduledAt must be in the future' });
    const entry = {
        id: 'sched-' + Date.now(),
        name: name || 'Scheduled Broadcast',
        message: message || '',
        imageBase64: imageBase64 || null,
        jids,
        scheduledAt: ts,
        createdAt: Date.now(),
        status: 'pending'
    };
    s.scheduledBroadcasts.push(entry);
    saveStoreToDisk(s, true);
    res.json({ success: true, entry });
});

app.delete('/api/whatsapp/scheduled-broadcasts', (req, res) => {
    const s = getSessionFromReq(req);
    const id = req.query.id;
    if (!id) return res.status(400).json({ success: false, message: 'id required' });
    s.scheduledBroadcasts = s.scheduledBroadcasts.filter(b => b.id !== id);
    saveStoreToDisk(s, true);
    res.json({ success: true });
});

// Broadcast History
app.get('/api/whatsapp/broadcast-history', (req, res) => {
    const s = getSessionFromReq(req);
    res.json({ history: s.broadcastHistory });
});

app.delete('/api/whatsapp/broadcast-history', (req, res) => {
    const s = getSessionFromReq(req);
    const id = req.query.id;
    if (!id) return res.status(400).json({ success: false });
    s.broadcastHistory = s.broadcastHistory.filter(h => h.id !== id);
    saveStoreToDisk(s, true);
    res.json({ success: true });
});

// Sync
app.post('/api/whatsapp/sync', async (req, res) => {
    const s = getSessionFromReq(req);
    await syncChatsAndGroups(s);
    res.json({ success: true, chatCount: s.allChatsMap.size });
});

async function logoutSession(s) {
    s.connectionStatus = 'disconnected';
    s.currentQRDataURL = null;
    s.contactsStore = {};
    s.allChatsMap.clear();
    s.messagesStore.clear();
    s.profilePicCache.clear();
    s._connecting = false;

    if (s.waSock) {
        try { s.waSock.logout(); } catch (e) {}
        try { s.waSock.end(); } catch (e) {}
        s.waSock = null;
    }

    if (fs.existsSync(s.authFolder)) fs.rmSync(s.authFolder, { recursive: true, force: true });
    if (fs.existsSync(s.storeFilePath)) fs.unlinkSync(s.storeFilePath);

    // Reset lists/templates so they don't carry over
    s.savedListsStore = [];
    s.savedTemplatesStore = [];
    s.scheduledBroadcasts = [];
    s.broadcastHistory = [];

    setTimeout(() => connectToWhatsApp(s), 1000);
}

// Logout current user's session
app.post('/api/whatsapp/logout', async (req, res) => {
    const s = getSessionFromReq(req);
    try {
        await logoutSession(s);
        res.json({ success: true, message: 'Logged out. Generating new QR...' });
    } catch (err) {
        res.status(500).json({ success: false, message: err.message });
    }
});

// Super admin: logout a specific user's session by userId
app.post('/api/whatsapp/sessions/:userId/logout', async (req, res) => {
    const { userId } = req.params;
    const s = sessions.get(userId);
    if (!s) return res.status(404).json({ success: false, message: 'Session not found' });
    try {
        await logoutSession(s);
        res.json({ success: true, message: `Session for user ${userId} logged out.` });
    } catch (err) {
        res.status(500).json({ success: false, message: err.message });
    }
});

app.listen(PORT, () => {
    console.log(`🚀 WhatsApp Multi-Account Server running on http://localhost:${PORT}`);
    // Sessions are created on first API call; no auto-start here
});
