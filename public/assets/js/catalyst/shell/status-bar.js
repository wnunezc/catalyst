/**
 * Catalyst Status Bar — WebSocket client + notification panel
 *
 * Reads window.__catalystWs = { token, url, isAuth, unread }
 * Manages:
 *   - WebSocket connection with exponential back-off reconnect
 *   - WS status dot + text in the bottom bar
 *   - Notification badge counter
 *   - Notification panel open/close
 *   - REST-based notification list load + mark-read
 */
import { getHttpClient } from '../core/http.js';

const STATUS_BAR_ICONS = {
    info:    'ti ti-info-circle',
    success: 'ti ti-circle-check',
    warning: 'ti ti-alert-triangle',
    error:   'ti ti-circle-x',
    system:  'ti ti-settings',
};

function fallback(value, defaultValue) {
    return value === null || value === undefined ? defaultValue : value;
}

export class StatusBarManager {
    _ws                 = null;
    _panelOpen          = false;
    _loaded             = false;   // notifications fetched at least once
    _unread             = 0;
    _reconnectDelay     = 2000;
    _reconnectTimer     = null;
    _unreadRefreshTimer = null;
    _destroyed          = false;
    _needsTokenRefresh  = false;   // set when server rejects token (auth_fail / error)
    _presenceSubscriptions = new Map();

    // DOM refs
    _dot        = null;
    _statusText = null;
    _bellBtn    = null;
    _badge      = null;
    _panel      = null;
    _notifList  = null;
    _emptyMsg   = null;
    _markAllBtn = null;
    _panelCount = null;

    /** @type {{ token: string, url: string, isAuth: boolean, unread: number, userId?: number|null, tenantId?: number|null, tenantKey?: string }} */
    _cfg = {};
    _http = getHttpClient();

    _i18n(key, defaultValue) {
        return fallback(this._cfg?.i18n?.[key], defaultValue);
    }

    init(config = null) {
        this._cfg = fallback(config, fallback(window.__catalystWs, { isAuth: false, unread: 0 }));
        this._unread = fallback(this._cfg.unread, 0);

        this._dot        = document.getElementById('ws-dot');
        this._statusText = document.getElementById('ws-status');
        this._bellBtn    = document.getElementById('notif-bell-btn');
        this._badge      = document.getElementById('notif-badge');
        this._panel      = document.getElementById('notif-panel');
        this._notifList  = document.getElementById('notif-list');
        this._emptyMsg   = document.getElementById('notif-empty');
        this._markAllBtn = document.getElementById('notif-mark-all');
        this._panelCount = document.getElementById('notif-panel-count');

        if (!this._cfg.isAuth) {
            this._setDotState('closed', this._i18n('guest', 'Guest'));
            return;
        }

        this._syncBadge();
        this._bindBell();
        this._bindMarkAll();
        this._bindOutsideClick();

        if (!this._cfg.wsAvailable || !this._cfg.url) {
            this._setDotState('closed', this._i18n('realtime_unavailable', 'Realtime unavailable'));
            this._startUnreadPolling();
            void this._refreshUnreadCount();
            return;
        }

        void this._connect();
    }

    subscribeRecordPresence(resourceKey, recordId, tenantId, callback) {
        const key = this._presenceKey(resourceKey, recordId, tenantId);
        const entry = fallback(this._presenceSubscriptions.get(key), {
            resourceKey,
            recordId,
            tenantId,
            callbacks: new Set(),
        });

        entry.callbacks.add(callback);
        this._presenceSubscriptions.set(key, entry);
        this._sendPresenceSubscription(entry);
    }

    unsubscribeRecordPresence(resourceKey, recordId, tenantId, callback = null) {
        const key = this._presenceKey(resourceKey, recordId, tenantId);
        const entry = this._presenceSubscriptions.get(key);

        if (!entry) {
            return;
        }

        if (callback) {
            entry.callbacks.delete(callback);
        } else {
            entry.callbacks.clear();
        }

        if (entry.callbacks.size > 0) {
            return;
        }

        this._presenceSubscriptions.delete(key);
        this._sendSocketAction({
            action: 'unsubscribe',
            tenant_id: tenantId,
            resource_key: resourceKey,
            record_id: recordId,
        });
    }

    currentUserId() {
        const userId = Number(fallback(this._cfg.userId, 0));
        return Number.isFinite(userId) && userId > 0 ? userId : null;
    }

    // --- WebSocket ------------------------------------------------------------

    async _connect() {
        if (this._destroyed) return;
        this._setDotState('connecting', this._i18n('connecting', 'Connecting…'));

        try {
            this._ws = new WebSocket(this._cfg.url);
        } catch {
            this._scheduleReconnect();
            return;
        }

        this._ws.addEventListener('open', () => {
            this._reconnectDelay = 2000;
            // Authenticate
            this._ws.send(JSON.stringify({ action: 'auth', token: this._cfg.token }));
        });

        this._ws.addEventListener('message', (e) => {
            let msg;
            try { msg = JSON.parse(e.data); } catch { return; }
            this._handleMessage(msg);
        });

        this._ws.addEventListener('close', () => {
            if (!this._destroyed) {
                this._setDotState('closed', this._i18n('disconnected', 'Disconnected'));
                this._scheduleReconnect();
            }
        });

        this._ws.addEventListener('error', () => {
            this._setDotState('error', this._i18n('error', 'Error'));
        });
    }

    _handleMessage(msg) {
        switch (msg.type) {
            case 'auth_ok':
            case 'authenticated':
                this._needsTokenRefresh = false;
                this._cfg.userId = Number(fallback(msg.user_id, fallback(this._cfg.userId, 0))) || null;
                this._cfg.tenantId = Number(fallback(msg.tenant_id, fallback(this._cfg.tenantId, 0))) || null;
                this._cfg.tenantKey = fallback(msg.tenant_key, fallback(this._cfg.tenantKey, ''));
                this._setDotState('connected', this._i18n('connected', 'Connected'));
                this._resubscribePresence();
                break;

            case 'auth_fail':
                // Token is invalid (tampered or structurally bad) — mark for refresh.
                // Server will close the connection; the close event will trigger reconnect.
                this._needsTokenRefresh = true;
                this._setDotState('error', this._i18n('auth_failed', 'Auth failed'));
                break;

            case 'error':
                // Server rejected the token (likely expired) and will close the connection.
                // Mark for refresh so the next reconnect fetches a new token first.
                this._needsTokenRefresh = true;
                this._setDotState('error', this._i18n('reconnecting', 'Reconnecting…'));
                break;

            case 'pong':
                break;

            case 'notification':
                this._onNewNotification(msg);
                break;

            case 'presence':
                this._dispatchPresence(msg);
                break;
        }
    }

    _scheduleReconnect() {
        if (this._destroyed) return;
        clearTimeout(this._reconnectTimer);
        this._reconnectTimer = setTimeout(async () => {
            this._reconnectDelay = Math.min(this._reconnectDelay * 1.5, 30000);

            if (this._needsTokenRefresh) {
                const ok = await this._refreshToken();
                if (!ok) return; // Session ended or server error — stop reconnecting
                this._needsTokenRefresh = false;
            }

            void this._connect();
        }, this._reconnectDelay);
    }

    /**
     * Fetch a fresh WS token from the server.
     * Returns true on success (this._cfg.token updated), false if the session ended.
     */
    async _refreshToken() {
        try {
            this._setDotState('connecting', this._i18n('refreshing', 'Refreshing…'));
            const { response: res, data: json } = await this._http.json('/runtime/websocket/token', {
                background: true,
            });

            if (res.status === 401 || res.status === 403) {
                // User is no longer authenticated — do not reconnect
                this._setDotState('closed', this._i18n('session_expired', 'Session expired'));
                return false;
            }

            if (!res.ok) return false;
            if (!json.success || !json.data || !json.data.token) return false;

            this._cfg.token = json.data.token;
            this._cfg.userId = Number(fallback(json.data.user_id, fallback(this._cfg.userId, 0))) || fallback(this._cfg.userId, null);
            this._cfg.tenantId = Number(fallback(json.data.tenant_id, fallback(this._cfg.tenantId, 0))) || fallback(this._cfg.tenantId, null);
            this._cfg.tenantKey = fallback(json.data.tenant_key, fallback(this._cfg.tenantKey, ''));
            return true;
        } catch {
            // Network error — will retry on next scheduleReconnect cycle
            return false;
        }
    }

    // --- Real-time notification received -------------------------------------

    _onNewNotification(msg) {
        const notificationType = fallback(msg.notif_type, fallback(msg.type, 'info'));

        this._unread++;
        this._syncBadge();
        this._updatePanelCount();

        // If panel is open, prepend the new item
        if (this._panelOpen && this._loaded) {
            const item = this._buildItem({
                id:         msg.id,
                notif_type: notificationType,
                title:      msg.title,
                body:       msg.body,
                created_at: msg.created_at,
                read_at:    null,
            });
            if (this._emptyMsg) {
                this._emptyMsg.classList.add('d-none');
            }
            if (this._notifList) {
                this._notifList.prepend(item);
            }
        }

        // Show toaster if Catalyst is available
        if (window.Catalyst) {
            window.Catalyst[notificationType === 'error' ? 'error'
                : notificationType === 'warning' ? 'warning'
                : notificationType === 'success' ? 'success'
                : 'info'](msg.title + (msg.body ? ': ' + msg.body : ''));
        }
    }

    _dispatchPresence(msg) {
        const key = this._presenceKey(
            String(fallback(msg.resource_key, '')),
            Number.parseInt(String(fallback(msg.record_id, '0')), 10),
            Number.parseInt(String(fallback(msg.tenant_id, '0')), 10)
        );
        const entry = this._presenceSubscriptions.get(key);

        if (!entry) {
            return;
        }

        entry.callbacks.forEach((callback) => {
            try {
                callback(msg);
            } catch {
                // Consumer errors must not break the shared realtime connection.
            }
        });
    }

    // --- Bell / panel ---------------------------------------------------------

    _bindBell() {
        if (!this._bellBtn) {
            return;
        }

        this._bellBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            this._togglePanel();
        });
    }

    _togglePanel() {
        this._panelOpen = !this._panelOpen;
        if (this._panel) {
            this._panel.classList.toggle('open', this._panelOpen);
        }

        if (this._panelOpen) {
            void this._loadNotifications();
        }
    }

    _bindOutsideClick() {
        document.addEventListener('click', (e) => {
            if (!this._panelOpen) return;
            if (this._panel && this._panel.contains(e.target)) return;
            if (this._bellBtn && this._bellBtn.contains(e.target)) return;
            this._panelOpen = false;
            if (this._panel) {
                this._panel.classList.remove('open');
            }
        });
    }

    // --- REST: load notifications ---------------------------------------------

    async _loadNotifications() {
        this._showLoading();

        try {
            const { data: json } = await this._http.json('/runtime/notifications?limit=30');

            if (!json.success) throw new Error('API error');

            const items = json.data && Array.isArray(json.data.notifications) ? json.data.notifications : [];
            this._unread = json.data ? fallback(json.data.unread_count, 0) : 0;
            this._syncBadge();
            this._updatePanelCount();
            this._renderList(items);
            this._loaded = true;
        } catch {
            this._showError();
        }
    }

    async _refreshUnreadCount() {
        try {
            const { data: json } = await this._http.json('/runtime/notifications/unread-count', {
                background: true,
            });
            if (!json.success) {
                return;
            }

            this._unread = json.data ? fallback(json.data.unread_count, 0) : 0;
            this._syncBadge();
            this._updatePanelCount();
        } catch {
            // REST fallback is best-effort only.
        }
    }

    _renderList(items) {
        if (!this._notifList) return;

        // Clear everything except _notif-empty
        Array.from(this._notifList.children).forEach(c => {
            if (c.id !== 'notif-empty') c.remove();
        });

        if (items.length === 0) {
            if (this._emptyMsg) {
                this._emptyMsg.classList.remove('d-none');
            }
            return;
        }

        if (this._emptyMsg) {
            this._emptyMsg.classList.add('d-none');
        }
        items.forEach(n => this._notifList.appendChild(this._buildItem(n)));
    }

    _buildItem(n) {
        const isUnread = !n.read_at;
        const notificationType = fallback(n.notif_type, fallback(n.type, 'info'));
        const icon     = fallback(STATUS_BAR_ICONS[notificationType], STATUS_BAR_ICONS.info);
        const time     = this._relativeTime(n.created_at);

        const el = document.createElement('div');
        el.className = `notif-item${isUnread ? ' unread' : ''}`;
        el.dataset.id = n.id;
        el.innerHTML = `
            <div class="notif-icon type-${notificationType}">
                <i class="${icon}"></i>
            </div>
            <div class="notif-content">
                <div class="notif-title">${this._esc(n.title)}</div>
                ${n.body ? `<div class="notif-body">${this._esc(n.body)}</div>` : ''}
                <div class="notif-time">${time}</div>
            </div>
        `;

        el.addEventListener('click', () => this._markRead(n.id, el));
        return el;
    }

    // --- REST: mark read -----------------------------------------------------

    async _markRead(id, el) {
        if (!el.classList.contains('unread')) return;

        try {
            const res = await this._http.request(`/runtime/notifications/${id}/read`, {
                method: 'POST',
            });
            if (!res.ok) return;

            el.classList.remove('unread');
            this._unread = Math.max(0, this._unread - 1);
            this._syncBadge();
            this._updatePanelCount();
        } catch { /* silent */ }
    }

    _bindMarkAll() {
        if (!this._markAllBtn) {
            return;
        }

        this._markAllBtn.addEventListener('click', async () => {
            try {
                await this._http.request('/runtime/notifications/read-all', {
                    method: 'POST',
                });
            } catch { /* silent */ }

            // Update UI regardless
            document.querySelectorAll('.notif-item.unread').forEach(el => el.classList.remove('unread'));
            this._unread = 0;
            this._syncBadge();
            this._updatePanelCount();
        });
    }

    // --- UI helpers ----------------------------------------------------------

    _syncBadge() {
        if (!this._badge) return;
        const count = this._unread;
        this._badge.textContent = count > 99 ? '99+' : count;
        this._badge.classList.toggle('d-none', count === 0);
    }

    _updatePanelCount() {
        if (!this._panelCount) return;
        this._panelCount.textContent = this._unread > 0 ? `(${this._unread} ${this._i18n('unread_suffix', 'unread')})` : '';
    }

    _setDotState(state, text) {
        if (!this._dot) return;
        this._dot.className = `status-dot ws-${state}`;
        if (!this._statusText) return;

        if (!this._cfg.isAuth) {
            this._statusText.textContent = text;
            return;
        }

        this._statusText.textContent = `${this._i18n('authenticated_prefix', 'Authenticated')} · ${text}`;
    }

    _startUnreadPolling() {
        if (this._unreadRefreshTimer !== null) {
            return;
        }

        this._unreadRefreshTimer = window.setInterval(() => {
            if (this._destroyed || !this._cfg.isAuth) {
                return;
            }

            void this._refreshUnreadCount();
        }, 30000);
    }

    _showLoading() {
        if (!this._notifList) return;
        Array.from(this._notifList.children).forEach(c => {
            if (c.id !== 'notif-empty') c.remove();
        });
        if (this._emptyMsg) {
            this._emptyMsg.classList.add('d-none');
        }

        const loader = document.createElement('div');
        loader.className = 'notif-loading';
        loader.id = 'notif-loader';
        loader.innerHTML = `<span class="spinner-border spinner-border-sm"></span> ${this._i18n('loading', 'Loading…')}`;
        this._notifList.appendChild(loader);
    }

    _showError() {
        const loader = document.getElementById('notif-loader');
        if (loader) {
            loader.remove();
        }
        if (!this._emptyMsg) return;
        this._emptyMsg.innerHTML = `<i class="ti ti-alert-triangle"></i><p>${this._i18n('failed_to_load', 'Failed to load')}</p>`;
        this._emptyMsg.classList.remove('d-none');
    }

    _resubscribePresence() {
        this._presenceSubscriptions.forEach((entry) => this._sendPresenceSubscription(entry));
    }

    _sendPresenceSubscription(entry) {
        this._sendSocketAction({
            action: 'subscribe',
            tenant_id: entry.tenantId,
            resource_key: entry.resourceKey,
            record_id: entry.recordId,
        });
    }

    _sendSocketAction(payload) {
        if (!this._ws || this._ws.readyState !== WebSocket.OPEN) {
            return;
        }

        this._ws.send(JSON.stringify(payload));
    }

    _presenceKey(resourceKey, recordId, tenantId) {
        return `${tenantId}:${resourceKey}:${recordId}`;
    }

    _relativeTime(dateStr) {
        if (!dateStr) return '';
        const diff = Math.floor((Date.now() - new Date(dateStr).getTime()) / 1000);
        if (diff < 60)   return 'Just now';
        if (diff < 3600) return `${Math.floor(diff / 60)}m ago`;
        if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`;
        return `${Math.floor(diff / 86400)}d ago`;
    }

    _esc(str) {
        if (!str) return '';
        return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    destroy() {
        this._destroyed = true;
        clearTimeout(this._reconnectTimer);
        if (this._unreadRefreshTimer !== null) {
            clearInterval(this._unreadRefreshTimer);
            this._unreadRefreshTimer = null;
        }
        this._presenceSubscriptions.clear();
        if (this._ws) {
            this._ws.close();
        }
    }
}
