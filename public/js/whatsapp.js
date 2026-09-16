document.addEventListener('DOMContentLoaded', () => {
    const relativePath = window.location.pathname.substring(0, window.location.pathname.indexOf('/whatsapp'));
const API_BASE = window.location.origin + (relativePath || '') + '/api/whatsapp';
    let isConnected = false;
    let allChats = [];
    let activeChatJid = null;
    let activeFilter = 'all';
    let bulkSelectedGroups = new Set();
    let attachedChatImageBase64 = null;
    let attachedBulkImageBase64 = null;
    let isSending = false;

    // DOM Elements
    const chatsListContainer = document.getElementById('chats-list-container');
    const chatSearchInput = document.getElementById('chat-search');

    const activeChatAvatar = document.getElementById('active-chat-avatar');
    const activeChatTitle = document.getElementById('active-chat-title');
    const activeChatSubtext = document.getElementById('active-chat-subtext');
    const conversationArea = document.getElementById('conversation-area');

    const formChatSend = document.getElementById('form-chat-send');
    const chatMessageInput = document.getElementById('chat-message-input');
    const btnAttachClip = document.getElementById('btn-attach-clip');
    const chatFileInput = document.getElementById('chat-file-input');

    const accountStatusBadge = document.getElementById('account-status-badge');
    const accountNameDisplay = document.getElementById('account-name-display');
    const qrModal = document.getElementById('qr-modal');
    const btnWaAuth = document.getElementById('btn-wa-auth');
    const btnCloseQr = document.getElementById('btn-close-qr');

    // New Chat Modal Elements
    const btnOpenNewChat = document.getElementById('btn-open-new-chat');
    const newChatModal = document.getElementById('new-chat-modal');
    const btnCloseNewChat = document.getElementById('btn-close-new-chat');
    const formAddContact = document.getElementById('form-add-contact');
    const newContactPhone = document.getElementById('new-contact-phone');
    const newContactName = document.getElementById('new-contact-name');

    // Bulk Drawer Elements
    const btnToggleBulk = document.getElementById('btn-toggle-bulk');
    const bulkDrawer = document.getElementById('bulk-drawer');
    const btnCloseBulk = document.getElementById('btn-close-bulk');
    const bulkGroupList = document.getElementById('bulk-group-list');
    const bulkSelectedCount = document.getElementById('bulk-selected-count');
    const btnBulkSelectAll = document.getElementById('btn-bulk-select-all');
    const btnBulkDeselectAll = document.getElementById('btn-bulk-deselect-all');
    const formBulkMsg = document.getElementById('form-bulk-msg');
    const bulkImageInput = document.getElementById('bulk-image-input');
    const btnStartBulk = document.getElementById('btn-start-bulk');
    const bulkProgressSection = document.getElementById('bulk-progress-section');
    const bulkProgressBarInner = document.getElementById('bulk-progress-bar-inner');
    const bulkProgressText = document.getElementById('bulk-progress-text');
    const bulkConsoleLog = document.getElementById('bulk-console-log');

    // ------------------------------------------------------------------
    // Live Server Polling & Status
    // ------------------------------------------------------------------

    async function pollWhatsAppStatus() {
        try {
            const res = await fetch(`${API_BASE}/status`);
            const data = await res.json();

            const dot = document.getElementById('account-online-dot');
            if (data.status === 'connected') {
                if (!isConnected) {
                    isConnected = true;
                    if (accountStatusBadge) {
                        accountStatusBadge.style.color = '#00a884';
                        accountStatusBadge.style.background = 'rgba(0,168,132,0.1)';
                        accountStatusBadge.style.borderColor = 'rgba(0,168,132,0.25)';
                        accountStatusBadge.innerHTML = '● Connected';
                    }
                    if (dot) { dot.style.background = '#00a884'; }
                    if (accountNameDisplay) {
                        accountNameDisplay.textContent = `${data.user.name}`;
                    }
                    if (data.user.profilePic) {
                        const avatarEl = document.getElementById('my-profile-avatar');
                        if (avatarEl) {
                            avatarEl.innerHTML = `<img src="${data.user.profilePic}" style="width:100%;height:100%;border-radius:50%;object-fit:cover;" onerror="this.parentElement.innerHTML='👤'">`;
                        }
                    }
                    if (qrModal) {
                        qrModal.classList.add('hidden');
                    }
                    if (btnWaAuth) {
                        btnWaAuth.textContent = 'Logout';
                        btnWaAuth.style.color = '#f87171';
                        btnWaAuth.style.borderColor = 'rgba(248,113,113,0.4)';
                    }
                }
                fetchChats();
                if (activeChatJid) {
                    fetchConversationMessages();
                }
            } else if (data.status === 'qr_ready') {
                fetchLiveQR();
                if (dot) { dot.style.background = '#facc15'; }
                if (accountStatusBadge) {
                    accountStatusBadge.style.color = '#facc15';
                    accountStatusBadge.style.background = 'rgba(250,204,21,0.1)';
                    accountStatusBadge.style.borderColor = 'rgba(250,204,21,0.25)';
                    accountStatusBadge.innerHTML = '● Scan QR';
                }
            } else {
                if (isConnected) { isConnected = false; }
                if (dot) { dot.style.background = '#f87171'; }
                if (accountStatusBadge) {
                    accountStatusBadge.style.color = '#f87171';
                    accountStatusBadge.style.background = 'rgba(248,113,113,0.1)';
                    accountStatusBadge.style.borderColor = 'rgba(248,113,113,0.2)';
                    accountStatusBadge.innerHTML = '● Disconnected';
                }
                if (btnWaAuth) {
                    btnWaAuth.textContent = 'Login';
                    btnWaAuth.style.color = '#00a884';
                    btnWaAuth.style.borderColor = 'rgba(0,168,132,0.4)';
                }
            }
        } catch (err) {}
    }

    async function fetchLiveQR() {
        try {
            const res = await fetch(`${API_BASE}/qr`);
            const data = await res.json();
            if (data.qrDataURL) {
                const qrImgElement = document.querySelector('#qr-modal img');
                if (qrImgElement) {
                    qrImgElement.src = data.qrDataURL;
                }
            }
        } catch (err) {}
    }

    let _fetchChatsTimer = null;
    let _fetchChatsInFlight = false;
    async function fetchChats() {
        if (_fetchChatsInFlight) return;
        if (_fetchChatsTimer) return;
        _fetchChatsTimer = setTimeout(() => { _fetchChatsTimer = null; }, 3000);
        _fetchChatsInFlight = true;
        try {
            const res = await fetch(`${API_BASE}/chats`);
            const data = await res.json();
            if (data.chats) {
                allChats = data.chats;
                renderChatList();
            }
        } catch (err) {}
        _fetchChatsInFlight = false;
    }

    setInterval(pollWhatsAppStatus, 5000);
    pollWhatsAppStatus();

    // ------------------------------------------------------------------
    // New 1-on-1 Contact Handlers
    // ------------------------------------------------------------------

    if (btnOpenNewChat && newChatModal) {
        btnOpenNewChat.addEventListener('click', (e) => {
            e.preventDefault();
            newChatModal.classList.remove('hidden');
        });
    }

    if (btnCloseNewChat && newChatModal) {
        btnCloseNewChat.addEventListener('click', (e) => {
            e.preventDefault();
            newChatModal.classList.add('hidden');
        });
    }

    if (formAddContact) {
        formAddContact.addEventListener('submit', async (e) => {
            e.preventDefault();
            const phone = newContactPhone.value.trim();
            const name = newContactName.value.trim();
            if (!phone) return;
            try {
                const res = await fetch(`${API_BASE}/add-contact`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ phone, name })
                });
                const data = await res.json();
                if (data.success && data.contact) {
                    newChatModal.classList.add('hidden');
                    newContactPhone.value = '';
                    newContactName.value = '';
                    const existingIdx = allChats.findIndex(c => c.id === data.contact.id);
                    if (existingIdx >= 0) allChats[existingIdx] = data.contact;
                    else allChats.unshift(data.contact);
                    renderChatList();
                    selectChat(data.contact);
                } else {
                    alert(data.message || 'Could not add contact.');
                }
            } catch (err) {
                alert('Error adding contact: ' + err.message);
            }
        });
    }

    // ------------------------------------------------------------------
    // Sidebar Chat List Rendering & Filtering
    // ------------------------------------------------------------------

    const CHAT_PAGE_SIZE = 50;
    let chatRenderOffset = 0;
    let currentFilteredChats = [];

    function buildChatItem(chat) {
        const isActive = chat.id === activeChatJid;
        const item = document.createElement('div');
        item.className = 'wa-chat-item' + (isActive ? ' active' : '');
        item.dataset.jid = chat.id;
        item.title = chat.name || chat.id;

        const badgeType = chat.isGroup
            ? '<span style="font-size:9px;padding:2px 6px;background:rgba(0,168,132,0.15);color:#00a884;border-radius:4px;border:1px solid rgba(0,168,132,0.3);">Group</span>'
            : '<span style="font-size:9px;padding:2px 6px;background:rgba(100,116,139,0.15);color:#64748b;border-radius:4px;border:1px solid rgba(100,116,139,0.3);">Contact</span>';

        const avatarBg = chat.isGroup ? '#4f46e5' : '#64748b';
        const avatarIcon = chat.isGroup ? '👥' : '👤';
        const avatarContent = chat.profilePic
            ? `<img src="${escapeHtml(chat.profilePic)}" style="width:100%;height:100%;border-radius:50%;object-fit:cover;" onerror="this.style.display='none'">`
            : avatarIcon;

        item.innerHTML = `
            <div style="width:42px;height:42px;border-radius:50%;background:${avatarBg};color:white;display:flex;align-items:center;justify-content:center;font-weight:bold;font-size:14px;flex-shrink:0;overflow:hidden;">${avatarContent}</div>
            <div style="flex:1;min-width:0;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:3px;">
                    <h4 class="wa-chat-title-name" style="font-size:13px;font-weight:700;margin:0;display:flex;align-items:center;gap:5px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                        ${escapeHtml(chat.name||chat.id)} ${badgeType}
                    </h4>
                    <span class="wa-chat-timestamp" style="font-size:10px;flex-shrink:0;">${escapeHtml(chat.timestamp||'')}</span>
                </div>
                <div style="display:flex;align-items:center;justify-content:space-between;">
                    <p class="wa-chat-last-msg" style="font-size:11px;margin:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${escapeHtml(chat.lastMessage||(chat.isGroup?`${chat.participants||0} members`:'Click to chat'))}</p>
                    ${chat.unreadCount>0?`<span style="min-width:16px;height:16px;padding:0 4px;border-radius:8px;background:#10b981;color:white;font-weight:800;font-size:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">${chat.unreadCount}</span>`:''}
                </div>
            </div>`;

        item.addEventListener('click', () => selectChat(chat));
        return item;
    }

    function renderMoreChats() {
        const slice = currentFilteredChats.slice(chatRenderOffset, chatRenderOffset + CHAT_PAGE_SIZE);
        const frag = document.createDocumentFragment();
        slice.forEach(chat => frag.appendChild(buildChatItem(chat)));
        const old = chatsListContainer.querySelector('#chat-load-sentinel');
        if (old) old.remove();
        chatsListContainer.appendChild(frag);
        chatRenderOffset += slice.length;

        if (chatRenderOffset < currentFilteredChats.length) {
            const sentinel = document.createElement('div');
            sentinel.id = 'chat-load-sentinel';
            sentinel.style.cssText = 'height:1px;';
            chatsListContainer.appendChild(sentinel);
            chatScrollObserver.observe(sentinel);
        }
    }

    const chatScrollObserver = new IntersectionObserver((entries) => {
        if (entries[0].isIntersecting) {
            chatScrollObserver.disconnect();
            renderMoreChats();
        }
    }, { root: chatsListContainer, threshold: 0.1 });

    function renderChatList() {
        if (!chatsListContainer) return;
        chatScrollObserver.disconnect();
        chatsListContainer.innerHTML = '';
        chatRenderOffset = 0;

        const searchQuery = chatSearchInput ? chatSearchInput.value.toLowerCase() : '';

        currentFilteredChats = allChats.filter(chat => {
            const name = (chat.name || chat.id || '').toLowerCase();
            if (!name.includes(searchQuery)) return false;
            if (activeFilter === 'unread') return chat.unreadCount > 0;
            if (activeFilter === 'groups') return chat.isGroup;
            if (activeFilter === 'personal') return !chat.isGroup;
            return true;
        });

        currentFilteredChats.sort((a, b) => (b.lastMessageTimestamp||0) - (a.lastMessageTimestamp||0));

        if (currentFilteredChats.length === 0) {
            chatsListContainer.innerHTML = `
                <div style="padding:24px;text-align:center;color:#8696a0;font-size:13px;">
                    No chats found.<br>
                    <button type="button" id="btn-empty-add-contact" style="margin-top:8px;color:#00a884;font-weight:bold;background:none;border:none;text-decoration:underline;cursor:pointer;">
                        + Add Personal Contact
                    </button>
                </div>`;
            const emptyBtn = document.getElementById('btn-empty-add-contact');
            if (emptyBtn && newChatModal) emptyBtn.addEventListener('click', () => newChatModal.classList.remove('hidden'));
            return;
        }

        renderMoreChats();
    }

    document.querySelectorAll('.wa-filter-btn, .chat-filter-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            document.querySelectorAll('.wa-filter-btn, .chat-filter-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            activeFilter = btn.getAttribute('data-filter') || 'all';
            renderChatList();
        });
    });

    if (chatSearchInput) {
        chatSearchInput.addEventListener('input', renderChatList);
    }

    // ------------------------------------------------------------------
    // WhatsApp Message Search
    // ------------------------------------------------------------------
    const btnMsgSearchToggle = document.getElementById('btn-msg-search-toggle');
    const msgSearchPanel     = document.getElementById('msg-search-panel');
    const msgSearchInput     = document.getElementById('msg-search-input');
    const msgSearchResults   = document.getElementById('msg-search-results');
    const btnMsgSearchClear  = document.getElementById('btn-msg-search-clear');

    if (btnMsgSearchToggle && msgSearchPanel) {
        btnMsgSearchToggle.addEventListener('click', () => {
            const open = msgSearchPanel.style.display !== 'none';
            msgSearchPanel.style.display = open ? 'none' : 'block';
            btnMsgSearchToggle.classList.toggle('active', !open);
            if (!open) {
                btnMsgSearchToggle.style.background = 'color-mix(in srgb,var(--cp) 10%,transparent)';
                btnMsgSearchToggle.style.color = 'var(--cp)';
                btnMsgSearchToggle.style.borderColor = 'var(--cp)';
                msgSearchInput?.focus();
            } else {
                btnMsgSearchToggle.style.background = 'transparent';
                btnMsgSearchToggle.style.color = '#8696a0';
                btnMsgSearchToggle.style.borderColor = '#2a3942';
                if (msgSearchResults) msgSearchResults.innerHTML = '';
                if (msgSearchInput) msgSearchInput.value = '';
                if (btnMsgSearchClear) btnMsgSearchClear.style.display = 'none';
            }
        });
    }

    let msgSearchTimer = null;
    if (msgSearchInput) {
        msgSearchInput.addEventListener('input', () => {
            const q = msgSearchInput.value.trim();
            if (btnMsgSearchClear) btnMsgSearchClear.style.display = q ? 'block' : 'none';
            clearTimeout(msgSearchTimer);
            if (!q || q.length < 2) {
                if (msgSearchResults) msgSearchResults.innerHTML = '';
                return;
            }
            if (msgSearchResults) msgSearchResults.innerHTML = '<div style="text-align:center;padding:12px;color:#8696a0;font-size:11px;">Searching...</div>';
            msgSearchTimer = setTimeout(() => doMsgSearch(q), 400);
        });
    }

    if (btnMsgSearchClear) {
        btnMsgSearchClear.addEventListener('click', () => {
            if (msgSearchInput) msgSearchInput.value = '';
            btnMsgSearchClear.style.display = 'none';
            if (msgSearchResults) msgSearchResults.innerHTML = '';
        });
    }

    async function doMsgSearch(q) {
        try {
            const res = await fetch(`${API_BASE}/search?q=${encodeURIComponent(q)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();
            renderMsgSearchResults(data.results || [], q);
        } catch {
            if (msgSearchResults) msgSearchResults.innerHTML = '<div style="text-align:center;padding:12px;color:#8696a0;font-size:11px;">Search failed.</div>';
        }
    }

    function renderMsgSearchResults(results, q) {
        if (!msgSearchResults) return;
        if (!results.length) {
            msgSearchResults.innerHTML = '<div style="text-align:center;padding:16px;color:#8696a0;font-size:11px;">No messages found.</div>';
            return;
        }
        const esc = s => s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
        const highlight = text => {
            const safe = esc(text);
            const re = new RegExp(`(${q.replace(/[.*+?^${}()|[\]\\]/g,'\\$&')})`, 'gi');
            return safe.replace(re, '<mark style="background:color-mix(in srgb,var(--cp) 30%,#fff);color:inherit;border-radius:2px;padding:0 1px;">$1</mark>');
        };
        msgSearchResults.innerHTML = results.map(r => `
            <div onclick="selectChatByJid('${esc(r.jid)}')" style="padding:8px 10px;border-radius:9px;background:#111b21;border:1px solid #1e3040;cursor:pointer;transition:all 0.15s;"
                onmouseover="this.style.borderColor='var(--cp)';this.style.background='color-mix(in srgb,var(--cp) 8%,#111b21)'"
                onmouseout="this.style.borderColor='#1e3040';this.style.background='#111b21'">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:3px;">
                    <span style="font-size:11px;font-weight:700;color:var(--cp);">${esc(r.chatName)}</span>
                    <span style="font-size:9.5px;color:#8696a0;">${new Date(r.timestamp*1000).toLocaleString()}</span>
                </div>
                <div style="font-size:11.5px;color:#e9edef;line-height:1.4;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;">${highlight(r.text)}</div>
                <div style="font-size:10px;color:#8696a0;margin-top:2px;">${r.fromMe ? '✓ You' : esc(r.senderName || '')}</div>
            </div>
        `).join('');
    }

    window.selectChatByJid = function selectChatByJid(jid) {
        const chat = allChats.find(c => c.id === jid);
        if (chat) {
            selectChat(chat);
            if (msgSearchPanel) {
                msgSearchPanel.style.display = 'none';
                if (btnMsgSearchToggle) {
                    btnMsgSearchToggle.classList.remove('active');
                    btnMsgSearchToggle.style.background = 'transparent';
                    btnMsgSearchToggle.style.color = '#8696a0';
                    btnMsgSearchToggle.style.borderColor = '#2a3942';
                }
            }
        }
    }

    // ------------------------------------------------------------------
    // Active Conversation Area & Message History
    // ------------------------------------------------------------------

    function selectChat(chat) {
        activeChatJid = chat.id;
        chat.unreadCount = 0;
        renderChatList();
        activeChatTitle.textContent = chat.name;
        activeChatSubtext.textContent = chat.isGroup ? `Group Chat • ${chat.participants || 0} members` : 'Personal Contact • 1-on-1 Chat';
        activeChatAvatar.innerHTML = chat.isGroup ? '👥' : '👤';
        activeChatAvatar.style.backgroundColor = chat.isGroup ? '#005c4b' : '#374248';
        fetchConversationMessages();
    }

    async function fetchConversationMessages() {
        if (!activeChatJid || !conversationArea) return;
        try {
            const res = await fetch(`${API_BASE}/messages?jid=${encodeURIComponent(activeChatJid)}`);
            const data = await res.json();
            renderConversation(data.messages || []);
        } catch (err) {}
    }

    function renderConversation(messages) {
        conversationArea.innerHTML = `
            <div style="text-align: center; margin: 24px 0;">
                <span style="padding: 4px 12px; background-color: #182229; font-size: 11px; color: #facc15; border-radius: 8px; border: 1px solid #222d34;">
                    🔒 End-to-end encrypted • Messages & calls are secured
                </span>
            </div>
        `;

        messages.forEach(msg => {
            const bubble = document.createElement('div');
            const isOutgoing = msg.fromMe;
            bubble.style.display = 'flex';
            bubble.style.justifyContent = isOutgoing ? 'flex-end' : 'flex-start';
            bubble.style.marginBottom = '8px';
            bubble.innerHTML = `
                <div class="${isOutgoing ? 'msg-bubble-outgoing' : 'msg-bubble-incoming'}" style="max-width: 70%; border-radius: 14px; padding: 8px 14px; box-shadow: 0 1px 2px rgba(0,0,0,0.1);">
                    ${!isOutgoing ? `<p style="font-size: 10px; font-weight: 700; color: #00a884; margin: 0 0 2px 0;">${escapeHtml(msg.senderName || 'Contact')}</p>` : ''}
                    ${msg.imageBase64 ? `<img src="${msg.imageBase64}" style="max-width: 260px; width: 100%; border-radius: 10px; margin-bottom: 6px; border: 1px solid rgba(0,0,0,0.1);" onerror="this.style.display='none'">` : (msg.hasImage ? `<div style="max-width:260px;background:rgba(0,0,0,0.05);border-radius:10px;padding:20px;text-align:center;margin-bottom:6px;font-size:12px;">🖼️ Photo</div>` : '')}
                    <p style="font-size: 12px; line-height: 1.5; white-space: pre-wrap; margin: 0;">${escapeHtml(msg.text || '')}</p>
                    <div class="msg-time" style="display: flex; align-items: center; justify-content: flex-end; gap: 4px; margin-top: 4px; font-size: 9px;">
                        <span>${escapeHtml(msg.timestamp || '')}</span>
                        ${isOutgoing ? '<span style="color: #10b981; font-weight: bold;">✓✓</span>' : ''}
                    </div>
                </div>
            `;
            conversationArea.appendChild(bubble);
        });

        conversationArea.scrollTop = conversationArea.scrollHeight;
    }

    if (formChatSend) {
        formChatSend.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (!activeChatJid) { alert('Please select a chat first.'); return; }
            const text = chatMessageInput.value.trim();
            if (!text && !attachedChatImageBase64) return;

            const sendPayload = { targetJid: activeChatJid, text, imageBase64: attachedChatImageBase64 };
            chatMessageInput.value = '';
            attachedChatImageBase64 = null;
            if (chatFileInput) chatFileInput.value = '';
            if (chatImagePreviewBar) chatImagePreviewBar.style.display = 'none';

            const targetChat = allChats.find(c => c.id === activeChatJid);
            if (targetChat) {
                targetChat.lastMessage = text || '🖼️ Image';
                targetChat.timestamp = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                targetChat.lastMessageTimestamp = Date.now();
                renderChatList();
            }

            try {
                await fetch(`${API_BASE}/send`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(sendPayload)
                });
                fetchConversationMessages();
            } catch (err) {
                alert('Failed to send message: ' + err.message);
            }
        });
    }

    const chatImagePreviewBar = document.getElementById('chat-image-preview-bar');
    const chatPreviewImg = document.getElementById('chat-preview-img');
    const chatPreviewFilename = document.getElementById('chat-preview-filename');
    const btnRemoveAttachment = document.getElementById('btn-remove-attachment');
    const btnEmojiPicker = document.getElementById('btn-emoji-picker');

    if (btnAttachClip && chatFileInput) {
        btnAttachClip.addEventListener('click', (e) => {
            e.preventDefault();
            chatFileInput.click();
        });
        chatFileInput.addEventListener('change', (e) => {
            if (e.target.files && e.target.files[0]) {
                const file = e.target.files[0];
                const reader = new FileReader();
                reader.onload = (ev) => {
                    attachedChatImageBase64 = ev.target.result;
                    if (chatPreviewImg) chatPreviewImg.src = ev.target.result;
                    if (chatPreviewFilename) chatPreviewFilename.textContent = file.name || 'Photo Attached';
                    if (chatImagePreviewBar) chatImagePreviewBar.style.display = 'flex';
                };
                reader.readAsDataURL(file);
            }
        });
    }

    if (btnRemoveAttachment) {
        btnRemoveAttachment.addEventListener('click', () => {
            attachedChatImageBase64 = null;
            if (chatFileInput) chatFileInput.value = '';
            if (chatImagePreviewBar) chatImagePreviewBar.style.display = 'none';
        });
    }

    if (btnEmojiPicker && chatMessageInput) {
        const emojis = ['😊', '👍', '❤️', '🔥', '🎉', '🙏', '💯', '👌'];
        btnEmojiPicker.addEventListener('click', (e) => {
            e.preventDefault();
            chatMessageInput.value += emojis[Math.floor(Math.random() * emojis.length)];
            chatMessageInput.focus();
        });
    }

    // ------------------------------------------------------------------
    // ------------------------------------------------------------------
    // Main View Tab Controls (💬 Chat vs 📢 Bulk)
    // ------------------------------------------------------------------

    function isDarkMode() {
        return document.documentElement.classList.contains('dark');
    }

    const btnTabChat = document.getElementById('btn-tab-chat');
    const btnTabBulk = document.getElementById('btn-tab-bulk');
    const btnBackToChat = document.getElementById('btn-back-to-chat');
    const viewChatContainer = document.getElementById('view-chat-container');
    const viewBulkContainer = document.getElementById('view-bulk-container');

    function switchToTab(tabName) {
        const isDark = isDarkMode();
        if (tabName === 'bulk') {
            if (viewChatContainer) viewChatContainer.style.display = 'none';
            if (viewBulkContainer) viewBulkContainer.style.display = 'flex';

            if (btnTabChat) {
                btnTabChat.style.background = isDark ? '#111b21' : '#ffffff';
                btnTabChat.style.color = isDark ? '#8696a0' : '#4b5563';
                btnTabChat.style.border = isDark ? '1px solid #374248' : '1px solid #cbd5e1';
                btnTabChat.style.boxShadow = 'none';
            }
            if (btnTabBulk) {
                btnTabBulk.style.background = 'linear-gradient(135deg, #4f46e5, #4338ca)';
                btnTabBulk.style.color = '#ffffff';
                btnTabBulk.style.border = '1px solid #6366f1';
                btnTabBulk.style.boxShadow = '0 2px 6px rgba(79,70,229,0.3)';
            }

            fetchTemplates();
            renderBulkGroupList();
            fetchSavedLists().then(() => renderQuickSelectLists());
        } else {
            if (viewBulkContainer) viewBulkContainer.style.display = 'none';
            if (viewChatContainer) viewChatContainer.style.display = 'flex';

            if (btnTabChat) {
                btnTabChat.style.background = 'linear-gradient(135deg, #4f46e5, #4338ca)';
                btnTabChat.style.color = '#ffffff';
                btnTabChat.style.border = '1px solid #6366f1';
                btnTabChat.style.boxShadow = '0 2px 6px rgba(79,70,229,0.3)';
            }
            if (btnTabBulk) {
                btnTabBulk.style.background = isDark ? '#111b21' : '#ffffff';
                btnTabBulk.style.color = isDark ? '#8696a0' : '#4b5563';
                btnTabBulk.style.border = isDark ? '1px solid #374248' : '1px solid #cbd5e1';
                btnTabBulk.style.boxShadow = 'none';
            }
        }
    }

    if (btnTabChat) {
        btnTabChat.addEventListener('click', (e) => {
            e.preventDefault();
            switchToTab('chat');
        });
    }

    if (btnTabBulk) {
        btnTabBulk.addEventListener('click', (e) => {
            e.preventDefault();
            switchToTab('bulk');
        });
    }

    if (btnToggleBulk) {
        btnToggleBulk.addEventListener('click', (e) => {
            e.preventDefault();
            switchToTab('bulk');
        });
    }

    if (btnBackToChat) {
        btnBackToChat.addEventListener('click', (e) => {
            e.preventDefault();
            switchToTab('chat');
        });
    }

    let savedLists = [];
    let publicLists = [];
    let failedTargets = [];
    let bulkActiveFilter = 'all';

    function renderBulkGroupList(searchQuery = '') {
        if (!bulkGroupList) return;
        bulkGroupList.innerHTML = '';
        const isDark = isDarkMode();

        // Collect existing JIDs in allChats
        const existingJids = new Set((allChats || []).map(c => c.id));
        const extraChats = [];

        // Build fallback chat objects for any selected JIDs missing in allChats
        bulkSelectedGroups.forEach(jid => {
            if (!existingJids.has(jid)) {
                const cleanName = jid.replace('@s.whatsapp.net', '').replace('@g.us', '');
                const isGroup = jid.endsWith('@g.us');
                extraChats.push({ id: jid, name: cleanName, isGroup: isGroup });
                existingJids.add(jid);
            }
        });

        let targets = [...(allChats || []), ...extraChats];

        if (bulkActiveFilter === 'groups') targets = targets.filter(c => c.isGroup);
        else if (bulkActiveFilter === 'personal') targets = targets.filter(c => !c.isGroup);
        if (searchQuery && searchQuery.trim()) {
            const q = searchQuery.toLowerCase().trim();
            targets = targets.filter(c => (c.name || c.id).toLowerCase().includes(q));
        }

        // Sort: Selected items (isChecked === true) MUST appear at the top!
        targets.sort((a, b) => {
            const aSel = bulkSelectedGroups.has(a.id);
            const bSel = bulkSelectedGroups.has(b.id);
            if (aSel && !bSel) return -1;
            if (!aSel && bSel) return 1;
            return (a.name || a.id).localeCompare(b.name || b.id);
        });

        if (targets.length === 0) {
            bulkGroupList.innerHTML = `<p style="font-size:12px;color:${isDark?'#8696a0':'#64748b'};text-align:center;padding:20px 0;">No contacts found</p>`;
            if (bulkSelectedCount) bulkSelectedCount.textContent = bulkSelectedGroups.size + ' Selected';
            return;
        }

        targets.forEach(g => {
            const isChecked = bulkSelectedGroups.has(g.id);
            const initials = (g.name || g.id).trim().split(/\s+/).map(w=>w[0]).join('').substring(0,2).toUpperCase();
            const colors = ['#005c4b','#1d4ed8','#7c3aed','#b45309','#0f766e','#9f1239'];
            const color = colors[(g.name||g.id).charCodeAt(0) % colors.length];

            const itemBg = isChecked ? (isDark ? 'rgba(0,168,132,0.15)' : 'rgba(79,70,229,0.1)') : (isDark ? '#111b21' : '#ffffff');
            const itemBorder = isChecked ? (isDark ? 'rgba(0,168,132,0.4)' : 'rgba(79,70,229,0.4)') : (isDark ? '#1e3040' : '#e2e8f0');
            const textColor = isDark ? '#e9edef' : '#0f172a';
            const checkBg = isChecked ? (isDark ? '#00a884' : '#4f46e5') : 'transparent';
            const checkBorder = isChecked ? (isDark ? '#00a884' : '#4f46e5') : (isDark ? '#2a3942' : '#cbd5e1');

            const item = document.createElement('label');
            item.style.cssText = `display:flex;align-items:center;gap:10px;padding:8px 10px;border-radius:10px;cursor:pointer;border:1px solid ${itemBorder};background:${itemBg};transition:all 0.15s;`;

            item.innerHTML = `
                <input type="checkbox" class="bulk-group-checkbox" value="${escapeHtml(g.id)}" ${isChecked ? 'checked' : ''} style="display:none;">
                <div style="width:32px;height:32px;border-radius:50%;background:${color};display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:white;flex-shrink:0;">${initials}</div>
                <span style="font-size:12px;color:${textColor};overflow:hidden;text-overflow:ellipsis;white-space:nowrap;flex:1;font-weight:500;">${escapeHtml(g.name||g.id)}</span>
                <span style="font-size:10px;font-weight:600;padding:2px 7px;border-radius:10px;flex-shrink:0;${g.isGroup ? 'color:#818cf8;background:rgba(129,140,248,0.12);border:1px solid rgba(129,140,248,0.2);' : (isDark ? 'color:#8696a0;background:rgba(134,150,160,0.1);border:1px solid rgba(134,150,160,0.2);' : 'color:#64748b;background:rgba(100,116,139,0.1);border:1px solid rgba(100,116,139,0.2);')}">${g.isGroup ? '👥' : '👤'}</span>
                <div style="width:18px;height:18px;border-radius:5px;border:2px solid ${checkBorder};background:${checkBg};display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:all 0.15s;">
                    ${isChecked ? '<svg style="width:11px;height:11px;" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>' : ''}
                </div>
            `;

            const cb = item.querySelector('.bulk-group-checkbox');
            item.addEventListener('click', () => {
                cb.checked = !cb.checked;
                if (cb.checked) {
                    bulkSelectedGroups.add(g.id);
                } else {
                    bulkSelectedGroups.delete(g.id);
                }
                renderBulkGroupList(document.getElementById('bulk-recipient-search')?.value || '');
                renderQuickSelectLists();
            });

            bulkGroupList.appendChild(item);
        });

        if (bulkSelectedCount) bulkSelectedCount.textContent = bulkSelectedGroups.size + ' Selected';
    }

    document.querySelectorAll('.bulk-filter-tab').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const isDark = isDarkMode();
            document.querySelectorAll('.bulk-filter-tab').forEach(b => {
                b.style.background = 'transparent';
                b.style.color = isDark ? '#8696a0' : '#64748b';
            });
            btn.style.background = isDark ? '#00a884' : '#4f46e5';
            btn.style.color = '#ffffff';
            bulkActiveFilter = btn.getAttribute('data-filter') || 'all';
            renderBulkGroupList(document.getElementById('bulk-recipient-search')?.value || '');
        });
    });

    const bulkRecipientSearch = document.getElementById('bulk-recipient-search');
    if (bulkRecipientSearch) {
        bulkRecipientSearch.addEventListener('input', (e) => renderBulkGroupList(e.target.value));
    }

    const btnExpandBulk = document.getElementById('btn-expand-bulk');
    if (btnExpandBulk && bulkDrawer) {
        btnExpandBulk.addEventListener('click', (e) => {
            e.preventDefault();
            bulkDrawer.classList.toggle('expanded');
            const isExpanded = bulkDrawer.classList.contains('expanded');
            btnExpandBulk.innerHTML = isExpanded
                ? '<svg style="width:13px;height:13px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 9V4.5M9 9H4.5M9 9L3.75 3.75M9 15v4.5M9 15H4.5M9 15l-5.25 5.25M15 9h4.5M15 9V4.5M15 9l5.25-5.25M15 15h4.5M15 15v4.5m0-4.5l5.25 5.25"/></svg> Sidebar'
                : '<svg style="width:13px;height:13px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg> Expand';
        });
    }

    const isSuperAdmin = window.waConfig?.isSuperAdmin === true;
    let activeBroadcastListId = null;

    async function fetchSavedLists() {
        try {
            const res = await fetch(`${API_BASE}/saved-lists`);
            const data = await res.json();
            if (data.lists) {
                savedLists = data.lists;
                publicLists = Array.isArray(data.publicLists) ? data.publicLists : [];
                renderSavedLists();
                renderQuickSelectLists();
            }
        } catch(e) {}
    }

    function renderQuickSelectLists() {
        const container = document.getElementById('quick-select-lists');
        if (!container) return;
        container.innerHTML = '';
        const isDark = isDarkMode();

        if (!savedLists || savedLists.length === 0) {
            container.innerHTML = `<span style="font-size:11px;color:${isDark?'#8696a0':'#64748b'};font-style:italic;">No saved lists yet</span>`;
            return;
        }

        savedLists.forEach(list => {
            const colors = ['#005c4b','#1d4ed8','#7c3aed','#b45309','#0f766e','#be185d'];
            const color = colors[(list.name||'').charCodeAt(0) % colors.length];
            const isActive = list.jids.every(jid => bulkSelectedGroups.has(jid));

            const btnBg = isActive ? color + (isDark ? '22' : '15') : (isDark ? '#111b21' : '#ffffff');
            const btnBorder = isActive ? color : (isDark ? '#2a3942' : '#e2e8f0');
            const textColor = isActive ? (isDark ? '#e9edef' : '#0f172a') : (isDark ? '#c1c7cd' : '#475569');

            const btn = document.createElement('button');
            btn.type = 'button';
            btn.style.cssText = `display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border-radius:20px;border:2px solid ${btnBorder};background:${btnBg};cursor:pointer;transition:all 0.2s;`;
            btn.innerHTML = `
                <span style="width:8px;height:8px;border-radius:50%;background:${color};flex-shrink:0;"></span>
                <span style="font-size:11px;font-weight:700;color:${textColor};">${escapeHtml(list.name)}</span>
                <span style="font-size:10px;color:${isActive ? color : (isDark?'#8696a0':'#64748b')};font-weight:600;">${list.targetCount}</span>
                ${isActive ? '<svg style="width:11px;height:11px;" fill="none" stroke="#4f46e5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>' : ''}
            `;

            btn.addEventListener('click', () => {
                const alreadyAll = list.jids.every(jid => bulkSelectedGroups.has(jid));
                if (alreadyAll) list.jids.forEach(jid => bulkSelectedGroups.delete(jid));
                else list.jids.forEach(jid => bulkSelectedGroups.add(jid));
                if (bulkSelectedCount) bulkSelectedCount.textContent = bulkSelectedGroups.size + ' Selected';
                renderBulkGroupList(document.getElementById('bulk-recipient-search')?.value || '');
                renderQuickSelectLists();
            });

            container.appendChild(btn);
        });
    }

    function renderSavedLists() {
        const container = document.getElementById('broadcast-lists-items');
        const countBadge = document.getElementById('broadcast-lists-count');
        if (!container) return;

        if (countBadge) countBadge.textContent = savedLists.length;

        if (savedLists.length === 0) {
            container.innerHTML = '<p style="font-size:11px;color:#8696a0;text-align:center;padding:12px 0;margin:0;">No saved lists yet.<br><span style="font-size:10px;">Select contacts in Bulk panel and click 💾 Save</span></p>';
            return;
        }

        container.innerHTML = '';
        savedLists.forEach(list => {
            const item = document.createElement('div');
            item.className = 'bl-item' + (activeBroadcastListId === list.id ? ' active' : '');
            item.dataset.listId = list.id;

            const colors = ['#005c4b','#1d4ed8','#7c3aed','#b45309','#0f766e','#be185d'];
            const color = colors[(list.name||'').charCodeAt(0) % colors.length];
            const initials = (list.name||'L').substring(0,2).toUpperCase();

            item.innerHTML = `
                <div style="width:30px;height:30px;border-radius:8px;background:${color};display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;color:white;flex-shrink:0;">${initials}</div>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:12px;font-weight:700;color:#e9edef;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${escapeHtml(list.name)}${list.isPublic ? ' <span style="font-size:9px;color:#facc15;font-weight:600;">🌐 Public</span>' : ''}</div>
                    <div style="font-size:10px;color:#8696a0;">${list.targetCount} contacts</div>
                </div>
                <div class="bl-actions" style="display:flex;gap:3px;flex-shrink:0;">
                    <button class="bl-public-btn" title="${list.isPublic ? 'Make Private' : 'Make Public'}" style="width:26px;height:26px;border-radius:6px;background:${list.isPublic ? '#2a3010' : '#1a2a32'};border:1px solid ${list.isPublic ? '#facc15' : '#2a3942'};color:${list.isPublic ? '#facc15' : '#8696a0'};cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:12px;">
                        ${list.isPublic ? '🌐' : '🔒'}
                    </button>
                    <button class="bl-edit-btn" title="Rename" style="width:26px;height:26px;border-radius:6px;background:#1a2a32;border:1px solid #2a3942;color:#8696a0;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                        <svg style="width:12px;height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </button>
                    <button class="bl-del-btn" title="Delete" style="width:26px;height:26px;border-radius:6px;background:#1a2a32;border:1px solid #2a3942;color:#ef4444;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                        <svg style="width:12px;height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            `;

            item.addEventListener('click', (e) => {
                if (e.target.closest('.bl-actions')) return;
                activeBroadcastListId = list.id;
                document.querySelectorAll('.bl-item').forEach(el => el.classList.remove('active'));
                item.classList.add('active');
                bulkSelectedGroups.clear();
                list.jids.forEach(jid => bulkSelectedGroups.add(jid));
                switchToTab('bulk');
                renderBulkGroupList(document.getElementById('bulk-recipient-search')?.value || '');
                renderQuickSelectLists();
                fetchTemplates();
            });

            const editBtn = item.querySelector('.bl-edit-btn');
            if (editBtn) {
                editBtn.addEventListener('click', async (e) => {
                    e.stopPropagation();
                    const newName = prompt(`Rename "${list.name}" to:`, list.name);
                    if (!newName || !newName.trim() || newName.trim() === list.name) return;
                    try {
                        const res = await fetch(`${API_BASE}/saved-lists/${list.id}/rename`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ name: newName.trim() })
                        });
                        const data = await res.json();
                        if (data.success) { savedLists = data.lists; renderSavedLists(); renderQuickSelectLists(); }
                    } catch(err) {}
                });
            }

            const publicBtn = item.querySelector('.bl-public-btn');
            if (publicBtn) {
                publicBtn.addEventListener('click', async (e) => {
                    e.stopPropagation();
                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                        const res = await fetch(`${API_BASE}/saved-lists/${list.id}/toggle-public`, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': csrfToken }
                        });
                        const data = await res.json();
                        if (data.success) { savedLists = data.lists; renderSavedLists(); renderQuickSelectLists(); }
                    } catch(err) {}
                });
            }

            const delBtn = item.querySelector('.bl-del-btn');
            if (delBtn) {
                delBtn.addEventListener('click', async (e) => {
                    e.stopPropagation();
                    if (!confirm(`Delete list "${list.name}"?`)) return;
                    try {
                        await fetch(`${API_BASE}/saved-lists?id=${list.id}`, { method: 'DELETE' });
                        if (activeBroadcastListId === list.id) activeBroadcastListId = null;
                        fetchSavedLists();
                    } catch(err) {}
                });
            }

            container.appendChild(item);
        });

        // Show public lists from other users (admin/NOC only)
        if (publicLists && publicLists.length > 0) {
            const separator = document.createElement('div');
            separator.style.cssText = 'font-size:10px;font-weight:700;color:#facc15;text-transform:uppercase;letter-spacing:0.5px;padding:8px 4px 4px;border-top:1px solid #2a3942;margin-top:4px;';
            separator.textContent = '🌐 Shared Public Lists';
            container.appendChild(separator);

            publicLists.forEach(list => {
                const colors = ['#005c4b','#1d4ed8','#7c3aed','#b45309','#0f766e','#be185d'];
                const color = colors[(list.name||'').charCodeAt(0) % colors.length];
                const initials = (list.name||'L').substring(0,2).toUpperCase();
                const pubItem = document.createElement('div');
                pubItem.style.cssText = 'display:flex;align-items:center;gap:8px;padding:8px;border-radius:8px;cursor:pointer;background:#0d1b14;border:1px solid #facc1533;margin-bottom:4px;';
                pubItem.innerHTML = `
                    <div style="width:30px;height:30px;border-radius:8px;background:${color};display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;color:white;flex-shrink:0;">${initials}</div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:12px;font-weight:700;color:#e9edef;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${escapeHtml(list.name)}</div>
                        <div style="font-size:10px;color:#8696a0;">${list.targetCount} contacts · User #${list._ownerUserId}</div>
                    </div>
                `;
                pubItem.addEventListener('click', () => {
                    bulkSelectedGroups.clear();
                    list.jids.forEach(jid => bulkSelectedGroups.add(jid));
                    if (bulkSelectedCount) bulkSelectedCount.textContent = bulkSelectedGroups.size + ' Selected';
                    switchToTab('bulk');
                    renderBulkGroupList(document.getElementById('bulk-recipient-search')?.value || '');
                    renderQuickSelectLists();
                });
                container.appendChild(pubItem);
            });
        }
    }

    const btnAddBroadcastList = document.getElementById('btn-add-broadcast-list');
    if (btnAddBroadcastList) {
        btnAddBroadcastList.addEventListener('click', () => {
            bulkSelectedGroups.clear();
            activeBroadcastListId = null;
            document.querySelectorAll('.bl-item').forEach(el => el.classList.remove('active'));
            switchToTab('bulk');
            renderBulkGroupList('');
            renderQuickSelectLists();
            fetchTemplates();
        });
    }

    const btnSaveCurrentList = document.getElementById('btn-save-current-list');
    if (btnSaveCurrentList) {
        btnSaveCurrentList.addEventListener('click', async (e) => {
            e.preventDefault();
            const selectedJids = Array.from(bulkSelectedGroups);
            if (selectedJids.length === 0) { alert('Please select at least 1 contact to save.'); return; }
            const name = prompt('Enter a name for this saved list:');
            if (!name || !name.trim()) return;
            try {
                const res = await fetch(`${API_BASE}/saved-lists`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ name: name.trim(), jids: selectedJids })
                });
                const data = await res.json();
                if (data.success) { savedLists = data.lists; renderSavedLists(); alert(`List "${name.trim()}" saved!`); }
            } catch(err) { alert('Failed: ' + err.message); }
        });
    }

    // ── Templates ──────────────────────────────────────────
    let globalTemplates = [];
    let personalTemplates = [];

    async function fetchTemplates() {
        const container = document.getElementById('templates-container');
        try {
            const res = await fetch(`${API_BASE}/templates`);
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const data = await res.json();
            globalTemplates = Array.isArray(data.globalTemplates) ? data.globalTemplates : [];
            personalTemplates = Array.isArray(data.personalTemplates) ? data.personalTemplates : [];
        } catch(e) {
            globalTemplates = []; personalTemplates = [];
            if (container) container.innerHTML = '<span style="font-size:11px;color:#ef4444;">⚠ Could not load templates</span>';
            return;
        }
        renderTemplates();
    }

    function renderTemplates() {
        const container = document.getElementById('templates-container');
        const countBadge = document.getElementById('tmpl-count-badge');
        if (!container) return;
        container.innerHTML = '';

        const allTemplates = [
            ...globalTemplates.map(t => ({ ...t, scope: 'global' })),
            ...personalTemplates.map(t => ({ ...t, scope: 'personal' }))
        ];

        if (countBadge) countBadge.textContent = allTemplates.length;

        if (allTemplates.length === 0) {
            container.innerHTML = '<div style="text-align:center;padding:18px 0;color:#8696a0;font-size:11px;font-style:italic;">No templates yet.<br><span style="font-size:10px;">Write a message and click "Save Current"</span></div>';
            return;
        }

        const isDark = isDarkMode();
        const bgDefault = isDark ? '#111b21' : '#ffffff';
        const bgHover = isDark ? '#1a2a32' : '#f1f5f9';
        const borderDefault = isDark ? '#1e3040' : '#cbd5e1';
        const nameColor = isDark ? '#e9edef' : '#0f172a';
        const btnBg = isDark ? '#1a2a32' : '#f8fafc';
        const btnBorder = isDark ? '#2a3942' : '#cbd5e1';
        const subColor = isDark ? '#8696a0' : '#64748b';

        allTemplates.forEach(tmpl => {
            const isGlobal = tmpl.scope === 'global';
            const canDelete = isGlobal ? isSuperAdmin : true;
            const canEdit = isGlobal ? isSuperAdmin : true;
            const accentColor = isGlobal ? '#facc15' : (isDark ? '#00a884' : '#4f46e5');
            const badgeBg = isGlobal ? 'rgba(250,204,21,0.12)' : (isDark ? 'rgba(0,168,132,0.1)' : 'rgba(79,70,229,0.1)');
            const badgeBorder = isGlobal ? 'rgba(250,204,21,0.3)' : (isDark ? 'rgba(0,168,132,0.25)' : 'rgba(79,70,229,0.25)');
            const preview = tmpl.message ? (tmpl.message.length > 70 ? tmpl.message.substring(0,70) + '…' : tmpl.message) : '';

            const card = document.createElement('div');
            card.dataset.tmplId = tmpl.id;
            card.dataset.scope = tmpl.scope;
            card.style.cssText = `display:flex;align-items:center;gap:10px;padding:10px 12px;background:${bgDefault};border:1px solid ${borderDefault};border-left:3px solid ${accentColor};border-radius:10px;cursor:pointer;transition:all 0.18s;`;
            card.onmouseenter = () => { card.style.background = bgHover; card.style.borderColor = accentColor; card.style.borderLeftColor = accentColor; };
            card.onmouseleave = () => { if (!card.classList.contains('tmpl-active')) { card.style.background = bgDefault; card.style.borderColor = borderDefault; card.style.borderLeftColor = accentColor; } };

            card.innerHTML = `
                <div style="flex:1;min-width:0;">
                    <div style="display:flex;align-items:center;gap:6px;margin-bottom:3px;">
                        <span style="font-size:11px;font-weight:800;color:${nameColor};overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:140px;">${escapeHtml(tmpl.name)}</span>
                        <span style="flex-shrink:0;font-size:9px;font-weight:700;color:${accentColor};background:${badgeBg};border:1px solid ${badgeBorder};border-radius:10px;padding:1px 7px;">
                            ${isGlobal ? '🌐 Global' : '👤 Personal'}
                        </span>
                    </div>
                    <div style="font-size:10.5px;color:${subColor};overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${escapeHtml(preview)}</div>
                </div>
                <div style="display:flex;gap:4px;flex-shrink:0;" class="tmpl-actions">
                    ${canEdit ? `<button class="tmpl-edit-btn" data-id="${tmpl.id}" data-scope="${tmpl.scope}" title="Edit"
                        style="width:26px;height:26px;border-radius:7px;background:${btnBg};border:1px solid ${btnBorder};color:${isDark ? '#00a884' : '#4f46e5'};cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all 0.15s;">
                        <svg style="width:11px;height:11px;pointer-events:none;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </button>` : ''}
                    ${canDelete ? `<button class="tmpl-del-btn" data-id="${tmpl.id}" data-scope="${tmpl.scope}" title="Delete"
                        style="width:26px;height:26px;border-radius:7px;background:${btnBg};border:1px solid ${btnBorder};color:#ef4444;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all 0.15s;">
                        <svg style="width:11px;height:11px;pointer-events:none;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>` : ''}
                </div>
            `;

            // Click card body → apply to textarea
            card.addEventListener('click', (e) => {
                if (e.target.closest('.tmpl-actions')) return;
                const textarea = document.getElementById('bulk-message-content');
                if (textarea) { textarea.value = tmpl.message; textarea.dispatchEvent(new Event('input')); textarea.focus(); }
                document.querySelectorAll('#templates-container [data-tmpl-id]').forEach(c => {
                    c.classList.remove('tmpl-active');
                    c.style.background = bgDefault; c.style.borderColor = borderDefault;
                });
                card.classList.add('tmpl-active');
                card.style.background = isGlobal ? 'rgba(250,204,21,0.08)' : (isDark ? 'rgba(0,168,132,0.07)' : 'rgba(79,70,229,0.08)');
                card.style.borderColor = accentColor;
            });

            // Edit button
            const editBtn = card.querySelector('.tmpl-edit-btn');
            if (editBtn) {
                editBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    openTemplateEditModal(tmpl);
                });
            }

            // Delete button
            const delBtn = card.querySelector('.tmpl-del-btn');
            if (delBtn) {
                delBtn.addEventListener('click', async (e) => {
                    e.stopPropagation();
                    if (!confirm(`"${tmpl.name}" template delete করবেন?`)) return;
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                    await fetch(`${API_BASE}/templates?id=${tmpl.id}&scope=${tmpl.scope}`, {
                        method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrfToken }
                    });
                    fetchTemplates();
                });
            }

            container.appendChild(card);
        });
    }

    // ── Template Edit Modal ──────────────────────────────────────────
    function openTemplateEditModal(tmpl) {
        const modal = document.getElementById('tmpl-edit-modal');
        if (!modal) return;
        document.getElementById('tmpl-edit-id').value = tmpl.id;
        document.getElementById('tmpl-edit-scope').value = tmpl.scope;
        document.getElementById('tmpl-edit-name').value = tmpl.name;
        document.getElementById('tmpl-edit-message').value = tmpl.message;
        updateTmplEditPreview();
        modal.style.display = 'flex';
    }

    function updateTmplEditPreview() {
        const msg = document.getElementById('tmpl-edit-message')?.value || '';
        const preview = document.getElementById('tmpl-edit-preview');
        if (!preview) return;
        const rendered = msg.replace(/\{name\}/g, '<b style="color:#6ee7b7;">Rahim</b>').replace(/\{phone\}/g, '<b style="color:#6ee7b7;">017XXXXXXXX</b>');
        preview.innerHTML = rendered || '<span style="color:#8696a0;font-style:italic;">Preview will appear here...</span>';
    }

    const tmplEditMsg = document.getElementById('tmpl-edit-message');
    if (tmplEditMsg) tmplEditMsg.addEventListener('input', updateTmplEditPreview);

    document.querySelectorAll('.tmpl-tag-insert').forEach(btn => {
        btn.addEventListener('click', () => {
            const ta = document.getElementById('tmpl-edit-message');
            if (!ta) return;
            const pos = ta.selectionStart;
            ta.value = ta.value.slice(0, pos) + btn.dataset.tag + ta.value.slice(ta.selectionEnd);
            ta.selectionStart = ta.selectionEnd = pos + btn.dataset.tag.length;
            ta.focus();
            updateTmplEditPreview();
        });
    });

    const btnCloseTmplEdit = document.getElementById('btn-close-tmpl-edit');
    const btnCancelTmplEdit = document.getElementById('btn-cancel-tmpl-edit');
    [btnCloseTmplEdit, btnCancelTmplEdit].forEach(btn => {
        if (btn) btn.addEventListener('click', () => {
            const modal = document.getElementById('tmpl-edit-modal');
            if (modal) modal.style.display = 'none';
        });
    });

    const btnSaveTmplEdit = document.getElementById('btn-save-tmpl-edit');
    if (btnSaveTmplEdit) {
        btnSaveTmplEdit.addEventListener('click', async () => {
            const id = document.getElementById('tmpl-edit-id').value;
            const scope = document.getElementById('tmpl-edit-scope').value;
            const name = document.getElementById('tmpl-edit-name').value.trim();
            const message = document.getElementById('tmpl-edit-message').value.trim();
            if (!name || !message) { alert('Name and message required.'); return; }
            btnSaveTmplEdit.disabled = true;
            btnSaveTmplEdit.textContent = '⌛ Saving...';
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const res = await fetch(`${API_BASE}/templates/${id}/rename`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ name, message, scope })
                });
                const data = await res.json();
                if (data.success) {
                    globalTemplates = data.globalTemplates || globalTemplates;
                    personalTemplates = data.personalTemplates || personalTemplates;
                    renderTemplates();
                    document.getElementById('tmpl-edit-modal').style.display = 'none';
                } else {
                    alert('Failed to save.');
                }
            } catch(e) { alert('Error: ' + e.message); }
            finally {
                btnSaveTmplEdit.disabled = false;
                btnSaveTmplEdit.innerHTML = '<svg style="width:13px;height:13px;" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg> Save Changes';
            }
        });
    }

    // Close modal on backdrop click
    document.getElementById('tmpl-edit-modal')?.addEventListener('click', (e) => {
        if (e.target === document.getElementById('tmpl-edit-modal')) {
            document.getElementById('tmpl-edit-modal').style.display = 'none';
        }
    });

    // ── Template list collapse toggle ────────────────────────────────
    const btnToggleTemplates = document.getElementById('btn-toggle-templates');
    if (btnToggleTemplates) {
        btnToggleTemplates.addEventListener('click', () => {
            const container = document.getElementById('templates-container');
            if (!container) return;
            const collapsed = container.style.display === 'none';
            container.style.display = collapsed ? 'flex' : 'none';
            btnToggleTemplates.textContent = collapsed ? '▲' : '▼';
        });
    }

    const btnSaveTemplate = document.getElementById('btn-save-template');
    if (btnSaveTemplate) {
        btnSaveTemplate.addEventListener('click', async (e) => {
            e.preventDefault();
            const textarea = document.getElementById('bulk-message-content');
            const msg = textarea?.value?.trim();
            if (!msg) { alert('Write a message first.'); return; }
            const name = prompt('Template name:');
            if (!name || !name.trim()) return;

            let scope = 'personal';
            if (isSuperAdmin) {
                const makeGlobal = confirm(`"${name.trim()}" কে Global করবেন?\n\n✅ OK = 🌐 Global (সব user দেখবে)\n❌ Cancel = 👤 Personal (শুধু আপনি)`);
                if (makeGlobal) scope = 'global';
            }

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const res = await fetch(`${API_BASE}/templates`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ name: name.trim(), message: msg, scope })
                });
                if (!res.ok) { alert('Server error.'); return; }
                const data = await res.json();
                if (data.success) {
                    globalTemplates = data.globalTemplates || [];
                    personalTemplates = data.personalTemplates || [];
                    renderTemplates();
                }
            } catch(err) { alert('Failed: ' + err.message); }
        });
    }

    setTimeout(fetchTemplates, 500);

    document.querySelectorAll('.btn-tag-insert').forEach(btn => {
        btn.addEventListener('click', () => {
            const tag = btn.getAttribute('data-tag');
            const textarea = document.getElementById('bulk-message-content');
            if (!textarea || !tag) return;
            const start = textarea.selectionStart;
            textarea.value = textarea.value.substring(0, start) + tag + textarea.value.substring(textarea.selectionEnd);
            textarea.focus();
            textarea.selectionStart = textarea.selectionEnd = start + tag.length;
        });
    });

    if (btnBulkSelectAll) {
        btnBulkSelectAll.addEventListener('click', (e) => {
            e.preventDefault();
            allChats.forEach(g => bulkSelectedGroups.add(g.id));
            renderBulkGroupList(bulkRecipientSearch?.value || '');
        });
    }

    if (btnBulkDeselectAll) {
        btnBulkDeselectAll.addEventListener('click', (e) => {
            e.preventDefault();
            bulkSelectedGroups.clear();
            renderBulkGroupList(bulkRecipientSearch?.value || '');
        });
    }

    const btnRemoveBulkImg = document.getElementById('btn-remove-bulk-img');

    function resetBulkImageUpload() {
        attachedBulkImageBase64 = null;
        if (bulkImageInput) bulkImageInput.value = '';
        const thumbWrap = document.getElementById('bulk-img-thumb-wrap');
        const placeholder = document.getElementById('bulk-img-placeholder');
        const dropZone = document.getElementById('bulk-img-drop-zone');
        const previewImgWrap = document.getElementById('bulk-preview-img-wrap');
        if (thumbWrap) thumbWrap.style.display = 'none';
        if (placeholder) placeholder.style.display = 'flex';
        if (dropZone) { dropZone.style.borderColor = '#2a3942'; dropZone.style.background = '#111b21'; }
        if (previewImgWrap) previewImgWrap.style.display = 'none';
        if (btnRemoveBulkImg) btnRemoveBulkImg.style.display = 'none';
    }

    if (btnRemoveBulkImg) {
        btnRemoveBulkImg.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            resetBulkImageUpload();
        });
    }

    if (bulkImageInput) {
        bulkImageInput.addEventListener('change', (e) => {
            if (e.target.files && e.target.files[0]) {
                const file = e.target.files[0];
                const reader = new FileReader();
                reader.onload = (ev) => {
                    attachedBulkImageBase64 = ev.target.result;
                    // Show thumbnail in drop zone
                    const thumbWrap = document.getElementById('bulk-img-thumb-wrap');
                    const thumb = document.getElementById('bulk-img-thumb');
                    const filename = document.getElementById('bulk-img-filename');
                    const placeholder = document.getElementById('bulk-img-placeholder');
                    const dropZone = document.getElementById('bulk-img-drop-zone');
                    if (thumb) { thumb.src = ev.target.result; }
                    if (filename) filename.textContent = file.name;
                    if (thumbWrap) thumbWrap.style.display = 'block';
                    if (placeholder) placeholder.style.display = 'none';
                    if (dropZone) { dropZone.style.borderColor = '#00a884'; dropZone.style.background = 'rgba(0,168,132,0.05)'; }
                    if (btnRemoveBulkImg) btnRemoveBulkImg.style.display = 'inline-block';
                    // Show image in preview bubble
                    const previewImg = document.getElementById('bulk-preview-img');
                    const previewImgWrap = document.getElementById('bulk-preview-img-wrap');
                    if (previewImg) previewImg.src = ev.target.result;
                    if (previewImgWrap) previewImgWrap.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                resetBulkImageUpload();
            }
        });
    }

    // ── Live message preview ────────────────────────────────────────
    function updateBulkPreview() {
        const textarea = document.getElementById('bulk-message-content');
        const bubble = document.getElementById('bulk-preview-bubble');
        if (!textarea || !bubble) return;
        const raw = textarea.value;
        if (!raw.trim()) {
            bubble.innerHTML = '<span style="color:#8696a0;font-style:italic;">Message preview...</span>';
            return;
        }
        const rendered = escapeHtml(raw)
            .replace(/\{name\}/g, '<b style="color:#6ee7b7;">Rahim</b>')
            .replace(/\{phone\}/g, '<b style="color:#6ee7b7;">017XXXXXXXX</b>')
            .replace(/\n/g, '<br>');
        bubble.innerHTML = rendered;
    }
    const bulkMsgTextarea = document.getElementById('bulk-message-content');
    if (bulkMsgTextarea) bulkMsgTextarea.addEventListener('input', updateBulkPreview);

    // ── Broadcast Mode Toggle (immediate vs scheduled) ─────────────────
    const broadcastModeImmediate = document.getElementById('broadcast-mode-immediate');
    const broadcastModeScheduled = document.getElementById('broadcast-mode-scheduled');
    const scheduleFields = document.getElementById('schedule-fields');

    function updateBroadcastMode() {
        const isScheduled = broadcastModeScheduled?.checked;
        if (scheduleFields) scheduleFields.style.display = isScheduled ? 'block' : 'none';
        // Update visual card radio button state
        const nowLabel = document.getElementById('mode-now-label');
        const schedLabel = document.getElementById('mode-sched-label');
        const schedDot = document.getElementById('sched-radio-dot');
        const nowDotOuter = nowLabel?.querySelector('div[style*="border-radius:50%"]');
        if (nowLabel) nowLabel.style.borderColor = isScheduled ? '#2a3942' : '#00a884';
        if (schedLabel) schedLabel.style.borderColor = isScheduled ? '#00a884' : '#2a3942';
        if (schedDot) {
            schedDot.style.borderColor = isScheduled ? '#00a884' : '#374248';
            schedDot.style.background = isScheduled ? '#00a884' : 'transparent';
            const innerDot = schedDot.querySelector('div');
            if (innerDot) innerDot.style.background = isScheduled ? 'white' : 'transparent';
        }
        // Update send button text with icon SVG preserved
        if (btnStartBulk) {
            const svg = btnStartBulk.querySelector('svg');
            btnStartBulk.innerHTML = '';
            if (svg) btnStartBulk.appendChild(svg);
            btnStartBulk.appendChild(document.createTextNode(isScheduled ? ' Schedule Broadcast' : ' Start Bulk Broadcast'));
        }
    }

    if (broadcastModeImmediate) broadcastModeImmediate.addEventListener('change', updateBroadcastMode);
    if (broadcastModeScheduled) broadcastModeScheduled.addEventListener('change', updateBroadcastMode);

    // ── Preview Panel Toggle ───────────────────────────────────────────
    const btnTogglePreview = document.getElementById('btn-toggle-preview');
    const bulkPreviewPanel = document.getElementById('bulk-preview-panel');
    if (btnTogglePreview && bulkPreviewPanel) {
        btnTogglePreview.addEventListener('click', () => {
            const visible = bulkPreviewPanel.style.display !== 'none';
            bulkPreviewPanel.style.display = visible ? 'none' : 'block';
            btnTogglePreview.style.color = visible ? '#8696a0' : '#00a884';
            btnTogglePreview.style.borderColor = visible ? '#2a3942' : 'rgba(0,168,132,0.4)';
            btnTogglePreview.style.background = visible ? 'rgba(255,255,255,0.04)' : 'rgba(0,168,132,0.1)';
            if (!visible) updateBulkPreview();
        });
    }

    // Bulk Send / Schedule
    if (formBulkMsg) {
        formBulkMsg.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (bulkSelectedGroups.size === 0) {
                alert('Please select target recipients.');
                return;
            }

            const text = document.getElementById('bulk-message-content')?.value.trim() || '';
            const isScheduled = broadcastModeScheduled?.checked;
            const broadcastName = document.getElementById('broadcast-name')?.value.trim() || ('Broadcast ' + new Date().toLocaleString());

            if (isScheduled) {
                // Schedule broadcast
                const scheduledAtInput = document.getElementById('schedule-datetime');
                const scheduledAt = scheduledAtInput?.value;
                if (!scheduledAt) { alert('Please pick a date and time.'); return; }
                if (new Date(scheduledAt).getTime() <= Date.now()) { alert('Scheduled time must be in the future.'); return; }
                if (!text && !attachedBulkImageBase64) { alert('Please write a message or attach an image.'); return; }

                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                    const res = await fetch(`${API_BASE}/scheduled-broadcasts`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                        body: JSON.stringify({
                            name: broadcastName,
                            message: text,
                            imageBase64: attachedBulkImageBase64,
                            jids: Array.from(bulkSelectedGroups),
                            scheduledAt
                        })
                    });
                    const data = await res.json();
                    if (data.success) {
                        alert(`✅ Broadcast scheduled for ${new Date(scheduledAt).toLocaleString()}\nWill be sent to ${bulkSelectedGroups.size} recipients.`);
                        fetchScheduledBroadcasts();
                    } else {
                        alert('Failed to schedule: ' + (data.message || 'Unknown error'));
                    }
                } catch(err) {
                    alert('Error: ' + err.message);
                }
                return;
            }

            // Immediate broadcast via server-side endpoint
            if (!text && !attachedBulkImageBase64) { alert('Please write a message or attach an image.'); return; }

            isSending = true;
            btnStartBulk.disabled = true;
            btnStartBulk.textContent = '⏳ Sending...';
            if (bulkProgressSection) bulkProgressSection.style.display = 'block';
            if (bulkProgressBarInner) bulkProgressBarInner.style.width = '10%';
            if (bulkProgressText) bulkProgressText.textContent = `Sending to ${bulkSelectedGroups.size} recipients via server...`;
            if (bulkConsoleLog) bulkConsoleLog.innerHTML = `[${new Date().toLocaleTimeString()}] Starting broadcast "${broadcastName}" → ${bulkSelectedGroups.size} recipients...\n`;

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const res = await fetch(`${API_BASE}/broadcast`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({
                        name: broadcastName,
                        jids: Array.from(bulkSelectedGroups),
                        message: text,
                        imageBase64: attachedBulkImageBase64
                    })
                });
                const data = await res.json();
                if (bulkProgressBarInner) bulkProgressBarInner.style.width = '100%';
                if (data.success) {
                    if (bulkProgressText) bulkProgressText.textContent = `Done! ✅ ${data.sentCount} sent, ❌ ${data.failCount} failed`;
                    if (bulkConsoleLog) bulkConsoleLog.innerHTML += `[${new Date().toLocaleTimeString()}] 🎉 Finished! Sent: ${data.sentCount}, Failed: ${data.failCount}\n`;
                    fetchBroadcastHistory();
                } else {
                    if (bulkProgressText) bulkProgressText.textContent = 'Failed: ' + (data.message || 'Unknown error');
                }
            } catch(err) {
                if (bulkProgressText) bulkProgressText.textContent = 'Network error: ' + err.message;
            }

            btnStartBulk.disabled = false;
            btnStartBulk.textContent = '🚀 Start Bulk Broadcast';
            isSending = false;
        });
    }

    fetchSavedLists();

    function logBulkConsole(msg) {
        if (bulkConsoleLog) {
            bulkConsoleLog.innerHTML += msg + '\n';
            bulkConsoleLog.scrollTop = bulkConsoleLog.scrollHeight;
        }
    }

    // ── Scheduled Broadcasts ──────────────────────────────────────────
    async function fetchScheduledBroadcasts() {
        try {
            const res = await fetch(`${API_BASE}/scheduled-broadcasts`);
            const data = await res.json();
            renderScheduledBroadcasts(data.scheduled || []);
        } catch(e) {}
    }

    function renderScheduledBroadcasts(list) {
        const container = document.getElementById('scheduled-broadcasts-list');
        if (!container) return;
        if (list.length === 0) {
            container.innerHTML = '<p style="font-size:11px;color:#8696a0;text-align:center;padding:12px;margin:0;">No scheduled broadcasts</p>';
            return;
        }
        container.innerHTML = '';
        list.forEach(b => {
            const row = document.createElement('div');
            row.style.cssText = 'display:flex;align-items:center;gap:10px;padding:10px 12px;border-bottom:1px solid #1a2a32;';
            const dt = new Date(b.scheduledAt);
            row.innerHTML = `
                <div style="flex:1;min-width:0;">
                    <div style="font-size:12px;font-weight:700;color:#e9edef;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${escapeHtml(b.name)}</div>
                    <div style="font-size:11px;color:#00a884;">📅 ${dt.toLocaleString()}</div>
                    <div style="font-size:10px;color:#8696a0;">${b.jids.length} recipients · ${escapeHtml(b.message?.substring(0,40) || '')}</div>
                </div>
                <button class="sched-del-btn" data-id="${b.id}" style="width:26px;height:26px;border-radius:6px;background:#1a2a32;border:1px solid #2a3942;color:#ef4444;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg style="width:12px;height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            `;
            const delBtn = row.querySelector('.sched-del-btn');
            if (delBtn) {
                delBtn.addEventListener('click', async () => {
                    if (!confirm(`Cancel scheduled broadcast "${b.name}"?`)) return;
                    await fetch(`${API_BASE}/scheduled-broadcasts?id=${b.id}`, { method: 'DELETE' });
                    fetchScheduledBroadcasts();
                });
            }
            container.appendChild(row);
        });
    }

    // ── Broadcast History Panel ────────────────────────────────────────
    async function fetchBroadcastHistory() {
        try {
            const res = await fetch(`${API_BASE}/broadcast-history`);
            const data = await res.json();
            renderBroadcastHistory(data.history || []);
        } catch(e) {}
    }

    function renderBroadcastHistory(list) {
        const container = document.getElementById('broadcast-history-list');
        const countBadge = document.getElementById('broadcast-history-count');
        if (!container) return;
        if (countBadge) countBadge.textContent = list.length;
        if (list.length === 0) {
            container.innerHTML = '<p style="font-size:11px;color:#8696a0;text-align:center;padding:20px;margin:0;">No broadcast history yet</p>';
            return;
        }
        container.innerHTML = '';
        list.forEach(h => {
            const row = document.createElement('div');
            row.style.cssText = 'padding:12px 14px;border-bottom:1px solid #1a2a32;';
            const successRate = h.jids?.length > 0 ? Math.round((h.sentCount / h.jids.length) * 100) : 0;
            row.innerHTML = `
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px;margin-bottom:6px;">
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:13px;font-weight:700;color:#e9edef;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${escapeHtml(h.name)}</div>
                        <div style="font-size:10px;color:#8696a0;margin-top:2px;">${escapeHtml(h.timestamp)} · ${h.type === 'scheduled' ? '📅 Scheduled' : '🚀 Immediate'}</div>
                    </div>
                    <button class="hist-del-btn" data-id="${h.id}" style="width:22px;height:22px;border-radius:5px;background:#1a2a32;border:1px solid #2a3942;color:#ef4444;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:11px;">✕</button>
                </div>
                <div style="font-size:11px;color:#c1c7cd;background:#111b21;border-radius:6px;padding:6px 8px;margin-bottom:6px;max-height:40px;overflow:hidden;border:1px solid #1e3040;">${escapeHtml(h.message?.substring(0,120) || '')}</div>
                <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                    <span style="font-size:11px;color:#00a884;font-weight:600;">✅ ${h.sentCount} sent</span>
                    ${h.failCount > 0 ? `<span style="font-size:11px;color:#ef4444;font-weight:600;">❌ ${h.failCount} failed</span>` : ''}
                    <span style="font-size:11px;color:#8696a0;">${h.jids?.length || 0} total</span>
                    <div style="flex:1;height:4px;background:#1e3040;border-radius:2px;min-width:40px;">
                        <div style="height:100%;width:${successRate}%;background:#00a884;border-radius:2px;"></div>
                    </div>
                    <span style="font-size:10px;color:#8696a0;">${successRate}%</span>
                </div>
            `;
            const delBtn = row.querySelector('.hist-del-btn');
            if (delBtn) {
                delBtn.addEventListener('click', async () => {
                    await fetch(`${API_BASE}/broadcast-history?id=${h.id}`, { method: 'DELETE' });
                    fetchBroadcastHistory();
                });
            }
            container.appendChild(row);
        });
    }

    // History panel toggle
    const btnOpenHistory = document.getElementById('btn-open-history');
    const historyPanel = document.getElementById('history-panel');
    const btnCloseHistory = document.getElementById('btn-close-history');

    if (btnOpenHistory && historyPanel) {
        btnOpenHistory.addEventListener('click', () => {
            historyPanel.classList.toggle('open');
            if (historyPanel.classList.contains('open')) {
                fetchBroadcastHistory();
                fetchScheduledBroadcasts();
            }
        });
    }
    if (btnCloseHistory && historyPanel) {
        btnCloseHistory.addEventListener('click', () => historyPanel.classList.remove('open'));
    }

    // History tabs
    document.querySelectorAll('.hist-tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.hist-tab-btn').forEach(b => {
                b.style.background = 'transparent'; b.style.color = '#8696a0';
            });
            btn.style.background = '#00a884'; btn.style.color = '#0b141a';
            const tab = btn.getAttribute('data-tab');
            document.querySelectorAll('.hist-tab-pane').forEach(p => p.style.display = 'none');
            const pane = document.getElementById('hist-tab-' + tab);
            if (pane) pane.style.display = 'block';
        });
    });

    // Load history on open
    fetchBroadcastHistory();
    fetchScheduledBroadcasts();

    // ── Sessions Panel (super admin) ──────────────────────────────────
    if (isSuperAdmin) {
        async function fetchSessions() {
            try {
                const res = await fetch(`${API_BASE}/sessions`);
                const data = await res.json();
                renderSessionsList(data.sessions || []);
            } catch(e) {}
        }

        function renderSessionsList(sessionsList) {
            const container = document.getElementById('sessions-list');
            if (!container) return;
            container.innerHTML = '';
            if (sessionsList.length === 0) {
                container.innerHTML = '<p style="font-size:11px;color:#8696a0;text-align:center;padding:12px;">No active sessions</p>';
                return;
            }
            sessionsList.forEach(s => {
                const dot = s.status === 'connected' ? '#00a884' : s.status === 'qr_ready' ? '#facc15' : '#f87171';
                const row = document.createElement('div');
                row.style.cssText = 'display:flex;align-items:center;gap:10px;padding:12px 14px;border-bottom:1px solid #1a2a32;';
                row.innerHTML = `
                    <div style="width:36px;height:36px;border-radius:50%;background:#1a2a32;border:2px solid ${dot};display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0;">
                        ${s.user?.profilePic ? `<img src="${escapeHtml(s.user.profilePic)}" style="width:100%;height:100%;border-radius:50%;object-fit:cover;" onerror="this.outerHTML='👤'">` : '👤'}
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:13px;font-weight:700;color:#e9edef;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${escapeHtml(s.user?.name || 'User #' + s.userId)}</div>
                        <div style="font-size:10px;color:#8696a0;">${escapeHtml(s.user?.phone || '')} · ${s.chatCount} chats</div>
                        <span style="font-size:10px;padding:1px 7px;border-radius:8px;background:${dot}22;color:${dot};border:1px solid ${dot}44;font-weight:600;">${s.status}</span>
                    </div>
                    <button class="sess-logout-btn" data-userid="${escapeHtml(s.userId)}"
                        style="display:flex;align-items:center;gap:4px;padding:5px 10px;background:#2a1010;border:1px solid #7f1d1d;border-radius:8px;color:#ef4444;font-size:11px;font-weight:700;cursor:pointer;transition:all 0.2s;flex-shrink:0;"
                        onmouseover="this.style.background='#450a0a'" onmouseout="this.style.background='#2a1010'">
                        <svg style="width:12px;height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        Logout
                    </button>
                `;
                const logoutBtn = row.querySelector('.sess-logout-btn');
                if (logoutBtn) {
                    logoutBtn.addEventListener('click', async () => {
                        const uid = logoutBtn.getAttribute('data-userid');
                        const name = s.user?.name || ('User #' + uid);
                        if (!confirm(`"${name}" এর WhatsApp logout করবেন?`)) return;
                        logoutBtn.disabled = true;
                        logoutBtn.textContent = '⌛...';
                        try {
                            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                            const res = await fetch(`${API_BASE}/sessions/${uid}/logout`, {
                                method: 'POST',
                                headers: { 'X-CSRF-TOKEN': csrfToken }
                            });
                            const data = await res.json();
                            if (data.success) {
                                setTimeout(fetchSessions, 2000);
                            } else {
                                alert('Failed: ' + (data.message || 'Unknown error'));
                                logoutBtn.disabled = false;
                                logoutBtn.textContent = 'Logout';
                            }
                        } catch (err) {
                            alert('Error: ' + err.message);
                            logoutBtn.disabled = false;
                            logoutBtn.textContent = 'Logout';
                        }
                    });
                }
                container.appendChild(row);
            });
        }

        const btnOpenSessions = document.getElementById('btn-open-sessions');
        const sessionsPanel = document.getElementById('sessions-panel');
        const btnCloseSessions = document.getElementById('btn-close-sessions');

        if (btnOpenSessions && sessionsPanel) {
            btnOpenSessions.addEventListener('click', () => {
                sessionsPanel.classList.toggle('open');
                if (sessionsPanel.classList.contains('open')) fetchSessions();
            });
        }
        if (btnCloseSessions && sessionsPanel) {
            btnCloseSessions.addEventListener('click', () => sessionsPanel.classList.remove('open'));
        }

        setInterval(fetchSessions, 15000);
        fetchSessions();
    }

    // ── Resync / Logout ────────────────────────────────────────────────
    const btnResync = document.getElementById('btn-resync');
    if (btnResync) {
        btnResync.addEventListener('click', async (e) => {
            e.preventDefault();
            if (!confirm('Re-syncing will clear the session and request a new QR code. Proceed?')) return;
            btnResync.disabled = true;
            btnResync.textContent = '⌛ Syncing...';
            try {
                isConnected = false;
                allChats = [];
                activeChatJid = null;
                await fetch(`${API_BASE}/logout`, { method: 'POST' });
                if (qrModal) qrModal.classList.remove('hidden');
                setTimeout(() => { pollWhatsAppStatus(); fetchLiveQR(); }, 1000);
            } catch (err) {
                alert('Failed to reset session: ' + err.message);
            } finally {
                btnResync.disabled = false;
                btnResync.textContent = '🔄 Sync';
            }
        });
    }

    if (btnWaAuth) {
        btnWaAuth.addEventListener('click', async (e) => {
            e.preventDefault();
            if (isConnected) {
                if (!confirm('আপনার WhatsApp account disconnect হয়ে যাবে। নতুন করে QR scan করতে হবে। Continue?')) return;
                btnWaAuth.disabled = true;
                btnWaAuth.textContent = '⌛...';
                try {
                    isConnected = false;
                    allChats = [];
                    activeChatJid = null;
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                    await fetch(`${API_BASE}/logout`, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken } });
                    if (qrModal) qrModal.classList.remove('hidden');
                    fetchLiveQR();
                    setTimeout(() => pollWhatsAppStatus(), 1500);
                } catch (err) {
                    alert('Error: ' + err.message);
                } finally {
                    btnWaAuth.disabled = false;
                }
            } else {
                if (qrModal) {
                    qrModal.classList.remove('hidden');
                    fetchLiveQR();
                }
            }
        });
    }
    if (btnCloseQr && qrModal) btnCloseQr.addEventListener('click', (e) => { e.preventDefault(); qrModal.classList.add('hidden'); });

    // ── Sidebar Collapse ────────────────────────────────────────────────
    const sidebar = document.querySelector('.wa-sidebar');
    const btnCollapse = document.getElementById('btn-collapse-sidebar');

    if (btnCollapse && sidebar) {
        btnCollapse.addEventListener('click', () => {
            const isCollapsed = sidebar.classList.toggle('collapsed');
            btnCollapse.title = isCollapsed ? 'Expand sidebar' : 'Collapse sidebar';
            localStorage.setItem('wa_sidebar_collapsed', isCollapsed ? '1' : '0');
        });
        if (localStorage.getItem('wa_sidebar_collapsed') === '1') {
            sidebar.classList.add('collapsed');
            btnCollapse.title = 'Expand sidebar';
        }
        sidebar.addEventListener('click', (e) => {
            if (sidebar.classList.contains('collapsed') && e.target !== btnCollapse && !btnCollapse.contains(e.target)) {
                sidebar.classList.remove('collapsed');
                btnCollapse.title = 'Collapse sidebar';
                localStorage.setItem('wa_sidebar_collapsed', '0');
            }
        });
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
    }
});
