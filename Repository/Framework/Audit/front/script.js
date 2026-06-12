/**
 * Contract:
 * - Init: immediate module load after the Audit view asset is published.
 * - DOM: writes `data-audit-log="ready"` on `<html>` for smoke visibility.
 * - Events/Payload: no listeners and no server payload.
 * - CSP: no inline handlers, dynamic HTML or external assets.
 */
import { registerUiComponent } from '../../catalyst/runtime/registration-queue.js';

registerUiComponent({
    name: 'audit.ready-marker',
    phase: 'start',
    selector: '',
    mount() {
        document.documentElement.dataset.auditLog = 'ready';
    },
});
