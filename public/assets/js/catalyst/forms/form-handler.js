/**
 * Catalyst Form Handler
 *
 * Event-driven AJAX form submission with field-level validation feedback.
 * Implements the LH-Framework "Back/Event" pattern on the JS side.
 *
 * PHP counterpart: HandlesFormEventsTrait trait (app/Framework/Traits/HandlesFormEventsTrait.php)
 *
 * How it works:
 *   1. Detects forms marked with data-catalyst="form"
 *   2. On submit, reads the event name from the triggering button's data-event attribute
 *      (or from a hidden input[name="_event"])
 *   3. Appends _event to FormData and submits via fetch
 *   4. The existing fetch interceptor handles CSRF injection and notification display
 *   5. This handler then processes: redirect, refresh, and field-level errors
 *
 * HTML usage:
 *   <form data-catalyst="form" action="/users/store" method="POST">
 *     <input type="text" name="username" class="form-control">
 *     <div class="invalid-feedback"></div>
 *     <button type="submit" data-event="save">Save</button>
 *     <button type="submit" data-event="delete">Delete</button>
 *   </form>
 *
 * Response envelope (from PHP HandlesFormEventsTrait + JsonResponse):
 *   {
 *     "success": true|false,
 *     "message": "...",
 *     "data": {...},
 *     "notifications": { "toasters": [...], "modals": [...] },  ← handled by fetch interceptor
 *     "errors": { "fieldName": "error message" },               ← handled here
 *     "redirect": "/url",                                        ← handled here
 *     "redirectDelay": 300,
 *     "refresh": true,                                           ← handled here
 *     "refreshDelay": 300
 *   }
 *
 * @package Catalyst
 * @author Walter Nuñez (arcanisgk)
 */

import { getHttpClient, summarizeResponseError } from '../core/http.js';
import { setButtonLoading, clearButtonLoading } from '../core/loading.js';

/**
 * FormHandler — manages event-driven AJAX form submissions
 */
export class FormHandler {
    /**
     * @param {Object} options
     * @param {string} [options.formSelector]      - CSS selector for managed forms
     * @param {string} [options.eventAttr]         - Button attribute that holds the event name
     * @param {string} [options.eventField]        - Hidden input name for the event key
     * @param {number} [options.defaultDelay]      - Default ms before redirect/refresh (lets toasters appear)
     * @param {string} [options.loadingHtml]       - HTML to show inside the button while loading
     */
    constructor(options = {}) {
        this.options = {
            formSelector: '[data-catalyst="form"]',
            eventAttr: 'data-event',
            eventField: '_event',
            defaultDelay: 300,
            loadingHtml: '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>',
            ...options
        };

        this.initialized = false;
        this.http = getHttpClient();
    }

    /**
     * Initialize event delegation on the document.
     * Safe to call multiple times — only runs once.
     */
    init() {
        if (this.initialized) return;

        document.addEventListener('click', (e) => {
            const btn = e.target.closest('button[type="submit"], input[type="submit"]');
            if (!btn) return;
            const form = btn.closest(this.options.formSelector);
            if (!form) return;
            form.__catalystSubmitter = btn;
        });

        // Form submit delegation
        document.addEventListener('submit', (e) => {
            const form = e.target.closest(this.options.formSelector);
            if (!form) return;
            if (e.defaultPrevented) return;
            e.preventDefault();
            const submitter = e.submitter instanceof HTMLElement
                ? e.submitter
                : (form.__catalystSubmitter instanceof HTMLElement ? form.__catalystSubmitter : null);
            this.#handleSubmit(form, submitter);
        });

        // Button click delegation (explicit data-event buttons bypass native submit)
        document.addEventListener('click', (e) => {
            const btn = e.target.closest(`[${this.options.eventAttr}]`);
            if (!btn) return;
            if (e.defaultPrevented) return;
            const form = btn.closest('form');
            if (!form || !form.matches(this.options.formSelector)) return;
            e.preventDefault();
            this.#handleSubmit(form, btn);
        });

        this.initialized = true;
    }

    /**
     * Programmatically submit a form with a given event name.
     * Useful when triggering form actions from JS without user interaction.
     *
     * @param {HTMLFormElement|string} form   - Form element or CSS selector
     * @param {string|null}            event  - Event name (maps to on{Event}() in PHP)
     * @returns {Promise<void>}
     */
    async submit(form, event = null) {
        if (typeof form === 'string') {
            form = document.querySelector(form);
        }
        if (!form) {
            console.warn('[Catalyst FormHandler] Form not found');
            return;
        }
        await this.#handleSubmit(form, null, event);
    }

    // --- Private -------------------------------------------------------------

    /**
     * Core submit handler
     *
     * @param {HTMLFormElement} form
     * @param {HTMLElement|null} triggerBtn  - Button that triggered the action (may be null)
     * @param {string|null} overrideEvent    - Event name override from programmatic submit
     */
    async #handleSubmit(form, triggerBtn, overrideEvent = null) {
        // Resolve the submit button and event name
        const btn       = triggerBtn || form.querySelector('[type="submit"]');
        const eventName = overrideEvent
            ?? btn?.getAttribute(this.options.eventAttr)
            ?? form.querySelector(`[name="${this.options.eventField}"]`)?.value
            ?? null;
        const action = btn?.getAttribute('formaction') || form.action || window.location.href;
        const method = btn?.getAttribute('formmethod') || form.method || 'POST';

        try {
            if (btn) {
                setButtonLoading(btn, { html: this.options.loadingHtml });
            }

            // Build FormData
            let formData;
            try {
                formData = btn instanceof HTMLElement ? new FormData(form, btn) : new FormData(form);
            } catch {
                formData = new FormData(form);
            }

            if (btn instanceof HTMLButtonElement || btn instanceof HTMLInputElement) {
                const submitterName = btn.getAttribute('name');
                if (submitterName) {
                    formData.set(submitterName, btn.value);
                }
            }

            if (eventName && !formData.has(this.options.eventField)) {
                formData.append(this.options.eventField, eventName);
            }

            // Clear previous errors
            this.#clearFieldErrors(form);
            this.#clearFormError(form);

            // Submit — fetch interceptor handles CSRF injection + notification processing
            const { data } = await this.http.json(action, {
                method: method.toUpperCase(),
                form: formData,
            });

            // Inject field-level validation errors
            if (data.errors && typeof data.errors === 'object') {
                this.#displayFieldErrors(form, data.errors);
            }

            // Show form-level error (e.g. rate limit, auth failure) when no field errors
            const hasFieldErrors = data.errors && typeof data.errors === 'object' && Object.keys(data.errors).length > 0;
            if (data.success === false && data.message && !hasFieldErrors) {
                this.#displayFormError(form, data.message);
            } else {
                this.#clearFormError(form);
            }

            document.dispatchEvent(new CustomEvent('catalyst:form:response', {
                detail: {
                    form,
                    data,
                    submitter: btn,
                    action,
                    method: method.toUpperCase(),
                    eventName,
                }
            }));

            // Handle redirect (after notification delay)
            if (data.redirect) {
                const delay = data.redirectDelay ?? this.options.defaultDelay;
                document.dispatchEvent(new CustomEvent('catalyst:navigation:start'));
                setTimeout(() => { window.location.href = data.redirect; }, delay);
                return;
            }

            // Handle page refresh
            if (data.refresh) {
                const delay = data.refreshDelay ?? this.options.defaultDelay;
                document.dispatchEvent(new CustomEvent('catalyst:navigation:start'));
                setTimeout(() => { window.location.reload(); }, delay);
            }

        } catch (err) {
            console.error('[Catalyst FormHandler] Submit error:', err);
            this.#displayFormError(form, summarizeResponseError(err));
        } finally {
            delete form.__catalystSubmitter;
            if (btn) {
                clearButtonLoading(btn);
            }
        }
    }

    /**
     * Inject field-level validation errors into form inputs.
     *
     * Expects: errors = { fieldName: "message" | ["message", ...] }
     * Adds `is-invalid` to the input and populates `.invalid-feedback` sibling.
     * Creates an `.invalid-feedback` div if one doesn't exist.
     *
     * @param {HTMLFormElement} form
     * @param {Object} errors
     */
    #displayFieldErrors(form, errors) {
        for (const [field, messages] of Object.entries(errors)) {
            const input = form.querySelector(`[name="${field}"]`);
            if (!input) continue;

            input.classList.add('is-invalid');

            const fieldContainer = input.closest('.input-group, .form-floating')?.parentElement
                ?? input.parentElement;

            let feedback = input.parentElement.querySelector('.invalid-feedback')
                ?? fieldContainer?.querySelector('.invalid-feedback');
            if (!feedback) {
                feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                if (fieldContainer && fieldContainer !== input.parentElement) {
                    fieldContainer.append(feedback);
                } else {
                    input.after(feedback);
                }
            }

            feedback.textContent = Array.isArray(messages) ? messages[0] : String(messages);
            feedback.style.display = 'block';
        }
    }

    /**
     * Remove previously injected field errors from all inputs in the form.
     *
     * @param {HTMLFormElement} form
     */
    #clearFieldErrors(form) {
        form.querySelectorAll('.is-invalid').forEach(el => {
            el.classList.remove('is-invalid');
        });
        form.querySelectorAll('.invalid-feedback').forEach(el => {
            el.style.display = '';
            el.textContent   = '';
        });
    }

    /**
     * Show a form-level error alert above the first field.
     * Creates or reuses a .catalyst-form-error element.
     *
     * @param {HTMLFormElement} form
     * @param {string} message
     */
    #displayFormError(form, message) {
        let alert = form.querySelector('.catalyst-form-error');
        if (!alert) {
            alert = document.createElement('div');
            alert.className = 'catalyst-form-error alert alert-danger py-2 small';
            const firstField = form.querySelector('.mb-3, .mb-4, [name]');
            if (firstField) {
                form.insertBefore(alert, firstField);
            } else {
                form.prepend(alert);
            }
        }
        alert.textContent = message;
        alert.style.display = '';
    }

    /**
     * Remove the form-level error alert if present.
     *
     * @param {HTMLFormElement} form
     */
    #clearFormError(form) {
        const alert = form.querySelector('.catalyst-form-error');
        if (alert) {
            alert.style.display = 'none';
            alert.textContent   = '';
        }
    }
}
