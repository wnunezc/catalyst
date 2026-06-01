export function initFormValidation(options = {}) {
    const root = options.root instanceof HTMLElement ? options.root : document.body;

    root.querySelectorAll('.needs-validation').forEach((form) => {
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

