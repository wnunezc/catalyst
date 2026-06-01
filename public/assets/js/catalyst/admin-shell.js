document.addEventListener('DOMContentLoaded', () => {
    const html = document.documentElement;
    const body = document.body;

    const keepSidebarFixed = () => {
        html.setAttribute('data-sidenav-size', 'default');
        html.classList.remove('sidebar-enable');
        body.classList.remove('catalyst-admin-sidebar-open', 'migration-ui-no-scroll');

        try {
            window.sessionStorage.removeItem('catalyst-admin-sidebar-size');
        } catch (_) {}
    };

    keepSidebarFixed();
    window.addEventListener('resize', keepSidebarFixed, { passive: true });

    document.querySelectorAll('[data-nav-toggle]').forEach((button) => {
        const targetId = button.getAttribute('data-nav-target');
        const panel = targetId ? document.getElementById(targetId) : null;
        if (!(panel instanceof HTMLElement)) {
            return;
        }

        button.addEventListener('click', () => {
            const open = button.getAttribute('aria-expanded') !== 'true';
            button.setAttribute('aria-expanded', open ? 'true' : 'false');
            panel.hidden = !open;
            panel.classList.toggle('is-open', open);
            button.classList.toggle('is-open', open);
            button.closest('.side-nav-group, .side-nav-item')?.classList.toggle('is-open', open);
        });
    });
});
