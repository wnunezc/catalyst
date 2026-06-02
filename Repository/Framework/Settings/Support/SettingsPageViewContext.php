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

use Catalyst\Framework\Localization\LocalizationManager;
use Catalyst\Helpers\Config\AppEntryCatalog;

/**
 * Provides normalized configuration values and labels to settings view factories.
 *
 * @package Catalyst\Repository\Settings\Support
 * Responsibility: Exposes setup sections, translated notices and option maps without leaking view assembly concerns.
 */
final class SettingsPageViewContext
{
    /**
     * @var array<string, mixed>
     */
    private array $app;

    /**
     * @var array<string, mixed>
     */
    private array $db;

    /**
     * @var array<string, mixed>
     */
    private array $mail;

    /**
     * @var array<string, mixed>
     */
    private array $ftp;

    /**
     * @var array<string, mixed>
     */
    private array $session;

    /**
     * @var array<string, mixed>
     */
    private array $cache;

    /**
     * @var array<string, mixed>
     */
    private array $logging;

    /**
     * @var array<string, mixed>
     */
    private array $security;

    /**
     * @var array<string, mixed>
     */
    private array $websocket;

    /**
     * @var array<string, mixed>
     */
    private array $devtools;

    /**
     * @var array<string, mixed>
     */
    private array $cors;

    /**
     * Initializes the Settings Page View Context instance.
     *
     * Responsibility: Initializes the Settings Page View Context instance.
     */
    public function __construct(array $scope)
    {
        $this->app = (array) ($scope['app'] ?? []);
        $this->db = (array) ($scope['db'] ?? []);
        $this->mail = (array) ($scope['mail'] ?? []);
        $this->ftp = (array) ($scope['ftp'] ?? []);
        $this->session = (array) ($scope['session'] ?? []);
        $this->cache = (array) ($scope['cache'] ?? []);
        $this->logging = (array) ($scope['logging'] ?? []);
        $this->security = (array) ($scope['security'] ?? []);
        $this->websocket = (array) ($scope['websocket'] ?? []);
        $this->devtools = (array) ($scope['devtools'] ?? []);
        $this->cors = (array) ($scope['cors'] ?? []);
    }

    /**
     * Translates a settings label with optional replacements.
     *
     * Responsibility: Translates a settings label with optional replacements.
     */
    public function t(string $key, array $replace = []): string
    {
        return __($key, $replace);
    }

    /**
     * Returns application settings.
     *
     * Responsibility: Returns application settings.
     * @return array<string, mixed>
     */
    public function app(): array
    {
        return $this->app;
    }

    /**
     * Returns database settings.
     *
     * Responsibility: Returns database settings.
     * @return array<string, mixed>
     */
    public function db(): array
    {
        return $this->db;
    }

    /**
     * Returns mail settings.
     *
     * Responsibility: Returns mail settings.
     * @return array<string, mixed>
     */
    public function mail(): array
    {
        return $this->mail;
    }

    /**
     * Returns transfer settings.
     *
     * Responsibility: Returns transfer settings.
     * @return array<string, mixed>
     */
    public function ftp(): array
    {
        return $this->ftp;
    }

    /**
     * Returns session settings.
     *
     * Responsibility: Returns session settings.
     * @return array<string, mixed>
     */
    public function session(): array
    {
        return $this->session;
    }

    /**
     * Returns cache settings.
     *
     * Responsibility: Returns cache settings.
     * @return array<string, mixed>
     */
    public function cache(): array
    {
        return $this->cache;
    }

    /**
     * Returns logging settings.
     *
     * Responsibility: Returns logging settings.
     * @return array<string, mixed>
     */
    public function logging(): array
    {
        return $this->logging;
    }

    /**
     * Returns security settings.
     *
     * Responsibility: Returns security settings.
     * @return array<string, mixed>
     */
    public function security(): array
    {
        return $this->security;
    }

    /**
     * Returns WebSocket settings.
     *
     * Responsibility: Returns WebSocket settings.
     * @return array<string, mixed>
     */
    public function websocket(): array
    {
        return $this->websocket;
    }

    /**
     * Returns developer-tool compatibility settings.
     *
     * Responsibility: Returns developer-tool compatibility settings.
     * @return array<string, mixed>
     */
    public function devtools(): array
    {
        return $this->devtools;
    }

    /**
     * Returns CORS settings.
     *
     * Responsibility: Returns CORS settings.
     * @return array<string, mixed>
     */
    public function cors(): array
    {
        return $this->cors;
    }

    /**
     * Determines whether development-only entry points may be selected.
     *
     * Responsibility: Determines whether development-only entry points may be selected.
     */
    public function isDevelopmentEntryEnv(): bool
    {
        return ($this->app['project_env'] ?? 'production') === 'development';
    }

    /**
     * Determines whether production-only runtime options may be configured.
     *
     * Responsibility: Determines whether production-only runtime options may be configured.
     */
    public function isProductionRuntimeEnv(): bool
    {
        return ($this->app['project_env'] ?? 'production') === 'production';
    }

    /**
     * Returns translated environment options.
     *
     * Responsibility: Returns translated environment options.
     * @return array<string, string>
     */
    public function envMap(): array
    {
        return [
            'development' => $this->t('settings.options.environment.development'),
            'staging' => $this->t('settings.options.environment.staging'),
            'testing' => $this->t('settings.options.environment.testing'),
            'production' => $this->t('settings.options.environment.production'),
        ];
    }

    /**
     * Returns available locale labels.
     *
     * Responsibility: Returns available locale labels.
     * @return array<string, string>
     */
    public function langMap(): array
    {
        return LocalizationManager::getInstance()->localeLabels();
    }

    /**
     * Returns mail encryption options.
     *
     * Responsibility: Returns mail encryption options.
     * @return array<string, string>
     */
    public function encMap(): array
    {
        return [
            'tls' => 'TLS',
            'ssl' => 'SSL',
            'starttls' => 'STARTTLS',
            'none' => $this->t('settings.options.encryption.none'),
        ];
    }

    /**
     * Returns session SameSite options.
     *
     * Responsibility: Returns session SameSite options.
     * @return array<string, string>
     */
    public function siteMap(): array
    {
        return [
            'Strict' => $this->t('settings.options.same_site.strict'),
            'Lax' => $this->t('settings.options.same_site.lax'),
            'None' => $this->t('settings.options.same_site.none'),
        ];
    }

    /**
     * Returns cache driver options.
     *
     * Responsibility: Returns cache driver options.
     * @return array<string, string>
     */
    public function drvMap(): array
    {
        return [
            'file' => $this->t('settings.options.cache_driver.file'),
            'array' => $this->t('settings.options.cache_driver.array'),
            'null' => $this->t('settings.options.cache_driver.null'),
        ];
    }

    /**
     * Returns logging channel options.
     *
     * Responsibility: Returns logging channel options.
     * @return array<string, string>
     */
    public function chanMap(): array
    {
        return [
            'single' => $this->t('settings.options.log_channel.single'),
            'daily' => $this->t('settings.options.log_channel.daily'),
            'stderr' => 'STDERR',
        ];
    }

    /**
     * Returns logging severity options.
     *
     * Responsibility: Returns logging severity options.
     * @return array<string, string>
     */
    public function lvlMap(): array
    {
        return [
            'debug' => $this->t('settings.options.log_level.debug'),
            'info' => $this->t('settings.options.log_level.info'),
            'notice' => $this->t('settings.options.log_level.notice'),
            'warning' => $this->t('settings.options.log_level.warning'),
            'error' => $this->t('settings.options.log_level.error'),
            'critical' => $this->t('settings.options.log_level.critical'),
            'alert' => $this->t('settings.options.log_level.alert'),
            'emergency' => $this->t('settings.options.log_level.emergency'),
        ];
    }

    /**
     * Returns allowed primary application entry points.
     *
     * Responsibility: Returns allowed primary application entry points.
     * @return array<string, string>
     */
    public function entryMap(): array
    {
        return AppEntryCatalog::primaryLabels($this->isDevelopmentEntryEnv());
    }

    /**
     * Returns allowed secondary application entry points.
     *
     * Responsibility: Returns allowed secondary application entry points.
     * @return array<string, string>
     */
    public function entrySecondaryMap(): array
    {
        return ['' => $this->t('settings.options.entry_secondary_unused')]
            + AppEntryCatalog::secondaryLabels($this->isDevelopmentEntryEnv());
    }

    /**
     * Returns the transfer-configuration notice.
     *
     * Responsibility: Returns the transfer-configuration notice.
     */
    public function ftpNotice(): string
    {
        return $this->t('settings.notices.ftp');
    }

    /**
     * Returns the cache notice appropriate for the runtime environment.
     *
     * Responsibility: Returns the cache notice appropriate for the runtime environment.
     */
    public function cacheNotice(): string
    {
        return $this->isProductionRuntimeEnv()
            ? $this->t('settings.notices.cache_runtime')
            : $this->t('settings.notices.cache_locked');
    }

    /**
     * Returns the developer-tools compatibility notice.
     *
     * Responsibility: Returns the developer-tools compatibility notice.
     */
    public function devtoolsNotice(): string
    {
        return $this->t('settings.notices.devtools');
    }

    /**
     * Returns a masked application-key preview.
     *
     * Responsibility: Returns a masked application-key preview.
     */
    public function appKeyPreview(): string
    {
        $appKey = (string) ($this->app['project_key'] ?? '');

        return $appKey !== '' ? substr($appKey, 0, 4) . '••••••••' : '';
    }

    /**
     * Returns configured CORS origins as a comma-separated string.
     *
     * Responsibility: Returns configured CORS origins as a comma-separated string.
     */
    public function corsOrigins(): string
    {
        return is_array($this->cors['allowed_origins'] ?? null)
            ? implode(', ', $this->cors['allowed_origins'])
            : (string) ($this->cors['allowed_origins'] ?? '*');
    }
}
