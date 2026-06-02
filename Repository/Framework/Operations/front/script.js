/**
 * Contract:
 * - Init: DOMContentLoaded on Operations admin views.
 * - DOM: `[data-confirm]` forms and `.operations-theme-card` radio wrappers.
 * - Events/Payload: confirms submits and mirrors selected theme radio state.
 * - CSP: class-only DOM mutation, no inline style or dynamic HTML.
 */
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-confirm]').forEach((element) => {
        element.addEventListener('submit', (event) => {
            const message = element.getAttribute('data-confirm');
            if (message && !window.confirm(message)) {
                event.preventDefault();
            }
        });
    });

    const themeCards = Array.from(document.querySelectorAll('.operations-theme-card'));
    const syncThemeCards = () => {
        themeCards.forEach((card) => {
            const input = card.querySelector('input[type="radio"]');
            card.classList.toggle('is-selected', Boolean(input && input.checked));
        });
    };

    themeCards.forEach((card) => {
        card.addEventListener('click', () => {
            const input = card.querySelector('input[type="radio"]');
            if (input) {
                input.checked = true;
                syncThemeCards();
            }
        });
    });

    syncThemeCards();
});
