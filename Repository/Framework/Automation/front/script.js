/**
 * Contract:
 * - Init: DOMContentLoaded on Automation admin views.
 * - DOM: forms with `data-confirm` only.
 * - Events/Payload: intercepts submit and reads the confirm message string.
 * - CSP: no inline handlers; uses native `window.confirm`.
 */
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            const message = form.getAttribute('data-confirm');
            if (message && !window.confirm(message)) {
                event.preventDefault();
            }
        });
    });
});
