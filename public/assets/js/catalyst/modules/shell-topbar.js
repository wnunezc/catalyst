export function initTopbarState(options = {}) {
    const root = options.root instanceof HTMLElement ? options.root : document.body;
    const topbarSelector = typeof options.topbarSelector === 'string' && options.topbarSelector !== ''
        ? options.topbarSelector
        : '.app-topbar';
    const scrollContainerSelector = typeof options.scrollContainerSelector === 'string' && options.scrollContainerSelector !== ''
        ? options.scrollContainerSelector
        : '.content-page';
    const activeClass = typeof options.activeClass === 'string' && options.activeClass !== ''
        ? options.activeClass
        : 'topbar-active';
    const topbar = root.querySelector(topbarSelector);
    const scrollContainer = root.querySelector(scrollContainerSelector);
    const simpleBarScrollContainer = scrollContainer instanceof HTMLElement
        ? scrollContainer.querySelector(':scope > .simplebar-wrapper > .simplebar-mask > .simplebar-offset > .simplebar-content-wrapper')
        : null;
    const effectiveScrollContainer = simpleBarScrollContainer instanceof HTMLElement
        ? simpleBarScrollContainer
        : scrollContainer;
    const shouldUseWindowScroll = !(effectiveScrollContainer instanceof HTMLElement)
        || ['visible', 'clip'].includes(window.getComputedStyle(effectiveScrollContainer).overflowY);

    if (!(topbar instanceof HTMLElement)) {
        return;
    }

    const syncTopbar = () => {
        const scrollTop = shouldUseWindowScroll
            ? window.scrollY || document.documentElement.scrollTop || document.body.scrollTop || 0
            : effectiveScrollContainer.scrollTop;

        topbar.classList.toggle(activeClass, scrollTop > 50);
    };

    syncTopbar();

    if (shouldUseWindowScroll) {
        window.addEventListener('scroll', syncTopbar, { passive: true });
        return;
    }

    effectiveScrollContainer.addEventListener('scroll', syncTopbar, { passive: true });
}
