import { registerUiComponent } from '../../catalyst/runtime/registration-queue.js';

registerUiComponent({
    name: 'workspaces.catalogs.code-wrap',
    phase: 'scan',
    selector: '[data-catalog-code]',
    mount(root) {
        root.querySelectorAll('[data-catalog-code]').forEach((node) => {
            node.classList.add('text-break');
        });
    },
});
