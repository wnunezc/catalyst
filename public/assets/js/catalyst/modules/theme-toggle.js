/**
 * Catalyst / Inspinia Theme Toggle
 * ----------------------------------------------------------------------------
 * Small compatibility bridge for legacy `[data-theme-toggle]` controls.
 * The canonical shell customizer stores its state in `__THEME_CONFIG__` and
 * uses Bootstrap's `data-bs-theme`; this module only keeps those two in sync.
 */
(function (global) {
    'use strict';

    var STORAGE_KEY = '__THEME_CONFIG__';
    var ATTR = 'data-bs-theme';
    var VALID = ['light', 'dark'];
    var CLOSED_SKIN_CONFIGS = {
        'red-cross': {
            theme: 'light',
            'topbar-color': 'light',
            'sidenav-color': 'light'
        },
        'civil-protection': {
            theme: 'light',
            'topbar-color': 'dark',
            'sidenav-color': 'light'
        },
        firefighters: {
            theme: 'light',
            'topbar-color': 'dark',
            'sidenav-color': 'dark'
        },
        grempa: {
            theme: 'dark',
            'topbar-color': 'dark',
            'sidenav-color': 'dark'
        }
    };
    var DEFAULT_CONFIG = {
        skin: 'default',
        theme: 'light',
        'topbar-color': 'gray',
        'sidenav-color': 'dark',
        'sidenav-size': 'default',
        position: 'fixed',
        width: 'fluid',
        dir: 'ltr'
    };

    function platformAppearance() {
        return global.__CATALYST_APPEARANCE__ && typeof global.__CATALYST_APPEARANCE__ === 'object'
            ? global.__CATALYST_APPEARANCE__
            : {};
    }

    function isCustomizerLocked() {
        var appearance = platformAppearance();
        return appearance.adminCustomizerEnabled === false || appearance.admin_customizer_enabled === false;
    }

    function lockedConfig() {
        var appearance = platformAppearance();
        var candidate = appearance.lockedConfig || appearance.locked_config || null;
        return candidate && typeof candidate === 'object'
            ? enforceClosedSkinConfig(Object.assign(clone(DEFAULT_CONFIG), candidate))
            : null;
    }

    var listeners = [];
    var syncing = false;

    function storage() {
        try {
            var probe = STORAGE_KEY + '__probe__';
            localStorage.setItem(probe, '1');
            localStorage.removeItem(probe);
            return localStorage;
        } catch (_) {
            return sessionStorage;
        }
    }

    function clone(value) {
        return JSON.parse(JSON.stringify(value));
    }

    function getClosedSkinConfig(skin) {
        return Object.prototype.hasOwnProperty.call(CLOSED_SKIN_CONFIGS, skin)
            ? CLOSED_SKIN_CONFIGS[skin]
            : null;
    }

    function enforceClosedSkinConfig(config) {
        var preset = config ? getClosedSkinConfig(config.skin) : null;
        if (preset !== null) {
            config.theme = preset.theme;
            config['topbar-color'] = preset['topbar-color'];
            config['sidenav-color'] = preset['sidenav-color'];
        }

        return config;
    }

    function readConfig() {
        if (isCustomizerLocked()) {
            return lockedConfig() || clone(global.config || DEFAULT_CONFIG);
        }

        try {
            var raw = storage().getItem(STORAGE_KEY);
            if (!raw) {
                return clone(global.config || DEFAULT_CONFIG);
            }

            var parsed = JSON.parse(raw);
            return parsed && typeof parsed === 'object'
                ? Object.assign(clone(DEFAULT_CONFIG), parsed)
                : clone(DEFAULT_CONFIG);
        } catch (_) {
            return clone(global.config || DEFAULT_CONFIG);
        }
    }

    function writeConfig(config) {
        var normalized = isCustomizerLocked()
            ? (lockedConfig() || enforceClosedSkinConfig(Object.assign(clone(DEFAULT_CONFIG), config || {})))
            : enforceClosedSkinConfig(Object.assign(clone(DEFAULT_CONFIG), config || {}));
        normalized.theme = VALID.indexOf(normalized.theme) !== -1 ? normalized.theme : DEFAULT_CONFIG.theme;
        normalized['sidenav-size'] = DEFAULT_CONFIG['sidenav-size'];

        if (!isCustomizerLocked()) {
            try {
                storage().setItem(STORAGE_KEY, JSON.stringify(normalized));
            } catch (_) {}
        }

        global.defaultConfig = clone(DEFAULT_CONFIG);
        global.config = clone(normalized);
        if (global.__CATALYST_APPEARANCE__ && typeof global.__CATALYST_APPEARANCE__ === 'object') {
            global.__CATALYST_APPEARANCE__.effectiveConfig = clone(normalized);
        }
        return normalized;
    }

    function currentTheme() {
        var config = readConfig();
        var preset = getClosedSkinConfig(config.skin);
        if (preset !== null) {
            return preset.theme;
        }

        var htmlTheme = document.documentElement.getAttribute(ATTR);
        if (VALID.indexOf(htmlTheme) !== -1) {
            return htmlTheme;
        }

        var configTheme = config.theme;
        return VALID.indexOf(configTheme) !== -1 ? configTheme : DEFAULT_CONFIG.theme;
    }

    function apply(theme, persist) {
        var config = readConfig();
        var preset = getClosedSkinConfig(config.skin);
        if (preset !== null) {
            theme = preset.theme;
            config.theme = preset.theme;
            config['topbar-color'] = preset['topbar-color'];
            config['sidenav-color'] = preset['sidenav-color'];
        }

        if (VALID.indexOf(theme) === -1) {
            return;
        }

        syncing = true;
        document.documentElement.setAttribute(ATTR, theme);
        document.documentElement.setAttribute('data-sidenav-size', DEFAULT_CONFIG['sidenav-size']);
        document.documentElement.setAttribute('data-migrationui-theme-mode', theme);
        if (preset !== null) {
            document.documentElement.setAttribute('data-topbar-color', preset['topbar-color']);
            document.documentElement.setAttribute('data-menu-color', preset['sidenav-color']);
            document.documentElement.setAttribute('data-catalyst-closed-skin', config.skin);
        } else {
            document.documentElement.setAttribute('data-catalyst-closed-skin', 'none');
        }
        document.documentElement.setAttribute('data-catalyst-red-cross-mode', config.skin === 'red-cross' ? 'enabled' : 'disabled');
        syncing = false;

        if (persist !== false) {
            config.theme = theme;
            writeConfig(config);
        }
    }

    function notify(theme) {
        listeners.forEach(function (fn) {
            try { fn(theme); } catch (_) {}
        });
    }

    var api = {
        get: currentTheme,
        set: function (theme) {
            if (isCustomizerLocked()) {
                apply(api.get(), false);
                return;
            }
            if (VALID.indexOf(theme) === -1) return;
            apply(theme, true);
            notify(theme);
        },
        toggle: function () {
            if (isCustomizerLocked()) {
                apply(api.get(), false);
                return;
            }
            api.set(api.get() === 'dark' ? 'light' : 'dark');
        },
        onChange: function (fn) {
            if (typeof fn === 'function') listeners.push(fn);
        }
    };

    global.Catalyst = global.Catalyst || {};
    global.Catalyst.theme = api;
    global.CatalystTheme = api;

    document.addEventListener('click', function (event) {
        var target = event.target;
        while (target && target !== document.body) {
            if (target.hasAttribute && target.hasAttribute('data-theme-toggle')) {
                event.preventDefault();
                api.toggle();
                return;
            }

            if (target.hasAttribute && target.hasAttribute('data-theme-set')) {
                event.preventDefault();
                api.set(target.getAttribute('data-theme-set'));
                return;
            }

            target = target.parentElement;
        }
    });

    apply(currentTheme(), false);

    new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
            if (mutation.type !== 'attributes' || mutation.attributeName !== ATTR || syncing) {
                return;
            }

            var theme = currentTheme();
            var config = readConfig();
            var preset = getClosedSkinConfig(config.skin);
            if (preset !== null && document.documentElement.getAttribute(ATTR) !== preset.theme) {
                syncing = true;
                document.documentElement.setAttribute(ATTR, preset.theme);
                document.documentElement.setAttribute('data-topbar-color', preset['topbar-color']);
                document.documentElement.setAttribute('data-menu-color', preset['sidenav-color']);
                document.documentElement.setAttribute('data-catalyst-closed-skin', config.skin);
                document.documentElement.setAttribute('data-catalyst-red-cross-mode', config.skin === 'red-cross' ? 'enabled' : 'disabled');
                syncing = false;
                theme = preset.theme;
            }
            config.theme = theme;
            writeConfig(config);
            notify(theme);
        });
    }).observe(document.documentElement, {
        attributes: true,
        attributeFilter: [ATTR]
    });
})(window);
