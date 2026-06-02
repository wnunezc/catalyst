/**
 * Contract:
 * - Init: DOMContentLoaded on Catalog admin views.
 * - DOM: `.catalogs-admin-page code` nodes.
 * - Events/Payload: no events or server payload; adds a wrapping utility class.
 * - CSP: class-only DOM mutation, no inline style or dynamic HTML.
 */
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.catalogs-admin-page code').forEach((node) => {
        node.classList.add('text-break');
    });
});
