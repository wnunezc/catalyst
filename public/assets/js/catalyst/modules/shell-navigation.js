function setCollapseState(element, expanded) {
    element.classList.toggle('show', expanded);

    if (expanded) {
        element.removeAttribute('hidden');
        return;
    }

    element.setAttribute('hidden', 'hidden');
}

function resetDescendantCollapses(root) {
    root.querySelectorAll('.collapse').forEach((node) => {
        if (!(node instanceof HTMLElement)) {
            return;
        }

        setCollapseState(node, false);
        const item = node.closest('.side-nav-item');
        const toggle = item?.querySelector(':scope > .side-nav-link');
        toggle?.setAttribute('aria-expanded', 'false');
    });
}

export function applyShellMenuState(options = {}) {
    const root = options.root instanceof HTMLElement ? options.root : document.body;
    const defaultDoc = typeof options.defaultDoc === 'string' && options.defaultDoc !== ''
        ? options.defaultDoc
        : 'ui-alerts.html';
    const current = new URL(window.location.href);
    const menuLinks = Array.from(root.querySelectorAll('.sidenav-menu .side-nav a.side-nav-link[href]'));
    const routeableLinks = menuLinks.filter((link) => {
        const href = link.getAttribute('href') ?? '';
        return href !== '' && href !== '#!' && !href.startsWith('#');
    });

    if (routeableLinks.length === 0) {
        return;
    }

    const activeLinks = routeableLinks.filter((link) => {
        const href = link.getAttribute('href') ?? '';
        const target = new URL(href, window.location.origin);
        const targetDoc = target.searchParams.get('doc') ?? defaultDoc;
        const currentDoc = current.searchParams.get('doc') ?? defaultDoc;

        if (target.pathname !== current.pathname) {
            return false;
        }

        if (target.search !== '' || current.search !== '') {
            return targetDoc === currentDoc;
        }

        return true;
    });

    if (activeLinks.length === 0) {
        return;
    }

    root.querySelectorAll('.sidenav-menu .side-nav .active').forEach((node) => {
        node.classList.remove('active');
    });

    root.querySelectorAll('.sidenav-menu .side-nav .collapse').forEach((node) => {
        if (!(node instanceof HTMLElement)) {
            return;
        }

        setCollapseState(node, false);
    });

    activeLinks.forEach((link) => {
        link.classList.add('active');
        const item = link.closest('.side-nav-item');
        item?.classList.add('active');

        let parentCollapse = link.closest('.collapse');
        while (parentCollapse instanceof HTMLElement) {
            setCollapseState(parentCollapse, true);
            const parentItem = parentCollapse.closest('.side-nav-item');
            parentItem?.classList.add('active');
            const parentToggle = parentItem?.querySelector(':scope > .side-nav-link');
            if (parentToggle instanceof HTMLElement) {
                parentToggle.classList.add('active');
                parentToggle.setAttribute('aria-expanded', 'true');
            }
            parentCollapse = parentItem?.closest('.collapse') ?? null;
        }
    });
}

export function initShellSidebar(options = {}) {
    const html = document.documentElement;
    const body = document.body;
    const noScrollClass = typeof options.noScrollClass === 'string' && options.noScrollClass !== ''
        ? options.noScrollClass
        : 'migration-ui-no-scroll';

    const keepSidebarFixed = () => {
        html.setAttribute('data-sidenav-size', 'default');
        html.classList.remove('sidebar-enable');
        body.classList.remove(noScrollClass);

        try {
            window.sessionStorage.removeItem('catalyst-admin-sidebar-size');
        } catch (_) {}
    };

    keepSidebarFixed();
    window.addEventListener('resize', keepSidebarFixed, { passive: true });
}

export function initShellSectionCollapses(options = {}) {
    const root = options.root instanceof HTMLElement ? options.root : document.body;
    const collapseToggles = root.querySelectorAll('.sidenav-menu [data-shell-collapse="true"][href^="#"]');

    collapseToggles.forEach((toggle) => {
        toggle.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            if (!(toggle instanceof HTMLElement)) {
                return;
            }

            const targetSelector = toggle.getAttribute('href');
            if (!targetSelector) {
                return;
            }

            const target = root.querySelector(targetSelector);
            if (!(target instanceof HTMLElement)) {
                return;
            }

            const nextState = !target.classList.contains('show');
            const siblingCollapses = target.parentElement?.parentElement?.querySelectorAll(':scope > .side-nav-item > .collapse.show') ?? [];

            siblingCollapses.forEach((sibling) => {
                if (!(sibling instanceof HTMLElement) || sibling === target) {
                    return;
                }

                resetDescendantCollapses(sibling);
                setCollapseState(sibling, false);
                const siblingItem = sibling.closest('.side-nav-item');
                const siblingToggle = siblingItem?.querySelector(':scope > .side-nav-link');
                siblingItem?.classList.remove('active');
                siblingToggle?.setAttribute('aria-expanded', 'false');
            });

            if (!nextState) {
                resetDescendantCollapses(target);
            }

            setCollapseState(target, nextState);
            toggle.setAttribute('aria-expanded', nextState ? 'true' : 'false');
        });
    });
}

export function initShellInertNavLinks(options = {}) {
    const root = options.root instanceof HTMLElement ? options.root : document.body;

    root.querySelectorAll('.sidenav-menu a.side-nav-link[href="#!"]').forEach((link) => {
        link.addEventListener('click', (event) => {
            event.preventDefault();
        });
    });
}

export function initShellNavigation(options = {}) {
    applyShellMenuState(options);
    initShellSidebar(options);
    initShellSectionCollapses(options);
    initShellInertNavLinks(options);
}
