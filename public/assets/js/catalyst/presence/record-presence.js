import { getHttpClient } from '../core/http.js';

const instances = new WeakMap();
const HEARTBEAT_INTERVAL_MS = 60000;

function elements(root) {
    const matches = Array.from(root.querySelectorAll('[data-record-presence]'));
    if (root.matches?.('[data-record-presence]')) {
        matches.unshift(root);
    }
    return matches.filter((element) => element instanceof HTMLElement);
}

function setTimestamp(element, selector, label, value) {
    const target = element.querySelector(selector);
    if (!(target instanceof HTMLElement)) {
        return;
    }

    target.textContent = value ? `${label} ${value}` : '';
    target.classList.toggle('d-none', !value);
}

function resolveOwner(instance, payload) {
    const claimedBy = Number.parseInt(String(payload.claimed_by ?? '0'), 10);
    return instance.currentUserId > 0 && claimedBy === instance.currentUserId;
}

function stopHeartbeat(instance) {
    if (instance.heartbeatTimer !== null) {
        clearInterval(instance.heartbeatTimer);
        instance.heartbeatTimer = null;
    }
}

async function heartbeat(instance) {
    if (instance.heartbeatUrl === '') {
        return;
    }

    try {
        const { data } = await getHttpClient().json(instance.heartbeatUrl, {
            background: true,
            method: 'POST',
            json: {},
        });
        const payload = data?.data?.presence ?? data?.presence ?? null;
        if (payload && typeof payload === 'object') {
            applyPayload(instance, payload);
        }
    } catch (error) {
        console.warn('[Catalyst RecordPresence] Heartbeat failed.', error);
    }
}

function ensureHeartbeat(instance, isOwner) {
    if (!isOwner || instance.heartbeatUrl === '') {
        stopHeartbeat(instance);
        return;
    }
    if (instance.heartbeatTimer !== null) {
        return;
    }

    void heartbeat(instance);
    instance.heartbeatTimer = setInterval(() => {
        void heartbeat(instance);
    }, HEARTBEAT_INTERVAL_MS);
}

function applyPayload(instance, payload) {
    const { element } = instance;
    const status = String(payload.status ?? 'released');
    const isOwner = status === 'active' && resolveOwner(instance, payload);
    const actor = String(payload.claimed_by_label ?? '').trim()
        || element.dataset.fallbackActor
        || '';
    const headline = element.querySelector('[data-presence-headline]');
    const detail = element.querySelector('[data-presence-detail]');

    element.classList.remove('alert-info', 'alert-danger', 'alert-warning', 'alert-secondary');
    element.classList.add(
        status === 'released'
            ? 'alert-secondary'
            : status === 'expired'
                ? 'alert-warning'
                : isOwner ? 'alert-info' : 'alert-danger'
    );
    element.dataset.isOwner = isOwner ? '1' : '0';

    if (headline instanceof HTMLElement) {
        headline.textContent = status === 'released'
            ? element.dataset.releasedLabel ?? ''
            : status === 'expired'
                ? element.dataset.expiredLabel ?? ''
                : isOwner
                    ? element.dataset.activeOwnerLabel ?? ''
                    : element.dataset.activeOtherLabel ?? '';
    }

    if (detail instanceof HTMLElement) {
        detail.textContent = status === 'released'
            ? element.dataset.releasedDescription ?? ''
            : status === 'expired'
                ? element.dataset.expiredDescription ?? ''
                : isOwner
                    ? element.dataset.ownerDescription ?? ''
                    : (element.dataset.otherDescriptionTemplate ?? '__ACTOR__').replace('__ACTOR__', actor);
    }

    setTimestamp(
        element,
        '[data-presence-claimed-at]',
        element.dataset.claimedAtLabel ?? '',
        String(payload.claimed_at ?? '')
    );
    setTimestamp(
        element,
        '[data-presence-expires-at]',
        element.dataset.expiresAtLabel ?? '',
        String(payload.expires_at ?? '')
    );
    setTimestamp(
        element,
        '[data-presence-released-at]',
        element.dataset.releasedAtLabel ?? '',
        String(payload.released_at ?? '')
    );
    ensureHeartbeat(instance, isOwner);
}

function initialize(element) {
    if (instances.has(element)) {
        return instances.get(element);
    }

    const statusBar = window.CatalystStatusBar;
    const instance = {
        element,
        statusBar,
        tenantId: Number.parseInt(element.dataset.tenantId ?? '0', 10),
        resourceKey: element.dataset.resourceKey ?? '',
        recordId: Number.parseInt(element.dataset.recordId ?? '0', 10),
        currentUserId: statusBar?.currentUserId?.()
            ?? Number.parseInt(element.dataset.ownerActorId ?? '0', 10),
        heartbeatUrl: element.getAttribute('data-heartbeat-url') ?? '',
        heartbeatTimer: null,
        callback: null,
    };

    instance.callback = (payload) => applyPayload(instance, payload);
    instances.set(element, instance);

    if (
        statusBar
        && instance.tenantId > 0
        && instance.resourceKey !== ''
        && instance.recordId > 0
    ) {
        statusBar.subscribeRecordPresence(
            instance.resourceKey,
            instance.recordId,
            instance.tenantId,
            instance.callback
        );
    }

    ensureHeartbeat(instance, element.dataset.isOwner === '1');
    return instance;
}

function destroy(element) {
    const instance = instances.get(element);
    if (!instance) {
        return;
    }

    stopHeartbeat(instance);
    instance.statusBar?.unsubscribeRecordPresence?.(
        instance.resourceKey,
        instance.recordId,
        instance.tenantId,
        instance.callback
    );
    instances.delete(element);
}

export function initRecordPresence(options = {}) {
    const root = options.root instanceof HTMLElement ? options.root : document.body;
    if (!(root instanceof HTMLElement)) {
        return null;
    }

    elements(root).forEach(initialize);
    return { root };
}

export function destroyRecordPresence(options = {}) {
    const root = options.root instanceof HTMLElement ? options.root : document.body;
    if (!(root instanceof HTMLElement)) {
        return;
    }

    elements(root).forEach(destroy);
}
