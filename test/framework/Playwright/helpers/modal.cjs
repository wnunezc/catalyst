async function firstVisible(locator) {
    const count = await locator.count();

    for (let index = 0; index < count; index++) {
        const candidate = locator.nth(index);
        if (await candidate.isVisible()) {
            return candidate;
        }
    }

    throw new Error('No visible candidate found.');
}

async function assertModalLayering(page, expect) {
    const layer = await page.evaluate(() => {
        const modal = document.querySelector('.modal.show');
        const backdrop = document.querySelector('.modal-backdrop');

        if (!(modal instanceof HTMLElement)) {
            return { ok: false, reason: 'No visible .modal.show element.' };
        }

        if (!(backdrop instanceof HTMLElement)) {
            return { ok: false, reason: 'No .modal-backdrop element.' };
        }

        const modalZ = Number.parseInt(window.getComputedStyle(modal).zIndex || '0', 10);
        const backdropZ = Number.parseInt(window.getComputedStyle(backdrop).zIndex || '0', 10);
        const modalParentIsBody = modal.parentElement === document.body;
        const container = modal.closest('#catalyst-modal-container');
        const modalContainerIsBodyChild = container instanceof HTMLElement && container.parentElement === document.body;

        return {
            ok: modalZ > backdropZ && (modalParentIsBody || modalContainerIsBodyChild),
            modalZ,
            backdropZ,
            modalParentIsBody,
            modalContainerIsBodyChild,
        };
    });

    expect(layer.ok, JSON.stringify(layer)).toBe(true);
}

async function waitForModalTransition(page, modal) {
    await page.waitForFunction((element) => {
        const instance = window.bootstrap?.Modal?.getInstance(element);
        return !instance || instance._isTransitioning !== true;
    }, await modal.elementHandle(), { timeout: 10000 });
}

async function openModalFromTrigger(page, expect, trigger) {
    await trigger.scrollIntoViewIfNeeded();
    const target = await trigger.getAttribute('data-bs-target')
        || await trigger.getAttribute('href');
    await trigger.click();
    const modal = target?.startsWith('#')
        ? page.locator(target)
        : page.locator('.modal.show').last();
    await expect(modal).toBeVisible({ timeout: 10000 });
    await expect(modal).toHaveClass(/\bshow\b/);
    await waitForModalTransition(page, modal);
    await assertModalLayering(page, expect);

    return modal;
}

async function closeActiveModal(page, expect) {
    const modal = page.locator('.modal.show').last();
    await expect(modal).toBeVisible();

    const closeCandidates = modal.locator(
        '.btn-close, .modal-footer button[data-bs-dismiss="modal"], .modal-footer a[data-bs-dismiss="modal"], ' +
        'button:has-text("Cancel"), button:has-text("Close"), button:has-text("Cancelar"), button:has-text("Cerrar"), button:has-text("Got it"), ' +
        '[data-bs-dismiss="modal"]'
    );
    let close = null;
    const closeCount = await closeCandidates.count();

    for (let index = 0; index < closeCount; index++) {
        const candidate = closeCandidates.nth(index);
        if (await candidate.isVisible()) {
            close = candidate;
            break;
        }
    }

    if (close) {
        await waitForModalTransition(page, modal);
        await close.scrollIntoViewIfNeeded();
        await close.click();
    } else {
        await page.keyboard.press('Escape');
    }

    await expect(page.locator('.modal.show')).toHaveCount(0, { timeout: 10000 });
}

async function assertNoModalResidue(page, expect) {
    await expect(page.locator('.modal.show')).toHaveCount(0);
    await expect(page.locator('.modal-backdrop')).toHaveCount(0);

    const bodyState = await page.evaluate(() => ({
        modalOpen: document.body.classList.contains('modal-open'),
        overflow: document.body.style.overflow,
        paddingRight: document.body.style.paddingRight,
    }));

    expect(bodyState, JSON.stringify(bodyState)).toEqual({
        modalOpen: false,
        overflow: '',
        paddingRight: '',
    });
}

module.exports = {
    assertModalLayering,
    assertNoModalResidue,
    closeActiveModal,
    firstVisible,
    openModalFromTrigger,
};
