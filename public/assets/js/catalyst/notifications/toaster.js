/**
 * Catalyst Notification System - Toaster Manager
 *
 * Manages floating toast notifications using Bootstrap 5 Toast component.
 *
 * @package Catalyst
 * @author Walter Nuñez (arcanisgk)
 */

import { defaults, getDefaultIcon, getDefaultDuration } from '../config/defaults.js';
import { generateId, createElement, escapeHtml, delay } from '../core/utils.js';

/**
 * ToasterManager - Manages toast notifications
 */
export class ToasterManager {
    /**
     * Create a new ToasterManager instance
     *
     * @param {Object} options - Configuration options
     */
    constructor(options = {}) {
        this.options = {
            ...defaults.toaster,
            ...options
        };

        this.container = null;
        this.toasts = new Map();
        this.initialized = false;
    }

    /**
     * Initialize the toaster manager
     */
    init() {
        if (this.initialized) return;

        this.createContainer();
        this.initialized = true;
    }

    /**
     * Create the toast container element
     */
    createContainer() {
        // Check if container already exists
        this.container = document.getElementById('catalyst-toaster-container');

        if (!this.container) {
            this.container = createElement('div', {
                id: 'catalyst-toaster-container',
                className: defaults.classes.toasterContainer,
                dataset: { position: this.options.position },
                'aria-live': 'polite',
                'aria-atomic': 'true'
            });
            document.body.appendChild(this.container);
        } else {
            this.container.classList.add(defaults.classes.toasterContainer);
            this.container.dataset.position = this.options.position;
        }
    }

    /**
     * Show a toast notification
     *
     * @param {string} type - Notification type (success, error, warning, info)
     * @param {string} message - Message content
     * @param {Object} options - Additional options
     * @returns {string} Toast ID
     */
    show(type, message, options = {}) {
        if (!this.initialized) this.init();

        const id = options.id || generateId('toast');
        const icon = options.icon || getDefaultIcon(type);
        const duration = options.duration ?? getDefaultDuration(type);
        const title = options.title || this.getDefaultTitle(type);
        const dismissible = options.dismissible ?? true;
        const actions = options.actions || [];

        // Limit number of toasts
        if (this.toasts.size >= this.options.maxToasts) {
            const oldestId = this.toasts.keys().next().value;
            this.hide(oldestId);
        }

        // Create toast element
        const toast = this.createToastElement({
            id,
            type,
            message,
            title,
            icon,
            duration,
            dismissible,
            actions
        });

        // Add to container
        if (this.options.newestOnTop) {
            this.container.prepend(toast);
        } else {
            this.container.appendChild(toast);
        }

        // Store reference
        this.toasts.set(id, {
            element: toast,
            timer: null,
            paused: false
        });

        // Setup auto-dismiss
        if (duration > 0) {
            this.startTimer(id, duration);
        }

        // Setup hover pause
        if (this.options.pauseOnHover && duration > 0) {
            toast.addEventListener('mouseenter', () => this.pauseTimer(id));
            toast.addEventListener('mouseleave', () => this.resumeTimer(id));
        }

        return id;
    }

    /**
     * Create the toast DOM element
     *
     * @param {Object} config - Toast configuration
     * @returns {HTMLElement} Toast element
     */
    createToastElement({ id, type, message, title, icon, duration, dismissible, actions }) {
        const toast = createElement('div', {
            id,
            className: `${defaults.classes.toast} toast-${type}`,
            role: 'alert',
            'aria-live': 'assertive',
            'aria-atomic': 'true'
        });

        // Header
        const header = createElement('div', { className: 'toast-header' },
            createElement('i', { className: `toast-icon ${icon}` }),
            createElement('strong', { className: 'me-auto' }, title)
        );

        if (dismissible) {
            const closeBtn = createElement('button', {
                type: 'button',
                className: 'btn-close',
                'aria-label': 'Close',
                onClick: () => this.hide(id)
            });
            header.appendChild(closeBtn);
        }

        toast.appendChild(header);

        // Body
        const body = createElement('div', { className: 'toast-body' });
        body.innerHTML = escapeHtml(message);

        // Actions
        if (actions.length > 0) {
            const actionsDiv = createElement('div', { className: 'toast-actions' });
            actions.forEach(action => {
                const btn = createElement('button', {
                    type: 'button',
                    className: `btn btn-sm ${action.class || 'btn-outline-light'}`,
                    onClick: () => {
                        if (action.url) {
                            document.dispatchEvent(new CustomEvent('catalyst:navigation:start'));
                            window.location.href = action.url;
                        } else if (action.callback) {
                            action.callback();
                        }
                        if (action.dismiss !== false) {
                            this.hide(id);
                        }
                    }
                }, action.label);
                actionsDiv.appendChild(btn);
            });
            body.appendChild(actionsDiv);
        }

        toast.appendChild(body);

        // Progress bar
        if (duration > 0 && this.options.showProgress) {
            const progress = createElement('div', { className: 'toast-progress' });
            progress.style.animationDuration = `${duration}ms`;
            toast.appendChild(progress);
            // Start progress animation
            requestAnimationFrame(() => progress.classList.add('running'));
        }

        return toast;
    }

    /**
     * Get default title for a notification type
     *
     * @param {string} type - Notification type
     * @returns {string} Default title
     */
    getDefaultTitle(type) {
        const titles = {
            success: 'Success',
            error: 'Error',
            danger: 'Error',
            warning: 'Warning',
            info: 'Information',
            primary: 'Notice',
            secondary: 'Notice'
        };
        return titles[type] || 'Notification';
    }

    /**
     * Start auto-dismiss timer
     *
     * @param {string} id - Toast ID
     * @param {number} duration - Duration in milliseconds
     */
    startTimer(id, duration) {
        const toast = this.toasts.get(id);
        if (!toast) return;

        toast.remainingTime = duration;
        toast.startTime = Date.now();
        toast.timer = setTimeout(() => this.hide(id), duration);
    }

    /**
     * Pause the auto-dismiss timer
     *
     * @param {string} id - Toast ID
     */
    pauseTimer(id) {
        const toast = this.toasts.get(id);
        if (!toast || toast.paused) return;

        clearTimeout(toast.timer);
        toast.paused = true;
        toast.remainingTime = toast.remainingTime - (Date.now() - toast.startTime);

        // Pause progress animation
        const progress = toast.element.querySelector('.toast-progress');
        if (progress) {
            progress.style.animationPlayState = 'paused';
        }
    }

    /**
     * Resume the auto-dismiss timer
     *
     * @param {string} id - Toast ID
     */
    resumeTimer(id) {
        const toast = this.toasts.get(id);
        if (!toast || !toast.paused) return;

        toast.paused = false;
        toast.startTime = Date.now();
        toast.timer = setTimeout(() => this.hide(id), toast.remainingTime);

        // Resume progress animation
        const progress = toast.element.querySelector('.toast-progress');
        if (progress) {
            progress.style.animationPlayState = 'running';
        }
    }

    /**
     * Hide and remove a toast
     *
     * @param {string} id - Toast ID
     * @returns {Promise<void>}
     */
    async hide(id) {
        const toast = this.toasts.get(id);
        if (!toast) return;

        // Clear timer
        clearTimeout(toast.timer);

        // Add hiding animation
        toast.element.classList.add('hiding');

        // Wait for animation
        await delay(defaults.animations.toastOut);

        // Remove element
        toast.element.remove();
        this.toasts.delete(id);
    }

    /**
     * Hide all toasts
     */
    hideAll() {
        for (const id of this.toasts.keys()) {
            this.hide(id);
        }
    }

    /**
     * Show a success toast
     *
     * @param {string} message - Message content
     * @param {Object} options - Additional options
     * @returns {string} Toast ID
     */
    success(message, options = {}) {
        return this.show('success', message, options);
    }

    /**
     * Show an error toast
     *
     * @param {string} message - Message content
     * @param {Object} options - Additional options
     * @returns {string} Toast ID
     */
    error(message, options = {}) {
        return this.show('error', message, options);
    }

    /**
     * Show a warning toast
     *
     * @param {string} message - Message content
     * @param {Object} options - Additional options
     * @returns {string} Toast ID
     */
    warning(message, options = {}) {
        return this.show('warning', message, options);
    }

    /**
     * Show an info toast
     *
     * @param {string} message - Message content
     * @param {Object} options - Additional options
     * @returns {string} Toast ID
     */
    info(message, options = {}) {
        return this.show('info', message, options);
    }

    /**
     * Set the toaster position
     *
     * @param {string} position - Position value (top-right, top-left, etc.)
     */
    setPosition(position) {
        this.options.position = position;
        if (this.container) {
            this.container.dataset.position = position;
        }
    }

    /**
     * Set the default duration
     *
     * @param {number} duration - Duration in milliseconds
     */
    setDuration(duration) {
        this.options.duration = duration;
    }

    /**
     * Set the maximum number of toasts
     *
     * @param {number} count - Maximum count
     */
    setMaxToasts(count) {
        this.options.maxToasts = count;
    }

    /**
     * Get the number of active toasts
     *
     * @returns {number} Toast count
     */
    count() {
        return this.toasts.size;
    }
}
