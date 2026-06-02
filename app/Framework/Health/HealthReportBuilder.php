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

namespace Catalyst\Framework\Health;

use Catalyst\Framework\Cache\BootstrapCacheManager;
use Catalyst\Framework\Cache\CacheManager;
use Catalyst\Framework\Cli\Support\RouteContractInspector;
use Catalyst\Framework\Deployment\DeploymentManager;
use Catalyst\Framework\FeatureFlag\FeatureFlagManager;
use Catalyst\Framework\Plugin\PluginManager;
use Catalyst\Framework\Queue\QueueRepository;
use Catalyst\Framework\Route\Router;
use Catalyst\Framework\Schedule\ScheduleRegistry;
use Catalyst\Framework\Schedule\ScheduleRepository;
use Catalyst\Framework\Session\DatabaseSessionHandler;
use Catalyst\Framework\Session\SessionManager;
use Catalyst\Framework\Storage\StorageManager;
use Catalyst\Framework\Tenancy\TenancyManager;
use Catalyst\Helpers\Config\ConfigManager;
use Catalyst\Helpers\Path\ProjectPath;
use Catalyst\Helpers\Security\SensitiveValueRedactor;

/**
 * Defines the Health Report Builder class contract.
 *
 * @package Catalyst\Framework\Health
 * Responsibility: Coordinates the health report builder behavior within its module boundary.
 */
final class HealthReportBuilder
{
    private const MIN_PHP = '8.4.0';

    /** @var string[] */
    private const REQUIRED_EXTENSIONS = [
        'mbstring', 'pdo', 'json', 'fileinfo', 'openssl', 'ctype',
    ];

    /**
     * @return array{
     *   ok: bool,
     *   live: bool,
     *   ready: bool,
     *   configured: bool,
     *   environment: string,
     *   generated_at: string,
     *   summary: array{checks:int,warnings:int,failures:int,route_issues:int},
     *   core: array<int, array{label:string,status:string,detail?:string}>,
     *   runtime: array<int, array{label:string,status:string,detail?:string}>,
     *   platform: array<int, array{label:string,status:string,detail?:string}>,
     *   session: array<int, array{label:string,status:string,detail?:string}>,
     *   cache: array<int, array{label:string,status:string,detail?:string}>,
     *   queue: array<int, array{label:string,status:string,detail?:string}>,
     *   scheduler: array<int, array{label:string,status:string,detail?:string}>,
     *   storage: array<int, array{label:string,status:string,detail?:string}>,
     *   secrets: array<int, array{label:string,status:string,detail?:string}>,
     *   throttling: array<int, array{label:string,status:string,detail?:string}>,
     *   route_contract: array{
     *     ok: bool,
     *     issue_count: int,
     *     checks: array<string, array<string, int|bool>>,
     *     issues: array<int, array<string, string>>
     *   }
     * }
     */
    public function build(): array
    {
        $config = ConfigManager::getInstance();
        $core = $this->coreChecks();
        $runtime = $this->runtimeChecks($config);
        $platform = $this->platformChecks();
        $session = $this->sessionChecks($config);
        $cache = $this->cacheChecks($config);
        $queue = $this->queueChecks();
        $scheduler = $this->schedulerChecks();
        $storage = $this->storageChecks();
        $secrets = $this->secretChecks($config);
        $throttling = $this->throttlingChecks($config);
        $routeContract = (new RouteContractInspector())->inspect();

        $failures = 0;
        $warnings = 0;
        $checks = 0;

        foreach ([$core, $runtime, $platform, $session, $cache, $queue, $scheduler, $storage, $secrets, $throttling] as $section) {
            foreach ($section as $check) {
                $checks++;
                if (($check['status'] ?? '') === 'fail') {
                    $failures++;
                }
                if (($check['status'] ?? '') === 'warn') {
                    $warnings++;
                }
            }
        }

        $ok = $failures === 0 && $routeContract['ok'];

        return [
            'ok' => $ok,
            'live' => true,
            'ready' => $ok && $config->isConfigured(),
            'configured' => $config->isConfigured(),
            'environment' => $config->getEnvironment(),
            'generated_at' => gmdate('c'),
            'summary' => [
                'checks' => $checks,
                'warnings' => $warnings,
                'failures' => $failures,
                'route_issues' => $routeContract['issue_count'],
            ],
            'core' => $core,
            'runtime' => $runtime,
            'platform' => $platform,
            'session' => $session,
            'cache' => $cache,
            'queue' => $queue,
            'scheduler' => $scheduler,
            'storage' => $storage,
            'secrets' => $secrets,
            'throttling' => $throttling,
            'route_contract' => $routeContract,
        ];
    }

    /**
     * @return array<int, array{label:string,status:string,detail?:string}>
     */
    private function coreChecks(): array
    {
        $missing = [];
        foreach (self::REQUIRED_EXTENSIONS as $extension) {
            if (!extension_loaded($extension)) {
                $missing[] = $extension;
            }
        }

        return [
            [
                'label' => __('settings.health.checks.php_version') . ' ' . PHP_VERSION,
                'status' => version_compare(PHP_VERSION, self::MIN_PHP, '>=') ? 'ok' : 'fail',
            ],
            [
                'label' => __('settings.health.checks.extensions'),
                'status' => $missing === [] ? 'ok' : 'fail',
                'detail' => $missing === [] ? implode(', ', self::REQUIRED_EXTENSIONS) : 'missing: ' . implode(', ', $missing),
            ],
            [
                'label' => __('settings.health.checks.env_file'),
                'status' => file_exists(PD . DS . 'boot-core' . DS . 'config' . DS . 'env' . DS . '.env') ? 'ok' : 'fail',
            ],
            [
                'label' => __('settings.health.checks.vendor'),
                'status' => is_dir(PD . DS . 'vendor') ? 'ok' : 'fail',
            ],
            [
                'label' => __('settings.health.checks.composer_autoloader'),
                'status' => class_exists('Composer\\Autoload\\ClassLoader') ? 'ok' : 'fail',
            ],
        ];
    }

    /**
     * @return array<int, array{label:string,status:string,detail?:string}>
     */
    private function runtimeChecks(ConfigManager $config): array
    {
        return [
            [
                'label' => __('settings.health.checks.environment'),
                'status' => 'ok',
                'detail' => $config->getEnvironment(),
            ],
            [
                'label' => __('settings.health.checks.json_config_bootstrap'),
                'status' => $config->all() === [] && !$config->isConfigured() ? 'warn' : 'ok',
                'detail' => $config->all() === []
                    ? __('settings.health.details.running_defaults')
                    : __('settings.health.details.sections_loaded', ['count' => count($config->all())]),
            ],
            [
                'label' => __('settings.health.checks.project_configured'),
                'status' => $config->isConfigured() ? 'ok' : 'warn',
                'detail' => $config->isConfigured()
                    ? __('settings.health.details.project_configured')
                    : __('settings.health.details.setup_owns_readiness'),
            ],
        ];
    }

    /**
     * @return array<int, array{label:string,status:string,detail?:string}>
     */
    private function platformChecks(): array
    {
        $flags = FeatureFlagManager::getInstance()->summary();
        $plugins = PluginManager::getInstance()->all();
        $enabledPlugins = count(array_filter($plugins, static fn (array $plugin): bool => (bool) ($plugin['enabled'] ?? false)));
        $deployments = DeploymentManager::getInstance()->summary();
        $tenancy = TenancyManager::getInstance()->summary();

        return [
            [
                'label' => __('settings.health.checks.feature_flags'),
                'status' => $flags['disabled'] > 0 ? 'warn' : 'ok',
                'detail' => __('settings.health.details.feature_flags_summary', [
                    'total' => $flags['count'],
                    'enabled' => $flags['enabled'],
                    'disabled' => $flags['disabled'],
                    'readonly' => $flags['read_only'],
                ]),
            ],
            [
                'label' => __('settings.health.checks.plugins'),
                'status' => $plugins === [] ? 'warn' : 'ok',
                'detail' => __('settings.health.details.plugins_enabled', [
                    'enabled' => $enabledPlugins,
                    'total' => count($plugins),
                ]),
            ],
            [
                'label' => __('settings.health.checks.deploy_profiles'),
                'status' => ($deployments['profiles'] ?? []) === [] ? 'warn' : 'ok',
                'detail' => __('settings.health.details.deployments_summary', [
                    'profiles' => count((array) ($deployments['profiles'] ?? [])),
                    'runs' => (int) ($deployments['run_count'] ?? 0),
                ]),
            ],
            [
                'label' => __('settings.health.checks.tenancy_baseline'),
                'status' => !empty($tenancy['data_isolation_active']) ? 'ok' : 'warn',
                'detail' => __('settings.health.details.tenancy_summary', [
                    'runtime' => $tenancy['strategy'] ?? 'single',
                    'target' => $tenancy['target_strategy'] ?? 'shared-db-tenant-id',
                    'isolation' => !empty($tenancy['data_isolation_active'])
                        ? __('settings.health.details.isolation_active')
                        : __('settings.health.details.isolation_inactive'),
                    'tenants' => (int) ($tenancy['tenant_count'] ?? 0),
                ]),
            ],
        ];
    }

    /**
     * @return array<int, array{label:string,status:string,detail?:string}>
     */
    private function sessionChecks(ConfigManager $config): array
    {
        $session = $config->entry('session', 'session');
        $driver = strtolower((string) ($session['session_driver'] ?? 'file'));
        $connection = (string) ($session['session_connection'] ?? 'db1');
        $table = (string) ($session['session_table'] ?? 'sessions');
        $sameSite = (string) ($session['session_same_site'] ?? 'Strict');
        $sameSiteLabel = $this->translateSameSite($sameSite);
        $secure = (bool) ($session['session_secure'] ?? false);
        $backendStatus = 'ok';
        $backendDetail = __('settings.health.details.session_native_handler');

        if ($driver === 'database') {
            $probe = new DatabaseSessionHandler($connection, $table);
            $backendStatus = $probe->open('', '') ? 'ok' : 'fail';
            $backendDetail = sprintf('%s.%s', $connection, $table);
        } elseif ($driver !== 'file') {
            $backendStatus = 'fail';
            $backendDetail = __('settings.health.details.unsupported_driver', ['driver' => $driver]);
        }

        return [
            [
                'label' => __('settings.health.checks.session_config'),
                'status' => isset($session['session_name'], $session['session_lifetime']) ? 'ok' : 'fail',
                'detail' => (string) ($session['session_name'] ?? 'missing'),
            ],
            [
                'label' => __('settings.health.checks.session_backend'),
                'status' => $backendStatus,
                'detail' => __('settings.health.details.session_backend_target', [
                    'driver' => $driver,
                    'target' => $backendDetail,
                ]),
            ],
            [
                'label' => __('settings.health.checks.session_cookies'),
                'status' => $sameSite === 'None' && !$secure ? 'fail' : 'ok',
                'detail' => __('settings.health.details.session_cookie_detail', [
                    'secure' => $secure ? __('ui.common.yes') : __('ui.common.no'),
                    'same_site' => $sameSiteLabel,
                ]),
            ],
            [
                'label' => __('settings.health.checks.form_state_bridge'),
                'status' => method_exists(SessionManager::class, 'flashOldInput') ? 'ok' : 'fail',
                'detail' => __('settings.health.details.form_state_bridge'),
            ],
        ];
    }

    /**
     * @return array<int, array{label:string,status:string,detail?:string}>
     */
    private function cacheChecks(ConfigManager $config): array
    {
        $cache = CacheManager::getInstance()->summary();
        $routeCacheEnabled = (bool) ($cache['route_cache'] ?? false);
        $routeCacheFile = Router::getInstance()->getCacheFile();
        $environment = (string) ($cache['environment'] ?? $config->getEnvironment());
        $runtimeEnabled = (bool) ($cache['runtime_enabled'] ?? false);
        $configCacheFile = BootstrapCacheManager::configCacheFile();
        $discoveryCacheFile = BootstrapCacheManager::discoveryCacheFile();

        return [
            [
                'label' => __('settings.health.checks.cache_activation'),
                'status' => $runtimeEnabled ? 'ok' : (($cache['cache_enabled'] ?? false) ? 'warn' : 'ok'),
                'detail' => __('settings.health.details.cache_activation', [
                    'configured' => ($cache['cache_enabled'] ?? false) ? __('ui.common.yes') : __('ui.common.no'),
                    'runtime' => $runtimeEnabled ? __('ui.common.yes') : __('ui.common.no'),
                    'env' => $environment,
                ]),
            ],
            [
                'label' => __('settings.health.checks.application_cache_store'),
                'status' => ($cache['app_cache'] ?? false) && ($cache['driver'] ?? 'null') === 'null' ? 'warn' : 'ok',
                'detail' => __('settings.health.details.driver_prefix', [
                    'driver' => $cache['driver'] ?? 'null',
                    'prefix' => $cache['prefix'] ?? 'catalyst_',
                ]),
            ],
            [
                'label' => __('settings.health.checks.config_cache_artifact'),
                'status' => ($cache['config_cache'] ?? false) && !is_file($configCacheFile) ? 'warn' : 'ok',
                'detail' => is_file($configCacheFile)
                    ? __('settings.health.details.artifact_present_cache')
                    : __('settings.health.details.artifact_not_generated'),
            ],
            [
                'label' => __('settings.health.checks.discovery_cache_artifact'),
                'status' => ($cache['discovery_cache'] ?? false) && !is_file($discoveryCacheFile) ? 'warn' : 'ok',
                'detail' => is_file($discoveryCacheFile)
                    ? __('settings.health.details.artifact_present_cache')
                    : __('settings.health.details.artifact_not_generated'),
            ],
            [
                'label' => __('settings.health.checks.route_cache_artifact'),
                'status' => $routeCacheEnabled && !is_file($routeCacheFile) ? 'warn' : 'ok',
                'detail' => is_file($routeCacheFile)
                    ? __('settings.health.details.artifact_present_path', ['path' => str_replace(PD . DS, '', ProjectPath::routeCacheFile())])
                    : __('settings.health.details.artifact_not_generated'),
            ],
        ];
    }

    /**
     * @return array<int, array{label:string,status:string,detail?:string}>
     */
    private function storageChecks(): array
    {
        $storage = StorageManager::getInstance()->summary();
        $uploadsDir = PD . DS . 'public' . DS . 'uploads';

        return [
            [
                'label' => __('settings.health.checks.storage_manager'),
                'status' => 'ok',
                'detail' => __('settings.health.details.storage_default_local', [
                    'default' => $storage['default_disk'],
                    'driver' => $storage['local_driver'],
                ]),
            ],
            [
                'label' => __('settings.health.checks.uploads_directory'),
                'status' => is_dir($uploadsDir) && is_writable($uploadsDir) ? 'ok' : 'warn',
                'detail' => is_dir($uploadsDir)
                    ? __('settings.health.details.uploads_directory_present')
                    : __('settings.health.details.uploads_directory_missing'),
            ],
            [
                'label' => __('settings.health.checks.ftp_runtime'),
                'status' => function_exists('ftp_connect') ? 'ok' : 'warn',
                'detail' => function_exists('ftp_ssl_connect')
                    ? __('settings.health.details.ftp_ftps_available')
                    : __('settings.health.details.ftp_only_available'),
            ],
            [
                'label' => __('settings.health.checks.sftp_runtime'),
                'status' => function_exists('curl_init') ? 'ok' : 'warn',
                'detail' => function_exists('curl_init')
                    ? __('settings.health.details.sftp_curl_available')
                    : __('settings.health.details.sftp_curl_missing'),
            ],
            [
                'label' => __('settings.health.checks.remote_storage_disk'),
                'status' => $storage['remote_ready'] ? 'ok' : 'warn',
                'detail' => __('settings.health.details.remote_storage_summary', [
                    'driver' => $storage['remote_driver'],
                    'root' => $storage['remote_root'],
                ]),
            ],
        ];
    }

    /**
     * @return array<int, array{label:string,status:string,detail?:string}>
     */
    private function queueChecks(): array
    {
        try {
            $summary = QueueRepository::getInstance()->summary();

            return [
                [
                    'label' => __('settings.health.checks.queue_backend'),
                    'status' => 'ok',
                    'detail' => __('settings.health.details.queue_backend', [
                        'db' => $summary['connection'],
                        'queue' => $summary['default_queue'],
                    ]),
                ],
                [
                    'label' => __('settings.health.checks.queued_jobs'),
                    'status' => 'ok',
                    'detail' => __('settings.health.details.queued_jobs', [
                        'pending' => $summary['pending_jobs'],
                        'failed' => $summary['failed_jobs'],
                    ]),
                ],
            ];
        } catch (\Throwable $e) {
            return [
                [
                    'label' => __('settings.health.checks.queue_backend'),
                    'status' => 'warn',
                    'detail' => $e->getMessage(),
                ],
            ];
        }
    }

    /**
     * @return array<int, array{label:string,status:string,detail?:string}>
     */
    private function schedulerChecks(): array
    {
        try {
            $registry = ScheduleRegistry::getInstance()->all();
            $summary = ScheduleRepository::getInstance()->summary();

            return [
                [
                    'label' => __('settings.health.checks.schedule_registry'),
                    'status' => $registry === [] ? 'warn' : 'ok',
                    'detail' => __('settings.health.details.registered_tasks', ['count' => count($registry)]),
                ],
                [
                    'label' => __('settings.health.checks.scheduler_history'),
                    'status' => 'ok',
                    'detail' => __('settings.health.details.scheduler_history', [
                        'table' => $summary['history_table'],
                        'runs' => $summary['total_runs'],
                        'last' => $summary['last_run'] !== null
                            ? __('settings.health.details.scheduler_last', ['last' => $summary['last_run']])
                            : '',
                    ]),
                ],
            ];
        } catch (\Throwable $e) {
            return [
                [
                    'label' => __('settings.health.checks.schedule_registry'),
                    'status' => 'warn',
                    'detail' => $e->getMessage(),
                ],
            ];
        }
    }

    /**
     * @return array<int, array{label:string,status:string,detail?:string}>
     */
    private function secretChecks(ConfigManager $config): array
    {
        $app = $config->entry('app', 'project');
        $db = $config->entry('db', 'db1');
        $mail = $config->entry('mail', 'mail1');
        $ftp = $config->entry('ftp', 'ftp1');
        $secretStore = $config->secretStore();
        $publicLeaks = $secretStore->publicSecretLeaks();
        $env = defined('GET_ENV_VAR') && is_array(GET_ENV_VAR) ? GET_ENV_VAR : [];
        $appKey = (string) ($app['project_key'] ?? '');
        $ftpHost = trim((string) ($ftp['ftp_host'] ?? ''));
        $ftpUser = trim((string) ($ftp['ftp_username'] ?? ''));
        $ftpPassword = trim((string) ($ftp['ftp_password'] ?? ''));
        $googleClientId = trim((string) ($env['GOOGLE_CLIENT_ID'] ?? ''));
        $googleClientSecret = trim((string) ($env['GOOGLE_CLIENT_SECRET'] ?? ''));
        $githubClientId = trim((string) ($env['GITHUB_CLIENT_ID'] ?? ''));
        $githubClientSecret = trim((string) ($env['GITHUB_CLIENT_SECRET'] ?? ''));
        $oauthDetail = 'no providers configured';
        $oauthStatus = 'warn';

        $googleReady = $googleClientId !== '' && $googleClientSecret !== '';
        $githubReady = $githubClientId !== '' && $githubClientSecret !== '';
        $googlePartial = ($googleClientId !== '') xor ($googleClientSecret !== '');
        $githubPartial = ($githubClientId !== '') xor ($githubClientSecret !== '');

        if ($googlePartial || $githubPartial) {
            $providers = [];

            if ($googlePartial) {
                $providers[] = 'google incomplete';
            }

            if ($githubPartial) {
                $providers[] = 'github incomplete';
            }

            $oauthDetail = implode(', ', $providers);
        } elseif ($googleReady || $githubReady) {
            $oauthStatus = 'ok';
            $providers = [];

            if ($googleReady) {
                $providers[] = 'google';
            }

            if ($githubReady) {
                $providers[] = 'github';
            }

            $oauthDetail = 'configured: ' . implode(', ', $providers);
        }

        return [
            [
                'label' => __('settings.health.checks.redaction_boundary'),
                'status' => class_exists(SensitiveValueRedactor::class) ? 'ok' : 'fail',
                'detail' => class_exists(SensitiveValueRedactor::class)
                    ? __('settings.health.details.redactor_present')
                    : __('settings.health.details.redactor_missing'),
            ],
            [
                'label' => __('settings.health.checks.secret_config_split'),
                'status' => $publicLeaks === [] ? 'ok' : 'fail',
                'detail' => $publicLeaks === []
                    ? ($secretStore->exists()
                        ? __('settings.health.details.secret_store_isolated')
                        : __('settings.health.details.secret_store_missing_no_leaks'))
                    : __('settings.health.details.public_secrets_leak', ['keys' => implode(', ', $publicLeaks)]),
            ],
            [
                'label' => __('settings.health.checks.project_key'),
                'status' => $appKey === '' || str_contains($appKey, 'FREE-') ? 'warn' : 'ok',
                'detail' => $appKey === '' ? __('ui.common.missing') : __('ui.common.present'),
            ],
            [
                'label' => __('settings.health.checks.db_credentials'),
                'status' => ($db['db_host'] ?? '') !== '' && ($db['db_username'] ?? '') !== '' ? 'ok' : 'warn',
                'detail' => ($db['db_host'] ?? '') !== ''
                    ? (string) $db['db_host']
                    : __('settings.health.details.host_missing'),
            ],
            [
                'label' => __('settings.health.checks.mail_credentials'),
                'status' => ($mail['mail_host'] ?? '') !== '' ? 'ok' : 'warn',
                'detail' => ($mail['mail_host'] ?? '') !== ''
                    ? (string) $mail['mail_host']
                    : __('settings.health.details.mail_host_missing'),
            ],
            [
                'label' => __('settings.health.checks.ftp_credentials'),
                'status' => $ftpHost === '' ? 'warn' : (($ftpUser !== '' && $ftpPassword !== '') ? 'ok' : 'warn'),
                'detail' => $ftpHost === ''
                    ? __('settings.health.details.ftp_host_missing')
                    : (($ftpUser !== '' && $ftpPassword !== '')
                        ? $ftpHost
                        : __('settings.health.details.host_present_credentials_incomplete')),
            ],
            [
                'label' => __('settings.health.checks.oauth_secrets'),
                'status' => $oauthStatus,
                'detail' => $oauthDetail,
            ],
        ];
    }

    /**
     * @return array<int, array{label:string,status:string,detail?:string}>
     */
    private function throttlingChecks(ConfigManager $config): array
    {
        $environment = $config->getEnvironment();

        return [
            [
                'label' => __('settings.health.checks.login_throttle'),
                'status' => 'ok',
                'detail' => $environment === 'development'
                    ? __('settings.health.details.login_throttle_dev')
                    : __('settings.health.details.login_throttle_prod'),
            ],
            [
                'label' => __('settings.health.checks.request_throttle'),
                'status' => 'ok',
                'detail' => $environment === 'development'
                    ? __('settings.health.details.request_throttle_dev')
                    : __('settings.health.details.request_throttle_prod'),
            ],
        ];
    }

    /**
     * Handles the translate same site workflow.
     */
    private function translateSameSite(string $sameSite): string
    {
        return match (strtolower($sameSite)) {
            'strict' => __('settings.options.same_site.strict'),
            'lax' => __('settings.options.same_site.lax'),
            'none' => __('settings.options.same_site.none'),
            default => $sameSite,
        };
    }
}
