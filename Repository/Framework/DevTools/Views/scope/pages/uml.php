<?php

declare(strict_types=1);

/**
 * Catalyst PHP Framework
 *
 * A modern PHP 8.4 framework for building
 * robust and scalable web applications.
 *
 * PHP Version 8.4 (Required).
 *
 * @package    Catalyst
 *
 * @author     Walter Nuñez (arcanisgk/original founder)
 * @email      <wnunez@lh-2.net>
 * @email      <icarosnet@gmail.com>
 * @copyright  2024-2026 Walter Francisco Nuñez Cruz and Icaros Net
 * @license    Proprietary - https://catalyst.lh-2.net/license
 *
 * @version    GIT: See repository tags
 *
 * @category   Framework
 * @filesource
 *
 * @link       https://catalyst.lh-2.net Project homepage
 * @see        https://catalyst.lh-2.net/docs Documentation
 *
 */

use Catalyst\Framework\View\TrustedHtml;

return static function (array $scope): array {
    $jsonSections = array_values(array_map('strval', (array) ($scope['jsonSections'] ?? [])));
    $environmentName = (string) ($scope['environment'] ?? 'unknown');
    $sectionCount = count($jsonSections);
    $configuredLabel = (bool) ($scope['isConfigured'] ?? false) ? __('uml.configured_yes') : __('uml.configured_no');

    $tabSlugs = [
        'bootstrap',
        'lifecycle',
        'constants',
        'config',
        'middleware',
        'layers',
    ];

    $umlTabs = [];
    foreach ($tabSlugs as $index => $slug) {
        $isActive = $index === 0;
        $umlTabs[] = [
            'slug' => $slug,
            'label' => __('uml.tabs.' . $slug),
            'active' => $isActive,
            'button_id' => 'uml-tab-button-' . $slug,
            'panel_id' => 'uml-tab-' . $slug,
            'aria_selected' => $isActive ? 'true' : 'false',
            'tabindex' => $isActive ? '0' : '-1',
        ];
    }

    return [
        'environment_name' => $environmentName,
        'section_count' => $sectionCount,
        'configured_label' => $configuredLabel,
        'chip_environment' => sprintf(__('uml.chips.environment'), $environmentName),
        'chip_configured' => sprintf(__('uml.chips.configured'), $configuredLabel),
        'chip_loaded_sections' => sprintf(__('uml.chips.loaded_sections'), (string) $sectionCount),
        'uml_tabs' => $umlTabs,
        'rail_scope_items' => [
            ['href' => '#uml-tab-bootstrap', 'label' => __('uml.rail.scope_items.bootstrap')],
            ['href' => '#uml-tab-lifecycle', 'label' => __('uml.rail.scope_items.lifecycle')],
            ['href' => '#uml-tab-config', 'label' => __('uml.rail.scope_items.config')],
            ['href' => '#uml-tab-middleware', 'label' => __('uml.rail.scope_items.middleware')],
            ['href' => '#uml-tab-layers', 'label' => __('uml.rail.scope_items.layers')],
        ],
        'json_sections' => $jsonSections,
        'bootstrap_diagram_html' => TrustedHtml::fromString(__('uml.bootstrap.diagram')),
        'lifecycle_diagram_html' => TrustedHtml::fromString(__('uml.lifecycle.diagram')),
        'config_diagram_html' => TrustedHtml::fromString(__('uml.config.diagram')),
        'middleware_diagram_html' => TrustedHtml::fromString(__('uml.middleware.diagram')),
        'layers_diagram_html' => TrustedHtml::fromString(__('uml.layers.diagram')),
        'constants_sys_rows' => [
            ['name' => 'DS', 'type' => 'string', 'description' => __('uml.constants.sys.rows.ds')],
            ['name' => 'PD', 'type' => 'string', 'description' => __('uml.constants.sys.rows.pd')],
            ['name' => 'IS_CLI', 'type' => 'bool', 'description' => __('uml.constants.sys.rows.is_cli')],
            ['name' => 'IS_REQUEST', 'type' => 'bool', 'description' => __('uml.constants.sys.rows.is_request')],
            ['name' => 'TW', 'type' => 'int', 'description' => __('uml.constants.sys.rows.tw')],
            ['name' => 'NL', 'type' => 'string', 'description' => __('uml.constants.sys.rows.nl')],
            ['name' => 'LOG_DIR', 'type' => 'string', 'description' => __('uml.constants.sys.rows.log_dir')],
            ['name' => 'LOADED_SYS_CONSTANT', 'type' => 'bool', 'description' => __('uml.constants.sys.rows.loaded_sys_constant')],
        ],
        'constants_env_rows' => [
            ['name' => 'ENV', 'type' => 'string', 'description' => __('uml.constants.env.rows.env')],
            ['name' => 'IS_DEVELOPMENT', 'type' => 'bool', 'description' => __('uml.constants.env.rows.is_development')],
            ['name' => 'IS_STAGING', 'type' => 'bool', 'description' => __('uml.constants.env.rows.is_staging')],
            ['name' => 'IS_TESTING', 'type' => 'bool', 'description' => __('uml.constants.env.rows.is_testing')],
            ['name' => 'IS_PRODUCTION', 'type' => 'bool', 'description' => __('uml.constants.env.rows.is_production')],
            ['name' => 'GET_ENV_VAR', 'type' => 'array', 'description' => __('uml.constants.env.rows.get_env_var')],
            ['name' => 'IS_CONFIGURED', 'type' => 'bool', 'description' => __('uml.constants.env.rows.is_configured')],
            ['name' => 'DISPLAY_LOGS', 'type' => 'bool', 'description' => __('uml.constants.env.rows.display_logs')],
            ['name' => 'ROUTE_CACHE', 'type' => 'bool', 'description' => __('uml.constants.env.rows.route_cache')],
            ['name' => 'DB_HOST', 'type' => 'string', 'description' => __('uml.constants.env.rows.db_host')],
            ['name' => 'DB_PORT', 'type' => 'int', 'description' => __('uml.constants.env.rows.db_port')],
            ['name' => 'DB_DATABASE', 'type' => 'string', 'description' => __('uml.constants.env.rows.db_database')],
            ['name' => 'DB_USERNAME', 'type' => 'string', 'description' => __('uml.constants.env.rows.db_username')],
            ['name' => 'DB_PASSWORD', 'type' => 'string', 'description' => __('uml.constants.env.rows.db_password')],
            ['name' => 'LOADED_ENV_CONSTANT', 'type' => 'bool', 'description' => __('uml.constants.env.rows.loaded_env_constant')],
        ],
        'config_rows' => [
            ['key' => 'app', 'file' => 'app.json', 'description' => __('uml.config.rows.app')],
            ['key' => 'db', 'file' => 'db.json', 'description' => __('uml.config.rows.db')],
            ['key' => 'mail', 'file' => 'mail.json', 'description' => __('uml.config.rows.mail')],
            ['key' => 'ftp', 'file' => 'ftp.json', 'description' => __('uml.config.rows.ftp')],
            ['key' => 'session', 'file' => 'session.json', 'description' => __('uml.config.rows.session')],
            ['key' => 'cache', 'file' => 'cache.json', 'description' => __('uml.config.rows.cache')],
            ['key' => 'logging', 'file' => 'logging.json', 'description' => __('uml.config.rows.logging')],
            ['key' => 'security', 'file' => 'security.json', 'description' => __('uml.config.rows.security')],
            ['key' => 'websocket', 'file' => 'websocket.json', 'description' => __('uml.config.rows.websocket')],
            ['key' => 'cors', 'file' => 'cors.json', 'description' => __('uml.config.rows.cors')],
            ['key' => 'devtools', 'file' => 'devtools.json', 'description' => __('uml.config.rows.devtools')],
        ],
        'middleware_rows' => [
            ['class_name' => 'SecurityHeadersMiddleware', 'layer' => __('uml.middleware.rows.security_headers.layer'), 'behaviour' => __('uml.middleware.rows.security_headers.behaviour')],
            ['class_name' => 'CorsMiddleware', 'layer' => __('uml.middleware.rows.cors.layer'), 'behaviour' => __('uml.middleware.rows.cors.behaviour')],
            ['class_name' => 'CanonicalPathRedirectMiddleware', 'layer' => __('uml.middleware.rows.canonical_path.layer'), 'behaviour' => __('uml.middleware.rows.canonical_path.behaviour')],
            ['class_name' => 'WebSocketBootMiddleware', 'layer' => __('uml.middleware.rows.websocket_boot.layer'), 'behaviour' => __('uml.middleware.rows.websocket_boot.behaviour')],
            ['class_name' => 'TenancyContextMiddleware', 'layer' => __('uml.middleware.rows.tenancy_context.layer'), 'behaviour' => __('uml.middleware.rows.tenancy_context.behaviour')],
            ['class_name' => 'SetupMiddleware', 'layer' => __('uml.middleware.rows.setup.layer'), 'behaviour' => __('uml.middleware.rows.setup.behaviour')],
            ['class_name' => 'RequestThrottlingMiddleware', 'layer' => __('uml.middleware.rows.request_throttle.layer'), 'behaviour' => __('uml.middleware.rows.request_throttle.behaviour')],
            ['class_name' => 'CsrfMiddleware', 'layer' => __('uml.middleware.rows.csrf.layer'), 'behaviour' => __('uml.middleware.rows.csrf.behaviour')],
            ['class_name' => 'AuthMiddleware', 'layer' => __('uml.middleware.rows.auth.layer'), 'behaviour' => __('uml.middleware.rows.auth.behaviour')],
            ['class_name' => 'GuestMiddleware', 'layer' => __('uml.middleware.rows.guest.layer'), 'behaviour' => __('uml.middleware.rows.guest.behaviour')],
            ['class_name' => 'RoleMiddleware', 'layer' => __('uml.middleware.rows.role.layer'), 'behaviour' => __('uml.middleware.rows.role.behaviour')],
            ['class_name' => 'LoginThrottleMiddleware', 'layer' => __('uml.middleware.rows.login_throttle.layer'), 'behaviour' => __('uml.middleware.rows.login_throttle.behaviour')],
            ['class_name' => 'SetupGuardMiddleware', 'layer' => __('uml.middleware.rows.setup_guard.layer'), 'behaviour' => __('uml.middleware.rows.setup_guard.behaviour')],
        ],
        'layer_cards' => [
            [
                'title' => __('uml.layers.cards.boot.title'),
                'description' => __('uml.layers.cards.boot.description'),
                'badges' => [
                    'boot-core/constant/sys-constant.php',
                    'boot-core/constant/env-constant.php',
                    'boot-core/requirement-loader/',
                    'boot-core/template/layouts/',
                    'boot-core/config/{env}/*.json',
                    'boot-core/routes/global-routes.php',
                    'boot-core/cache/routes.cache.php',
                ],
            ],
            [
                'title' => __('uml.layers.cards.core.title'),
                'description' => __('uml.layers.cards.core.description'),
                'badges' => [
                    'app/Kernel.php',
                    'Framework/Http',
                    'Framework/Route',
                    'Framework/Middleware',
                    'Framework/Auth',
                    'Framework/Authorization',
                    'Framework/Controllers',
                    'Framework/View',
                    'Framework/Session',
                    'Framework/Database',
                    'Framework/Mail',
                    'Framework/Notification',
                    'Framework/WebSocket',
                    'Framework/Cli',
                ],
            ],
            [
                'title' => __('uml.layers.cards.helpers.title'),
                'description' => __('uml.layers.cards.helpers.description'),
                'badges' => [
                    'Helpers/Config',
                    'Helpers/I18n',
                    'Helpers/Log',
                    'Helpers/Security',
                    'Helpers/Validation',
                    'Helpers/Error',
                    'Helpers/IO',
                    'Helpers/Debug',
                    'Helpers/ToolBox',
                ],
            ],
            [
                'title' => __('uml.layers.cards.modules.title'),
                'description' => __('uml.layers.cards.modules.description'),
                'badges' => [
                    __('uml.layers.cards.modules.badges.auth'),
                    __('uml.layers.cards.modules.badges.settings'),
                    __('uml.layers.cards.modules.badges.devtools'),
                    __('uml.layers.cards.modules.badges.notification'),
                    __('uml.layers.cards.modules.badges.roles'),
                    __('uml.layers.cards.modules.badges.common'),
                ],
            ],
        ],
        'rail_loaded_sections_empty' => __('uml.rail.loaded_sections_empty'),
        'layers_loaded_sections_heading' => __('uml.layers.loaded_sections_heading'),
        'layers_loaded_sections_path_description' => sprintf(__('uml.loaded_sections.path_description'), $environmentName),
        'layers_loaded_sections_empty' => __('uml.layers.loaded_sections_empty'),
    ];
};
