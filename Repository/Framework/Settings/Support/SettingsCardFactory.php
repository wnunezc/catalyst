<?php

declare(strict_types=1);

namespace Catalyst\Repository\Settings\Support;

final class SettingsCardFactory
{
    public function __construct(
        private readonly SettingsDisplayFactory $display
    ) {
    }

    /**
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
        $security = $context->security();
        $logging = $context->logging();
        $websocket = $context->websocket();
        $devtools = $context->devtools();
        $cors = $context->cors();
        $envMap = $context->envMap();
        $langMap = $context->langMap();
        $encMap = $context->encMap();
        $siteMap = $context->siteMap();
        $drvMap = $context->drvMap();
        $chanMap = $context->chanMap();
        $lvlMap = $context->lvlMap();
        $entryMap = $context->entryMap();
        $entrySecondaryMap = $context->entrySecondaryMap();

        return [
            [
                'colClass' => 'col-xl-4 col-md-6',
                'icon' => 'fa-solid fa-gear',
                'title' => $context->t('settings.sections.app'),
                'modalId' => 'modal-app',
                'actionLabel' => __('ui.actions.edit'),
                'actionIcon' => 'fa-solid fa-pen me-1',
                'notice' => '',
                'rows' => [
                    $this->display->displayRow($context->t('settings.labels.name'), 'd-app-app_name', $app['project_name'] ?? ''),
                    $this->display->displayRow($context->t('settings.labels.url'), 'd-app-app_url', $app['project_url'] ?? ''),
                    $this->display->displayRow($context->t('settings.labels.environment'), 'd-app-app_env', $envMap[$app['project_env'] ?? ''] ?? ($app['project_env'] ?? '')),
                    $this->display->displayRow($context->t('settings.labels.language'), 'd-app-app_lang', $langMap[$app['project_lang'] ?? ''] ?? ($app['project_lang'] ?? '')),
                    $this->display->displayRow($context->t('settings.labels.timezone'), 'd-app-app_timezone', $app['project_timezone'] ?? ''),
                    $this->display->displayRow($context->t('settings.labels.entry_point'), 'd-app-app_entry', $entryMap[$app['project_entry'] ?? ''] ?? ($app['project_entry'] ?? '')),
                    $this->display->displayRow($context->t('settings.labels.secondary_entry_point'), 'd-app-app_entry_secondary', $entrySecondaryMap[$app['project_entry_secondary'] ?? ''] ?? ($app['project_entry_secondary'] ?? '')),
                    $this->display->displayRow($context->t('settings.labels.app_key'), 'd-app-app_key', $context->appKeyPreview()),
                    $this->display->displayRow($context->t('settings.labels.debug_mode'), 'd-app-app_debug', (bool) ($app['project_debug'] ?? false), false, true),
                ],
            ],
            [
                'colClass' => 'col-xl-4 col-md-6',
                'icon' => 'fa-solid fa-database',
                'title' => $context->t('settings.sections.db'),
                'modalId' => 'modal-db',
                'actionLabel' => __('ui.actions.edit'),
                'actionIcon' => 'fa-solid fa-pen me-1',
                'notice' => '',
                'rows' => [
                    $this->display->displayRow($context->t('settings.labels.host'), 'd-db-db_host', $db['db_host'] ?? ''),
                    $this->display->displayRow($context->t('settings.labels.port'), 'd-db-db_port', $db['db_port'] ?? ''),
                    $this->display->displayRow($context->t('settings.labels.database'), 'd-db-db_database', $db['db_database'] ?? ''),
                    $this->display->displayRow($context->t('settings.labels.username'), 'd-db-db_username', $db['db_username'] ?? ''),
                    $this->display->displayRow($context->t('settings.labels.password'), 'd-db-db_password', $db['db_password'] ?? '', true),
                ],
            ],
            [
                'colClass' => 'col-xl-4 col-md-6',
                'icon' => 'fa-solid fa-envelope',
                'title' => $context->t('settings.sections.mail'),
                'modalId' => 'modal-mail',
                'actionLabel' => __('ui.actions.edit'),
                'actionIcon' => 'fa-solid fa-pen me-1',
                'notice' => '',
                'rows' => [
                    $this->display->displayRow($context->t('settings.labels.host'), 'd-mail-mail_host', $mail['mail_host'] ?? ''),
                    $this->display->displayRow($context->t('settings.labels.port'), 'd-mail-mail_port', $mail['mail_port'] ?? ''),
                    $this->display->displayRow($context->t('settings.labels.encryption'), 'd-mail-mail_encryption', $encMap[$mail['mail_encryption'] ?? ''] ?? ($mail['mail_encryption'] ?? '')),
                    $this->display->displayRow($context->t('settings.labels.from_address'), 'd-mail-mail_from_address', $mail['mail_from_address'] ?? ''),
                    $this->display->displayRow($context->t('settings.labels.from_name'), 'd-mail-mail_from_name', $mail['mail_from_name'] ?? ''),
                ],
            ],
            [
                'colClass' => 'col-xl-4 col-md-6',
                'icon' => 'fa-solid fa-server',
                'title' => $context->t('settings.sections.ftp'),
                'modalId' => 'modal-ftp',
                'actionLabel' => __('ui.actions.edit'),
                'actionIcon' => 'fa-solid fa-pen me-1',
                'notice' => $context->ftpNotice(),
                'rows' => [
                    $this->display->displayRow($context->t('settings.labels.protocol'), 'd-ftp-ftp_protocol', strtoupper((string) ($ftp['ftp_protocol'] ?? ((bool) ($ftp['ftp_ssl'] ?? false) ? 'ftps' : 'ftp')))),
                    $this->display->displayRow($context->t('settings.labels.host'), 'd-ftp-ftp_host', $ftp['ftp_host'] ?? ''),
                    $this->display->displayRow($context->t('settings.labels.port'), 'd-ftp-ftp_port', $ftp['ftp_port'] ?? ''),
                    $this->display->displayRow($context->t('settings.labels.username'), 'd-ftp-ftp_username', $ftp['ftp_username'] ?? ''),
                    $this->display->displayRow($context->t('settings.labels.root'), 'd-ftp-ftp_root', $ftp['ftp_root'] ?? ''),
                    $this->display->displayRow($context->t('settings.labels.timeout_seconds_short'), 'd-ftp-ftp_timeout', $ftp['ftp_timeout'] ?? 10),
                    $this->display->displayRow($context->t('settings.labels.ftps'), 'd-ftp-ftp_ssl', (bool) ($ftp['ftp_ssl'] ?? false), false, true),
                    $this->display->displayRow($context->t('settings.labels.passive'), 'd-ftp-ftp_passive', (bool) ($ftp['ftp_passive'] ?? true), false, true),
                ],
            ],
            [
                'colClass' => 'col-xl-4 col-md-6',
                'icon' => 'fa-solid fa-clock',
                'title' => $context->t('settings.sections.session'),
                'modalId' => 'modal-session',
                'actionLabel' => __('ui.actions.edit'),
                'actionIcon' => 'fa-solid fa-pen me-1',
                'notice' => '',
                'rows' => [
                    $this->display->displayRow($context->t('settings.labels.driver'), 'd-session-session_driver', $context->t('settings.options.session_driver.' . (string) ($session['session_driver'] ?? 'file'))),
                    $this->display->displayRow($context->t('settings.labels.connection'), 'd-session-session_connection', $session['session_connection'] ?? 'db1'),
                    $this->display->displayRow($context->t('settings.labels.table'), 'd-session-session_table', $session['session_table'] ?? 'sessions'),
                    $this->display->displayRow($context->t('settings.labels.name'), 'd-session-session_name', $session['session_name'] ?? ''),
                    $this->display->displayRow($context->t('settings.labels.lifetime'), 'd-session-session_lifetime', $session['session_lifetime'] ?? ''),
                    $this->display->displayRow($context->t('settings.labels.domain'), 'd-session-session_domain', $session['session_domain'] ?? ''),
                    $this->display->displayRow($context->t('settings.labels.same_site'), 'd-session-session_same_site', $siteMap[$session['session_same_site'] ?? ''] ?? ($session['session_same_site'] ?? '')),
                    $this->display->displayRow($context->t('settings.labels.secure'), 'd-session-session_secure', (bool) ($session['session_secure'] ?? true), false, true),
                    $this->display->displayRow($context->t('settings.labels.http_only'), 'd-session-session_http_only', (bool) ($session['session_http_only'] ?? true), false, true),
                ],
            ],
            [
                'colClass' => 'col-xl-4 col-md-6',
                'icon' => 'fa-solid fa-hard-drive',
                'title' => $context->t('settings.sections.cache'),
                'modalId' => $context->isProductionRuntimeEnv() ? 'modal-cache' : '',
                'actionLabel' => __('ui.actions.edit'),
                'actionIcon' => 'fa-solid fa-pen me-1',
                'notice' => $context->cacheNotice(),
                'rows' => [
                    $this->display->displayRow($context->t('settings.labels.master_switch'), 'd-cache-cache_enabled', (bool) ($cache['cache_enabled'] ?? false), false, true),
                    $this->display->displayRow($context->t('settings.labels.driver'), 'd-cache-cache_driver', $drvMap[$cache['cache_driver'] ?? ''] ?? ($cache['cache_driver'] ?? '')),
                    $this->display->displayRow($context->t('settings.labels.prefix'), 'd-cache-cache_prefix', $cache['cache_prefix'] ?? ''),
                    $this->display->displayRow($context->t('settings.labels.app_cache'), 'd-cache-app_cache', (bool) ($cache['app_cache'] ?? false), false, true),
                    $this->display->displayRow($context->t('settings.labels.config_cache'), 'd-cache-config_cache', (bool) ($cache['config_cache'] ?? false), false, true),
                    $this->display->displayRow($context->t('settings.labels.discovery_cache'), 'd-cache-discovery_cache', (bool) ($cache['discovery_cache'] ?? false), false, true),
                    $this->display->displayRow($context->t('settings.labels.route_cache'), 'd-cache-route_cache', (bool) ($cache['route_cache'] ?? false), false, true),
                ],
            ],
            [
                'colClass' => 'col-xl-4 col-md-6',
                'icon' => 'fa-solid fa-lock',
                'title' => $context->t('settings.sections.security'),
                'modalId' => 'modal-security',
                'actionLabel' => __('ui.actions.edit'),
                'actionIcon' => 'fa-solid fa-pen me-1',
                'notice' => '',
                'rows' => [
                    $this->display->displayRow($context->t('settings.labels.bcrypt_rounds'), 'd-security-bcrypt_rounds', $security['bcrypt_rounds'] ?? 12),
                    $this->display->displayRow($context->t('settings.labels.mfa_enabled'), 'd-security-mfa_enabled', (bool) ($security['mfa_enabled'] ?? false), false, true),
                ],
            ],
            [
                'colClass' => 'col-xl-4 col-md-6',
                'icon' => 'fa-solid fa-file-lines',
                'title' => $context->t('settings.sections.logging'),
                'modalId' => 'modal-logging',
                'actionLabel' => __('ui.actions.edit'),
                'actionIcon' => 'fa-solid fa-pen me-1',
                'notice' => '',
                'rows' => [
                    $this->display->displayRow(
                        $context->t('settings.labels.channel'),
                        'd-logging-log_channel',
                        $chanMap[$logging['log_channel'] ?? ''] ?? ($logging['log_channel'] ?? '')
                    ),
                    $this->display->displayRow(
                        $context->t('settings.labels.level'),
                        'd-logging-log_level',
                        $lvlMap[$logging['log_level'] ?? ''] ?? ($logging['log_level'] ?? '')
                    ),
                    $this->display->displayRow(
                        $context->t('settings.labels.display_logs'),
                        'd-logging-display_logs',
                        (bool) ($logging['display_logs'] ?? false),
                        false,
                        true
                    ),
                    $this->display->displayRow(
                        $context->t('settings.labels.log_rotation_enabled'),
                        'd-logging-log_rotation_enabled',
                        (bool) ($logging['log_rotation_enabled'] ?? true),
                        false,
                        true
                    ),
                    $this->display->displayRow(
                        $context->t('settings.labels.log_max_file_size_mb'),
                        'd-logging-log_max_file_size_mb',
                        $logging['log_max_file_size_mb'] ?? 2
                    ),
                    $this->display->displayRow(
                        $context->t('settings.labels.log_max_rotated_files'),
                        'd-logging-log_max_rotated_files',
                        $logging['log_max_rotated_files'] ?? 5
                    ),
                ],
            ],
            [
                'colClass' => 'col-xl-4 col-md-6',
                'icon' => 'fa-solid fa-plug',
                'title' => $context->t('settings.sections.websocket'),
                'modalId' => 'modal-websocket',
                'actionLabel' => __('ui.actions.edit'),
                'actionIcon' => 'fa-solid fa-pen me-1',
                'notice' => '',
                'rows' => [
                    $this->display->displayRow($context->t('settings.labels.enabled'), 'd-websocket-ws_enabled', (bool) ($websocket['enabled'] ?? true), false, true),
                    $this->display->displayRow($context->t('settings.labels.bind_host'), 'd-websocket-ws_host', $websocket['ws_host'] ?? ''),
                    $this->display->displayRow($context->t('settings.labels.ws_port'), 'd-websocket-ws_port', $websocket['ws_port'] ?? ''),
                    $this->display->displayRow($context->t('settings.labels.internal_port'), 'd-websocket-ws_internal_port', $websocket['ws_internal_port'] ?? ''),
                    $this->display->displayRow($context->t('settings.labels.publisher_url'), 'd-websocket-ws_publisher_url', $websocket['ws_publisher_url'] ?? ''),
                ],
            ],
            [
                'colClass' => 'col-xl-4 col-md-6',
                'icon' => 'fa-solid fa-screwdriver-wrench',
                'title' => $context->t('settings.sections.devtools'),
                'modalId' => 'modal-devtools',
                'actionLabel' => __('ui.actions.edit'),
                'actionIcon' => 'fa-solid fa-pen me-1',
                'notice' => $context->devtoolsNotice(),
                'rows' => [
                    $this->display->displayRow($context->t('settings.labels.debug_mode'), 'd-devtools-app_debug', (bool) ($devtools['app_debug'] ?? false), false, true),
                    $this->display->displayRow($context->t('settings.labels.display_logs'), 'd-devtools-display_logs', (bool) ($devtools['display_logs'] ?? false), false, true),
                ],
            ],
            [
                'colClass' => 'col-xl-4 col-md-6',
                'icon' => 'fa-solid fa-shield-halved',
                'title' => $context->t('settings.sections.cors'),
                'modalId' => 'modal-cors',
                'actionLabel' => __('ui.actions.edit'),
                'actionIcon' => 'fa-solid fa-pen me-1',
                'notice' => '',
                'rows' => [
                    $this->display->displayRow($context->t('settings.labels.enabled'), 'd-cors-cors_enabled', (bool) ($cors['enabled'] ?? true), false, true),
                    $this->display->displayRow($context->t('settings.labels.origins'), 'd-cors-cors_allowed_origins', $context->corsOrigins()),
                    $this->display->displayRow($context->t('settings.labels.credentials'), 'd-cors-cors_allow_credentials', (bool) ($cors['allow_credentials'] ?? false), false, true),
                    $this->display->displayRow($context->t('settings.labels.max_age_seconds_short'), 'd-cors-cors_max_age', $cors['max_age'] ?? 86400),
                ],
            ],
        ];
    }
    /**
     * Builds compact executive groups for the settings overview.
     *
     * @return array<int, array<string, mixed>>
     */
    public function buildGroups(SettingsPageViewContext $context): array
    {
        $cards = $this->build($context);

        return [
            $this->group(
                'foundation',
                $context->t('settings.groups.foundation.eyebrow'),
                $context->t('settings.groups.foundation.title'),
                $context->t('settings.groups.foundation.description'),
                $cards,
                [0, 1, 2]
            ),
            $this->group(
                'runtime',
                $context->t('settings.groups.runtime.eyebrow'),
                $context->t('settings.groups.runtime.title'),
                $context->t('settings.groups.runtime.description'),
                $cards,
                [4, 5, 8]
            ),
            $this->group(
                'security',
                $context->t('settings.groups.security.eyebrow'),
                $context->t('settings.groups.security.title'),
                $context->t('settings.groups.security.description'),
                $cards,
                [6, 10],
                true
            ),
            $this->group(
                'operations',
                $context->t('settings.groups.operations.eyebrow'),
                $context->t('settings.groups.operations.title'),
                $context->t('settings.groups.operations.description'),
                $cards,
                [3, 7, 9]
            ),
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $cards
     * @param array<int, int> $indexes
     * @return array<string, mixed>
     */
    private function group(
        string $id,
        string $eyebrow,
        string $title,
        string $description,
        array $cards,
        array $indexes,
        bool $includesDkim = false
    ): array {
        $groupCards = [];

        foreach ($indexes as $index) {
            if (!isset($cards[$index])) {
                continue;
            }

            $card = $cards[$index];
            $card['colClass'] = 'col-xxl-4 col-xl-4 col-lg-6';
            $groupCards[] = $card;
        }

        return [
            'id' => $id,
            'eyebrow' => $eyebrow,
            'title' => $title,
            'description' => $description,
            'cards' => $groupCards,
            'includesDkim' => $includesDkim,
        ];
    }

}
