<?php

declare(strict_types=1);

namespace Catalyst\Repository\Settings\Support;

use Catalyst\Framework\Localization\LocalizationManager;
use Catalyst\Helpers\Config\AppEntryCatalog;

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

    public function t(string $key, array $replace = []): string
    {
        return __($key, $replace);
    }

    /**
     * @return array<string, mixed>
     */
    public function app(): array
    {
        return $this->app;
    }

    /**
     * @return array<string, mixed>
     */
    public function db(): array
    {
        return $this->db;
    }

    /**
     * @return array<string, mixed>
     */
    public function mail(): array
    {
        return $this->mail;
    }

    /**
     * @return array<string, mixed>
     */
    public function ftp(): array
    {
        return $this->ftp;
    }

    /**
     * @return array<string, mixed>
     */
    public function session(): array
    {
        return $this->session;
    }

    /**
     * @return array<string, mixed>
     */
    public function cache(): array
    {
        return $this->cache;
    }

    /**
     * @return array<string, mixed>
     */
    public function logging(): array
    {
        return $this->logging;
    }

    /**
     * @return array<string, mixed>
     */
    public function security(): array
    {
        return $this->security;
    }

    /**
     * @return array<string, mixed>
     */
    public function websocket(): array
    {
        return $this->websocket;
    }

    /**
     * @return array<string, mixed>
     */
    public function devtools(): array
    {
        return $this->devtools;
    }

    /**
     * @return array<string, mixed>
     */
    public function cors(): array
    {
        return $this->cors;
    }

    public function isDevelopmentEntryEnv(): bool
    {
        return ($this->app['project_env'] ?? 'production') === 'development';
    }

    public function isProductionRuntimeEnv(): bool
    {
        return ($this->app['project_env'] ?? 'production') === 'production';
    }

    /**
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
     * @return array<string, string>
     */
    public function langMap(): array
    {
        return LocalizationManager::getInstance()->localeLabels();
    }

    /**
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
     * @return array<string, string>
     */
    public function entryMap(): array
    {
        return AppEntryCatalog::primaryLabels($this->isDevelopmentEntryEnv());
    }

    /**
     * @return array<string, string>
     */
    public function entrySecondaryMap(): array
    {
        return ['' => $this->t('settings.options.entry_secondary_unused')]
            + AppEntryCatalog::secondaryLabels($this->isDevelopmentEntryEnv());
    }

    public function ftpNotice(): string
    {
        return $this->t('settings.notices.ftp');
    }

    public function cacheNotice(): string
    {
        return $this->isProductionRuntimeEnv()
            ? $this->t('settings.notices.cache_runtime')
            : $this->t('settings.notices.cache_locked');
    }

    public function devtoolsNotice(): string
    {
        return $this->t('settings.notices.devtools');
    }

    public function appKeyPreview(): string
    {
        $appKey = (string) ($this->app['project_key'] ?? '');

        return $appKey !== '' ? substr($appKey, 0, 4) . '••••••••' : '';
    }

    public function corsOrigins(): string
    {
        return is_array($this->cors['allowed_origins'] ?? null)
            ? implode(', ', $this->cors['allowed_origins'])
            : (string) ($this->cors['allowed_origins'] ?? '*');
    }
}
