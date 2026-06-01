const DROPDOWN_TOGGLE_SELECTOR = '[data-bs-toggle="dropdown"]';
const BOUND_ATTR = 'data-catalyst-dropdown-bound';
const MANUAL_BOUND_ATTR = 'data-catalyst-dropdown-manual-bound';

function resolveDropdownMenu(toggle) {
    const siblingMenu = toggle.nextElementSibling;
    if (siblingMenu instanceof HTMLElement && siblingMenu.classList.contains('dropdown-menu')) {
        return siblingMenu;
    }

    const dropdown = toggle.closest('.dropdown');
    if (!(dropdown instanceof HTMLElement)) {
        return null;
    }

    return dropdown.querySelector('.dropdown-menu');
}

function closeDropdown(toggle, menu) {
    toggle.setAttribute('aria-expanded', 'false');
    menu.classList.remove('show');
    const dropdown = toggle.closest('.dropdown');
    dropdown?.classList.remove('show');
}

function openDropdown(toggle, menu) {
    toggle.setAttribute('aria-expanded', 'true');
    menu.setAttribute('data-bs-popper', 'static');
    menu.classList.add('show');
    const dropdown = toggle.closest('.dropdown');
    dropdown?.classList.add('show');
}

function closeSiblingFallbackDropdowns(root, exceptToggle = null) {
    root.querySelectorAll(DROPDOWN_TOGGLE_SELECTOR).forEach((node) => {
        if (!(node instanceof HTMLElement) || node === exceptToggle) {
            return;
        }

        const menu = resolveDropdownMenu(node);
        if (menu instanceof HTMLElement) {
            closeDropdown(node, menu);
        }
    });
}

function bindManualFallback(toggle, root) {
    if (toggle.hasAttribute(MANUAL_BOUND_ATTR)) {
        return;
    }

    toggle.setAttribute(MANUAL_BOUND_ATTR, 'true');
    toggle.addEventListener('click', (event) => {
        event.preventDefault();
        event.stopPropagation();

        const menu = resolveDropdownMenu(toggle);
        if (!(menu instanceof HTMLElement)) {
            return;
        }

        const willOpen = !menu.classList.contains('show');
        closeSiblingFallbackDropdowns(root, willOpen ? toggle : null);

        if (willOpen) {
            openDropdown(toggle, menu);
            return;
        }

        closeDropdown(toggle, menu);
    });
}

function ensureDocumentFallbackListeners() {
    if (document.documentElement.hasAttribute('data-catalyst-dropdown-document-bound')) {
        return;
    }

    document.documentElement.setAttribute('data-catalyst-dropdown-document-bound', 'true');
    document.addEventListener('click', (event) => {
        const target = event.target;
        if (target instanceof Element && target.closest('.dropdown-menu, [data-bs-toggle="dropdown"]')) {
            return;
        }

        closeSiblingFallbackDropdowns(document.body);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeSiblingFallbackDropdowns(document.body);
        }
    });
}

export function initDropdowns(options = {}) {
    const root = options.root instanceof HTMLElement ? options.root : document.body;
    if (!(root instanceof HTMLElement)) {
        return;
    }

    const bootstrapDropdown = window.bootstrap?.Dropdown ?? null;
    const toggles = Array.from(root.querySelectorAll(DROPDOWN_TOGGLE_SELECTOR))
        .filter((node) => node instanceof HTMLElement);

    toggles.forEach((toggle) => {
        if (toggle.hasAttribute(BOUND_ATTR)) {
            return;
        }

        toggle.setAttribute(BOUND_ATTR, 'true');

        if (bootstrapDropdown?.getOrCreateInstance) {
            bootstrapDropdown.getOrCreateInstance(toggle);
            return;
        }

        bindManualFallback(toggle, root);
    });

    if (!bootstrapDropdown?.getOrCreateInstance) {
        ensureDocumentFallbackListeners();
    }
}
