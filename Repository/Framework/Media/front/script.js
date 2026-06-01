(() => {
    document.querySelectorAll('[data-confirm]').forEach((element) => {
        element.addEventListener('submit', (event) => {
            const message = element.getAttribute('data-confirm');
            if (message && !window.confirm(message)) {
                event.preventDefault();
            }
        });

        element.addEventListener('click', (event) => {
            const target = event.target;
            if (!(target instanceof HTMLElement)) {
                return;
            }

            const button = target.closest('[data-confirm]');
            if (!(button instanceof HTMLElement) || button.tagName === 'FORM') {
                return;
            }

            const message = button.getAttribute('data-confirm');
            if (message && !window.confirm(message)) {
                event.preventDefault();
            }
        });
    });
})();
