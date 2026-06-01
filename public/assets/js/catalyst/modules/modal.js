/**
 * Catalyst Notification System - Modal Manager
 *
 * Manages modal dialogs using Bootstrap 5 Modal component.
 *
 * @package Catalyst
 * @author Walter Nuñez (arcanisgk)
 */

import { defaults, getDefaultIcon, getModalSizeClass } from '../config/defaults.js';
import { getHttpClient } from './http.js';
import { generateId, createElement, escapeHtml, createDeferred } from './utils.js';
import { applyTrustedHtml, readTrustedHtmlContractFromResponse } from './trusted-dom.js';

/**
 * ModalManager - Manages modal dialogs
 */
export class ModalManager {
    /**
     * Create a new ModalManager instance
     *
     * @param {Object} options - Configuration options
     */
    constructor(options = {}) {
        this.options = {
            ...defaults.modal,
            ...options
        };

        this.activeModal = null;
        this.waitModal = null;
        this.modalStack = [];
        this.initialized = false;
        this.http = getHttpClient();
    }

    /**
     * Initialize the modal manager
     */
    init() {
        if (this.initialized) return;

        // Ensure modal container exists
        this.ensureContainer();
        this.initialized = true;
    }

    /**
     * Ensure the modal container exists
     */
    ensureContainer() {
        if (!document.getElementById('catalyst-modal-container')) {
            const container = createElement('div', { id: 'catalyst-modal-container' });
            document.body.appendChild(container);
        }
    }

    /**
     * Show a modal with direct content
     *
     * @param {Object} options - Modal options
     * @returns {Promise<*>} Resolves when modal is closed
     */
    async show(options = {}) {
        if (!this.initialized) this.init();

        const id = options.id || generateId('modal');
        const deferred = createDeferred();

        const config = {
            id,
            title: options.title || '',
            content: options.content || '',
            size: options.size || this.options.size,
            backdrop: options.backdrop ?? this.options.backdrop,
            keyboard: options.keyboard ?? this.options.keyboard,
            scrollable: options.scrollable ?? this.options.scrollable,
            centered: options.centered ?? this.options.centered,
            footer: options.footer || null,
            onOpen: options.onOpen || null,
            onClose: options.onClose || null,
            deferred
        };

        const modal = this.createModalElement(config);
        document.getElementById('catalyst-modal-container').appendChild(modal);

        // Initialize Bootstrap modal
        const bsModal = new bootstrap.Modal(modal, {
            backdrop: config.backdrop,
            keyboard: config.keyboard,
            focus: this.options.focus
        });

        const closeButton = modal.querySelector('.btn-close');
        if (closeButton) {
            closeButton.addEventListener('click', () => this.hideModalInstance(modal, bsModal));
        }

        // Setup events
        modal.addEventListener('shown.bs.modal', () => {
            if (config.onOpen) config.onOpen(modal);
        });

        modal.addEventListener('hidden.bs.modal', () => {
            if (config.onClose) config.onClose(modal);
            modal.remove();
            this.activeModal = null;
            deferred.resolve(modal.dataset.result || null);
        });

        // Store reference
        this.activeModal = { element: modal, bsModal, config };

        // Show modal
        bsModal.show();

        return deferred.promise;
    }

    /**
     * Load modal content from a URL
     *
     * @param {string} url - URL to load content from
     * @param {Object} options - Modal options
     * @returns {Promise<*>} Resolves when modal is closed
     */
    async load(url, options = {}) {
        if (!this.initialized) this.init();

        const id = options.id || generateId('modal');
        const deferred = createDeferred();

        const config = {
            id,
            title: options.title || 'Loading...',
            content: this.createLoadingContent(),
            size: options.size || this.options.size,
            backdrop: options.backdrop ?? this.options.backdrop,
            keyboard: options.keyboard ?? this.options.keyboard,
            scrollable: options.scrollable ?? this.options.scrollable,
            centered: options.centered ?? this.options.centered,
            footer: options.footer || null,
            onOpen: options.onOpen || null,
            onClose: options.onClose || null,
            onLoaded: options.onLoaded || null,
            deferred
        };

        const modal = this.createModalElement(config);
        document.getElementById('catalyst-modal-container').appendChild(modal);

        // Initialize Bootstrap modal
        const bsModal = new bootstrap.Modal(modal, {
            backdrop: config.backdrop,
            keyboard: config.keyboard,
            focus: this.options.focus
        });

        const closeButton = modal.querySelector('.btn-close');
        if (closeButton) {
            closeButton.addEventListener('click', () => this.hideModalInstance(modal, bsModal));
        }

        // Store reference
        this.activeModal = { element: modal, bsModal, config };

        // Show modal
        bsModal.show();

        // Load content
        try {
            const { response, text } = await this.http.text(url, {
                headers: { 'Accept': 'text/html' },
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const body = modal.querySelector('.modal-body');
            if (!applyTrustedHtml(body, text, readTrustedHtmlContractFromResponse(response))) {
                throw new Error('Trusted HTML contract missing for modal content.');
            }

            // Update title if provided in response
            if (options.title) {
                modal.querySelector('.modal-title').textContent = options.title;
            }

            if (config.onLoaded) config.onLoaded(body, modal);
        } catch (error) {
            const body = modal.querySelector('.modal-body');
            body.innerHTML = `
                <div class="text-center text-danger p-4">
                    <i class="fa-solid fa-circle-exclamation fa-3x mb-3"></i>
                    <p>Error loading content</p>
                    <small class="text-muted">${escapeHtml(error.message)}</small>
                </div>
            `;
        }

        // Setup events
        modal.addEventListener('shown.bs.modal', () => {
            if (config.onOpen) config.onOpen(modal);
        });

        modal.addEventListener('hidden.bs.modal', () => {
            if (config.onClose) config.onClose(modal);
            modal.remove();
            this.activeModal = null;
            deferred.resolve(modal.dataset.result || null);
        });

        return deferred.promise;
    }

    /**
     * Show a confirmation dialog
     *
     * @param {string} message - Confirmation message
     * @param {Object} options - Dialog options
     * @returns {Promise<boolean>} True if confirmed, false otherwise
     */
    async confirm(message, options = {}) {
        if (!this.initialized) this.init();

        const config = {
            ...defaults.confirm,
            ...options,
            message
        };

        const id = generateId('modal-confirm');
        const deferred = createDeferred();

        const modal = this.createConfirmElement(id, config);
        document.getElementById('catalyst-modal-container').appendChild(modal);

        // Initialize Bootstrap modal
        const bsModal = new bootstrap.Modal(modal, {
            backdrop: 'static',
            keyboard: false
        });

        // Store reference
        this.activeModal = { element: modal, bsModal, config };

        // Setup button handlers
        modal.querySelector('.btn-confirm').addEventListener('click', () => {
            modal.dataset.result = 'true';
            this.hideModalInstance(modal, bsModal);
        });

        modal.querySelector('.btn-cancel').addEventListener('click', () => {
            modal.dataset.result = 'false';
            this.hideModalInstance(modal, bsModal);
        });

        modal.addEventListener('hidden.bs.modal', () => {
            modal.remove();
            this.activeModal = null;
            deferred.resolve(modal.dataset.result === 'true');
        });

        bsModal.show();

        return deferred.promise;
    }

    /**
     * Show an alert dialog
     *
     * @param {string} message - Alert message
     * @param {Object} options - Dialog options
     * @returns {Promise<void>}
     */
    async alert(message, options = {}) {
        if (!this.initialized) this.init();

        const config = {
            ...defaults.alert,
            ...options,
            message
        };

        const id = generateId('modal-alert');
        const deferred = createDeferred();

        const modal = this.createAlertElement(id, config);
        document.getElementById('catalyst-modal-container').appendChild(modal);

        // Initialize Bootstrap modal
        const bsModal = new bootstrap.Modal(modal, {
            backdrop: 'static',
            keyboard: true
        });

        // Store reference
        this.activeModal = { element: modal, bsModal, config };

        // Setup button handler
        modal.querySelector('.btn-ok').addEventListener('click', () => {
            this.hideModalInstance(modal, bsModal);
        });

        modal.addEventListener('hidden.bs.modal', () => {
            modal.remove();
            this.activeModal = null;
            deferred.resolve();
        });

        bsModal.show();

        return deferred.promise;
    }

    showWaitModal(message = 'Please wait...') {
        if (!this.initialized) this.init();

        const safeMessage = typeof message === 'string' && message.trim() !== '' ? message : 'Please wait...';

        if (this.waitModal) {
            const messageEl = this.waitModal.element.querySelector('[data-wait-message]');
            if (messageEl) {
                messageEl.textContent = safeMessage;
            }

            this.waitModal.bsModal.show();
            return this.waitModal.element;
        }

        const id = 'catalyst-wait-modal';
        const modal = this.createWaitModalElement(id, safeMessage);
        document.getElementById('catalyst-modal-container').appendChild(modal);

        const bsModal = new bootstrap.Modal(modal, {
            backdrop: 'static',
            keyboard: false,
            focus: false
        });

        modal.addEventListener('hidden.bs.modal', () => {
            modal.remove();

            if (this.waitModal?.element === modal) {
                this.waitModal = null;
            }
        });

        this.waitModal = { element: modal, bsModal };
        bsModal.show();

        return modal;
    }

    closeWaitModal() {
        if (this.waitModal) {
            this.hideModalInstance(this.waitModal.element, this.waitModal.bsModal);
        }
    }

    hideModalInstance(modal, bsModal) {
        if (!modal || !bsModal) {
            return;
        }

        bsModal.hide();

        window.setTimeout(() => {
            if (!modal.isConnected || !modal.classList.contains('show')) {
                return;
            }

            modal.classList.remove('show');
            modal.style.display = 'none';
            modal.setAttribute('aria-hidden', 'true');

            document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('padding-right');

            modal.dispatchEvent(new Event('hidden.bs.modal'));
        }, 250);
    }

    /**
     * Create the modal DOM element
     *
     * @param {Object} config - Modal configuration
     * @returns {HTMLElement} Modal element
     */
    createModalElement(config) {
        const sizeClass = getModalSizeClass(config.size);
        const scrollableClass = config.scrollable ? 'modal-dialog-scrollable' : '';
        const centeredClass = config.centered ? 'modal-dialog-centered' : '';

        const modal = createElement('div', {
            id: config.id,
            className: `modal fade ${defaults.classes.modal}`,
            tabindex: '-1',
            'aria-labelledby': `${config.id}-title`,
            'aria-hidden': 'true'
        });

        modal.innerHTML = `
            <div class="modal-dialog ${sizeClass} ${scrollableClass} ${centeredClass}">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="${config.id}-title">${escapeHtml(config.title)}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        ${config.content}
                    </div>
                    ${config.footer ? `<div class="modal-footer">${config.footer}</div>` : ''}
                </div>
            </div>
        `;

        return modal;
    }

    /**
     * Create loading content for modal body
     *
     * @returns {string} Loading HTML
     */
    createLoadingContent() {
        return `
            <div class="modal-loading">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `;
    }

    createWaitModalElement(id, message) {
        const modal = createElement('div', {
            id,
            className: `modal fade ${defaults.classes.modal} modal-wait`,
            tabindex: '-1',
            'aria-labelledby': `${id}-title`,
            'aria-hidden': 'true'
        });

        modal.innerHTML = `
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body text-center py-4">
                        <div class="spinner-border text-primary mb-3" role="status" aria-hidden="true"></div>
                        <div class="fw-semibold mb-1" id="${id}-title">Working...</div>
                        <p class="text-muted small mb-0" data-wait-message>${escapeHtml(message)}</p>
                    </div>
                </div>
            </div>
        `;

        return modal;
    }

    /**
     * Create a confirm dialog element
     *
     * @param {string} id - Modal ID
     * @param {Object} config - Configuration
     * @returns {HTMLElement} Modal element
     */
    createConfirmElement(id, config) {
        const icon = getDefaultIcon(config.type);
        const iconColor = this.getTypeColor(config.type);

        const modal = createElement('div', {
            id,
            className: `modal fade ${defaults.classes.modal} modal-confirm`,
            tabindex: '-1',
            'aria-labelledby': `${id}-title`,
            'aria-hidden': 'true'
        });

        modal.innerHTML = `
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="modal-icon ${iconColor}">
                            <i class="${icon}"></i>
                        </div>
                        <h5 class="modal-title mb-3" id="${id}-title">${escapeHtml(config.title)}</h5>
                        <p class="modal-message">${escapeHtml(config.message)}</p>
                        <div class="modal-buttons">
                            <button type="button" class="btn ${config.cancelClass} btn-cancel">
                                ${escapeHtml(config.cancelText)}
                            </button>
                            <button type="button" class="btn ${config.confirmClass} btn-confirm">
                                ${escapeHtml(config.confirmText)}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        return modal;
    }

    /**
     * Create an alert dialog element
     *
     * @param {string} id - Modal ID
     * @param {Object} config - Configuration
     * @returns {HTMLElement} Modal element
     */
    createAlertElement(id, config) {
        const icon = getDefaultIcon(config.type);
        const iconColor = this.getTypeColor(config.type);

        const modal = createElement('div', {
            id,
            className: `modal fade ${defaults.classes.modal} modal-confirm`,
            tabindex: '-1',
            'aria-labelledby': `${id}-title`,
            'aria-hidden': 'true'
        });

        modal.innerHTML = `
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="modal-icon ${iconColor}">
                            <i class="${icon}"></i>
                        </div>
                        <h5 class="modal-title mb-3" id="${id}-title">${escapeHtml(config.title)}</h5>
                        <p class="modal-message">${escapeHtml(config.message)}</p>
                        <div class="modal-buttons">
                            <button type="button" class="btn ${config.buttonClass} btn-ok">
                                ${escapeHtml(config.buttonText)}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        return modal;
    }

    /**
     * Get text color class for notification type
     *
     * @param {string} type - Notification type
     * @returns {string} Color class
     */
    getTypeColor(type) {
        const colors = {
            success: 'text-success',
            error: 'text-danger',
            danger: 'text-danger',
            warning: 'text-warning',
            info: 'text-info',
            primary: 'text-primary',
            secondary: 'text-secondary'
        };
        return colors[type] || 'text-primary';
    }

    /**
     * Close the active modal
     */
    close() {
        if (this.activeModal) {
            this.hideModalInstance(this.activeModal.element, this.activeModal.bsModal);
        }
    }

    /**
     * Set a result value on the active modal
     *
     * @param {*} result - Result value
     */
    setResult(result) {
        if (this.activeModal) {
            this.activeModal.element.dataset.result = JSON.stringify(result);
        }
    }

    /**
     * Set the default modal size
     *
     * @param {string} size - Size value (small, medium, large, xl, fullscreen)
     */
    setSize(size) {
        this.options.size = size;
    }

    /**
     * Set the default backdrop behavior
     *
     * @param {boolean|string} value - Backdrop value
     */
    setBackdrop(value) {
        this.options.backdrop = value;
    }

    /**
     * Check if a modal is currently open
     *
     * @returns {boolean} True if modal is open
     */
    isOpen() {
        return this.activeModal !== null;
    }
}
