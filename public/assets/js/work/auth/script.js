import { registerUiComponent } from '../../catalyst/runtime/registration-queue.js';

const QR_CODE_SCRIPT_SRC = 'https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js';

async function initMfaQrCode(root) {
    const qrRoot = root.querySelector('#mfa-qr');
    if (!(qrRoot instanceof HTMLElement)) {
        return;
    }

    const uri = qrRoot.dataset.qrUri || '';
    if (uri === '') {
        return;
    }

    await ensureExternalScript(QR_CODE_SCRIPT_SRC, 'catalyst-auth-qrcode');

    if (typeof window.QRCode !== 'function') {
        console.error('[auth] QRCode runtime unavailable after script load.');
        return;
    }

    qrRoot.innerHTML = '';

    new window.QRCode(qrRoot, {
        text: uri,
        width: 200,
        height: 200,
        colorDark: '#000000',
        colorLight: '#ffffff',
        correctLevel: window.QRCode.CorrectLevel.M,
    });
}

registerUiComponent({
    name: 'auth.mfa-qr-code',
    phase: 'scan',
    selector: '#mfa-qr[data-qr-uri]',
    mount: initMfaQrCode,
});

function ensureExternalScript(src, datasetKey) {
    const existing = document.querySelector(`script[data-external-script="${datasetKey}"]`);

    if (existing) {
        return existing.dataset.loaded === 'true'
            ? Promise.resolve()
            : waitForScript(existing);
    }

    const script = document.createElement('script');
    script.src = src;
    script.async = true;
    script.dataset.externalScript = datasetKey;
    script.dataset.loaded = 'false';

    document.head.appendChild(script);

    return waitForScript(script);
}

function waitForScript(script) {
    return new Promise((resolve, reject) => {
        if (script.dataset.loaded === 'true') {
            resolve();
            return;
        }

        script.addEventListener('load', () => {
            script.dataset.loaded = 'true';
            resolve();
        }, { once: true });

        script.addEventListener('error', () => {
            reject(new Error(`Failed to load external script: ${script.src}`));
        }, { once: true });
    });
}
