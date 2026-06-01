<?php

declare(strict_types=1);

namespace App\Services;

use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Helpers\Config\AppEntryCatalog;
use Catalyst\Helpers\Config\ConfigManager;

final class ApplicationEntryService
{
    public function resolveRootTarget(): ?string
    {
        $config = ConfigManager::getInstance();
        if (!$config->isConfigured()) {
            return null;
        }

        $project = $config->section('app')['project'] ?? [];
        $primary = trim((string) ($project['project_entry'] ?? ''));

        if ($primary === '' || $primary === 'Home') {
            return null;
        }

        if ($primary === AppEntryCatalog::USER_ACCESS) {
            $auth = AuthManager::getInstance();
            $isAuthenticated = $auth->check() || $auth->loginFromRemember();

            if (!$isAuthenticated) {
                return '/login';
            }

            return $this->resolveCatalogPath((string) ($project['project_entry_secondary'] ?? ''));
        }

        return $this->resolveCatalogPath($primary);
    }

    private function resolveCatalogPath(string $entry): ?string
    {
        $path = AppEntryCatalog::resolvePath($entry);

        if (!is_string($path) || trim($path) === '' || $path === '/') {
            return null;
        }

        return $path;
    }
}
