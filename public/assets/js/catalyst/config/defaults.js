/**
 * Catalyst Notification System - Default Configuration
 *
 * @package Catalyst
 * @author Walter Nuñez (arcanisgk)
 */

export const defaults = {
    // Toaster defaults
    toaster: {
        position: 'top-right',
        duration: 5000,
        maxToasts: 5,
        gap: 10,
        pauseOnHover: true,
        showProgress: true,
        newestOnTop: true,
    },

    // Modal defaults
    modal: {
        size: 'medium',
        backdrop: true,
        keyboard: true,
        scrollable: false,
        centered: true,
        focus: true,
    },

    // Type-specific icons (FontAwesome 6.x)
    icons: {
        success: 'fa-solid fa-circle-check',
        error: 'fa-solid fa-circle-xmark',
        danger: 'fa-solid fa-circle-xmark',
        warning: 'fa-solid fa-triangle-exclamation',
        info: 'fa-solid fa-circle-info',
        primary: 'fa-solid fa-circle',
        secondary: 'fa-solid fa-circle',
        loading: 'fa-solid fa-spinner fa-spin',
        confirm: 'fa-solid fa-question-circle',
    },

    // Type-specific durations
    durations: {
        success: 5000,
        error: 0,      // Don't auto-close errors
        danger: 0,
        warning: 7000,
        info: 5000,
        primary: 5000,
        secondary: 5000,
    },

    // Modal sizes (Bootstrap classes)
    modalSizes: {
        small: 'modal-sm',
        medium: '',
        large: 'modal-lg',
        xl: 'modal-xl',
        fullscreen: 'modal-fullscreen',
    },

    // Confirm dialog defaults
    confirm: {
        title: 'Confirm',
        confirmText: 'Confirm',
        cancelText: 'Cancel',
        confirmClass: 'btn-primary',
        cancelClass: 'btn-secondary',
        type: 'warning',
    },

    // Alert dialog defaults
    alert: {
        title: 'Alert',
        buttonText: 'OK',
        buttonClass: 'btn-primary',
        type: 'info',
    },

    // CSS classes
    classes: {
        toasterContainer: 'catalyst-toaster-container',
        toast: 'catalyst-toast',
        modal: 'catalyst-modal',
        alert: 'catalyst-alert',
        flashContainer: 'catalyst-flash-container',
    },

    // Animation durations (ms)
    animations: {
        toastIn: 300,
        toastOut: 300,
        modalIn: 200,
        modalOut: 200,
        alertIn: 300,
        alertOut: 300,
    },
};

/**
 * Get default icon for a notification type
 *
 * @param {string} type - Notification type
 * @returns {string} FontAwesome icon class
 */
export function getDefaultIcon(type) {
    return defaults.icons[type] || defaults.icons.info;
}

/**
 * Get default duration for a notification type
 *
 * @param {string} type - Notification type
 * @returns {number} Duration in milliseconds
 */
export function getDefaultDuration(type) {
    return defaults.durations[type] ?? defaults.toaster.duration;
}

/**
 * Get Bootstrap modal size class
 *
 * @param {string} size - Size name
 * @returns {string} Bootstrap modal class
 */
export function getModalSizeClass(size) {
    return defaults.modalSizes[size] || defaults.modalSizes.medium;
}
