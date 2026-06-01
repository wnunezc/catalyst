function clone(value) {
    return JSON.parse(JSON.stringify(value));
}

function resolveSystemTheme(mediaQuery) {
    return mediaQuery.matches ? 'dark' : 'light';
}

function resolveThemeStorage(themeStorageKey) {
    try {
        const probeKey = `${themeStorageKey}__probe__`;
        window.localStorage.setItem(probeKey, '1');
        window.localStorage.removeItem(probeKey);
        return window.localStorage;
    } catch (_) {
        return window.sessionStorage;
    }
}

const CLOSED_SKIN_CONFIGS = {
    'red-cross': {
        theme: 'light',
        'topbar-color': 'light',
        'sidenav-color': 'light',
    },
    'civil-protection': {
        theme: 'light',
        'topbar-color': 'dark',
        'sidenav-color': 'light',
    },
    firefighters: {
        theme: 'light',
        'topbar-color': 'dark',
        'sidenav-color': 'dark',
    },
    grempa: {
        theme: 'dark',
        'topbar-color': 'dark',
        'sidenav-color': 'dark',
    },
};

function getClosedSkinConfig(skin) {
    return Object.prototype.hasOwnProperty.call(CLOSED_SKIN_CONFIGS, skin)
        ? CLOSED_SKIN_CONFIGS[skin]
        : null;
}

function enforceClosedSkinConfig(config) {
    const preset = getClosedSkinConfig(config.skin);

    if (preset !== null) {
        config.theme = preset.theme;
        config['topbar-color'] = preset['topbar-color'];
        config['sidenav-color'] = preset['sidenav-color'];
    }

    return config;
}

function platformAppearance() {
    return window.__CATALYST_APPEARANCE__ && typeof window.__CATALYST_APPEARANCE__ === 'object'
        ? window.__CATALYST_APPEARANCE__
        : {};
}

function isCustomizerLocked() {
    const appearance = platformAppearance();
    return appearance.adminCustomizerEnabled === false || appearance.admin_customizer_enabled === false;
}

function getPlatformLockedConfig(fallback) {
    const appearance = platformAppearance();
    const candidate = appearance.lockedConfig || appearance.locked_config || null;
    return candidate && typeof candidate === 'object'
        ? enforceClosedSkinConfig(Object.assign(clone(fallback), candidate))
        : enforceClosedSkinConfig(clone(fallback));
}

function persistThemeConfig(storageArea, themeStorageKey, config) {
    const serialized = JSON.stringify(config);

    try {
        storageArea.setItem(themeStorageKey, serialized);
    } catch (_) {
        try {
            window.sessionStorage.setItem(themeStorageKey, serialized);
        } catch (_) {}
    }
}

export function initShellThemeCustomizer(options = {}) {
    const root = options.root instanceof HTMLElement ? options.root : document.body;
    const html = document.documentElement;
    const body = document.body;
    const themeStorageKey = typeof options.themeStorageKey === 'string' && options.themeStorageKey !== ''
        ? options.themeStorageKey
        : '__THEME_CONFIG__';
    const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
    const themeDefaults = options.themeDefaults ?? window.defaultConfig ?? {
        skin: 'default',
        theme: 'light',
        'topbar-color': 'gray',
        'sidenav-color': 'dark',
        'sidenav-size': 'default',
        position: 'fixed',
        width: 'fluid',
        dir: 'ltr',
    };
    const themeConfig = options.themeConfig ?? window.config ?? themeDefaults;
    const quickToggleSelector = typeof options.quickToggleSelector === 'string' && options.quickToggleSelector !== ''
        ? options.quickToggleSelector
        : '.catalyst-status-bar [data-migrationui-theme-toggle], .catalyst-status-bar [data-theme-quick-toggle]';
    const panel = root.querySelector('#theme-settings-offcanvas');
    const themeStorage = resolveThemeStorage(themeStorageKey);

    if (!(panel instanceof HTMLElement)) {
        return;
    }

    const panelBackdrop = document.createElement('div');
    panelBackdrop.className = 'migration-ui-theme-backdrop';

    const quickToggle = root.querySelector(quickToggleSelector);
    const openTriggers = root.querySelectorAll('[data-theme-customizer-toggle]');
    const closeTriggers = root.querySelectorAll('[data-theme-customizer-close]');
    const customizerLocked = isCustomizerLocked();
    const state = {
        defaults: clone(themeDefaults),
        config: customizerLocked
            ? getPlatformLockedConfig(themeDefaults)
            : enforceClosedSkinConfig(clone(themeConfig)),
    };

    const persistConfig = () => {
        enforceClosedSkinConfig(state.config);
        state.config['sidenav-size'] = state.defaults['sidenav-size'] ?? 'default';
        if (!customizerLocked) {
            persistThemeConfig(themeStorage, themeStorageKey, state.config);
        }
        window.config = clone(state.config);
        if (window.__CATALYST_APPEARANCE__ && typeof window.__CATALYST_APPEARANCE__ === 'object') {
            window.__CATALYST_APPEARANCE__.effectiveConfig = clone(state.config);
        }
    };

    const syncToggleIcons = () => {
        if (!(quickToggle instanceof HTMLElement)) {
            return;
        }

        const toggleIcon = quickToggle.querySelector('i');
        if (!(toggleIcon instanceof HTMLElement)) {
            return;
        }

        enforceClosedSkinConfig(state.config);
        const effectiveTheme = state.config.theme === 'system'
            ? resolveSystemTheme(mediaQuery)
            : state.config.theme;

        toggleIcon.className = effectiveTheme === 'dark'
            ? 'ti ti-sun'
            : 'ti ti-moon';
    };

    const syncCustomizerControls = () => {
        const controlMap = [
            ['data-skin', state.config.skin],
            ['data-bs-theme', state.config.theme],
            ['data-topbar-color', state.config['topbar-color']],
            ['data-menu-color', state.config['sidenav-color']],
        ];

        controlMap.forEach(([name, value]) => {
            root.querySelectorAll(`input[name="${name}"]`).forEach((input) => {
                if (!(input instanceof HTMLInputElement)) {
                    return;
                }

                input.checked = input.value === value;
            });
        });
    };

    const syncClosedSkinControlLocks = () => {
        const preset = getClosedSkinConfig(state.config.skin);
        const lockedGroups = [
            ['data-bs-theme', 'theme'],
            ['data-topbar-color', 'topbar-color'],
            ['data-menu-color', 'sidenav-color'],
        ];

        lockedGroups.forEach(([name, configKey]) => {
            root.querySelectorAll(`input[name="${name}"]`).forEach((input) => {
                if (!(input instanceof HTMLInputElement)) {
                    return;
                }

                const locked = preset !== null && input.value !== preset[configKey];
                input.disabled = locked;
                input.closest('.form-check')?.classList.toggle('is-disabled', locked);
            });
        });
    };

    const applyConfig = () => {
        enforceClosedSkinConfig(state.config);
        const appliedTheme = state.config.theme === 'system'
            ? resolveSystemTheme(mediaQuery)
            : state.config.theme;
        const closedSkin = getClosedSkinConfig(state.config.skin) !== null ? state.config.skin : 'none';

        html.setAttribute('data-skin', state.config.skin);
        html.setAttribute('data-bs-theme', appliedTheme);
        html.setAttribute('data-topbar-color', state.config['topbar-color']);
        html.setAttribute('data-menu-color', state.config['sidenav-color']);
        html.setAttribute('data-sidenav-size', state.defaults['sidenav-size'] ?? 'default');
        html.setAttribute('data-layout-width', state.defaults.width);
        html.setAttribute('data-layout-position', state.defaults.position);
        html.setAttribute('dir', state.defaults.dir);
        html.setAttribute('data-migrationui-theme-mode', state.config.theme);
        html.setAttribute('data-catalyst-closed-skin', closedSkin);
        html.setAttribute('data-catalyst-red-cross-mode', state.config.skin === 'red-cross' ? 'enabled' : 'disabled');
        persistConfig();
        syncToggleIcons();
        syncCustomizerControls();
        syncClosedSkinControlLocks();
    };

    if (customizerLocked) {
        applyConfig();
        return;
    }

    const closePanel = () => {
        panel.classList.remove('show');
        panel.setAttribute('aria-hidden', 'true');
        openTriggers.forEach((trigger) => {
            trigger.setAttribute('aria-expanded', 'false');
        });

        panelBackdrop.classList.remove('show');
        window.setTimeout(() => {
            if (panelBackdrop.parentNode) {
                panelBackdrop.parentNode.removeChild(panelBackdrop);
            }
        }, 200);
    };

    const openPanel = () => {
        if (!panelBackdrop.parentNode) {
            body.appendChild(panelBackdrop);
        }

        panel.classList.add('show');
        panel.setAttribute('aria-hidden', 'false');
        openTriggers.forEach((trigger) => {
            trigger.setAttribute('aria-expanded', 'true');
        });

        window.requestAnimationFrame(() => {
            panelBackdrop.classList.add('show');
        });
    };

    if (quickToggle instanceof HTMLElement) {
        quickToggle.addEventListener('click', (event) => {
            event.preventDefault();
            if (getClosedSkinConfig(state.config.skin) !== null) {
                enforceClosedSkinConfig(state.config);
                applyConfig();
                return;
            }

            const currentTheme = state.config.theme === 'system'
                ? resolveSystemTheme(mediaQuery)
                : state.config.theme;
            state.config.theme = currentTheme === 'dark' ? 'light' : 'dark';
            applyConfig();
        });
    }

    openTriggers.forEach((trigger) => {
        trigger.addEventListener('click', (event) => {
            event.preventDefault();
            if (panel.classList.contains('show')) {
                closePanel();
                return;
            }

            openPanel();
        });
    });

    closeTriggers.forEach((trigger) => {
        trigger.addEventListener('click', (event) => {
            event.preventDefault();
            closePanel();
        });
    });

    panelBackdrop.addEventListener('click', () => {
        closePanel();
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closePanel();
        }
    });

    root.querySelectorAll('input[name="data-skin"]').forEach((input) => {
        input.addEventListener('change', () => {
            if (!(input instanceof HTMLInputElement) || input.checked !== true) {
                return;
            }

            state.config.skin = input.value;
            applyConfig();
        });
    });

    root.querySelectorAll('input[name="data-bs-theme"]').forEach((input) => {
        input.addEventListener('change', () => {
            if (!(input instanceof HTMLInputElement) || input.checked !== true) {
                return;
            }

            const preset = getClosedSkinConfig(state.config.skin);
            state.config.theme = preset !== null ? preset.theme : input.value;
            applyConfig();
        });
    });

    root.querySelectorAll('input[name="data-topbar-color"]').forEach((input) => {
        input.addEventListener('change', () => {
            if (!(input instanceof HTMLInputElement) || input.checked !== true) {
                return;
            }

            const preset = getClosedSkinConfig(state.config.skin);
            state.config['topbar-color'] = preset !== null ? preset['topbar-color'] : input.value;
            applyConfig();
        });
    });

    root.querySelectorAll('input[name="data-menu-color"]').forEach((input) => {
        input.addEventListener('change', () => {
            if (!(input instanceof HTMLInputElement) || input.checked !== true) {
                return;
            }

            const preset = getClosedSkinConfig(state.config.skin);
            state.config['sidenav-color'] = preset !== null ? preset['sidenav-color'] : input.value;
            applyConfig();
        });
    });

    mediaQuery.addEventListener('change', () => {
        if (state.config.theme === 'system') {
            applyConfig();
        }
    });

    applyConfig();
}
