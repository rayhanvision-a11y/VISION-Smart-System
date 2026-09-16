<x-app-layout>
    <style>
        /* Base / Light Mode Styles */
        .wa-wrapper {
            height: calc(100vh - 64px); width: 100%; margin: 0; padding: 0;
            min-height: 600px;
            display: flex;
            overflow: hidden;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f0f2f5;
            color: #111b21;
            width: 100%;
            position: relative;
            box-sizing: border-box;
        }

        .wa-sidebar {
            width: 380px;
            min-width: 380px;
            max-width: 420px;
            background-color: #ffffff;
            border-right: 1px solid #e9edef;
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            height: 100%;
            transition: width 0.25s cubic-bezier(0.4,0,0.2,1), min-width 0.25s cubic-bezier(0.4,0,0.2,1);
            overflow: hidden;
        }

        .wa-sidebar-header-block {
            background: linear-gradient(135deg, #ffffff 0%, #f1f5f9 100%);
            border-bottom: 1px solid #e2e8f0;
            flex-shrink: 0;
        }

        #account-name-display { color: #111b21; }

        .wa-header-btn {
            background: #ffffff !important;
            border: 1px solid #cbd5e1 !important;
            color: #334155 !important;
        }

        .wa-btn-secondary {
            background: #ffffff !important;
            border: 1px solid #cbd5e1 !important;
            color: #334155 !important;
            transition: all 0.2s;
        }
        .wa-btn-secondary:hover {
            background: #f1f5f9 !important;
            border-color: #94a3b8 !important;
        }

        .wa-sidebar.collapsed {
            width: 68px !important;
            min-width: 68px !important;
        }

        /* Hide text-only elements when collapsed */
        .wa-sidebar.collapsed #account-name-display,
        .wa-sidebar.collapsed #account-status-badge,
        .wa-sidebar.collapsed #btn-wa-auth,
        .wa-sidebar.collapsed #btn-open-new-chat,
        .wa-sidebar.collapsed #btn-resync,
        .wa-sidebar.collapsed .wa-search-box,
        .wa-sidebar.collapsed #broadcast-lists-items,
        .wa-sidebar.collapsed #broadcast-lists-chevron,
        .wa-sidebar.collapsed #btn-add-broadcast-list,
        .wa-sidebar.collapsed #broadcast-lists-count {
            display: none !important;
        }

        /* Collapsed Header Layout */
        .wa-sidebar.collapsed .wa-sidebar-header-block {
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
            padding: 10px 4px !important;
            gap: 8px !important;
        }

        .wa-sidebar.collapsed .wa-sidebar-header-text {
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
            gap: 8px !important;
            padding: 4px 0 !important;
            width: 100% !important;
        }

        .wa-sidebar.collapsed .wa-sidebar-header-text > div:last-child {
            order: -1 !important;
            display: flex !important;
            justify-content: center !important;
            width: 100% !important;
        }

        .wa-sidebar.collapsed #btn-collapse-sidebar {
            width: 36px !important;
            height: 36px !important;
            border-radius: 10px !important;
            background: var(--cp) !important;
            border: 1px solid color-mix(in srgb,var(--cp) 85%,#fff) !important;
            color: #ffffff !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        .wa-sidebar.collapsed .wa-sidebar-header-actions {
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
            padding: 4px 0 !important;
            gap: 6px !important;
            width: 100% !important;
        }

        .wa-sidebar.collapsed #btn-tab-chat,
        .wa-sidebar.collapsed #btn-tab-bulk {
            width: 42px !important;
            height: 36px !important;
            padding: 0 !important;
            flex: none !important;
            justify-content: center !important;
            font-size: 0 !important;
        }
        .wa-sidebar.collapsed #btn-tab-chat svg,
        .wa-sidebar.collapsed #btn-tab-bulk svg {
            width: 16px !important;
            height: 16px !important;
            margin: 0 !important;
        }

        /* Collapsed Chat List Layout */
        .wa-sidebar.collapsed .wa-chat-list {
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
            padding: 8px 0 !important;
            gap: 6px !important;
            overflow-y: auto !important;
        }

        .wa-sidebar.collapsed .wa-chat-item {
            width: 46px !important;
            height: 46px !important;
            min-height: 46px !important;
            padding: 0 !important;
            margin: 0 auto !important;
            border-radius: 50% !important;
            justify-content: center !important;
            align-items: center !important;
            border-bottom: none !important;
            position: relative !important;
        }

        /* Hide text inside chat items when collapsed */
        .wa-sidebar.collapsed .wa-chat-item > div:nth-child(2) {
            display: none !important;
        }

        /* Center avatar inside collapsed chat item */
        .wa-sidebar.collapsed .wa-chat-item > div:first-child {
            margin: 0 !important;
        }

        /* Collapsed Bottom Broadcast Section */
        .wa-sidebar.collapsed #broadcast-lists-section {
            padding: 6px 0 !important;
            display: flex !important;
            justify-content: center !important;
        }
        .wa-sidebar.collapsed #broadcast-lists-toggle {
            padding: 6px !important;
            justify-content: center !important;
        }
        .wa-sidebar.collapsed #broadcast-lists-toggle span {
            display: none !important;
        }

        /* Collapse toggle button */
        .wa-collapse-btn {
            width: 32px; height: 32px; border-radius: 50%;
            background: transparent; border: none; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            color: #54656f; flex-shrink: 0; transition: background 0.15s;
        }
        .wa-collapse-btn:hover { background: #e9edef; }
        .wa-collapse-btn svg { width: 18px; height: 18px; transition: transform 0.25s; }
        .wa-sidebar.collapsed .wa-collapse-btn svg { transform: rotate(180deg); }

        .wa-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
            background-color: #efeae2;
            position: relative;
            height: 100%;
        }

        .wa-header {
            height: 60px;
            min-height: 60px;
            background-color: #f0f2f5;
            padding: 0 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #e9edef;
            color: #111b21;
            flex-shrink: 0;
        }

        .wa-search-box {
            padding: 10px;
            background-color: #ffffff;
            border-bottom: 1px solid #e9edef;
        }

        .wa-search-input {
            width: 100%;
            background-color: #f0f2f5;
            border: 1px solid #e9edef;
            border-radius: 8px;
            padding: 8px 12px 8px 36px;
            color: #111b21;
            font-size: 13px;
            outline: none;
        }

        .wa-filter-btn {
            padding: 4px 12px;
            font-size: 12px;
            border-radius: 16px;
            background-color: #e9edef;
            color: #54656f;
            border: none;
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.2s;
        }
        .wa-filter-btn.active {
            background-color: var(--cp);
            color: #ffffff;
            border: 1px solid color-mix(in srgb,var(--cp) 85%,#fff);
            font-weight: 600;
        }

        .wa-chat-list {
            flex: 1;
            overflow-y: auto;
        }

        .wa-chat-item {
            padding: 12px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            border-bottom: 1px solid #f0f2f5;
            transition: background-color 0.15s;
        }
        .wa-chat-item:hover {
            background-color: #f5f6f6;
        }
        .wa-chat-item.active {
            background-color: #f0f2f5;
        }

        .wa-chat-title-name { color: #111b21 !important; }
        .wa-chat-timestamp { color: #667781 !important; }
        .wa-chat-last-msg { color: #667781 !important; }

        .wa-chat-area {
            flex: 1;
            overflow-y: auto;
            padding: 16px;
            background-color: #efeae2;
            background-image: radial-gradient(#cbd5e1 1px, transparent 0);
            background-size: 24px 24px;
        }

        .wa-input-bar {
            height: 62px;
            min-height: 62px;
            background-color: #f0f2f5;
            padding: 0 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-top: 1px solid #e9edef;
            flex-shrink: 0;
        }

        .msg-bubble-outgoing {
            background-color: #d9fdd3 !important;
            color: #111b21 !important;
            border-top-right-radius: 2px !important;
            border: 1px solid #c8f5bf !important;
        }
        .msg-bubble-outgoing .msg-time {
            color: #667781 !important;
        }
        .msg-bubble-incoming {
            background-color: #ffffff !important;
            color: #111b21 !important;
            border-top-left-radius: 2px !important;
            border: 1px solid #e9edef !important;
        }
        .msg-bubble-incoming .msg-time {
            color: #667781 !important;
        }

        /* Bulk Workspace Theme Classes (Light Mode Defaults) */
        .wa-bulk-container {
            background: #f8fafc;
            color: #0f172a;
        }
        .wa-panel-left {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 4px rgba(0,0,0,0.03);
        }
        .wa-panel-right {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 4px rgba(0,0,0,0.03);
        }
        .wa-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }
        .wa-card-header {
            background: #f1f5f9;
            border-bottom: 1px solid #e2e8f0;
            color: #475569;
        }
        .wa-card-footer {
            background: #f1f5f9;
            border-top: 1px solid #e2e8f0;
        }
        .wa-input-reset {
            background: #ffffff !important;
            border: 1px solid #cbd5e1 !important;
            color: #0f172a !important;
        }
        .wa-input-reset::placeholder {
            color: #94a3b8 !important;
        }
        .wa-text-main { color: #0f172a !important; }
        .wa-text-sub { color: #64748b !important; }
        .wa-divider { background: #e2e8f0 !important; }

        .wa-modal-box {
            background-color: #ffffff !important;
            border: 1px solid #e2e8f0 !important;
            color: #0f172a !important;
            box-shadow: 0 20px 50px rgba(0,0,0,0.15) !important;
        }

        /* Dark Mode Overrides (.dark class on html) */
        .dark .wa-wrapper {
            background-color: #0b141a;
            color: #e9edef;
        }
        .dark .wa-sidebar {
            background-color: #111b21;
            border-right: 1px solid #222d34;
        }
        .dark .wa-sidebar-header-block {
            background: linear-gradient(135deg, #1a2a32 0%, #202c33 100%);
            border-bottom: 1px solid #222d34;
        }
        .dark #account-name-display { color: #e9edef; }
        .dark .wa-header-btn,
        .dark .wa-btn-secondary {
            background: #111b21 !important;
            border: 1px solid #374248 !important;
            color: #c1c7cd !important;
        }
        .dark .wa-btn-secondary:hover {
            background: #1a2a32 !important;
            border-color: #00a884 !important;
        }
        .dark .wa-search-box {
            background-color: #111b21;
            border-bottom: 1px solid #222d34;
        }
        .dark .wa-search-input {
            background-color: #202c33;
            color: #e9edef;
            border: none;
        }
        .dark .wa-filter-btn {
            background-color: #202c33;
            color: #8696a0;
        }
        .dark .wa-filter-btn.active {
            background-color: var(--cp);
            color: #ffffff;
            border: 1px solid color-mix(in srgb,var(--cp) 85%,#fff);
        }
        .dark .wa-chat-item {
            border-bottom: 1px solid #1f2c34;
        }
        .dark .wa-chat-item:hover {
            background-color: #202c33;
        }
        .dark .wa-chat-item.active {
            background-color: #2a3942;
        }
        .dark .wa-chat-title-name { color: #e9edef !important; }
        .dark .wa-chat-timestamp { color: #8696a0 !important; }
        .dark .wa-chat-last-msg { color: #8696a0 !important; }
        .dark .wa-main {
            background-color: #0b141a;
        }
        .dark .wa-header {
            background-color: #202c33;
            border-bottom: 1px solid #222d34;
            color: #e9edef;
        }
        .dark .wa-chat-area {
            background-color: #0b141a;
            background-image: radial-gradient(#202c33 1px, transparent 0);
        }
        .dark .wa-input-bar {
            background-color: #202c33;
            border-top: 1px solid #222d34;
        }
        .dark .msg-bubble-outgoing {
            background-color: #005c4b !important;
            color: #ffffff !important;
            border: 1px solid rgba(255,255,255,0.05) !important;
        }
        .dark .msg-bubble-outgoing .msg-time {
            color: rgba(255,255,255,0.6) !important;
        }
        .dark .msg-bubble-incoming {
            background-color: #202c33 !important;
            color: #e9edef !important;
            border: 1px solid rgba(255,255,255,0.05) !important;
        }
        .dark .msg-bubble-incoming .msg-time {
            color: rgba(255,255,255,0.6) !important;
        }

        /* Bulk Workspace Dark Overrides */
        .dark .wa-bulk-container {
            background: #0b1a21;
            color: #e9edef;
        }
        .dark .wa-panel-left {
            background: #0d1f27;
            border: 1px solid #1e3040;
            box-shadow: none;
        }
        .dark .wa-panel-right {
            background: #08131a;
            border: 1px solid #1c2e3d;
            box-shadow: none;
        }
        .dark .wa-card {
            background: #0e1e26;
            border: 1px solid #1c2e3d;
            box-shadow: 0 2px 6px rgba(0,0,0,0.18);
        }
        .dark .wa-card-header {
            background: #091720;
            border-bottom: 1px solid #1c2e3d;
            color: #8696a0;
        }
        .dark .wa-card-footer {
            background: #061016;
            border-top: 1px solid #1c2e3d;
        }
        .dark .wa-input-reset {
            background: #111b21 !important;
            border: 1px solid #2a3942 !important;
            color: #e9edef !important;
        }
        .dark .wa-text-main { color: #e9edef !important; }
        .dark .wa-text-sub { color: #8696a0 !important; }
        .dark .wa-divider { background: #1e3040 !important; }

        .dark .wa-modal-box {
            background-color: #111b21 !important;
            border: 1px solid rgba(0,168,132,0.4) !important;
            color: #e9edef !important;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5) !important;
        }

        .wa-bulk-drawer {
            position: absolute;
            top: 0; right: 0;
            width: 780px;
            max-width: 95vw;
            height: 100%;
            background: #ffffff;
            border-left: 1px solid #e2e8f0;
            z-index: 40;
            transform: translateX(100%);
            transition: transform 0.3s cubic-bezier(0.16,1,0.3,1);
            padding: 14px;
            box-shadow: -12px 0 32px rgba(0,0,0,0.12);
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .dark .wa-bulk-drawer {
            background: #0b1a21;
            border-left: 1px solid rgba(0,168,132,0.2);
            box-shadow: -12px 0 48px rgba(0,0,0,0.7);
        }

        .wa-bulk-drawer #form-bulk-msg {
            flex: 1;
            min-height: 0;
            overflow: hidden;
        }

        .wa-bulk-drawer.expanded {
            width: min(1100px, 95vw) !important;
        }

        /* Bulk drawer scrollbars */
        .wa-bulk-body-scroll::-webkit-scrollbar { width: 5px; }
        .wa-bulk-body-scroll::-webkit-scrollbar-track { background: transparent; }
        .wa-bulk-body-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .dark .wa-bulk-body-scroll::-webkit-scrollbar-thumb { background: #2a3942; }

        #templates-container::-webkit-scrollbar { width: 4px; }
        #templates-container::-webkit-scrollbar-track { background: transparent; }
        #templates-container::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .dark #templates-container::-webkit-scrollbar-thumb { background: #2a3942; }

        #form-bulk-msg input[type="text"]:focus,
        #form-bulk-msg input[type="datetime-local"]:focus,
        #form-bulk-msg textarea:focus,
        #form-bulk-msg select:focus {
            outline: none !important;
            box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.25) !important;
            border-color: var(--cp) !important;
        }

        .wa-bulk-drawer.open {
            transform: translateX(0) !important;
        }
        .bulk-filter-tab.active {
            background: var(--cp) !important;
            color: #ffffff !important;
        }
        .dark .bulk-filter-tab.active {
            background: #00a884 !important;
            color: #0b141a !important;
        }

        #bulk-group-list::-webkit-scrollbar { width: 4px; }
        #bulk-group-list::-webkit-scrollbar-track { background: transparent; }
        #bulk-group-list::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .dark #bulk-group-list::-webkit-scrollbar-thumb { background: #2a3942; }

        /* Broadcast Lists panel */
        #broadcast-lists-section.bl-open #broadcast-lists-items { display: block !important; }
        #broadcast-lists-section.bl-open #broadcast-lists-chevron { transform: rotate(180deg); }
        .bl-item { display:flex;align-items:center;gap:8px;padding:7px 10px;border-radius:10px;cursor:pointer;border:1px solid transparent;transition:all 0.15s;margin-bottom:3px; }
        .bl-item:hover { background:#f1f5f9;border-color:#e2e8f0; }
        .dark .bl-item:hover { background:#1a2a32;border-color:#1e3040; }
        .bl-item.active { background:color-mix(in srgb,var(--cp) 10%,transparent);border-color:color-mix(in srgb,var(--cp) 30%,transparent); }
        .dark .bl-item.active { background:rgba(0,168,132,0.1);border-color:rgba(0,168,132,0.35); }

        .wa-modal {
            position: fixed;
            inset: 0;
            z-index: 50;
            background-color: rgba(15, 23, 42, 0.5);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }
        .dark .wa-modal {
            background-color: rgba(11, 20, 26, 0.85);
        }
        .wa-modal.hidden {
            display: none !important;
        }

        /* ── Theme primary color overrides for WA page (uses CSS var from app layout) ── */
        .wa-sidebar.collapsed #btn-collapse-sidebar        { background: var(--cp) !important; border-color: color-mix(in srgb, var(--cp) 80%, #fff) !important; }
        .wa-filter-btn.active, .dark .wa-filter-btn.active { background-color: var(--cp) !important; border-color: color-mix(in srgb, var(--cp) 80%, #fff) !important; }
        .bulk-filter-tab.active                            { background: var(--cp) !important; }
        .bl-item.active                                    { background: color-mix(in srgb, var(--cp) 10%, transparent) !important; border-color: color-mix(in srgb, var(--cp) 35%, transparent) !important; }
        #form-bulk-msg input:focus, #form-bulk-msg textarea:focus, #form-bulk-msg select:focus {
            border-color: var(--cp) !important;
            box-shadow: 0 0 0 2px color-mix(in srgb, var(--cp) 25%, transparent) !important;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const cp = getComputedStyle(document.documentElement).getPropertyValue('--cp').trim();
            if (!cp) return;
            // Active chat avatar uses JS-set bg (changes dynamically per chat)
            const chatAvatar = document.getElementById('active-chat-avatar');
            if (chatAvatar) chatAvatar.style.backgroundColor = cp;
        });
    </script>

    <div class="wa-wrapper">
        
        <!-- Left Sidebar: Chat List & Search -->
        <aside class="wa-sidebar">
            
            <!-- Top App Header -->
            <div class="wa-sidebar-header-block">

                <!-- Profile Row -->
                <div class="wa-sidebar-header-text" style="display: flex; align-items: center; gap: 10px; padding: 12px 12px 8px 12px;">
                    <!-- Avatar -->
                    <div style="position: relative; flex-shrink: 0;">
                        <div id="my-profile-avatar" style="width: 42px; height: 42px; border-radius: 50%; background: linear-gradient(135deg, var(--cp), color-mix(in srgb,var(--cp) 80%,#000)); border: 2px solid color-mix(in srgb,var(--cp) 85%,#fff); color: white; display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: bold; box-shadow: 0 2px 8px color-mix(in srgb,var(--cp) 25%,transparent);">
                            👤
                        </div>
                        <!-- Online dot -->
                        <span id="account-online-dot" style="position: absolute; bottom: 1px; right: 1px; width: 10px; height: 10px; border-radius: 50%; background: #f87171; border: 2px solid #ffffff;"></span>
                    </div>
                    <!-- Name & Status -->
                    <div style="min-width: 0; flex: 1;">
                        <p id="account-name-display" class="wa-text-main" style="font-size: 13px; font-weight: 700; margin: 0 0 2px 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; letter-spacing: 0.2px;">Connecting...</p>
                        <span id="account-status-badge" style="font-size: 10px; color: #ef4444; font-weight: 600; display: inline-flex; align-items: center; gap: 3px; background: rgba(239,68,68,0.1); padding: 1px 6px; border-radius: 10px; border: 1px solid rgba(239,68,68,0.2);">● Disconnected</span>
                    </div>
                    <!-- QR + Collapse -->
                    <div style="display: flex; align-items: center; gap: 4px; flex-shrink: 0;">
                        <!-- Dynamic Login / Logout button -->
                        <button type="button" id="btn-wa-auth" class="wa-btn-secondary"
                            style="display:flex;align-items:center;gap:4px;padding:5px 10px;border-radius:8px;font-size:11px;font-weight:700;cursor:pointer;white-space:nowrap;">
                            <svg style="width:13px;height:13px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 3.5a.5.5 0 11-1 0 .5.5 0 011 0z"/></svg>
                            Login
                        </button>
                        <button class="wa-collapse-btn wa-btn-secondary" id="btn-collapse-sidebar" title="Collapse sidebar" style="width: 30px; height: 30px; border-radius: 8px;">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/></svg>
                        </button>
                    </div>
                </div>

                <!-- Action Buttons Row (Main Tabs: Chat vs Bulk) -->
                <div class="wa-sidebar-header-actions" style="display: flex; align-items: center; gap: 5px; padding: 0 10px 10px 10px;">
                    <button type="button" id="btn-tab-chat"
                        style="flex: 1; display: flex; align-items: center; justify-content: center; gap: 5px; padding: 7px 6px; background: var(--cp); color: #ffffff; font-size: 11px; font-weight: 700; border-radius: 8px; border: 1px solid color-mix(in srgb,var(--cp) 85%,#fff); cursor: pointer; white-space: nowrap; transition: all 0.2s; letter-spacing: 0.2px; box-shadow: 0 2px 6px color-mix(in srgb,var(--cp) 30%,transparent);">
                        <svg style="width:13px;height:13px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        Chat
                    </button>
                    <button type="button" id="btn-tab-bulk" class="wa-btn-secondary"
                        style="flex: 1; display: flex; align-items: center; justify-content: center; gap: 5px; padding: 7px 6px; font-size: 11px; font-weight: 700; border-radius: 8px; cursor: pointer; white-space: nowrap; transition: all 0.2s; letter-spacing: 0.2px;">
                        <svg style="width:13px;height:13px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                        Bulk
                    </button>
                    <button type="button" id="btn-open-new-chat" class="wa-btn-secondary" title="Start New Chat"
                        style="display: flex; align-items: center; justify-content: center; gap: 4px; padding: 7px 8px; color: #10b981 !important; border-radius: 8px; cursor: pointer; font-size: 11px; font-weight: 600; white-space: nowrap; transition: all 0.2s;">
                        + Chat
                    </button>
                    <button type="button" id="btn-resync" class="wa-btn-secondary" title="Resync WhatsApp"
                        style="display: flex; align-items: center; justify-content: center; gap: 4px; padding: 7px 8px; color: #eab308 !important; border-radius: 8px; cursor: pointer; font-size: 11px; font-weight: 600; white-space: nowrap; transition: all 0.2s;">
                        <svg style="width:13px;height:13px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Sync
                    </button>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="wa-search-box">
                <div style="position: relative; display:flex; gap:5px;">
                    <div style="position:relative;flex:1;">
                        <input type="text" id="chat-search" placeholder="Search chats or groups..." class="wa-search-input" style="padding-right:36px;">
                        <span style="position: absolute; left: 12px; top: 8px; color: #8696a0; font-size: 12px;">🔍</span>
                    </div>
                    <button type="button" id="btn-msg-search-toggle" title="Search inside messages"
                        style="width:36px;height:36px;border-radius:10px;border:1px solid #2a3942;background:transparent;color:#8696a0;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:all 0.2s;"
                        onmouseover="this.style.background='color-mix(in srgb,var(--cp) 10%,transparent)';this.style.color='var(--cp)';this.style.borderColor='var(--cp)'"
                        onmouseout="if(!this.classList.contains('active')){this.style.background='transparent';this.style.color='#8696a0';this.style.borderColor='#2a3942';}">
                        <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 10h.01M10 13h.01M10 16h.01"/></svg>
                    </button>
                </div>

                <!-- Message Search Panel (hidden by default) -->
                <div id="msg-search-panel" style="display:none;margin-top:8px;">
                    <div style="position:relative;">
                        <input type="text" id="msg-search-input" placeholder="Search messages..." autocomplete="off"
                            style="width:100%;padding:8px 36px 8px 34px;font-size:12px;border-radius:10px;border:1px solid #2a3942;background:#111b21;color:#e9edef;outline:none;box-sizing:border-box;transition:border-color 0.2s;"
                            onfocus="this.style.borderColor='var(--cp)'" onblur="this.style.borderColor='#2a3942'">
                        <span style="position:absolute;left:10px;top:9px;color:#8696a0;font-size:12px;">🔍</span>
                        <button type="button" id="btn-msg-search-clear" style="position:absolute;right:8px;top:7px;background:none;border:none;color:#8696a0;cursor:pointer;font-size:15px;display:none;line-height:1;">✕</button>
                    </div>
                    <div id="msg-search-results" style="margin-top:6px;max-height:240px;overflow-y:auto;display:flex;flex-direction:column;gap:3px;"></div>
                </div>

                <!-- Chat Filter Badges -->
                <div style="display: flex; gap: 6px; margin-top: 10px; overflow-x: auto; padding-bottom: 2px;">
                    <button type="button" class="wa-filter-btn active" data-filter="all">All</button>
                    <button type="button" class="wa-filter-btn" data-filter="personal">Personal 👤</button>
                    <button type="button" class="wa-filter-btn" data-filter="groups">Groups 👥</button>
                    <button type="button" class="wa-filter-btn" data-filter="unread">Unread</button>
                </div>
            </div>

            <!-- Scrollable Chats List -->
            <div id="chats-list-container" class="wa-chat-list">
                <!-- Populated dynamically via JS -->
            </div>

            <!-- Broadcast Lists Panel -->
            <div class="wa-sidebar-body wa-card-header" id="broadcast-lists-section" style="border-top:1px solid #e2e8f0;flex-shrink:0;">

                <!-- Header row — click to toggle -->
                <div id="broadcast-lists-toggle" style="display:flex;align-items:center;justify-content:space-between;padding:10px 12px;cursor:pointer;user-select:none;">
                    <div style="display:flex;align-items:center;gap:7px;flex:1;" onclick="document.getElementById('broadcast-lists-section').classList.toggle('bl-open')">
                        <div style="width:22px;height:22px;border-radius:6px;background:linear-gradient(135deg,var(--cp),color-mix(in srgb,var(--cp) 80%,#000));display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <svg style="width:12px;height:12px;" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                        </div>
                        <span class="wa-text-main" style="font-size:12px;font-weight:700;">Bulk Broadcaster</span>
                        <span id="broadcast-lists-count" style="font-size:10px;font-weight:700;color:var(--cp);background:color-mix(in srgb,var(--cp) 12%,transparent);padding:1px 6px;border-radius:8px;border:1px solid color-mix(in srgb,var(--cp) 25%,transparent);">0</span>
                    </div>
                    <div style="display:flex;align-items:center;gap:6px;">
                        <!-- Add button -->
                        <button type="button" id="btn-add-broadcast-list" title="Create new broadcast list"
                            style="display:flex;align-items:center;gap:4px;padding:4px 9px;background:color-mix(in srgb,var(--cp) 12%,transparent);border:1px solid color-mix(in srgb,var(--cp) 30%,transparent);border-radius:8px;color:var(--cp);font-size:11px;font-weight:700;cursor:pointer;transition:all 0.15s;">
                            <svg style="width:11px;height:11px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                            Add
                        </button>
                        <!-- Chevron -->
                        <svg id="broadcast-lists-chevron" onclick="document.getElementById('broadcast-lists-section').classList.toggle('bl-open')"
                            style="width:14px;height:14px;transition:transform 0.2s;flex-shrink:0;" fill="none" stroke="#64748b" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>

                <!-- Collapsible list -->
                <div id="broadcast-lists-items" style="padding:0 8px 8px 8px;max-height:220px;overflow-y:auto;display:none;">
                    <!-- Populated via JS -->
                </div>
            </div>
        </aside>

        <!-- Right Main Workspace (Tab 1: Chat | Tab 2: Bulk) -->
        <main class="wa-main">
            
            <!-- TAB 1: Chat Conversation Workspace -->
            <div id="view-chat-container" style="display: flex; flex-direction: column; flex: 1; height: 100%; min-width: 0; position: relative;">
                
                <!-- Active Chat Header -->
                <div id="active-chat-header" class="wa-header">
                    <div style="display: flex; align-items: center; gap: 12px; min-width: 0;">
                        <div id="active-chat-avatar" style="width: 40px; height: 40px; border-radius: 50%; background-color: var(--cp); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px; flex-shrink: 0;">
                            💬
                        </div>
                        <div style="min-width: 0;">
                            <h3 id="active-chat-title" class="wa-text-main" style="font-size: 14px; font-weight: 700; margin: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">Select a Chat or Click '+ Chat' to Start</h3>
                            <p id="active-chat-subtext" class="wa-text-sub" style="font-size: 11px; margin: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">WhatsApp Web Sync Active</p>
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; gap: 8px;">
                        <button type="button" id="btn-open-history" class="wa-btn-secondary" title="Broadcast History & Scheduled"
                            style="display:flex;align-items:center;gap:5px;padding:6px 12px;border-radius:8px;font-size:11px;font-weight:700;cursor:pointer;transition:all 0.2s;">
                            <svg style="width:13px;height:13px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            History
                            <span id="broadcast-history-count" style="font-size:9px;background:color-mix(in srgb,var(--cp) 15%,transparent);color:var(--cp);padding:0 5px;border-radius:8px;border:1px solid color-mix(in srgb,var(--cp) 30%,transparent);">0</span>
                        </button>
                        @if(auth()->user()?->isSuperAdminOnly())
                        <button type="button" id="btn-open-sessions" class="wa-btn-secondary" title="Connected Sessions"
                            style="display:flex;align-items:center;gap:5px;padding:6px 12px;border-radius:8px;font-size:11px;font-weight:700;cursor:pointer;transition:all 0.2s;">
                            <svg style="width:13px;height:13px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            Sessions
                        </button>
                        @endif
                    </div>
                </div>

                <!-- Conversation Message Area -->
                <div id="conversation-area" class="wa-chat-area">
                    <div style="text-align: center; margin: 24px 0;">
                        <span class="wa-card" style="padding: 6px 14px; font-size: 11px; color: #d97706; border-radius: 8px; font-weight: 600;">
                            🔒 End-to-end encrypted • ISP Tickets WhatsApp Sync
                        </span>
                    </div>
                </div>

                <!-- Image Attachment Live Preview Bar -->
                <div id="chat-image-preview-bar" class="wa-header" style="display: none; padding: 10px 16px; align-items: center; gap: 12px;">
                    <div style="position: relative;">
                        <img id="chat-preview-img" src="" alt="Attached Image" style="max-height: 70px; border-radius: 8px; border: 1px solid var(--cp); object-fit: cover;">
                        <button type="button" id="btn-remove-attachment" style="position: absolute; top: -6px; right: -6px; background-color: #ef4444; color: white; border: none; border-radius: 50%; width: 20px; height: 20px; font-size: 11px; font-weight: bold; cursor: pointer; display: flex; align-items: center; justify-content: center;">✕</button>
                    </div>
                    <div>
                        <p id="chat-preview-filename" style="font-size: 12px; font-weight: 600; color: var(--cp); margin: 0;">Photo Attached</p>
                        <p class="wa-text-sub" style="font-size: 11px; margin: 0;">Type a caption below and press send</p>
                    </div>
                </div>

                <!-- Message Input Footer Bar -->
                <form id="form-chat-send" class="wa-input-bar" style="padding: 10px 16px; display: flex; align-items: center; justify-content: center; min-height: 62px; flex-shrink: 0;">
                    <div class="wa-input-reset" style="width: 100%; display: flex; align-items: center; border-radius: 24px; padding: 4px 14px; gap: 8px;">
                        <button type="button" id="btn-attach-clip" class="wa-text-sub" style="background: none; border: none; cursor: pointer; padding: 6px; display: flex; align-items: center; justify-content: center;" title="Attach Photo/File">
                            <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor">
                                <path d="M1.816 15.556v.002c0 1.5.584 2.912 1.644 3.972s2.472 1.644 3.972 1.644a5.59 5.59 0 0 0 3.972-1.644l9.201-9.202c.868-.868 1.346-2.023 1.346-3.251 0-1.229-.478-2.383-1.346-3.251a4.57 4.57 0 0 0-3.251-1.347 4.57 4.57 0 0 0-3.251 1.347l-8.47 8.47a2.82 2.82 0 0 0-.825 1.996c0 .753.293 1.46.825 1.992.532.533 1.239.826 1.992.826.754 0 1.461-.293 1.993-.826l7.747-7.747-.962-.962-7.747 7.747a1.45 1.45 0 0 1-1.031.427 1.45 1.45 0 0 1-1.031-.427 1.45 1.45 0 0 1-.427-1.031c0-.388.151-.752.427-1.031l8.47-8.47a3.17 3.17 0 0 1 2.251-.933c.849 0 1.648.331 2.251.933a3.17 3.17 0 0 1 .933 2.251c0 .849-.331 1.648-.933 2.251l-9.202 9.202a4.2 4.2 0 0 1-2.972 1.231 4.2 4.2 0 0 1-2.972-1.231 4.2 4.2 0 0 1-1.231-2.972c0-1.127.439-2.186 1.231-2.972l8.476-8.476-.962-.962-8.476 8.476a5.59 5.59 0 0 0-1.644 3.972z"></path>
                            </svg>
                        </button>
                        <input type="file" id="chat-file-input" accept="image/*" style="display: none;">

                        <button type="button" id="btn-emoji-picker" class="wa-text-sub" style="background: none; border: none; cursor: pointer; padding: 6px; display: flex; align-items: center; justify-content: center;" title="Insert Emoji">
                            <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor">
                                <path d="M9.153 11.603c.795 0 1.439-.879 1.439-1.962s-.644-1.962-1.439-1.962-1.439.879-1.439 1.962.644 1.962 1.439 1.962zm5.694 0c.795 0 1.439-.879 1.439-1.962s-.644-1.962-1.439-1.962-1.439.879-1.439 1.962.644 1.962 1.439 1.962zM12 2a10 10 0 1 0 10 10A10.011 10.011 0 0 0 12 2zm0 18a8 8 0 1 1 8-8 8.009 8.009 0 0 1-8 8zm0-4.4c-2.33 0-4.32-1.45-5.12-3.5h10.24c-.8 2.05-2.79 3.5-5.12 3.5z"></path>
                            </svg>
                        </button>

                        <input type="text" id="chat-message-input" class="wa-text-main" placeholder="Type a message" autocomplete="off"
                               style="flex: 1; font-size: 14px; background: transparent; border: none; outline: none; padding: 8px 4px;">

                        <button type="submit" id="btn-send-chat" style="background: none; border: none; color: var(--cp); cursor: pointer; padding: 6px; display: flex; align-items: center; justify-content: center;" title="Send Message">
                            <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor">
                                <path d="M1.101 21.757 23.8 12.028 1.101 2.3l.011 7.912 13.523 1.816-13.523 1.817-.011 7.912z"></path>
                            </svg>
                        </button>
                    </div>
                </form>
            </div>

            <!-- TAB 2: Full Width Bulk Broadcaster Workspace -->
            <div id="view-bulk-container" class="wa-bulk-container" style="display: none; flex-direction: column; flex: 1; height: 100%; min-width: 0; position: relative; overflow: hidden;">
                
                <!-- Bulk Header Bar -->
                <div class="wa-header" style="padding: 0 18px; display: flex; align-items: center; justify-content: space-between; height: 60px; min-height: 60px; flex-shrink: 0;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div style="width: 36px; height: 36px; border-radius: 10px; background: linear-gradient(135deg, var(--cp), color-mix(in srgb,var(--cp) 80%,#000)); display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px color-mix(in srgb,var(--cp) 30%,transparent); flex-shrink: 0;">
                            <svg style="width: 18px; height: 18px; color: white;" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                        </div>
                        <div>
                            <h3 class="wa-text-main" style="font-size: 15px; font-weight: 800; margin: 0 0 1px 0; letter-spacing: 0.3px;">Bulk Broadcaster System</h3>
                            <p class="wa-text-sub" style="font-size: 11px; margin: 0;">Send personalized broadcasts & campaigns to multiple contacts & groups</p>
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <button type="button" class="wa-btn-secondary" title="Broadcast History & Scheduled"
                            style="display:flex;align-items:center;gap:5px;padding:6px 12px;border-radius:8px;font-size:11px;font-weight:700;cursor:pointer;"
                            onclick="document.getElementById('btn-open-history').click()">
                            📋 History
                        </button>
                        <button type="button" id="btn-back-to-chat" class="wa-btn-secondary" title="Switch back to Chat View"
                            style="display:flex;align-items:center;gap:5px;padding:6px 12px;border-radius:8px;color:var(--cp) !important;border-color:color-mix(in srgb,var(--cp) 85%,#fff) !important;font-size:11px;font-weight:700;cursor:pointer;">
                            💬 Back to Chat
                        </button>
                    </div>
                </div>

                <!-- Full Width Bulk Broadcast Form -->
                <form id="form-bulk-msg" style="display: grid; grid-template-columns: 360px 1fr; gap: 14px; align-items: stretch; flex: 1; min-height: 0; padding: 14px; box-sizing: border-box; overflow: hidden;">

                    <!-- LEFT: Recipients Selection Panel -->
                    <div class="wa-panel-left" style="display:flex;flex-direction:column;gap:8px;border-radius:14px;padding:12px;height:100%;min-height:0;box-sizing:border-box;overflow:hidden;">

                        <!-- Quick Select from Saved Lists -->
                        <div style="flex-shrink:0;">
                            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:5px;">
                                <span class="wa-text-sub" style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;">⚡ Quick Select</span>
                                <span class="wa-text-sub" style="font-size:10px;">Click to add</span>
                            </div>
                            <div id="quick-select-lists" style="display:flex;gap:4px;flex-wrap:wrap;min-height:24px;">
                                <span class="wa-text-sub" style="font-size:10.5px;font-style:italic;">No saved lists yet</span>
                            </div>
                        </div>

                        <!-- Divider -->
                        <div class="wa-divider" style="height:1px;margin:0 -2px;flex-shrink:0;"></div>

                        <!-- Search -->
                        <div style="position:relative;flex-shrink:0;">
                            <svg style="position:absolute;left:10px;top:50%;transform:translateY(-50%);width:13px;height:13px;color:#8696a0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <input type="text" id="bulk-recipient-search" class="wa-input-reset" placeholder="Search contacts or groups..."
                                style="width:100%;font-size:11.5px;border-radius:8px;padding:7px 10px 7px 30px;outline:none;box-sizing:border-box;">
                        </div>

                        <!-- Divider -->
                        <div class="wa-divider" style="height:1px;margin:0 -2px;flex-shrink:0;"></div>

                        <!-- Recipients Header -->
                        <div style="display:flex;align-items:center;justify-content:space-between;flex-shrink:0;">
                            <span class="wa-text-main" style="font-size:11.5px;font-weight:700;">Recipients</span>
                            <span id="bulk-selected-count" style="font-size:10px;color:var(--cp);font-weight:800;background:color-mix(in srgb,var(--cp) 12%,transparent);padding:2px 8px;border-radius:20px;border:1px solid color-mix(in srgb,var(--cp) 30%,transparent);">0 Selected</span>
                        </div>

                        <!-- Filter tabs -->
                        <div class="wa-card-header" style="display:flex;gap:3px;border-radius:8px;padding:3px;flex-shrink:0;">
                            <button type="button" class="bulk-filter-tab active" data-filter="all" style="flex:1;padding:4px 5px;font-size:10px;font-weight:700;border-radius:6px;border:none;cursor:pointer;background:var(--cp);color:#ffffff;transition:all 0.2s;">All</button>
                            <button type="button" class="bulk-filter-tab wa-text-sub" data-filter="personal" style="flex:1;padding:4px 5px;font-size:10px;font-weight:700;border-radius:6px;border:none;cursor:pointer;background:transparent;transition:all 0.2s;">Personal</button>
                            <button type="button" class="bulk-filter-tab wa-text-sub" data-filter="groups" style="flex:1;padding:4px 5px;font-size:10px;font-weight:700;border-radius:6px;border:none;cursor:pointer;background:transparent;transition:all 0.2s;">Groups</button>
                        </div>

                        <!-- Contact List -->
                        <div id="bulk-group-list" style="flex:1;min-height:100px;overflow-y:auto;display:flex;flex-direction:column;gap:3px;padding-right:2px;"></div>

                        <!-- Action Buttons -->
                        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:5px;margin-top:auto;flex-shrink:0;">
                            <button type="button" id="btn-bulk-select-all" class="wa-btn-secondary"
                                style="padding:6px 4px;font-size:10.5px;font-weight:600;border-radius:7px;cursor:pointer;transition:all 0.2s;">
                                ✓ All
                            </button>
                            <button type="button" id="btn-bulk-deselect-all" class="wa-btn-secondary"
                                style="padding:6px 4px;font-size:10.5px;font-weight:600;border-radius:7px;cursor:pointer;transition:all 0.2s;">
                                ✕ Clear
                            </button>
                            <button type="button" id="btn-save-current-list"
                                style="padding:6px 4px;background:var(--cp);color:white;font-size:10.5px;font-weight:700;border:none;border-radius:7px;cursor:pointer;transition:all 0.2s;"
                                title="Save selection as preset">
                                💾 Save
                            </button>
                        </div>
                    </div>

                    <!-- RIGHT: Compose Panel -->
                    <div class="wa-panel-right" style="display:flex;flex-direction:column;gap:0;border-radius:14px;height:100%;min-height:0;box-sizing:border-box;overflow:hidden;">

                        <!-- Compose Scrollable Body -->
                        <div class="wa-bulk-body-scroll" style="flex:1;min-height:0;overflow-y:auto;padding:12px;display:flex;flex-direction:column;gap:12px;">

                        <!-- ① Campaign Name -->
                        <div class="wa-card" style="border-radius:10px;overflow:hidden;flex-shrink:0;">
                            <div class="wa-card-header" style="padding:8px 12px;display:flex;align-items:center;gap:8px;">
                                <div style="width:20px;height:20px;border-radius:5px;background:rgba(250,204,21,0.15);display:flex;align-items:center;justify-content:center;font-size:11px;flex-shrink:0;">📌</div>
                                <span class="wa-text-sub" style="font-size:10.5px;font-weight:800;text-transform:uppercase;letter-spacing:0.8px;">Campaign Name</span>
                            </div>
                            <div style="padding:10px 12px;">
                                <input type="text" id="broadcast-name" class="wa-input-reset" placeholder="e.g. Monthly Bill Notice — Sep 2026"
                                    style="width:100%;height:34px;font-size:12.5px;border-radius:7px;padding:0 10px;box-sizing:border-weight:500;">
                            </div>
                        </div>

                        <!-- ② Templates -->
                        <div class="wa-card" style="border-radius:10px;overflow:hidden;flex-shrink:0;">
                            <div class="wa-card-header" style="padding:8px 12px;display:flex;align-items:center;justify-content:space-between;gap:8px;">
                                <div style="display:flex;align-items:center;gap:8px;min-width:0;">
                                    <div style="width:20px;height:20px;border-radius:5px;background:color-mix(in srgb,var(--cp) 15%,transparent);display:flex;align-items:center;justify-content:center;font-size:11px;flex-shrink:0;">📋</div>
                                    <span class="wa-text-sub" style="font-size:10.5px;font-weight:800;text-transform:uppercase;letter-spacing:0.8px;white-space:nowrap;">Templates</span>
                                    <span id="tmpl-count-badge" style="font-size:9.5px;font-weight:700;color:var(--cp);background:color-mix(in srgb,var(--cp) 12%,transparent);border:1px solid color-mix(in srgb,var(--cp) 25%,transparent);border-radius:20px;padding:1px 6px;min-width:16px;text-align:center;flex-shrink:0;">0</span>
                                </div>
                                <div style="display:flex;align-items:center;gap:5px;flex-shrink:0;">
                                    <button type="button" id="btn-save-template"
                                        style="display:flex;align-items:center;gap:4px;padding:3px 8px;background:color-mix(in srgb,var(--cp) 15%,transparent);color:var(--cp);font-size:10px;font-weight:700;border-radius:5px;border:1px solid color-mix(in srgb,var(--cp) 30%,transparent);cursor:pointer;transition:all 0.2s;white-space:nowrap;">
                                        <svg style="width:10px;height:10px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                                        Save Current
                                    </button>
                                    <button type="button" id="btn-toggle-templates" class="wa-btn-secondary"
                                        style="width:24px;height:24px;border-radius:5px;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:10px;">
                                        <svg style="width:11px;height:11px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/></svg>
                                    </button>
                                </div>
                            </div>
                            <div id="templates-container" style="max-height:140px;overflow-y:auto;padding:8px;display:flex;flex-direction:column;gap:4px;">
                                <div class="wa-text-sub" style="text-align:center;padding:10px 0;font-size:10.5px;font-style:italic;">No templates saved yet. Write a message and click "Save Current".</div>
                            </div>
                        </div>

                        <!-- ③ Message Composer -->
                        <div class="wa-card" style="border-radius:10px;overflow:hidden;flex-shrink:0;">
                            
                            <!-- Card Header -->
                            <div class="wa-card-header" style="padding:8px 12px;display:flex;align-items:center;justify-content:space-between;gap:8px;">
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <div style="width:20px;height:20px;border-radius:5px;background:color-mix(in srgb,var(--cp) 15%,transparent);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                        <svg style="width:11px;height:11px;" fill="none" stroke="var(--cp)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                    </div>
                                    <span class="wa-text-sub" style="font-size:10.5px;font-weight:800;text-transform:uppercase;letter-spacing:0.8px;">Message Content</span>
                                </div>
                                <button type="button" id="btn-toggle-preview"
                                    style="display:flex;align-items:center;gap:4px;padding:3px 8px;background:color-mix(in srgb,var(--cp) 12%,transparent);color:var(--cp);font-size:10px;font-weight:700;border-radius:5px;border:1px solid color-mix(in srgb,var(--cp) 25%,transparent);cursor:pointer;transition:all 0.2s;white-space:nowrap;">
                                    <svg style="width:11px;height:11px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    Live Preview
                                </button>
                            </div>

                            <!-- Inner Composer Area -->
                            <div style="padding:10px 12px;display:flex;flex-direction:column;gap:10px;">

                                <!-- Tag Insertion Toolbar -->
                                <div class="wa-card-header" style="padding:6px 10px;border-radius:7px;display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                                    <span class="wa-text-sub" style="font-size:10px;font-weight:700;letter-spacing:0.3px;">Insert Variable:</span>
                                    <button type="button" class="btn-tag-insert" data-tag="{name}"
                                        style="padding:2px 8px;background:color-mix(in srgb,var(--cp) 12%,transparent);color:var(--cp);font-size:10.5px;font-weight:700;border-radius:5px;border:1px solid color-mix(in srgb,var(--cp) 30%,transparent);cursor:pointer;transition:all 0.15s;font-family:monospace;">
                                        {name}
                                    </button>
                                    <button type="button" class="btn-tag-insert" data-tag="{phone}"
                                        style="padding:2px 8px;background:color-mix(in srgb,var(--cp) 12%,transparent);color:var(--cp);font-size:10.5px;font-weight:700;border-radius:5px;border:1px solid color-mix(in srgb,var(--cp) 30%,transparent);cursor:pointer;transition:all 0.15s;font-family:monospace;">
                                        {phone}
                                    </button>
                                </div>

                                <!-- Message Textarea -->
                                <textarea id="bulk-message-content" class="wa-input-reset" rows="3" required placeholder="Hello {name}! Important notice from ISP Tickets..."
                                    style="width:100%;font-size:12.5px;line-height:1.5;border-radius:7px;padding:10px;box-sizing:border-box;resize:vertical;font-family:inherit;min-height:85px;"></textarea>

                                <!-- Live Preview Panel -->
                                <div id="bulk-preview-panel" class="wa-card-header" style="display:none;border-radius:8px;padding:10px;">
                                    <div class="wa-text-sub" style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.6px;margin-bottom:8px;display:flex;align-items:center;justify-content:space-between;gap:8px;flex-wrap:wrap;">
                                        <span>📱 WhatsApp Chat Bubble Preview</span>
                                        <span style="color:var(--cp);font-size:9.5px;font-weight:600;">Recipient: John Doe (01712345678)</span>
                                    </div>
                                    <!-- Chat bubble simulation -->
                                    <div class="msg-bubble-outgoing" style="border-radius:10px 10px 2px 10px;padding:10px 12px;font-size:12.5px;line-height:1.5;white-space:pre-wrap;word-break:break-word;max-height:160px;overflow-y:auto;" id="bulk-preview-content">
                                        <span class="wa-text-sub" style="font-style:italic;">Start typing a message to see preview...</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ④ Attachments & Delay grid -->
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;flex-shrink:0;">
                            
                            <!-- Image Attachment Box -->
                            <div class="wa-card" style="border-radius:10px;overflow:hidden;display:flex;flex-direction:column;">
                                <div class="wa-card-header" style="padding:8px 12px;display:flex;align-items:center;justify-content:space-between;">
                                    <div style="display:flex;align-items:center;gap:6px;">
                                        <span style="font-size:11px;">📷</span>
                                        <span class="wa-text-sub" style="font-size:10.5px;font-weight:800;text-transform:uppercase;letter-spacing:0.6px;">Image Attachment</span>
                                    </div>
                                    <button type="button" id="btn-remove-bulk-img" style="display:none;padding:1px 6px;background:#ef4444;color:white;font-size:9.5px;font-weight:700;border-radius:4px;border:none;cursor:pointer;">✕ Remove</button>
                                </div>
                                <div style="padding:10px 12px;flex:1;display:flex;align-items:center;justify-content:center;">
                                    <label for="bulk-image-input" class="wa-input-reset" style="width:100%;padding:10px;border-style:dashed !important;border-radius:8px;cursor:pointer;text-align:center;transition:all 0.2s;display:block;">
                                        
                                        <div id="bulk-img-thumb-wrap" style="display:none;width:100%;text-align:center;">
                                            <img id="bulk-img-thumb" src="" style="max-height:50px;border-radius:5px;border:1px solid #cbd5e1;object-fit:cover;display:inline-block;">
                                            <div id="bulk-img-filename" style="font-size:9px;color:var(--cp);margin-top:3px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:100%;font-weight:600;"></div>
                                        </div>

                                        <div id="bulk-img-placeholder" style="display:flex;flex-direction:column;align-items:center;gap:2px;">
                                            <svg class="wa-text-sub" style="width:18px;height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            <span class="wa-text-sub" style="font-size:10.5px;font-weight:600;">Choose image</span>
                                            <span class="wa-text-sub" style="font-size:9px;opacity:0.8;">PNG, JPG, WEBP</span>
                                        </div>
                                        <input type="file" id="bulk-image-input" accept="image/*" style="display:none;">
                                    </label>
                                </div>
                            </div>

                            <!-- Send Delay Box -->
                            <div class="wa-card" style="border-radius:10px;overflow:hidden;display:flex;flex-direction:column;">
                                <div class="wa-card-header" style="padding:8px 12px;display:flex;align-items:center;gap:6px;">
                                    <span style="font-size:11px;">⏱</span>
                                    <span class="wa-text-sub" style="font-size:10.5px;font-weight:800;text-transform:uppercase;letter-spacing:0.6px;">Send Delay</span>
                                </div>
                                <div style="padding:10px 12px;display:flex;flex-direction:column;gap:6px;flex:1;justify-content:center;">
                                    <select id="bulk-delay-interval" class="wa-input-reset"
                                        style="width:100%;height:34px;font-size:11.5px;border-radius:7px;padding:0 8px;cursor:pointer;text-overflow:ellipsis;box-sizing:border-box;">
                                        <option value="3">⚡ 3s — Fast</option>
                                        <option value="5" selected>🛡️ 5s — Recommended</option>
                                        <option value="10">🔒 10s — Safe</option>
                                        <option value="30">🐢 30s — Very Safe</option>
                                    </select>
                                    <p class="wa-text-sub" style="font-size:9.5px;margin:0;line-height:1.3;">Interval between messages to avoid WhatsApp bans.</p>
                                </div>
                            </div>
                        </div>

                        <!-- ⑤ Send Mode -->
                        <div class="wa-card" style="border-radius:10px;overflow:hidden;flex-shrink:0;">
                            <div class="wa-card-header" style="padding:8px 12px;display:flex;align-items:center;gap:8px;">
                                <div style="width:20px;height:20px;border-radius:5px;background:rgba(250,204,21,0.12);display:flex;align-items:center;justify-content:center;font-size:11px;flex-shrink:0;">📅</div>
                                <span class="wa-text-sub" style="font-size:10.5px;font-weight:800;text-transform:uppercase;letter-spacing:0.8px;">Send Mode</span>
                            </div>
                            <div style="padding:10px 12px;display:flex;flex-direction:column;gap:8px;">
                                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                                    <label id="mode-now-label" class="wa-input-reset" style="display:flex;align-items:center;gap:7px;padding:8px 10px;border:2px solid var(--cp) !important;border-radius:7px;cursor:pointer;">
                                        <input type="radio" name="broadcast-mode" id="broadcast-mode-immediate" value="immediate" checked style="display:none;">
                                        <div style="width:13px;height:13px;border-radius:50%;border:2px solid var(--cp);background:var(--cp);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                            <div style="width:4px;height:4px;border-radius:50%;background:white;"></div>
                                        </div>
                                        <div style="min-width:0;">
                                            <div class="wa-text-main" style="font-size:11px;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">🚀 Send Now</div>
                                            <div class="wa-text-sub" style="font-size:9px;">Immediately</div>
                                        </div>
                                    </label>
                                    <label id="mode-sched-label" class="wa-input-reset" style="display:flex;align-items:center;gap:7px;padding:8px 10px;border-radius:7px;cursor:pointer;">
                                        <input type="radio" name="broadcast-mode" id="broadcast-mode-scheduled" value="scheduled" style="display:none;">
                                        <div id="sched-radio-dot" style="width:13px;height:13px;border-radius:50%;border:2px solid #94a3b8;background:transparent;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                            <div style="width:4px;height:4px;border-radius:50%;background:transparent;"></div>
                                        </div>
                                        <div style="min-width:0;">
                                            <div class="wa-text-main" style="font-size:11px;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">📅 Schedule</div>
                                            <div class="wa-text-sub" style="font-size:9px;">Future date</div>
                                        </div>
                                    </label>
                                </div>
                                <div id="schedule-fields" style="display:none;">
                                    <label class="wa-text-sub" style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:3px;display:block;">📆 Date & Time</label>
                                    <input type="datetime-local" id="schedule-datetime" class="wa-input-reset"
                                        style="width:100%;height:34px;font-size:11.5px;border-radius:7px;padding:0 8px;box-sizing:border-box;">
                                </div>
                            </div>
                        </div>

                        <!-- ⑥ Progress (hidden until send) -->
                        <div id="bulk-progress-section" class="wa-card" style="display:none;border-radius:10px;overflow:hidden;flex-shrink:0;">
                            <div class="wa-card-header" style="padding:8px 12px;display:flex;align-items:center;justify-content:space-between;">
                                <div style="display:flex;align-items:center;gap:6px;">
                                    <span style="font-size:11px;">📤</span>
                                    <span class="wa-text-sub" style="font-size:10.5px;font-weight:800;text-transform:uppercase;letter-spacing:0.8px;">Sending Progress</span>
                                </div>
                                <button type="button" id="btn-retry-failed" style="display:none;padding:2px 8px;background:#ef4444;color:white;font-size:9.5px;font-weight:700;border-radius:5px;border:none;cursor:pointer;">↺ Retry Failed</button>
                            </div>
                            <div style="padding:10px 12px;">
                                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
                                    <p id="bulk-progress-text" class="wa-text-sub" style="font-size:10.5px;margin:0;font-weight:600;">Sending 0 of 0 (0%)</p>
                                </div>
                                <div class="wa-card-header" style="width:100%;height:5px;border-radius:3px;overflow:hidden;margin-bottom:8px;">
                                    <div id="bulk-progress-bar-inner" style="height:100%;background:linear-gradient(90deg,var(--cp),color-mix(in srgb,var(--cp) 85%,#fff));width:0%;transition:width 0.4s cubic-bezier(0.4,0,0.2,1);border-radius:3px;"></div>
                                </div>
                                <pre id="bulk-console-log" class="wa-card-header" style="width:100%;height:75px;padding:8px 10px;color:#10b981;font-family:monospace;font-size:10px;border-radius:7px;overflow-y:auto;box-sizing:border-box;margin:0;line-height:1.5;"></pre>
                            </div>
                        </div>

                        </div><!-- end scrollable body -->

                        <!-- Send Button — sticky bottom -->
                        <div class="wa-card-footer" style="padding:10px 12px;flex-shrink:0;">
                            <button type="submit" id="btn-start-bulk"
                                style="width:100%;padding:13px;background:linear-gradient(135deg,#00a884 0%,#00876a 100%);color:white;font-weight:800;font-size:13px;border:none;border-radius:11px;cursor:pointer;box-shadow:0 4px 18px rgba(0,168,132,0.3);transition:all 0.2s;display:flex;align-items:center;justify-content:center;gap:8px;letter-spacing:0.4px;"
                                onmouseover="this.style.transform='translateY(-1px)';this.style.boxShadow='0 7px 24px rgba(0,168,132,0.45)'"
                                onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 4px 18px rgba(0,168,132,0.3)'">
                                <svg style="width:16px;height:16px;" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                Start Bulk Broadcast
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Broadcast History & Scheduled Panel -->
            <div id="history-panel" class="wa-panel-right" style="position:absolute;top:0;right:0;width:460px;max-width:95vw;height:100%;z-index:45;transform:translateX(100%);transition:transform 0.3s cubic-bezier(0.16,1,0.3,1);display:flex;flex-direction:column;overflow:hidden;">
                <!-- Header -->
                <div class="wa-card-header" style="display:flex;align-items:center;justify-content:space-between;padding:16px 18px;flex-shrink:0;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="width:34px;height:34px;border-radius:10px;background:linear-gradient(135deg,var(--cp),color-mix(in srgb,var(--cp) 80%,#000));display:flex;align-items:center;justify-content:center;">
                            <svg style="width:16px;height:16px;" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        </div>
                        <div>
                            <div class="wa-text-main" style="font-size:14px;font-weight:800;">Broadcast Center</div>
                            <div class="wa-text-sub" style="font-size:11px;">History & Scheduled</div>
                        </div>
                    </div>
                    <button type="button" id="btn-close-history" class="wa-btn-secondary" style="width:30px;height:30px;border-radius:8px;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                        <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <!-- Tabs -->
                <div class="wa-card-header" style="display:flex;gap:4px;padding:10px 14px;flex-shrink:0;">
                    <button type="button" class="hist-tab-btn" data-tab="history" style="flex:1;padding:6px 10px;font-size:11px;font-weight:700;border-radius:8px;border:none;cursor:pointer;background:var(--cp);color:#ffffff;transition:all 0.2s;">📋 History</button>
                    <button type="button" class="hist-tab-btn wa-text-sub" data-tab="scheduled" style="flex:1;padding:6px 10px;font-size:11px;font-weight:700;border-radius:8px;border:none;cursor:pointer;background:transparent;transition:all 0.2s;">📅 Scheduled</button>
                </div>
                <!-- Tab Panes -->
                <div style="flex:1;overflow-y:auto;">
                    <div id="hist-tab-history" class="hist-tab-pane">
                        <div id="broadcast-history-list" style="min-height:60px;">
                            <p class="wa-text-sub" style="font-size:11px;text-align:center;padding:20px;">Loading...</p>
                        </div>
                    </div>
                    <div id="hist-tab-scheduled" class="hist-tab-pane" style="display:none;">
                        <div id="scheduled-broadcasts-list" style="min-height:60px;">
                            <p class="wa-text-sub" style="font-size:11px;text-align:center;padding:12px;">No scheduled broadcasts</p>
                        </div>
                    </div>
                </div>
            </div>
            <style>
                #history-panel.open { transform: translateX(0); }
                #sessions-panel.open { transform: translateX(0); }
            </style>

            @if(auth()->user()?->isSuperAdminOnly())
            <!-- Sessions Panel (super admin) -->
            <div id="sessions-panel" class="wa-panel-right" style="position:absolute;top:0;right:0;width:360px;max-width:95vw;height:100%;z-index:44;transform:translateX(100%);transition:transform 0.3s cubic-bezier(0.16,1,0.3,1);display:flex;flex-direction:column;overflow:hidden;">
                <div class="wa-card-header" style="display:flex;align-items:center;justify-content:space-between;padding:16px 18px;flex-shrink:0;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="width:34px;height:34px;border-radius:10px;background:linear-gradient(135deg,#d97706,#f59e0b);display:flex;align-items:center;justify-content:center;">
                            <svg style="width:16px;height:16px;" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        </div>
                        <div>
                            <div class="wa-text-main" style="font-size:14px;font-weight:800;">Active Sessions</div>
                            <div class="wa-text-sub" style="font-size:11px;">All connected WhatsApp accounts</div>
                        </div>
                    </div>
                    <button type="button" id="btn-close-sessions" class="wa-btn-secondary" style="width:30px;height:30px;border-radius:8px;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                        <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div style="flex:1;overflow-y:auto;">
                    <div id="sessions-list">
                        <p class="wa-text-sub" style="font-size:11px;text-align:center;padding:20px;">Loading sessions...</p>
                    </div>
                </div>
            </div>
            @endif

        </main>

        <!-- Bulk Broadcast Drawer Panel -->
        <div id="bulk-drawer" class="wa-bulk-drawer">

                <!-- Header -->
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;flex-shrink:0;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,var(--cp),color-mix(in srgb,var(--cp) 80%,#000));display:flex;align-items:center;justify-content:center;box-shadow:0 4px 12px color-mix(in srgb,var(--cp) 30%,transparent);flex-shrink:0;">
                            <svg style="width:18px;height:18px;color:white;" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                        </div>
                        <div>
                            <h3 class="wa-text-main" style="font-size:14.5px;font-weight:800;margin:0 0 1px 0;letter-spacing:0.3px;">Bulk Broadcast</h3>
                            <p class="wa-text-sub" style="font-size:10.5px;margin:0;">Send messages to multiple contacts & groups</p>
                        </div>
                    </div>
                    <div style="display:flex;align-items:center;gap:6px;">
                        <button type="button" id="btn-expand-bulk" class="wa-btn-secondary" title="Expand"
                            style="display:flex;align-items:center;gap:5px;padding:5px 10px;color:#10b981 !important;font-size:11px;font-weight:700;border-radius:8px;cursor:pointer;">
                            <svg style="width:13px;height:13px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                            Expand
                        </button>
                        <button type="button" id="btn-close-bulk" class="wa-btn-secondary"
                            style="width:30px;height:30px;border-radius:8px;font-size:16px;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                            <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
    </div>

    <!-- Start New 1-on-1 Contact Modal -->
    <div id="new-chat-modal" class="wa-modal hidden">
        <div class="wa-modal-box" style="border-radius: 16px; padding: 24px; max-width: 380px; width: 100%;">
            <h3 class="wa-text-main" style="font-size: 16px; font-weight: 700; margin: 0 0 8px 0; display: flex; align-items: center; gap: 8px;">
                <span style="color: var(--cp);">💬</span> Start New 1-on-1 Chat
            </h3>
            <p class="wa-text-sub" style="font-size: 12px; margin: 0 0 16px 0;">
                Enter any WhatsApp phone number to add as a personal contact.
            </p>

            <form id="form-add-contact" style="display: flex; flex-direction: column; gap: 12px;">
                <div>
                    <label class="wa-text-sub" style="display: block; font-size: 12px; font-weight: 600; margin-bottom: 4px;">WhatsApp Phone Number:</label>
                    <input type="text" id="new-contact-phone" class="wa-input-reset" required placeholder="01712345678 or 8801712345678"
                           style="width: 100%; font-size: 13px; border-radius: 10px; padding: 10px 12px; outline: none; box-sizing: border-box;">
                </div>

                <div>
                    <label class="wa-text-sub" style="display: block; font-size: 12px; font-weight: 600; margin-bottom: 4px;">Contact Name (Optional):</label>
                    <input type="text" id="new-contact-name" class="wa-input-reset" placeholder="e.g. Redoy Khan"
                           style="width: 100%; font-size: 13px; border-radius: 10px; padding: 10px 12px; outline: none; box-sizing: border-box;">
                </div>

                <div style="display: flex; gap: 8px; justify-content: flex-end; margin-top: 8px;">
                    <button type="button" id="btn-close-new-chat" class="wa-btn-secondary" style="padding: 8px 16px; font-weight: 600; font-size: 12px; border-radius: 10px; cursor: pointer;">
                        Cancel
                    </button>
                    <button type="submit" style="padding: 8px 20px; background-color: var(--cp); color: white; font-weight: 700; font-size: 12px; border: none; border-radius: 10px; cursor: pointer;">
                        Add & Open Chat
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Template Edit Modal -->
    <div id="tmpl-edit-modal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.5);z-index:200;align-items:center;justify-content:center;">
        <div class="wa-modal-box" style="border-radius:18px;padding:24px;width:460px;max-width:95vw;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
                <div style="display:flex;align-items:center;gap:10px;">
                    <div style="width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,var(--cp),color-mix(in srgb,var(--cp) 80%,#000));display:flex;align-items:center;justify-content:center;">
                        <svg style="width:16px;height:16px;" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </div>
                    <div>
                        <div class="wa-text-main" style="font-size:14px;font-weight:800;">Edit Template</div>
                        <div class="wa-text-sub" style="font-size:11px;">Update name and message content</div>
                    </div>
                </div>
                <button type="button" id="btn-close-tmpl-edit" class="wa-btn-secondary" style="width:30px;height:30px;border-radius:8px;font-size:16px;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                    <svg style="width:13px;height:13px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <input type="hidden" id="tmpl-edit-id">
            <input type="hidden" id="tmpl-edit-scope">

            <div style="margin-bottom:14px;">
                <label class="wa-text-sub" style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.6px;margin-bottom:6px;display:block;">Template Name</label>
                <input type="text" id="tmpl-edit-name" class="wa-input-reset" placeholder="e.g. Monthly Bill Reminder"
                    style="width:100%;font-size:13px;border-radius:10px;padding:10px 12px;outline:none;box-sizing:border-box;">
            </div>

            <div style="margin-bottom:18px;">
                <label class="wa-text-sub" style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.6px;margin-bottom:6px;display:block;">Message Content</label>
                <textarea id="tmpl-edit-message" class="wa-input-reset" rows="5" placeholder="Hello {name}! ..."
                    style="width:100%;font-size:12.5px;line-height:1.6;border-radius:10px;padding:10px 12px;outline:none;box-sizing:border-box;resize:vertical;font-family:inherit;"></textarea>
                <div style="margin-top:6px;display:flex;gap:5px;">
                    <button type="button" class="tmpl-tag-insert" data-tag="{name}" style="padding:2px 8px;background:color-mix(in srgb,var(--cp) 12%,transparent);color:var(--cp);font-size:10px;font-weight:700;border-radius:5px;border:1px solid color-mix(in srgb,var(--cp) 35%,transparent);cursor:pointer;">+ name</button>
                    <button type="button" class="tmpl-tag-insert" data-tag="{phone}" style="padding:2px 8px;background:color-mix(in srgb,var(--cp) 12%,transparent);color:var(--cp);font-size:10px;font-weight:700;border-radius:5px;border:1px solid color-mix(in srgb,var(--cp) 35%,transparent);cursor:pointer;">+ phone</button>
                </div>
            </div>

            <!-- Preview in modal -->
            <div class="wa-card" style="margin-bottom:18px;border-radius:12px;padding:12px;">
                <div class="wa-text-sub" style="font-size:10px;font-weight:700;margin-bottom:8px;text-transform:uppercase;letter-spacing:0.5px;">📱 Output Preview</div>
                <div id="tmpl-edit-preview" class="msg-bubble-outgoing" style="border-radius:10px 10px 2px 10px;padding:10px 12px;font-size:12px;line-height:1.6;white-space:pre-wrap;word-break:break-word;min-height:40px;max-height:120px;overflow-y:auto;">
                    <span class="wa-text-sub" style="font-style:italic;">Preview will appear here...</span>
                </div>
            </div>

            <div style="display:flex;gap:8px;justify-content:flex-end;">
                <button type="button" id="btn-cancel-tmpl-edit" class="wa-btn-secondary"
                    style="padding:9px 20px;font-size:12px;font-weight:600;border-radius:10px;cursor:pointer;">
                    Cancel
                </button>
                <button type="button" id="btn-save-tmpl-edit"
                    style="padding:9px 22px;background:linear-gradient(135deg,var(--cp),color-mix(in srgb,var(--cp) 80%,#000));color:white;font-size:12px;font-weight:700;border:none;border-radius:10px;cursor:pointer;display:flex;align-items:center;gap:6px;box-shadow:0 3px 10px color-mix(in srgb,var(--cp) 30%,transparent);">
                    <svg style="width:13px;height:13px;" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    Save Changes
                </button>
            </div>
        </div>
    </div>

    <!-- QR Code Scan Modal -->
    <div id="qr-modal" class="wa-modal hidden">
        <div class="wa-modal-box" style="border-radius: 16px; padding: 24px; max-width: 360px; width: 100%; text-align: center;">
            <h3 class="wa-text-main" style="font-size: 16px; font-weight: 700; margin: 0 0 8px 0; display: flex; align-items: center; justify-content: center; gap: 8px;">
                <span style="color: var(--cp);">📱</span> WhatsApp Web Login
            </h3>
            <p class="wa-text-sub" style="font-size: 12px; margin: 0 0 16px 0;">
                Scan the QR code below from your phone's WhatsApp (Linked Devices)
            </p>

            <div style="display: flex; justify-content: center; margin-bottom: 16px;">
                <img src="{{ asset('images/qr_code.jpg') }}" alt="WhatsApp QR Code" style="width: 220px; height: 220px; border-radius: 12px; border: 2px solid var(--cp);">
            </div>

            <div style="display: flex; gap: 8px; justify-content: center;">
                <button type="button" id="btn-close-qr" class="wa-btn-secondary" style="padding: 8px 20px; font-weight: 600; font-size: 12px; border-radius: 10px; cursor: pointer;">
                    Close
                </button>
            </div>
        </div>
    </div>

    <script>
        window.waConfig = {
            isSuperAdmin: @json(auth()->user()?->isSuperAdminOnly() ?? false)
        };
    </script>
    <script src="{{ asset('js/whatsapp.js') }}?v={{ time() }}"></script>
</x-app-layout>
