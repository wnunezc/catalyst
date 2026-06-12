import { registerUiComponent } from '../../catalyst/runtime/registration-queue.js';

registerUiComponent({
    name: 'catalogs.code-wrap',
    phase: 'scan',
    selector: '.catalogs-page code',
    mount(root) {
        root.querySelectorAll('.catalogs-page code').forEach((node) => {
            node.classList.add('text-break');
        });
    },
});
