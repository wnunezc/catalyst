export function initCardActions(options = {}) {
    const root = options.root instanceof HTMLElement ? options.root : document.body;

    root.querySelectorAll('[data-action="card-close"]').forEach((trigger) => {
        trigger.addEventListener('click', (event) => {
            event.preventDefault();
            const card = trigger.closest('.card');
            if (!(card instanceof HTMLElement)) {
                return;
            }

            card.classList.add('migration-ui-card-closing');
            window.setTimeout(() => {
                card.remove();
            }, 300);
        });
    });

    root.querySelectorAll('[data-action="card-toggle"]').forEach((trigger) => {
        trigger.addEventListener('click', (event) => {
            event.preventDefault();
            const card = trigger.closest('.card');
            if (!(card instanceof HTMLElement)) {
                return;
            }

            card.classList.toggle('migration-ui-card-collapsed');
        });
    });

    root.querySelectorAll('[data-action="code-collapse"]').forEach((trigger) => {
        trigger.addEventListener('click', (event) => {
            event.preventDefault();
            const card = trigger.closest('.card');
            if (!(card instanceof HTMLElement) || !card.querySelector('.code-body')) {
                return;
            }

            card.classList.toggle('migration-ui-code-collapsed');
        });
    });

    root.querySelectorAll('[data-action="card-refresh"]').forEach((trigger) => {
        trigger.addEventListener('click', (event) => {
            event.preventDefault();
            const card = trigger.closest('.card');
            if (!(card instanceof HTMLElement)) {
                return;
            }

            let overlay = card.querySelector('.card-overlay');
            if (!(overlay instanceof HTMLElement)) {
                overlay = document.createElement('div');
                overlay.className = 'card-overlay';
                overlay.innerHTML = '<div class="spinner-border text-primary" role="status" aria-hidden="true"></div>';
                card.appendChild(overlay);
            }

            card.classList.add('is-refreshing');
            window.setTimeout(() => {
                card.classList.remove('is-refreshing');
            }, 1500);
        });
    });
}

