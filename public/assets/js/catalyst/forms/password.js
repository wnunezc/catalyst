/**
 * Catalyst Password Manager
 *
 * Features:
 *   - Eye toggle button: shows/hides password text
 *   - Real-time strength meter: reads policy from data-password-policy first,
 *     then falls back to window.CatalystPasswordPolicy
 *
 * HTML conventions:
 *
 *   Toggle button (must be a sibling inside .input-group):
 *     <button type="button" data-password-toggle tabindex="-1" aria-label="Show password">
 *         <i class="fa-solid fa-eye"></i>
 *     </button>
 *
 *   Strength meter (add data-strength to the input, place .password-strength after the .input-group):
 *     <input type="password" class="form-control" data-strength>
 *     ...
 *     <div class="password-strength d-none mt-1">
 *         <div class="progress" style="height:4px">
 *             <div class="progress-bar" role="progressbar"></div>
 *         </div>
 *         <small class="password-strength-label text-muted"></small>
 *     </div>
 *
 *   Policy (preferred on the field or any ancestor container):
 *     <form data-password-policy='{"minLength":12,"requireUppercase":true,"requireLowercase":true,"requireNumber":true,"requireSymbol":true}'>
 *
 *   Legacy global policy fallback:
 *     window.CatalystPasswordPolicy = { minLength: 12, requireUppercase: true, requireLowercase: true, requireNumber: true, requireSymbol: true };
 *
 * @package Catalyst
 */

export class PasswordManager {
    /**
     * @param {Object} options
     * @param {string} [options.toggleAttr]            - Attribute on toggle buttons
     * @param {string} [options.strengthAttr]          - Attribute on password inputs that need a meter
     * @param {string} [options.strengthContainerClass]- CSS class of the strength container element
     * @param {string} [options.policyKey]             - window property name that holds the policy object
     * @param {Object} [options.defaultPolicy]         - Fallback policy when window[policyKey] is absent
     */
    constructor(options = {}) {
        this.options = {
            toggleAttr:             'data-password-toggle',
            strengthAttr:           'data-strength',
            strengthContainerClass: 'password-strength',
            policyKey:              'CatalystPasswordPolicy',
            defaultPolicy: {
                minLength:        12,
                requireUppercase: true,
                requireLowercase: true,
                requireNumber:    true,
                requireSymbol:    true,
            },
            ...options
        };
        this.boundRoots = new WeakSet();
        this.strengthInputs = new WeakSet();
    }

    /**
     * Bind delegated toggles once and scan the supplied subtree for meters.
     */
    init(options = {}) {
        const scanRoot = options.scanRoot instanceof HTMLElement ? options.scanRoot : document.body;
        const eventRoot = options.eventRoot instanceof HTMLElement ? options.eventRoot : document.body;
        if (!(scanRoot instanceof HTMLElement) || !(eventRoot instanceof HTMLElement)) {
            return;
        }

        this.#bindToggle(eventRoot);
        this.#initStrength(scanRoot);
    }

    // --- Private -------------------------------------------------------------

    #bindToggle(eventRoot) {
        if (this.boundRoots.has(eventRoot)) {
            return;
        }

        this.boundRoots.add(eventRoot);
        eventRoot.addEventListener('click', (event) => {
            const origin = event.target instanceof Element ? event.target : null;
            const trigger = origin?.closest(`[${this.options.toggleAttr}]`);
            if (!(trigger instanceof HTMLElement) || !eventRoot.contains(trigger)) {
                return;
            }

            const group = trigger.closest('.input-group') ?? trigger.parentElement;
            const input = group?.querySelector('input[type="password"], input[type="text"]');
            if (!(input instanceof HTMLInputElement)) {
                return;
            }

            event.preventDefault();
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            trigger.setAttribute('aria-pressed', show ? 'true' : 'false');
            trigger.dataset.passwordShowLabel ??= trigger.getAttribute('aria-label') || 'Show password';
            if (show && trigger.dataset.passwordHideLabel) {
                trigger.setAttribute('aria-label', trigger.dataset.passwordHideLabel);
            } else if (!show) {
                trigger.setAttribute('aria-label', trigger.dataset.passwordShowLabel);
            }

            const icon = trigger.querySelector('i');
            if (icon) {
                if (icon.classList.contains('fa-eye') || icon.classList.contains('fa-eye-slash')) {
                    icon.classList.toggle('fa-eye', !show);
                    icon.classList.toggle('fa-eye-slash', show);
                }
                if (icon.classList.contains('ti-eye') || icon.classList.contains('ti-eye-off')) {
                    icon.classList.toggle('ti-eye', !show);
                    icon.classList.toggle('ti-eye-off', show);
                }
            }
        });
    }

    #initStrength(scanRoot) {
        const selector = `input[${this.options.strengthAttr}]`;
        const inputs = [
            ...(scanRoot.matches(selector) ? [scanRoot] : []),
            ...scanRoot.querySelectorAll(selector),
        ];

        inputs.forEach(input => {
            if (!(input instanceof HTMLInputElement) || this.strengthInputs.has(input)) {
                return;
            }

            const policy = this.#resolvePolicy(input);

            // Strength container: sibling of input-group (the input-group's next siblings)
            const group     = input.closest('.input-group');
            const container = group?.parentElement
                ?.querySelector('.' + this.options.strengthContainerClass);
            if (!container) return;

            const bar   = container.querySelector('.progress-bar');
            const label = container.querySelector('.password-strength-label');
            if (!bar || !label) return;

            this.strengthInputs.add(input);
            input.addEventListener('input', () => {
                const value = input.value;

                if (value.length === 0) {
                    container.classList.add('d-none');
                    bar.style.width   = '0%';
                    bar.className     = 'progress-bar';
                    label.textContent = '';
                    return;
                }

                container.classList.remove('d-none');
                this.#update(bar, label, this.#score(value, policy));
            });
        });
    }

    /**
     * Resolve policy from the input, its ancestors, the legacy global window
     * slot, or finally the built-in default policy.
     *
     * @param {HTMLInputElement} input
     * @returns {Object}
     */
    #resolvePolicy(input) {
        const rawPolicy = input.getAttribute('data-password-policy')
            || input.closest('[data-password-policy]')?.getAttribute('data-password-policy')
            || null;

        if (rawPolicy) {
            try {
                const parsed = JSON.parse(rawPolicy);
                if (parsed && typeof parsed === 'object') {
                    return { ...this.options.defaultPolicy, ...parsed };
                }
            } catch (error) {
                console.warn('[password] invalid data-password-policy payload:', error);
            }
        }

        const globalPolicy = window[this.options.policyKey];
        if (globalPolicy && typeof globalPolicy === 'object') {
            return { ...this.options.defaultPolicy, ...globalPolicy };
        }

        return this.options.defaultPolicy;
    }

    /**
     * Score password strength: 0 (terrible) → 5 (excellent)
     *
     * @param {string} value
     * @param {Object} policy
     * @returns {number} 0–5
     */
    #score(value, policy) {
        let score = 0;
        if (value.length >= policy.minLength) score++;
        if (value.length >= 12)              score++;
        if (/[A-Z]/.test(value))             score++;
        if (/[a-z]/.test(value))             score++;
        if (/[0-9]/.test(value))             score++;
        if (/[^A-Za-z0-9]/.test(value))      score++;
        return score;
    }

    /**
     * Update the strength bar and label to reflect a given score (0–5)
     *
     * @param {HTMLElement} bar
     * @param {HTMLElement} label
     * @param {number}      score
     */
    #update(bar, label, score) {
        const levels = [
            { pct: 10,  cls: 'bg-danger',  text: 'Very weak',   labelCls: 'text-danger'  },
            { pct: 25,  cls: 'bg-danger',  text: 'Weak',        labelCls: 'text-danger'  },
            { pct: 50,  cls: 'bg-warning', text: 'Fair',        labelCls: 'text-warning' },
            { pct: 75,  cls: 'bg-info',    text: 'Good',        labelCls: 'text-info'    },
            { pct: 90,  cls: 'bg-primary', text: 'Strong',      labelCls: 'text-primary' },
            { pct: 100, cls: 'bg-success', text: 'Very strong', labelCls: 'text-success' },
        ];

        const level = levels[Math.min(score, levels.length - 1)];

        bar.style.width   = level.pct + '%';
        bar.className     = 'progress-bar ' + level.cls;
        label.textContent = level.text;
        label.className   = 'password-strength-label small ' + level.labelCls;
    }
}
