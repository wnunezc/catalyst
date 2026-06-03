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

namespace Catalyst\Repository\Settings\Support;

/**
 * Builds editable modal descriptors for all setup configuration sections.
 *
 * @package Catalyst\Repository\Settings\Support
 * Responsibility: Maps current configuration values into the modal forms rendered by the setup surface.
 */
final class SettingsModalFactory
{
    /**
     * Initializes the Settings Modal Factory instance.
     *
     * Responsibility: Binds required collaborators or immutable state without executing the main workflow.
     */
    public function __construct(
        private readonly SettingsDisplayFactory $display
    ) {
    }

    /**
     * Builds editable modals for every supported configuration section.
     *
     * Responsibility: Composes derived framework data from validated inputs while keeping persistence and rendering separate.
     * @return array<int, array<string, mixed>>
     */
    public function build(SettingsPageViewContext $context): array
    {
        $app = $context->app();
        $db = $context->db();
        $mail = $context->mail();
        $ftp = $context->ftp();
        $session = $context->session();
        $cache = $context->cache();
        $logging = $context->logging();
        $security = $context->security();
        $websocket = $context->websocket();
        $devtools = $context->devtools();
        $cors = $context->cors();
        $isProductionRuntimeEnv = $context->isProductionRuntimeEnv();

        return [
            $this->display->modal(
                'modal-app',
                'fa-solid fa-gear',
                $context->t('settings.modal_titles.app'),
                'app',
                [
                    $this->display->inputField('app', 'app_name', $context->t('settings.labels.application_name'), $app['project_name'] ?? ''),
                    $this->display->inputField('app', 'app_url', $context->t('settings.labels.application_url'), $app['project_url'] ?? ''),
                    $this->display->inputField('app', 'app_key', $context->t('settings.labels.app_key_min'), $app['project_key'] ?? '', 'text', $context->t('settings.hints.app_key')),
                    $this->display->selectField('app', 'app_entry', $context->t('settings.labels.primary_entry_point'), $app['project_entry'] ?? 'User-Access', $context->entryMap()),
                    $this->display->selectField('app', 'app_entry_secondary', $context->t('settings.labels.secondary_entry_point'), $app['project_entry_secondary'] ?? '', $context->entrySecondaryMap()),
                    $this->display->inputField('app', 'app_timezone', $context->t('settings.labels.timezone'), $app['project_timezone'] ?? 'UTC'),
                    $this->display->selectField('app', 'app_env', $context->t('settings.labels.environment'), $app['project_env'] ?? 'production', $context->envMap(), $context->t('settings.hints.environment_readonly'), true),
                    $this->display->selectField('app', 'app_lang', $context->t('settings.labels.language'), $app['project_lang'] ?? 'en', $context->langMap()),
                    $this->display->checkboxField('app_debug', $context->t('settings.labels.enable_debug_mode'), (bool) ($app['project_debug'] ?? false)),
                ],
                'lg'
            ),
            $this->display->modal(
                'modal-db',
                'fa-solid fa-database',
                $context->t('settings.modal_titles.db'),
                'db',
                [
                    $this->display->inputField('db', 'db_host', $context->t('settings.labels.host'), $db['db_host'] ?? 'localhost'),
                    $this->display->inputField('db', 'db_port', $context->t('settings.labels.port'), $db['db_port'] ?? 3306, 'number'),
                    $this->display->inputField('db', 'db_database', $context->t('settings.labels.database'), $db['db_database'] ?? ''),
                    $this->display->inputField('db', 'db_username', $context->t('settings.labels.username'), $db['db_username'] ?? ''),
                    $this->display->passwordField('db', 'db_password', $context->t('settings.labels.password'), isset($db['db_password']) && $db['db_password'] !== ''),
                ]
            ),
            $this->display->modal(
                'modal-mail',
                'fa-solid fa-envelope',
                $context->t('settings.modal_titles.mail'),
                'mail',
                [
                    $this->display->inputField('mail', 'mail_host', $context->t('settings.labels.smtp_host'), $mail['mail_host'] ?? ''),
                    $this->display->inputField('mail', 'mail_port', $context->t('settings.labels.smtp_port'), $mail['mail_port'] ?? 587, 'number'),
                    $this->display->inputField('mail', 'mail_username', $context->t('settings.labels.username'), $mail['mail_username'] ?? ''),
                    $this->display->passwordField('mail', 'mail_password', $context->t('settings.labels.password'), isset($mail['mail_password']) && $mail['mail_password'] !== ''),
                    $this->display->selectField('mail', 'mail_encryption', $context->t('settings.labels.encryption'), $mail['mail_encryption'] ?? 'tls', $context->encMap()),
                    $this->display->inputField('mail', 'mail_from_address', $context->t('settings.labels.from_address'), $mail['mail_from_address'] ?? '', 'email'),
                    $this->display->inputField('mail', 'mail_from_name', $context->t('settings.labels.from_name'), $mail['mail_from_name'] ?? ''),
                ],
                'lg'
            ),
            $this->display->modal(
                'modal-ftp',
                'fa-solid fa-server',
                $context->t('settings.modal_titles.ftp'),
                'ftp',
                [
                    $this->display->alertField($context->t('settings.alerts.ftp_pretest')),
                    $this->display->selectField('ftp', 'ftp_protocol', $context->t('settings.labels.protocol'), $ftp['ftp_protocol'] ?? ((bool) ($ftp['ftp_ssl'] ?? false) ? 'ftps' : 'ftp'), ['ftp' => 'FTP', 'ftps' => 'FTPS', 'sftp' => 'SFTP']),
                    $this->display->inputField('ftp', 'ftp_host', $context->t('settings.labels.host'), $ftp['ftp_host'] ?? ''),
                    $this->display->inputField('ftp', 'ftp_port', $context->t('settings.labels.port'), $ftp['ftp_port'] ?? 21, 'number'),
                    $this->display->inputField('ftp', 'ftp_username', $context->t('settings.labels.username'), $ftp['ftp_username'] ?? ''),
                    $this->display->passwordField('ftp', 'ftp_password', $context->t('settings.labels.password'), isset($ftp['ftp_password']) && $ftp['ftp_password'] !== ''),
                    $this->display->inputField('ftp', 'ftp_root', $context->t('settings.labels.remote_root'), $ftp['ftp_root'] ?? '/', 'text', $context->t('settings.hints.ftp_root')),
                    $this->display->inputField('ftp', 'ftp_timeout', $context->t('settings.labels.timeout_seconds'), $ftp['ftp_timeout'] ?? 10, 'number'),
                    $this->display->checkboxField('ftp_passive', $context->t('settings.labels.enable_passive_mode'), (bool) ($ftp['ftp_passive'] ?? true)),
                ],
                'lg',
                '/configuration/environment-setup/ftp/pretest'
            ),
            $this->display->modal(
                'modal-session',
                'fa-solid fa-clock',
                $context->t('settings.modal_titles.session'),
                'session',
                [
                    $this->display->selectField('session', 'session_driver', $context->t('settings.labels.session_driver'), $session['session_driver'] ?? 'file', ['file' => $context->t('settings.options.session_driver.file'), 'database' => $context->t('settings.options.session_driver.database')]),
                    $this->display->inputField('session', 'session_connection', $context->t('settings.labels.db_connection'), $session['session_connection'] ?? 'db1', 'text', $context->t('settings.hints.session_connection')),
                    $this->display->inputField('session', 'session_table', $context->t('settings.labels.session_table'), $session['session_table'] ?? 'sessions', 'text', $context->t('settings.hints.session_table')),
                    $this->display->inputField('session', 'session_name', $context->t('settings.labels.session_name'), $session['session_name'] ?? 'catalyst-session'),
                    $this->display->inputField('session', 'session_lifetime', $context->t('settings.labels.lifetime_seconds'), $session['session_lifetime'] ?? 2592000, 'number', $context->t('settings.hints.session_lifetime')),
                    $this->display->inputField('session', 'session_domain', $context->t('settings.labels.domain_leave_empty_auto'), $session['session_domain'] ?? ''),
                    $this->display->selectField('session', 'session_same_site', $context->t('settings.labels.same_site'), $session['session_same_site'] ?? 'Strict', $context->siteMap()),
                    $this->display->checkboxField('session_secure', $context->t('settings.labels.secure_https_only'), (bool) ($session['session_secure'] ?? true)),
                    $this->display->checkboxField('session_http_only', $context->t('settings.labels.http_only'), (bool) ($session['session_http_only'] ?? true)),
                ]
            ),
            $this->display->modal(
                'modal-cache',
                'fa-solid fa-hard-drive',
                $context->t('settings.modal_titles.cache'),
                'cache',
                [
                    $this->display->alertField($isProductionRuntimeEnv
                        ? $context->t('settings.alerts.cache_supported_activation')
                        : $context->t('settings.alerts.cache_blocked_outside_production')),
                    $this->display->checkboxField('cache_enabled', $context->t('settings.labels.enable_framework_cache'), (bool) ($cache['cache_enabled'] ?? false), !$isProductionRuntimeEnv),
                    $this->display->selectField('cache', 'cache_driver', $context->t('settings.labels.cache_driver'), $cache['cache_driver'] ?? 'file', $context->drvMap(), '', !$isProductionRuntimeEnv),
                    $this->display->inputField('cache', 'cache_prefix', $context->t('settings.labels.cache_prefix'), $cache['cache_prefix'] ?? 'catalyst_', 'text', $context->t('settings.hints.cache_prefix'), !$isProductionRuntimeEnv),
                    $this->display->checkboxField('app_cache', $context->t('settings.labels.enable_reusable_application_cache'), (bool) ($cache['app_cache'] ?? false), !$isProductionRuntimeEnv),
                    $this->display->checkboxField('config_cache', $context->t('settings.labels.enable_compiled_config_cache'), (bool) ($cache['config_cache'] ?? false), !$isProductionRuntimeEnv),
                    $this->display->checkboxField('discovery_cache', $context->t('settings.labels.enable_route_discovery_manifest_cache'), (bool) ($cache['discovery_cache'] ?? false), !$isProductionRuntimeEnv),
                    $this->display->checkboxField('route_cache', $context->t('settings.labels.enable_route_cache'), (bool) ($cache['route_cache'] ?? false), !$isProductionRuntimeEnv),
                ],
                '',
                '',
                !$isProductionRuntimeEnv
            ),
            $this->display->modal(
                'modal-logging',
                'fa-solid fa-file-lines',
                $context->t('settings.modal_titles.logging'),
                'logging',
                [
                    $this->display->selectField(
                        'logging',
                        'log_channel',
                        $context->t('settings.labels.log_channel'),
                        $logging['log_channel'] ?? 'single',
                        $context->chanMap()
                    ),
                    $this->display->selectField(
                        'logging',
                        'log_level',
                        $context->t('settings.labels.log_level'),
                        $logging['log_level'] ?? 'debug',
                        $context->lvlMap()
                    ),
                    $this->display->checkboxField(
                        'display_logs',
                        $context->t('settings.labels.display_logs_on_screen'),
                        (bool) ($logging['display_logs'] ?? false)
                    ),
                    $this->display->checkboxField(
                        'log_rotation_enabled',
                        $context->t('settings.labels.log_rotation_enabled'),
                        (bool) ($logging['log_rotation_enabled'] ?? true)
                    ),
                    $this->display->inputField(
                        'logging',
                        'log_max_file_size_mb',
                        $context->t('settings.labels.log_max_file_size_mb'),
                        $logging['log_max_file_size_mb'] ?? 2,
                        'number',
                        $context->t('settings.hints.log_max_file_size_mb')
                    ),
                    $this->display->inputField(
                        'logging',
                        'log_max_rotated_files',
                        $context->t('settings.labels.log_max_rotated_files'),
                        $logging['log_max_rotated_files'] ?? 5,
                        'number',
                        $context->t('settings.hints.log_max_rotated_files')
                    ),
                ]
            ),
            $this->display->modal(
                'modal-security',
                'fa-solid fa-lock',
                $context->t('settings.modal_titles.security'),
                'security',
                [
                    $this->display->inputField('security', 'bcrypt_rounds', $context->t('settings.labels.bcrypt_rounds_range'), $security['bcrypt_rounds'] ?? 12, 'number', $context->t('settings.hints.bcrypt_rounds')),
                    $this->display->checkboxField('mfa_enabled', $context->t('settings.labels.enable_mfa_framework_wide'), (bool) ($security['mfa_enabled'] ?? false)),
                ]
            ),
            $this->display->modal(
                'modal-features',
                'fa-solid fa-sliders',
                $context->t('settings.modal_titles.features'),
                'features',
                [
                    $this->display->alertField($context->t('settings.alerts.features_setup')),
                    $this->display->checkboxField('auth_registration_enabled', $context->t('settings.labels.allow_public_registration'), $context->featureEnabled('auth.registration_enabled', true)),
                    $this->display->checkboxField('mfa', $context->t('settings.labels.enable_mfa_routes'), $context->featureEnabled('mfa', true)),
                    $this->display->checkboxField('social_auth', $context->t('settings.labels.enable_social_auth'), $context->featureEnabled('social_auth', true)),
                    $this->display->checkboxField('notifications', $context->t('settings.labels.enable_notifications'), $context->featureEnabled('notifications', true)),
                ]
            ),
            $this->display->modal(
                'modal-websocket',
                'fa-solid fa-plug',
                $context->t('settings.modal_titles.websocket'),
                'websocket',
                [
                    $this->display->checkboxField('ws_enabled', $context->t('settings.labels.enable_websocket_server'), (bool) ($websocket['enabled'] ?? true)),
                    $this->display->inputField('websocket', 'ws_host', $context->t('settings.labels.bind_host'), $websocket['ws_host'] ?? '0.0.0.0'),
                    $this->display->inputField('websocket', 'ws_port', $context->t('settings.labels.ws_port'), $websocket['ws_port'] ?? 8080, 'number'),
                    $this->display->inputField('websocket', 'ws_internal_port', $context->t('settings.labels.internal_http_port'), $websocket['ws_internal_port'] ?? 8181, 'number'),
                    $this->display->inputField('websocket', 'ws_publisher_url', $context->t('settings.labels.publisher_url'), $websocket['ws_publisher_url'] ?? 'http://127.0.0.1:8181/publish'),
                ]
            ),
            $this->display->modal(
                'modal-devtools',
                'fa-solid fa-screwdriver-wrench',
                $context->t('settings.modal_titles.devtools'),
                'devtools',
                [
                    $this->display->alertField($context->t('settings.alerts.devtools_compatibility')),
                    $this->display->checkboxField('app_debug', $context->t('settings.labels.enable_debug_mode'), (bool) ($devtools['app_debug'] ?? false)),
                    $this->display->checkboxField('display_logs', $context->t('settings.labels.display_logs_on_screen'), (bool) ($devtools['display_logs'] ?? false)),
                ]
            ),
            $this->display->modal(
                'modal-cors',
                'fa-solid fa-shield-halved',
                $context->t('settings.modal_titles.cors'),
                'cors',
                [
                    $this->display->checkboxField('cors_enabled', $context->t('settings.labels.enable_cors'), (bool) ($cors['enabled'] ?? true)),
                    $this->display->inputField('cors', 'cors_allowed_origins', $context->t('settings.labels.allowed_origins_csv'), is_array($cors['allowed_origins'] ?? null) ? implode(', ', $cors['allowed_origins']) : ($cors['allowed_origins'] ?? '*'), 'text', $context->t('settings.hints.allowed_origins')),
                    $this->display->inputField('cors', 'cors_allowed_methods', $context->t('settings.labels.allowed_methods_csv'), is_array($cors['allowed_methods'] ?? null) ? implode(', ', $cors['allowed_methods']) : ($cors['allowed_methods'] ?? '')),
                    $this->display->inputField('cors', 'cors_allowed_headers', $context->t('settings.labels.allowed_headers_csv'), is_array($cors['allowed_headers'] ?? null) ? implode(', ', $cors['allowed_headers']) : ($cors['allowed_headers'] ?? '')),
                    $this->display->inputField('cors', 'cors_max_age', $context->t('settings.labels.max_age_seconds'), $cors['max_age'] ?? 86400, 'number'),
                    $this->display->checkboxField('cors_allow_credentials', $context->t('settings.labels.allow_credentials'), (bool) ($cors['allow_credentials'] ?? false)),
                ]
            ),
        ];
    }
}