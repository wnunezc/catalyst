/**
 * Catalyst Notification System - Utility Functions
 *
 * @package Catalyst
 * @author Walter Nuñez (arcanisgk)
 */

/**
 * Generate a unique ID
 *
 * @param {string} prefix - ID prefix
 * @returns {string} Unique ID
 */
export function generateId(prefix = 'catalyst') {
    return `${prefix}_${Date.now()}_${Math.random().toString(36).substring(2, 9)}`;
}

/**
 * Escape HTML to prevent XSS
 *
 * @param {string} text - Text to escape
 * @returns {string} Escaped HTML
 */
export function escapeHtml(text) {
    if (typeof text !== 'string') return text;
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

export function clearElement(element) {
    if (element instanceof Element) {
        element.replaceChildren();
    }
}

export function renderTextList(element, items, itemTag = 'li') {
    if (!(element instanceof Element)) {
        return;
    }

    const fragment = document.createDocumentFragment();

    (Array.isArray(items) ? items : []).forEach((item) => {
        const node = document.createElement(itemTag);
        node.textContent = typeof item === 'string' ? item : String(item ?? '');
        fragment.appendChild(node);
    });

    element.replaceChildren(fragment);
}

/**
 * Deep merge objects
 *
 * @param {Object} target - Target object
 * @param {...Object} sources - Source objects
 * @returns {Object} Merged object
 */
export function deepMerge(target, ...sources) {
    if (!sources.length) return target;
    const source = sources.shift();

    if (isObject(target) && isObject(source)) {
        for (const key in source) {
            if (isObject(source[key])) {
                if (!target[key]) Object.assign(target, { [key]: {} });
                deepMerge(target[key], source[key]);
            } else {
                Object.assign(target, { [key]: source[key] });
            }
        }
    }

    return deepMerge(target, ...sources);
}

/**
 * Check if value is a plain object
 *
 * @param {*} item - Value to check
 * @returns {boolean} True if plain object
 */
export function isObject(item) {
    return item && typeof item === 'object' && !Array.isArray(item);
}

/**
 * Create a DOM element with attributes and children
 *
 * @param {string} tag - HTML tag name
 * @param {Object} attrs - Element attributes
 * @param {...(string|Node)} children - Child elements or text
 * @returns {HTMLElement} Created element
 */
export function createElement(tag, attrs = {}, ...children) {
    const el = document.createElement(tag);

    for (const [key, value] of Object.entries(attrs)) {
        if (key === 'className') {
            el.className = value;
        } else if (key === 'dataset') {
            Object.assign(el.dataset, value);
        } else if (key.startsWith('on') && typeof value === 'function') {
            el.addEventListener(key.substring(2).toLowerCase(), value);
        } else if (value !== null && value !== undefined) {
            el.setAttribute(key, value);
        }
    }

    for (const child of children) {
        if (typeof child === 'string') {
            el.appendChild(document.createTextNode(child));
        } else if (child instanceof Node) {
            el.appendChild(child);
        }
    }

    return el;
}

/**
 * Wait for a specified duration
 *
 * @param {number} ms - Duration in milliseconds
 * @returns {Promise<void>}
 */
export function delay(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

/**
 * Debounce a function
 *
 * @param {Function} func - Function to debounce
 * @param {number} wait - Wait time in milliseconds
 * @returns {Function} Debounced function
 */
export function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Create a promise that can be resolved/rejected externally
 *
 * @returns {{promise: Promise, resolve: Function, reject: Function}}
 */
export function createDeferred() {
    let resolve, reject;
    const promise = new Promise((res, rej) => {
        resolve = res;
        reject = rej;
    });
    return { promise, resolve, reject };
}

/**
 * Dispatch a custom event
 *
 * @param {HTMLElement} element - Element to dispatch from
 * @param {string} eventName - Event name
 * @param {*} detail - Event detail data
 */
export function dispatchEvent(element, eventName, detail = null) {
    element.dispatchEvent(new CustomEvent(eventName, {
        bubbles: true,
        cancelable: true,
        detail
    }));
}

/**
 * Parse JSON safely
 *
 * @param {string} json - JSON string
 * @param {*} fallback - Fallback value on error
 * @returns {*} Parsed value or fallback
 */
export function parseJson(json, fallback = null) {
    try {
        return JSON.parse(json);
    } catch {
        return fallback;
    }
}

/**
 * Check if an element is visible in the viewport
 *
 * @param {HTMLElement} element - Element to check
 * @returns {boolean} True if visible
 */
export function isElementVisible(element) {
    const rect = element.getBoundingClientRect();
    return (
        rect.top >= 0 &&
        rect.left >= 0 &&
        rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
        rect.right <= (window.innerWidth || document.documentElement.clientWidth)
    );
}

/**
 * Get CSS variable value
 *
 * @param {string} name - Variable name (without --)
 * @param {HTMLElement} element - Element to get from (default: document.documentElement)
 * @returns {string} Variable value
 */
export function getCssVariable(name, element = document.documentElement) {
    return getComputedStyle(element).getPropertyValue(`--${name}`).trim();
}

/**
 * Set CSS variable value
 *
 * @param {string} name - Variable name (without --)
 * @param {string} value - Variable value
 * @param {HTMLElement} element - Element to set on (default: document.documentElement)
 */
export function setCssVariable(name, value, element = document.documentElement) {
    element.style.setProperty(`--${name}`, value);
}
