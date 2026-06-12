import { loadScript } from '../core/asset-loader.js';

const BOOTSTRAP_BUNDLE_URL = '/assets/vendor/bootstrap/js/bootstrap.bundle.min.js';
const hoistedOverlaysByRoot = new WeakMap();

function detectRequirements(root) {
    return {
        bundle: root.querySelector(
            '.accordion, .carousel, .modal, .offcanvas, [data-bs-spy="scroll"], ' +
            '[data-bs-toggle="collapse"], [data-bs-toggle="modal"], [data-bs-toggle="offcanvas"], ' +
            '[data-bs-toggle="popover"], [data-bs-toggle="tooltip"], [data-bs-toggle="tab"], [data-bs-toggle="pill"], [data-bs-toggle="dropdown"]'
        ) !== null,
        tooltips: root.querySelector('[data-bs-toggle="tooltip"]') !== null,
        popovers: root.querySelector('[data-bs-toggle="popover"]') !== null,
        tabs: root.querySelector('[data-bs-toggle="tab"], [data-bs-toggle="pill"]') !== null,
        scrollspy: root.querySelector('[data-bs-spy="scroll"]') !== null,
        carousel: root.querySelector('.carousel') !== null,
        modals: root.querySelector('.modal, [data-bs-toggle="modal"]') !== null,
        offcanvas: root.querySelector('.offcanvas, [data-bs-toggle="offcanvas"]') !== null,
        dropdowns: root.querySelector('[data-bs-toggle="dropdown"]') !== null,
    };
}

async function ensureBootstrapBundle() {
    if (window.bootstrap) {
        return window.bootstrap;
    }

    await loadScript(BOOTSTRAP_BUNDLE_URL);

    if (!window.bootstrap) {
        throw new Error('Bootstrap bundle did not expose window.bootstrap');
    }

    return window.bootstrap;
}

function initTooltips(root, bootstrapApi) {
    root.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((element) => {
        bootstrapApi.Tooltip.getOrCreateInstance(element);
    });
}

function initPopovers(root, bootstrapApi) {
    root.querySelectorAll('[data-bs-toggle="popover"]').forEach((element) => {
        bootstrapApi.Popover.getOrCreateInstance(element);
    });
}

function initTabs(root, bootstrapApi) {
    root.querySelectorAll('[data-bs-toggle="tab"], [data-bs-toggle="pill"]').forEach((element) => {
        const instance = bootstrapApi.Tab.getOrCreateInstance(element);
        if (element.__catalystBootstrapTabBound) {
            return;
        }

        element.__catalystBootstrapTabBound = true;
        element.addEventListener('click', (event) => {
            if (!(element instanceof HTMLElement)) {
                return;
            }

            if (element.classList.contains('disabled') || element.getAttribute('aria-disabled') === 'true') {
                event.preventDefault();
                return;
            }

            event.preventDefault();
            instance.show();
        });
    });
}

function initScrollSpy(root, bootstrapApi) {
    root.querySelectorAll('[data-bs-spy="scroll"]').forEach((element) => {
        const existing = bootstrapApi.ScrollSpy.getInstance(element);
        existing?.dispose();
        bootstrapApi.ScrollSpy.getOrCreateInstance(element);
    });
}

function initCarousels(root, bootstrapApi) {
    root.querySelectorAll('.carousel').forEach((element) => {
        bootstrapApi.Carousel.getOrCreateInstance(element);
    });
}

function parseBootstrapOption(value, fallback) {
    if (typeof value !== 'string' || value === '') {
        return fallback;
    }

    if (value === 'true') {
        return true;
    }

    if (value === 'false') {
        return false;
    }

    return value;
}

function cleanupManagedArtifacts() {
    document.querySelectorAll('[data-catalyst-managed-modal-backdrop], [data-catalyst-managed-offcanvas-backdrop]').forEach((node) => {
        node.remove();
    });
}

function cleanupOrphanBackdrops() {
    const hasOpenModal = document.querySelector('.modal.show') !== null;
    const hasOpenOffcanvas = document.querySelector('.offcanvas.show') !== null;

    if (!hasOpenModal) {
        document.querySelectorAll('.modal-backdrop').forEach((node) => node.remove());
    }

    if (!hasOpenOffcanvas) {
        document.querySelectorAll('.offcanvas-backdrop').forEach((node) => node.remove());
    }

    if (hasOpenModal) {
        document.body.classList.add('modal-open');
    }

    if (!hasOpenModal && !hasOpenOffcanvas) {
        document.body.classList.remove('modal-open');
        document.body.style.removeProperty('overflow');
        document.body.style.removeProperty('padding-right');
    }
}

function uniqueElements(elements) {
    return Array.from(new Set(elements.filter((element) => element instanceof HTMLElement)));
}

function hoistShellOverlays(root, selector, markerName) {
    const marker = `data-catalyst-${markerName}-hoisted`;
    root.querySelectorAll(selector).forEach((element) => {
        if (!(element instanceof HTMLElement)) {
            return;
        }

        if (element.parentElement === document.body || element.hasAttribute(marker) || element.hasAttribute('data-catalyst-no-hoist')) {
            return;
        }

        if (element.id === 'theme-settings-offcanvas') {
            return;
        }

        document.body.appendChild(element);
        element.setAttribute(marker, '1');

        const ownedOverlays = hoistedOverlaysByRoot.get(root) ?? new Set();
        ownedOverlays.add(element);
        hoistedOverlaysByRoot.set(root, ownedOverlays);
    });
}

function collectOverlayElements(root, selector, markerName) {
    const marker = `data-catalyst-${markerName}-hoisted`;
    return uniqueElements([
        ...root.querySelectorAll(selector),
        ...document.querySelectorAll(`${selector}[${marker}="1"]`),
    ]);
}

function initModals(root, bootstrapApi) {
    cleanupManagedArtifacts();
    hoistShellOverlays(root, '.modal', 'modal');

    collectOverlayElements(root, '.modal', 'modal').forEach((element) => {
        bootstrapApi.Modal.getOrCreateInstance(element, {
            backdrop: parseBootstrapOption(element.dataset.bsBackdrop, true),
            keyboard: parseBootstrapOption(element.dataset.bsKeyboard, true) !== false,
            focus: true,
        });

        if (element.__catalystBootstrapModalCleanupBound) {
            return;
        }

        element.__catalystBootstrapModalCleanupBound = true;
        element.addEventListener('hidden.bs.modal', cleanupOrphanBackdrops);
    });

}

function initDropdownComponents(root, bootstrapApi) {
    if (!bootstrapApi.Dropdown?.getOrCreateInstance) {
        return;
    }

    root.querySelectorAll('[data-bs-toggle="dropdown"]').forEach((element) => {
        bootstrapApi.Dropdown.getOrCreateInstance(element);
    });
}

function initOffcanvas(root, bootstrapApi) {
    cleanupManagedArtifacts();
    hoistShellOverlays(root, '.offcanvas', 'offcanvas');

    collectOverlayElements(root, '.offcanvas', 'offcanvas').forEach((element) => {
        if (element.id === 'theme-settings-offcanvas') {
            return;
        }

        bootstrapApi.Offcanvas.getOrCreateInstance(element, {
            backdrop: parseBootstrapOption(element.dataset.bsBackdrop, true),
            keyboard: parseBootstrapOption(element.dataset.bsKeyboard, true) !== false,
            scroll: element.dataset.bsScroll === 'true',
        });

        if (element.__catalystBootstrapOffcanvasCleanupBound) {
            return;
        }

        element.__catalystBootstrapOffcanvasCleanupBound = true;
        element.addEventListener('hidden.bs.offcanvas', cleanupOrphanBackdrops);
    });

    root.querySelectorAll('[data-bs-toggle="offcanvas"]').forEach((trigger) => {
        if (trigger.__catalystBootstrapOffcanvasBound) {
            return;
        }

        trigger.__catalystBootstrapOffcanvasBound = true;
        trigger.addEventListener('click', () => {
            cleanupManagedArtifacts();
            cleanupOrphanBackdrops();
        }, { capture: true });
    });
}

export async function initBootstrapComponents(options = {}) {
    const root = options.root instanceof HTMLElement ? options.root : document.body;
    if (!(root instanceof HTMLElement)) {
        return;
    }

    const requirements = detectRequirements(root);
    if (!requirements.bundle) {
        return;
    }

    const bootstrapApi = await ensureBootstrapBundle();

    if (requirements.carousel) {
        initCarousels(root, bootstrapApi);
    }

    if (requirements.modals) {
        initModals(root, bootstrapApi);
    }

    if (requirements.offcanvas) {
        initOffcanvas(root, bootstrapApi);
    }

    if (requirements.dropdowns) {
        initDropdownComponents(root, bootstrapApi);
    }

    if (requirements.tabs) {
        initTabs(root, bootstrapApi);
    }

    if (requirements.tooltips) {
        initTooltips(root, bootstrapApi);
    }

    if (requirements.popovers) {
        initPopovers(root, bootstrapApi);
    }

    if (requirements.scrollspy) {
        initScrollSpy(root, bootstrapApi);
    }
}

export function destroyBootstrapComponents(options = {}) {
    const root = options.root instanceof HTMLElement ? options.root : document.body;
    const bootstrapApi = window.bootstrap;
    if (!(root instanceof HTMLElement) || !bootstrapApi) {
        return;
    }

    const definitions = [
        ['.carousel', bootstrapApi.Carousel],
        ['.modal', bootstrapApi.Modal],
        ['.offcanvas', bootstrapApi.Offcanvas],
        ['[data-bs-toggle="dropdown"]', bootstrapApi.Dropdown],
        ['[data-bs-toggle="tab"], [data-bs-toggle="pill"]', bootstrapApi.Tab],
        ['[data-bs-toggle="tooltip"]', bootstrapApi.Tooltip],
        ['[data-bs-toggle="popover"]', bootstrapApi.Popover],
        ['[data-bs-spy="scroll"]', bootstrapApi.ScrollSpy],
    ];

    definitions.forEach(([selector, Component]) => {
        if (!Component?.getInstance) {
            return;
        }

        root.querySelectorAll(selector).forEach((element) => {
            Component.getInstance(element)?.dispose?.();
        });
    });

    const hoistedOverlays = hoistedOverlaysByRoot.get(root);
    hoistedOverlays?.forEach((element) => {
        if (element.classList.contains('modal')) {
            bootstrapApi.Modal?.getInstance(element)?.dispose?.();
        }
        if (element.classList.contains('offcanvas')) {
            bootstrapApi.Offcanvas?.getInstance(element)?.dispose?.();
        }
        element.remove();
    });
    hoistedOverlaysByRoot.delete(root);

    cleanupOrphanBackdrops();
}
