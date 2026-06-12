(function () {
    'use strict';

    var source = document.getElementById('catalyst-appearance-config');
    var html = document.documentElement;
    var storageKey = '__THEME_CONFIG__';
    var appearance = {};

    if (source) {
        try {
            appearance = JSON.parse(source.textContent || '{}') || {};
        } catch (_) {
            appearance = {};
        }
    }

    window.__CATALYST_APPEARANCE__ = appearance;

    var defaults = {
        skin: 'default',
        theme: 'light',
        'topbar-color': 'gray',
        'sidenav-color': 'dark',
        'sidenav-size': 'default',
        position: 'fixed',
        width: 'fluid',
        dir: 'ltr'
    };
    var allowed = {
        skin: ['default', 'minimal', 'modern', 'material', 'pixel', 'luxe', 'flat', 'red-cross', 'civil-protection', 'firefighters', 'grempa'],
        theme: ['light', 'dark', 'system'],
        'topbar-color': ['gray', 'light', 'dark'],
        'sidenav-color': ['dark', 'light', 'gray']
    };
    var closedSkins = {
        'red-cross': { theme: 'light', 'topbar-color': 'light', 'sidenav-color': 'light' },
        'civil-protection': { theme: 'light', 'topbar-color': 'dark', 'sidenav-color': 'light' },
        firefighters: { theme: 'light', 'topbar-color': 'dark', 'sidenav-color': 'dark' },
        grempa: { theme: 'dark', 'topbar-color': 'dark', 'sidenav-color': 'dark' }
    };

    function clone(value) {
        return JSON.parse(JSON.stringify(value));
    }

    function isLocked() {
        return appearance.customizerEnabled === false
            || appearance.customizer_enabled === false;
    }

    function storage() {
        try {
            var probe = storageKey + '__probe__';
            window.localStorage.setItem(probe, '1');
            window.localStorage.removeItem(probe);
            return window.localStorage;
        } catch (_) {
            return window.sessionStorage;
        }
    }

    function readStoredConfig() {
        try {
            var raw = storage().getItem(storageKey);
            return raw ? JSON.parse(raw) : null;
        } catch (_) {
            return null;
        }
    }

    function writeStoredConfig(config) {
        if (isLocked()) {
            return;
        }

        try {
            storage().setItem(storageKey, JSON.stringify(config));
        } catch (_) {}
    }

    function pick(value, fallback, choices) {
        return choices.indexOf(value) >= 0 ? value : fallback;
    }

    function getClosedSkinConfig(skin) {
        return Object.prototype.hasOwnProperty.call(closedSkins, skin)
            ? closedSkins[skin]
            : null;
    }

    function enforceClosedSkinConfig(config) {
        var preset = getClosedSkinConfig(config.skin);
        if (preset !== null) {
            config.theme = preset.theme;
            config['topbar-color'] = preset['topbar-color'];
            config['sidenav-color'] = preset['sidenav-color'];
        }
        return config;
    }

    function sanitize(raw) {
        var sourceConfig = raw && typeof raw === 'object' ? raw : {};
        return enforceClosedSkinConfig({
            skin: pick(sourceConfig.skin, defaults.skin, allowed.skin),
            theme: pick(sourceConfig.theme, defaults.theme, allowed.theme),
            'topbar-color': pick(sourceConfig['topbar-color'], defaults['topbar-color'], allowed['topbar-color']),
            'sidenav-color': pick(sourceConfig['sidenav-color'], defaults['sidenav-color'], allowed['sidenav-color']),
            'sidenav-size': defaults['sidenav-size'],
            position: defaults.position,
            width: defaults.width,
            dir: defaults.dir
        });
    }

    function systemTheme() {
        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }

    function currentMarkupConfig() {
        return {
            skin: html.getAttribute('data-skin') || defaults.skin,
            theme: html.getAttribute('data-bs-theme') || defaults.theme,
            'topbar-color': html.getAttribute('data-topbar-color') || defaults['topbar-color'],
            'sidenav-color': html.getAttribute('data-menu-color') || defaults['sidenav-color']
        };
    }

    function lockedConfig() {
        var candidate = appearance.lockedConfig || appearance.locked_config || null;
        return sanitize(Object.assign(
            {},
            defaults,
            candidate && typeof candidate === 'object' ? candidate : {}
        ));
    }

    var config = isLocked()
        ? lockedConfig()
        : sanitize(Object.assign({}, currentMarkupConfig(), readStoredConfig() || {}));
    var appliedTheme = config.theme === 'system' ? systemTheme() : config.theme;
    var closedSkin = getClosedSkinConfig(config.skin) !== null ? config.skin : 'none';

    window.defaultConfig = clone(defaults);
    window.config = clone(config);
    window.__CATALYST_APPEARANCE__.effectiveConfig = clone(config);

    writeStoredConfig(config);
    html.setAttribute('data-skin', config.skin);
    html.setAttribute('data-bs-theme', appliedTheme);
    html.setAttribute('data-topbar-color', config['topbar-color']);
    html.setAttribute('data-menu-color', config['sidenav-color']);
    html.setAttribute('data-sidenav-size', defaults['sidenav-size']);
    html.setAttribute('data-layout-position', defaults.position);
    html.setAttribute('data-layout-width', defaults.width);
    html.setAttribute('dir', defaults.dir);
    html.setAttribute('data-catalyst-theme-mode', config.theme);
    html.setAttribute('data-catalyst-closed-skin', closedSkin);
    html.setAttribute('data-catalyst-customizer-policy', isLocked() ? 'locked' : 'user');
    html.setAttribute('data-catalyst-red-cross-mode', config.skin === 'red-cross' ? 'enabled' : 'disabled');
})();
