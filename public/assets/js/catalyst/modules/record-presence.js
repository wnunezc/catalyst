import { getHttpClient } from './http.js';

class RecordPresenceRuntime {
    #http = getHttpClient();
    #timers = new WeakMap();

    init() {
        document.querySelectorAll('[data-record-presence]').forEach((banner) => this.#bindBanner(banner));
    }

    #bindBanner(banner) {
        const resourceKey = String(banner.dataset.resourceKey ?? '').trim();
        const recordId = Number.parseInt(String(banner.dataset.recordId ?? '0'), 10);
        const tenantId = Number.parseInt(String(banner.dataset.tenantId ?? '0'), 10);
        const heartbeatUrl = String(banner.dataset.heartbeatUrl ?? '').trim();
        const isOwner = String(banner.dataset.isOwner ?? '0') === '1';

        if (!resourceKey || Number.isNaN(recordId) || recordId <= 0 || Number.isNaN(tenantId) || tenantId <= 0) {
            return;
        }

        const statusBar = window.CatalystStatusBar;
        if (statusBar && typeof statusBar.subscribeRecordPresence === 'function') {
            statusBar.subscribeRecordPresence(resourceKey, recordId, tenantId, (payload) => {
                this.#applyPresence(banner, payload, statusBar.currentUserId?.() ?? null);
            });
        }

        if (!isOwner || heartbeatUrl === '') {
            return;
        }

        void this.#heartbeat(banner, heartbeatUrl);

        const timer = window.setInterval(() => {
            void this.#heartbeat(banner, heartbeatUrl);
        }, 45000);

        this.#timers.set(banner, timer);
    }

    async #heartbeat(banner, url) {
        try {
            const { response, data } = await this.#http.json(url, { method: 'POST' });
            const presence = data?.data?.presence ?? null;

            if (presence) {
                this.#applyPresence(banner, presence, window.CatalystStatusBar?.currentUserId?.() ?? null);
            }

            if (!response.ok) {
                return;
            }
        } catch {
            // Best-effort only. Expiry semantics still protect the claim server-side.
        }
    }

    #applyPresence(banner, payload, currentUserId = null) {
        const claimedBy = Number.parseInt(String(payload?.claimed_by ?? '0'), 10);
        const isOwner = currentUserId !== null && !Number.isNaN(claimedBy) && claimedBy > 0 && claimedBy === Number(currentUserId);
        const status = String(payload?.status ?? 'released');
        const claimedByLabel = String(payload?.claimed_by_label ?? '').trim();
        const claimedAt = String(payload?.claimed_at ?? '').trim();
        const expiresAt = String(payload?.expires_at ?? '').trim();
        const releasedAt = String(payload?.released_at ?? '').trim();

        const alertClass = status === 'released'
            ? 'alert-secondary'
            : status === 'expired'
                ? 'alert-warning'
                : isOwner
                    ? 'alert-info'
                    : 'alert-danger';

        const headline = status === 'released'
            ? 'Claim released'
            : status === 'expired'
                ? 'Claim expired'
                : isOwner
                    ? 'Active editing claim'
                    : 'Claim owned by another actor';

        const detail = status === 'released'
            ? 'The last claim for this record has already been released.'
            : status === 'expired'
                ? 'The last claim expired and can be reclaimed on the next protected mutation.'
                : isOwner
                    ? 'This record is reserved for your current editing session.'
                    : `This record is currently reserved by ${claimedByLabel || 'another actor'}.`;

        banner.classList.remove('alert-secondary', 'alert-warning', 'alert-info', 'alert-danger');
        banner.classList.add(alertClass);
        banner.dataset.isOwner = isOwner ? '1' : '0';

        this.#setText(banner, '[data-presence-headline]', headline);
        this.#setText(banner, '[data-presence-detail]', detail);
        this.#setTimestamp(banner, '[data-presence-claimed-at]', 'Claimed', claimedAt);
        this.#setTimestamp(banner, '[data-presence-expires-at]', 'Expires', expiresAt);
        this.#setTimestamp(banner, '[data-presence-released-at]', 'Released', releasedAt);
    }

    #setText(root, selector, value) {
        const node = root.querySelector(selector);
        if (node) {
            node.textContent = value;
        }
    }

    #setTimestamp(root, selector, label, value) {
        const node = root.querySelector(selector);
        if (!node) {
            return;
        }

        if (value === '') {
            node.classList.add('d-none');
            node.textContent = '';
            return;
        }

        node.classList.remove('d-none');
        node.textContent = `${label}: ${value}`;
    }
}

const runtime = new RecordPresenceRuntime();

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => runtime.init(), { once: true });
} else {
    runtime.init();
}
