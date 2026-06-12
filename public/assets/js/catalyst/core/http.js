/**
 * Catalyst HTTP Client
 *
 * Centralized transport layer for framework JavaScript.
 * Responsibilities:
 * - Normalize fetch options
 * - Inject X-Requested-With on AJAX requests
 * - Inject CSRF token on mutating requests
 * - Process JSON responses for notifications and CSRF refresh
 * - Provide consistent JSON/text helpers for framework modules
 *
 * @package Catalyst
 */

import { applyDomInjection } from './response-actions.js';

const JSON_CONTENT_TYPES = ['application/json', 'application/problem+json'];

/**
 * Check whether a content type should be parsed as JSON.
 *
 * @param {string|null} contentType
 * @returns {boolean}
 */
export function isJsonContentType(contentType) {
    if (!contentType) {
        return false;
    }

    return JSON_CONTENT_TYPES.some(type => contentType.includes(type));
}

/**
 * Build a short user-facing message from a transport/parsing error.
 *
 * @param {Error & { status?: number, body?: string }} error
 * @param {number} maxLength
 * @returns {string}
 */
export function summarizeResponseError(error, maxLength = 200) {
    if (error?.body) {
        const snippet = String(error.body).replace(/\s+/g, ' ').trim().slice(0, maxLength);
        const prefix = error.status ? `Unexpected response (HTTP ${error.status}). ` : 'Unexpected response. ';
        return prefix + snippet;
    }

    return error?.message ?? 'Unexpected request error.';
}

/**
 * Normalize a payload to FormData.
 *
 * @param {FormData|HTMLFormElement|Object<string, any>} payload
 * @returns {FormData}
 */
export function toFormData(payload) {
    if (payload instanceof FormData) {
        return payload;
    }

    if (payload instanceof HTMLFormElement) {
        return new FormData(payload);
    }

    const formData = new FormData();
    Object.entries(payload || {}).forEach(([key, value]) => {
        if (Array.isArray(value)) {
            value.forEach(item => formData.append(key, item ?? ''));
            return;
        }

        formData.append(key, value ?? '');
    });

    return formData;
}

export class HttpClient {
    constructor() {
        this.notificationHandler = null;
        this.interceptorEnabled = false;
        this.originalFetch = null;
    }

    /**
     * Register the notification handler used to process JSON envelopes.
     *
     * @param {Object|null} handler
     * @returns {HttpClient}
     */
    setNotificationHandler(handler) {
        this.notificationHandler = handler;
        return this;
    }

    /**
     * Resolve the current CSRF token from the DOM.
     *
     * @returns {string}
     */
    getCsrfToken() {
        const fieldToken = document.querySelector('input[name="csrf_token"]')?.value;
        if (fieldToken) {
            return fieldToken;
        }

        return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    }

    /**
     * Persist a refreshed CSRF token back into the DOM.
     *
     * @param {string} token
     */
    updateCsrfToken(token) {
        if (typeof token !== 'string' || token === '') {
            return;
        }

        document.querySelectorAll('meta[name="csrf-token"]').forEach(meta => {
            meta.setAttribute('content', token);
        });

        document.querySelectorAll('input[name="csrf_token"]').forEach(input => {
            input.value = token;
        });
    }

    /**
     * Enable the legacy global fetch interceptor so existing raw fetch() calls
     * still benefit from the shared transport rules.
     */
    enableFetchInterceptor() {
        if (this.interceptorEnabled || typeof window.fetch !== 'function') {
            return;
        }

        this.originalFetch = window.fetch.bind(window);

        window.fetch = async (input, options = {}) => {
            const prepared = this.prepareOptions(options);
            const response = await this.originalFetch(input, prepared);
            await this.processResponse(response);
            return response;
        };

        this.interceptorEnabled = true;
    }

    /**
     * Restore the original fetch implementation.
     */
    disableFetchInterceptor() {
        if (!this.interceptorEnabled || !this.originalFetch) {
            return;
        }

        window.fetch = this.originalFetch;
        this.interceptorEnabled = false;
    }

    /**
     * Perform a request using the centralized transport rules.
     *
     * @param {RequestInfo|URL} input
     * @param {Object} options
     * @returns {Promise<Response>}
     */
    async request(input, options = {}) {
        const prepared = this.prepareOptions(options);
        const fetchFn = this.originalFetch ?? window.fetch.bind(window);
        const response = await fetchFn(input, prepared);
        await this.processResponse(response);
        return response;
    }

    /**
     * Perform a request and parse the JSON payload.
     *
     * @param {RequestInfo|URL} input
     * @param {Object} options
     * @returns {Promise<{response: Response, data: any}>}
     */
    async json(input, options = {}) {
        const response = await this.request(input, {
            acceptJson: true,
            ...options,
        });

        const data = await this.parseJsonResponse(response);
        return { response, data };
    }

    /**
     * Perform a request and read the text body.
     *
     * @param {RequestInfo|URL} input
     * @param {Object} options
     * @returns {Promise<{response: Response, text: string}>}
     */
    async text(input, options = {}) {
        const response = await this.request(input, options);
        const text = await response.text();
        return { response, text };
    }

    /**
     * Prepare a fetch options object.
     *
     * @param {Object} options
     * @returns {Object}
     */
    prepareOptions(options = {}) {
        const prepared = { ...options };
        const headers = new Headers(options.headers || {});
        const method = (options.method || 'GET').toUpperCase();

        prepared.method = method;

        if (prepared.credentials === undefined) {
            prepared.credentials = 'same-origin';
        }

        if (prepared.xhr !== false && !headers.has('X-Requested-With')) {
            headers.set('X-Requested-With', 'XMLHttpRequest');
        }

        if (prepared.acceptJson === true && !headers.has('Accept')) {
            headers.set('Accept', 'application/json');
        }

        if (Object.prototype.hasOwnProperty.call(prepared, 'json')) {
            if (!headers.has('Content-Type')) {
                headers.set('Content-Type', 'application/json');
            }

            prepared.body = JSON.stringify(prepared.json);
        } else if (Object.prototype.hasOwnProperty.call(prepared, 'form')) {
            prepared.body = toFormData(prepared.form);
        }

        if (this.isMutatingMethod(method)) {
            this.injectCsrf(headers, prepared);
        }

        prepared.headers = headers;

        delete prepared.acceptJson;
        delete prepared.form;
        delete prepared.json;
        delete prepared.xhr;

        return prepared;
    }

    /**
     * Inject the CSRF token into headers and body when relevant.
     *
     * @param {Headers} headers
     * @param {Object} options
     */
    injectCsrf(headers, options) {
        const csrfToken = this.getCsrfToken();
        if (!csrfToken) {
            return;
        }

        if (!headers.has('X-CSRF-TOKEN')) {
            headers.set('X-CSRF-TOKEN', csrfToken);
        }

        if (options.body instanceof FormData) {
            if (!options.body.has('csrf_token')) {
                options.body.append('csrf_token', csrfToken);
            }

            return;
        }

        if (options.body instanceof URLSearchParams) {
            if (!options.body.has('csrf_token')) {
                options.body.set('csrf_token', csrfToken);
            }

            return;
        }

        const contentType = headers.get('Content-Type') || '';
        if (typeof options.body === 'string' && contentType.includes('application/json')) {
            try {
                const payload = JSON.parse(options.body);
                if (!payload.csrf_token) {
                    payload.csrf_token = csrfToken;
                    options.body = JSON.stringify(payload);
                }
            } catch {
                // Ignore invalid JSON bodies and keep the request untouched.
            }
        }
    }

    /**
     * Process a response for notifications and token refresh.
     *
     * @param {Response} response
     * @returns {Promise<Response>}
     */
    async processResponse(response) {
        const contentType = response.headers.get('content-type') || '';
        if (!isJsonContentType(contentType)) {
            return response;
        }

        try {
            const data = await response.clone().json();

            if (data && typeof data === 'object') {
                if (typeof data.new_token === 'string' && data.new_token !== '') {
                    this.updateCsrfToken(data.new_token);
                }

                if (this.notificationHandler?.processResponse) {
                    this.notificationHandler.processResponse(data);
                }

                const domUpdated = await applyDomInjection(data);

                document.dispatchEvent(new CustomEvent('catalyst:http:response', {
                    detail: { response, data, domUpdated }
                }));
            }
        } catch {
            // Ignore malformed JSON here; explicit callers can fail later when parsing.
        }

        return response;
    }

    /**
     * Parse a Response as JSON and throw a rich error when the payload is not JSON.
     *
     * @param {Response} response
     * @returns {Promise<any>}
     */
    async parseJsonResponse(response) {
        const contentType = response.headers.get('content-type') || '';
        const body = await response.text();

        if (body === '') {
            return null;
        }

        if (isJsonContentType(contentType)) {
            return JSON.parse(body);
        }

        const error = new Error(
            `Expected JSON response but received ${contentType || 'unknown content type'} (HTTP ${response.status}).`
        );
        error.status = response.status;
        error.body = body;
        error.response = response;
        throw error;
    }

    /**
     * @param {string} method
     * @returns {boolean}
     */
    isMutatingMethod(method) {
        return ['POST', 'PUT', 'PATCH', 'DELETE'].includes(method.toUpperCase());
    }
}

let sharedHttpClient = null;

/**
 * Return the shared HTTP client singleton.
 *
 * @returns {HttpClient}
 */
export function getHttpClient() {
    if (!sharedHttpClient) {
        sharedHttpClient = new HttpClient();
    }

    return sharedHttpClient;
}
