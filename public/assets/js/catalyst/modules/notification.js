/**
 * Catalyst Notification System - Notification Handler
 *
 * Handles processing of JSON responses and automatic notification display.
 *
 * @package Catalyst
 * @author Walter Nuñez (arcanisgk)
 */

import { ToasterManager } from './toaster.js';
import { ModalManager } from './modal.js';

/**
 * NotificationHandler - Processes API responses and displays notifications
 */
export class NotificationHandler {
    /**
     * Create a new NotificationHandler instance
     *
     * @param {ToasterManager} toaster - Toaster manager instance
     * @param {ModalManager} modal - Modal manager instance
     */
    constructor(toaster, modal) {
        this.toaster = toaster;
        this.modal = modal;
        this.interceptorEnabled = false;
    }

    /**
     * Process a JSON response for notifications
     *
     * @param {Object} response - JSON response data
     */
    processResponse(response) {
        if (!response || typeof response !== 'object') return;

        // Check for notifications object
        const notifications = response.notifications;
        if (!notifications) return;

        // Process toasters
        if (notifications.toasters && Array.isArray(notifications.toasters)) {
            this.processToasters(notifications.toasters);
        }

        // Process modals
        if (notifications.modals && Array.isArray(notifications.modals)) {
            this.processModals(notifications.modals);
        }

        // Process alerts
        if (notifications.alerts && Array.isArray(notifications.alerts)) {
            this.processAlerts(notifications.alerts);
        }
    }

    /**
     * Process toaster notifications
     *
     * @param {Array} toasters - Array of toaster configurations
     */
    processToasters(toasters) {
        for (const toaster of toasters) {
            this.toaster.show(toaster.type || 'info', toaster.message, {
                id: toaster.id,
                title: toaster.title,
                icon: toaster.icon,
                duration: toaster.duration,
                dismissible: toaster.dismissible,
                actions: toaster.actions
            });
        }
    }

    /**
     * Process modal notifications
     *
     * @param {Array} modals - Array of modal configurations
     */
    processModals(modals) {
        // Process modals sequentially (one at a time)
        this.processModalsSequentially(modals, 0);
    }

    /**
     * Process modals one at a time
     *
     * @param {Array} modals - Array of modal configurations
     * @param {number} index - Current index
     */
    async processModalsSequentially(modals, index) {
        if (index >= modals.length) return;

        const modalConfig = modals[index];
        if (modalConfig.url) {
            await this.modal.load(modalConfig.url, {
                title: modalConfig.title,
                size: modalConfig.size,
                backdrop: modalConfig.backdrop,
                keyboard: modalConfig.keyboard,
                scrollable: modalConfig.scrollable,
                centered: modalConfig.centered
            });
        }

        // Process next modal (await so modals open sequentially, not simultaneously)
        await this.processModalsSequentially(modals, index + 1);
    }

    /**
     * Process inline alert notifications
     *
     * @param {Array} alerts - Array of alert configurations
     */
    processAlerts(alerts) {
        // Alerts are typically handled by the page itself
        // Dispatch custom event for page to handle
        document.dispatchEvent(new CustomEvent('catalyst:alerts', {
            detail: { alerts }
        }));
    }

    /**
     * Manually show a toaster from response-like data
     *
     * @param {string} type - Notification type
     * @param {string} message - Message content
     * @param {Object} options - Additional options
     * @returns {string} Toast ID
     */
    showToaster(type, message, options = {}) {
        return this.toaster.show(type, message, options);
    }

    /**
     * Manually show a modal from response-like data
     *
     * @param {Object} config - Modal configuration
     * @returns {Promise<*>}
     */
    async showModal(config) {
        if (config.url) {
            return this.modal.load(config.url, config);
        }
        return this.modal.show(config);
    }
}
