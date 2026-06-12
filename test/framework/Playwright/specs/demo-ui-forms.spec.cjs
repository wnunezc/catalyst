const { test, expect } = require('../helpers/playwright.cjs');
const {
    expectResourceLoaded,
    openDemoUiSurface,
    runOrSkipForEnvironment,
} = require('../helpers/demo-ui.cjs');

const cases = [
    {
        path: '/demo-ui/pickers',
        doc: 'form-pickers.html',
        resources: [
            '/assets/vendor/inspinia/plugins/jquery/jquery.min.js',
            '/assets/vendor/inspinia/plugins/moment/moment.min.js',
            '/assets/vendor/inspinia/plugins/daterangepicker/daterangepicker.js',
            '/assets/vendor/inspinia/plugins/flatpickr/flatpickr.min.js',
            '/assets/vendor/inspinia/plugins/pickr/pickr.min.js',
        ],
        initialized: '.flatpickr-input, .pcr-button',
    },
    {
        path: '/demo-ui/select',
        doc: 'form-select.html',
        resources: [
            '/assets/vendor/inspinia/plugins/choices/choices.min.js',
            '/assets/vendor/inspinia/plugins/select2/select2.min.js',
        ],
        initialized: '.choices, .select2-container',
    },
    {
        path: '/demo-ui/file-uploads',
        doc: 'form-fileuploads.html',
        resources: [
            '/assets/vendor/inspinia/plugins/dropzone/dropzone-min.js',
            '/assets/vendor/inspinia/plugins/filepond/filepond.min.js',
        ],
        initialized: '.dropzone, .filepond--root',
    },
    {
        path: '/demo-ui/text-editors',
        doc: 'form-text-editors.html',
        resources: [
            '/assets/vendor/inspinia/plugins/quill/quill.js',
            '/assets/vendor/inspinia/plugins/summernote/summernote-bs5.min.js',
        ],
        initialized: '.ql-container, .note-editor',
    },
    {
        path: '/demo-ui/range-slider',
        doc: 'form-range-slider.html',
        resources: [
            '/assets/vendor/inspinia/plugins/nouislider/nouislider.min.js',
        ],
        initialized: '.noUi-target',
    },
];

test.describe('@demo-ui @demo-ui-forms Demo UI form plugin assets', () => {
    for (const definition of cases) {
        test(`${definition.path} loads only its required plugin family and initializes it`, async ({ page }) => {
            await runOrSkipForEnvironment(test, async () => {
                await openDemoUiSurface(page, expect, definition);

                for (const resource of definition.resources) {
                    await expectResourceLoaded(page, expect, resource);
                }

                await expect(page.locator(definition.initialized).first()).toBeVisible();
            });
        });
    }

    test('/demo-ui/wizard uses the runtime adapter without a legacy page script', async ({ page }) => {
        await runOrSkipForEnvironment(test, async () => {
            const entry = {
                path: '/demo-ui/wizard',
                doc: 'form-wizard.html',
            };
            await openDemoUiSurface(page, expect, entry);
            await expect(page.locator('[data-wizard]').first()).toBeVisible();
            await expect(page.locator('[data-wizard-nav] .nav-link.active').first()).toBeVisible();
        });
    });

    test('/demo-ui/validation uses the common form-validation adapter', async ({ page }) => {
        await runOrSkipForEnvironment(test, async () => {
            const entry = {
                path: '/demo-ui/validation',
                doc: 'form-validation.html',
            };
            await openDemoUiSurface(page, expect, entry);
            const form = page.locator('form.needs-validation').first();
            await expect(form).toBeVisible();
            await form.locator('[type="submit"]').click();
            await expect(form).toHaveClass(/was-validated/);
        });
    });
});
