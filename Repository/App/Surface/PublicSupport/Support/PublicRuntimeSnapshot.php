<?php

declare(strict_types=1);

namespace App\Surface\PublicSupport\Support;

use Catalyst\Framework\Module\ModuleRegistry;
use Catalyst\Framework\Navigation\NavigationRegistry;
use Catalyst\Framework\Route\Router;
use Catalyst\Helpers\Config\ConfigManager;

final class PublicRuntimeSnapshot
{
    /**
     * @return array<string, mixed>
     */
    public function build(): array
    {
        $config = ConfigManager::getInstance();
        $project = $config->section('app')['project'] ?? [];
        $entryCatalog = array_map(static function (array $item): array {
            return [
                'key' => (string)($item['label'] ?? ''),
                'path' => (string)($item['href'] ?? ''),
            ];
        }, NavigationRegistry::getInstance()->publicMenu('/'));

        return [
            'appName' => (string)($project['project_name'] ?? 'Catalyst Framework'),
            'configured' => $config->isConfigured(),
            'environment' => (string)($project['project_env'] ?? $config->getEnvironment()),
            'language' => (string)($project['project_lang'] ?? 'en'),
            'timezone' => (string)($project['project_timezone'] ?? 'UTC'),
            'primaryEntry' => (string)($project['project_entry'] ?? ''),
            'secondaryEntry' => (string)($project['project_entry_secondary'] ?? ''),
            'routeCount' => Router::getInstance()->getRoutes()->count(),
            'publicEntryCount' => count($entryCatalog),
            'entryCatalog' => $entryCatalog,
            'moduleCount' => count(ModuleRegistry::getInstance()->active()),
            'generatedAt' => gmdate(DATE_ATOM),
        ];
    }
}
