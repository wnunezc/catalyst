/**
 * Contract:
 * - Init: declarative registration through the shared Catalyst UI queue.
 * - DOM: the `data-surface-context="demo-ui"` document and the varying-content modal example.
 * - Events/Payload: registers Demo UI-only behavior with the shared Catalyst UI runtime.
 * - CSP: no inline handlers or secondary runtime imports.
 */
import { registerUiEvent } from '../../catalyst/runtime/registration-queue.js';

function handleVaryingModalShow(event) {
    const varyingModal = event.target;
    if (!(varyingModal instanceof HTMLElement) || varyingModal.id !== 'exampleModal') {
        return;
    }

    const trigger = event.relatedTarget;
    const recipient = trigger instanceof HTMLElement ? trigger.dataset.bsWhatever || '' : '';
    const title = varyingModal.querySelector('.modal-title');
    const input = varyingModal.querySelector('#recipient-name');

    if (title) {
        title.textContent = recipient !== '' ? `New message to ${recipient}` : 'New message';
    }

    if (input instanceof HTMLInputElement) {
        input.value = recipient;
    }
}

registerUiEvent({
    name: 'demo-ui.varying-modal',
    target: 'document',
    type: 'show.bs.modal',
    listener: handleVaryingModalShow,
});
