function initLiveAlertDemo(root) {
    const alertPlaceholder = root.querySelector('#liveAlertPlaceholder');
    const alertTrigger = root.querySelector('#liveAlertBtn');
    if (!(alertPlaceholder instanceof HTMLElement) || !(alertTrigger instanceof HTMLButtonElement)) {
        return;
    }

    if (alertTrigger.__catalystLiveAlertBound) {
        return;
    }

    const appendAlert = (message, type) => {
        const wrapper = document.createElement('div');
        wrapper.innerHTML = [
            `<div class="alert alert-${type} alert-dismissible" role="alert">`,
            `   <div>${message}</div>`,
            '   <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>',
            '</div>',
        ].join('');
        alertPlaceholder.append(wrapper);
    };

    alertTrigger.__catalystLiveAlertBound = true;
    alertTrigger.addEventListener('click', () => {
        appendAlert('Nice, you triggered this alert message!', 'success');
    });
}

export function initBootstrapPrimitives(options = {}) {
    const root = options.root instanceof HTMLElement ? options.root : document.body;
    if (!(root instanceof HTMLElement)) {
        return;
    }

    initLiveAlertDemo(root);
}
