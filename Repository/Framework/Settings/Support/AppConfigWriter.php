<?php

declare(strict_types=1);

namespace Catalyst\Repository\Settings\Support;

use Catalyst\Helpers\Config\ConfigManager;

final class AppConfigWriter
{
    /**
     * @param array<string, mixed> $data
     */
    public function save(array $data): void
    {
        $config = ConfigManager::getInstance();
        $existing = $config->section('app')['project'] ?? [];

        $config->writeSection('app', [
            'project' => [
                'project_config' => (bool) ($existing['project_config'] ?? false),
                'project_name' => (string) ($data['app_name'] ?? ''),
                'project_url' => (string) ($data['app_url'] ?? ''),
                'project_env' => (string) ($data['app_env'] ?? $config->getEnvironment()),
                'project_lang' => (string) ($data['app_lang'] ?? 'en'),
                'project_timezone' => (string) ($data['app_timezone'] ?? 'UTC'),
                'project_entry' => (string) ($data['app_entry'] ?? ''),
                'project_entry_secondary' => (string) ($data['app_entry_secondary'] ?? ''),
                'project_key' => (string) ($data['app_key'] ?? ''),
                'project_debug' => (bool) ($data['app_debug'] ?? false),
            ],
        ]);
    }
}
