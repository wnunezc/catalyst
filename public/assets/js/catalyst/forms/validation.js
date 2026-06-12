export function initFormValidation(options = {}) {
    const root = options.root instanceof HTMLElement ? options.root : document.body;

    root.querySelectorAll('.needs-validation').forEach((form) => {
        if (form.dataset.catalystValidationBound === '1') {
            return;
        }
        form.dataset.catalystValidationBound = '1';
        form.addEventListener('submit', (event) => {
            if (!(form instanceof HTMLFormElement)) {
                return;
            }

            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }

            form.classList.add('was-validated');
        }, false);
    });
}
