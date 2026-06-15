export function initCardActions(options = {}) {
    const root = options.root instanceof HTMLElement ? options.root : document.body;

    root.querySelectorAll('[data-action="card-close"]').forEach((trigger) => {
        if (trigger.dataset.catalystCardActionBound === '1') {
            return;
        }
        trigger.dataset.catalystCardActionBound = '1';
        trigger.addEventListener('click', (event) => {
            event.preventDefault();
            const card = trigger.closest('.card');
            if (!(card instanceof HTMLElement)) {
                return;
            }

            card.dataset.catalystClosing = 'true';
            window.setTimeout(() => {
                card.remove();
            }, 300);
        });
    });

    root.querySelectorAll('[data-action="card-toggle"]').forEach((trigger) => {
        if (trigger.dataset.catalystCardActionBound === '1') {
            return;
        }
        trigger.dataset.catalystCardActionBound = '1';
        trigger.addEventListener('click', (event) => {
            event.preventDefault();
            const card = trigger.closest('.card');
            if (!(card instanceof HTMLElement)) {
                return;
            }

            if (card.dataset.catalystCollapsed === 'true') {
                delete card.dataset.catalystCollapsed;
            } else {
                card.dataset.catalystCollapsed = 'true';
            }
        });
    });

    root.querySelectorAll('[data-action="code-collapse"]').forEach((trigger) => {
        if (trigger.dataset.catalystCardActionBound === '1') {
            return;
        }
        trigger.dataset.catalystCardActionBound = '1';
        trigger.addEventListener('click', (event) => {
            event.preventDefault();
            const card = trigger.closest('.card');
            if (!(card instanceof HTMLElement) || !card.querySelector('.code-body')) {
                return;
            }

            if (card.dataset.catalystCodeCollapsed === 'true') {
                delete card.dataset.catalystCodeCollapsed;
            } else {
                card.dataset.catalystCodeCollapsed = 'true';
            }
        });
    });

    root.querySelectorAll('[data-action="card-refresh"]').forEach((trigger) => {
        if (trigger.dataset.catalystCardActionBound === '1') {
            return;
        }
        trigger.dataset.catalystCardActionBound = '1';
        trigger.addEventListener('click', (event) => {
            event.preventDefault();
            const card = trigger.closest('.card');
            if (!(card instanceof HTMLElement)) {
                return;
            }

            let overlay = card.querySelector('.activity-overlay');
            if (!(overlay instanceof HTMLElement)) {
                overlay = document.createElement('div');
                overlay.className = 'activity-overlay';
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
