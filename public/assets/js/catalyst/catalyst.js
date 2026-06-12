/**
 * Catalyst Notification System - Main Entry Point
 *
 * Unified notification system supporting flash messages, toasters, and modals.
 *
 * @package Catalyst
 * @author Walter Nuñez (arcanisgk)
 * @version 1.0.0
 */

import { ToasterManager } from './notifications/toaster.js';
import { ModalManager } from './notifications/modal.js';
import { NotificationHandler } from './notifications/notification.js';
import { FormHandler } from './forms/form-handler.js';
import { PasswordManager } from './forms/password.js';
import { HttpClient, getHttpClient } from './core/http.js';
import { setButtonLoading, clearButtonLoading } from './core/loading.js';
import { registerUiComponent, registerUiEvent } from './runtime/registration-queue.js';
import { defaults } from './config/defaults.js';

// Export individual modules for direct imports
export { ToasterManager } from './notifications/toaster.js';
export { ModalManager } from './notifications/modal.js';
export { NotificationHandler } from './notifications/notification.js';
export { FormHandler } from './forms/form-handler.js';
export { PasswordManager } from './forms/password.js';
export { HttpClient, getHttpClient } from './core/http.js';
export { setButtonLoading, clearButtonLoading } from './core/loading.js';
export { defaults } from './config/defaults.js';
export * from './core/utils.js';

const runtimeApi = {
    initRuntime: async () => null,
    scan: async () => null,
    destroy: async () => null,
};

export function bindUiRuntimeApi(api = {}) {
    Object.assign(runtimeApi, api);
}

export { registerUiComponent, registerUiEvent };


/**
 * Catalyst - Global notification system namespace
 */
class CatalystNotificationSystem {
    /**
     * Create the Catalyst notification system
     */
    constructor() {
        this.toaster   = null;
        this.modal     = null;
        this.handler   = null;
        this.http      = null;
        this.forms     = null;
        this.passwords = null;
        this.initialized = false;
        this.config = {};
        this.ui = {
            initRuntime: (...args) => runtimeApi.initRuntime(...args),
            scan: (...args) => runtimeApi.scan(...args),
            destroy: (...args) => runtimeApi.destroy(...args),
            register: registerUiComponent,
            registerEvent: registerUiEvent,
        };
    }

    /**
     * Initialize the Catalyst notification system
     *
     * @param {Object} config - Configuration options
     * @param {Object} config.toaster - Toaster configuration
     * @param {Object} config.modal - Modal configuration
     * @param {Object} config.forms - FormHandler configuration
     * @param {boolean} config.fetchIntercept - Enable fetch interceptor
     * @param {boolean} config.formHandler - Enable automatic form event delegation (default: true)
     * @returns {CatalystNotificationSystem} Self for chaining
     */
    init(config = {}) {
        if (this.initialized) {
            console.warn('Catalyst notification system already initialized');
            return this;
        }

        this.config = config;

        // Initialize toaster
        this.toaster = new ToasterManager(config.toaster || {});
        this.toaster.init();

        // Initialize modal
        this.modal = new ModalManager(config.modal || {});
        this.modal.init();

        // Initialize notification handler
        this.handler = new NotificationHandler(this.toaster, this.modal);

        // Initialize the shared HTTP client before enabling interception.
        this.http = getHttpClient().setNotificationHandler(this.handler);

        // Setup fetch interceptor if enabled (handles CSRF + notifications)
        if (config.fetchIntercept !== false) {
            this.http.enableFetchInterceptor();
        }

        // Initialize form handler if enabled
        if (config.formHandler !== false) {
            this.forms = new FormHandler(config.forms || {});
            this.forms.init();
        }

        // Initialize password manager (strength meter) — always on
        this.passwords = new PasswordManager(config.password || {});

        this.initialized = true;

        // Dispatch ready event asynchronously so listeners registered in
        // sibling/subsequent ES modules (e.g. /assets/js/work/{slug}/script.js
        // loaded AFTER the inline _catalyst-init.php script) still receive it.
        // Modules evaluate in source order synchronously; a sync dispatch here
        // would fire before later modules run their top-level addEventListener.
        setTimeout(() => {
            document.dispatchEvent(new CustomEvent('catalyst:ready', {
                detail: { catalyst: this }
            }));
        }, 0);

        return this;
    }

    /**
     * Process a JSON response for notifications
     *
     * @param {Object} response - JSON response data
     */
    processResponse(response) {
        if (!this.initialized) {
            console.warn('Catalyst not initialized. Call Catalyst.init() first.');
            return;
        }
        this.handler.processResponse(response);
    }

    /**
     * Perform a centralized HTTP request.
     *
     * @param {RequestInfo|URL} input
     * @param {Object} options
     * @returns {Promise<Response>}
     */
    request(input, options = {}) {
        if (!this.#requireInit('request')) {
            return Promise.reject(new Error('Catalyst not initialized.'));
        }

        return this.http.request(input, options);
    }

    /**
     * Perform a centralized HTTP request and parse JSON.
     *
     * @param {RequestInfo|URL} input
     * @param {Object} options
     * @returns {Promise<{response: Response, data: any}>}
     */
    json(input, options = {}) {
        if (!this.#requireInit('json')) {
            return Promise.reject(new Error('Catalyst not initialized.'));
        }

        return this.http.json(input, options);
    }

    /**
     * Warn when a UI method is called before init()
     *
     * @param {string} method - Method name for the warning message
     * @returns {boolean} False if not initialized
     */
    #requireInit(method) {
        if (!this.initialized) {
            console.warn(`Catalyst.${method}() called before Catalyst.init(). Call init() first.`);
            return false;
        }
        return true;
    }

    /**
     * Show a success toaster
     *
     * @param {string} message - Message content
     * @param {Object} options - Additional options
     * @returns {string|null} Toast ID or null if not initialized
     */
    success(message, options = {}) {
        return this.#requireInit('success') ? this.toaster.success(message, options) : null;
    }

    /**
     * Show an error toaster
     *
     * @param {string} message - Message content
     * @param {Object} options - Additional options
     * @returns {string|null} Toast ID or null if not initialized
     */
    error(message, options = {}) {
        return this.#requireInit('error') ? this.toaster.error(message, options) : null;
    }

    /**
     * Show a warning toaster
     *
     * @param {string} message - Message content
     * @param {Object} options - Additional options
     * @returns {string|null} Toast ID or null if not initialized
     */
    warning(message, options = {}) {
        return this.#requireInit('warning') ? this.toaster.warning(message, options) : null;
    }

    /**
     * Show an info toaster
     *
     * @param {string} message - Message content
     * @param {Object} options - Additional options
     * @returns {string|null} Toast ID or null if not initialized
     */
    info(message, options = {}) {
        return this.#requireInit('info') ? this.toaster.info(message, options) : null;
    }

    /**
     * Show a confirmation dialog
     *
     * @param {string} message - Confirmation message
     * @param {Object} options - Dialog options
     * @returns {Promise<boolean>} True if confirmed
     */
    confirm(message, options = {}) {
        return this.#requireInit('confirm') ? this.modal.confirm(message, options) : Promise.resolve(false);
    }

    /**
     * Show an alert dialog
     *
     * @param {string} message - Alert message
     * @param {Object} options - Dialog options
     * @returns {Promise<void>}
     */
    alert(message, options = {}) {
        return this.#requireInit('alert') ? this.modal.alert(message, options) : Promise.resolve();
    }

    /**
     * Load content into a modal
     *
     * @param {string} url - URL to load
     * @param {Object} options - Modal options
     * @returns {Promise<*>}
     */
    loadModal(url, options = {}) {
        return this.#requireInit('loadModal') ? this.modal.load(url, options) : Promise.resolve(null);
    }

    /**
     * Show a modal with direct content
     *
     * @param {Object} options - Modal options
     * @returns {Promise<*>}
     */
    showModal(options = {}) {
        return this.#requireInit('showModal') ? this.modal.show(options) : Promise.resolve(null);
    }

    /**
     * Close the active modal
     */
    closeModal() {
        if (this.#requireInit('closeModal')) this.modal.close();
    }

    showWaitModal(message = 'Please wait...') {
        return this.#requireInit('showWaitModal') ? this.modal.showWaitModal(message) : null;
    }

    closeWaitModal() {
        if (this.#requireInit('closeWaitModal')) this.modal.closeWaitModal();
    }

    setButtonLoading(button, options = {}) {
        return setButtonLoading(button, options);
    }

    clearButtonLoading(button) {
        return clearButtonLoading(button);
    }

    /**
     * Programmatically submit a Catalyst-managed form
     *
     * @param {HTMLFormElement|string} form - Form element or CSS selector
     * @param {string|null} event - Event name (maps to on{Event}() in PHP HandlesFormEventsTrait)
     * @returns {Promise<void>}
     */
    submitForm(form, event = null) {
        if (!this.#requireInit('submitForm')) return Promise.resolve();
        return this.forms.submit(form, event);
    }

    /**
     * Get the version number
     *
     * @returns {string} Version
     */
    get version() {
        return '1.0.0';
    }
}

// Create global instance
const Catalyst = new CatalystNotificationSystem();

// Preserve canonical runtime extensions (for example theme controls) that may
// have been registered before this module evaluates.
if (typeof window !== 'undefined') {
    const existingCatalyst = window.Catalyst;

    if (existingCatalyst && typeof existingCatalyst === 'object') {
        Object.keys(existingCatalyst).forEach((key) => {
            if (!(key in Catalyst)) {
                Catalyst[key] = existingCatalyst[key];
            }
        });
    }

    window.Catalyst = Catalyst;
}

// Export default
export default Catalyst;
