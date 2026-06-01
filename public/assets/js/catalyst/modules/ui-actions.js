/**
 * Catalyst UI Actions
 * ----------------------------------------------------------------------------
 * CSP-safe replacement for common inline event handlers. Global event
 * delegation on `document` so any view loaded inside a layout that includes
 * this module automatically gets the behavior — no per-view wiring needed.
 *
 * Supported attributes:
 *
 *   [data-confirm]          on any element inside a <form> (or on the <form>
 *                           itself) — intercepts the form submission and
 *                           shows a native window.confirm dialog with the
 *                           attribute value as the message. If the user
 *                           cancels, the submission is blocked.
 *                           Replaces: onsubmit="return confirm('...')"
 *
 *   [data-history-back]     on any clickable element — calls
 *                           history.back() on click.
 *                           Replaces: onclick="history.back()"
 *
 *   [data-password-toggle]  on a button placed inside a .input-group that
 *                           wraps a password input. Toggles the input type
 *                           between "password" and "text" and flips the
 *                           eye / eye-slash icon inside the button.
 *                           (Strength meter is handled by modules/password.js)
 *
 *   [data-catalyst-href]    on a button-like control — navigates without using
 *                           anchors styled as Bootstrap buttons in admin UI.
 *                           Use data-catalyst-target="_blank" for new tabs.
 *
 * Loaded by boot-core/template/layouts/admin.php and base.php after Bootstrap,
 * before _catalyst-init.php. No ES-module imports — plain script.
 */

(function () {
    'use strict';

    if (window.__catalystUiActionsBound) return;
    window.__catalystUiActionsBound = true;

    // ----- data-confirm on form submit ----------------------------------------
    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!form || form.tagName !== 'FORM') return;

        // Attribute may live on the form itself or on the triggering submitter
        var msg = form.getAttribute('data-confirm');
        if (!msg && e.submitter) {
            msg = e.submitter.getAttribute('data-confirm');
        }
        if (!msg) return;

        if (!window.confirm(msg)) {
            e.preventDefault();
            e.stopPropagation();
        }
    }, true); // capture phase — run before Catalyst form-handler

    // ----- click delegation: history-back + password-toggle -------------------
    document.addEventListener('click', function (e) {
        var el = e.target;
        while (el && el !== document.body) {
            if (el.hasAttribute) {
                if (el.hasAttribute('data-history-back')) {
                    e.preventDefault();
                    if (window.history && typeof window.history.back === 'function') {
                        window.history.back();
                    }
                    return;
                }
                if (el.hasAttribute('data-catalyst-href')) {
                    e.preventDefault();
                    var href = el.getAttribute('data-catalyst-href') || '';
                    var target = el.getAttribute('data-catalyst-target') || '';
                    if (!href || href === '#') {
                        return;
                    }
                    if (target === '_blank') {
                        window.open(href, '_blank', 'noopener,noreferrer');
                    } else {
                        window.location.assign(href);
                    }
                    return;
                }
                if (el.hasAttribute('data-password-toggle')) {
                    var group = el.closest('.input-group') || el.parentElement;
                    if (!group) return;
                    var inp = group.querySelector('input[type="password"]')
                           || group.querySelector('input[type="text"]');
                    if (!inp) return;
                    var show = inp.type === 'password';
                    inp.type = show ? 'text' : 'password';
                    var ico = el.querySelector('i');
                    if (ico) {
                        ico.className = show ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
                    }
                    el.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
                    return;
                }
            }
            el = el.parentElement;
        }
    });
})();
