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

namespace App\Services;

use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Helpers\Config\AppEntryCatalog;
use Catalyst\Helpers\Config\ConfigManager;

/**
 * Service for resolving the configured root entry target.
 *
 * @package App\Services
 * Responsibility: Maps project entry configuration and authentication state to the first route users should reach.
 */
final class ApplicationEntryService
{
    /**
     * Resolves the root redirect target from project configuration.
     *
     * Responsibility: Resolves the root redirect target from project configuration.
     */
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

    /**
     * Resolves a configured catalog entry into a navigable path.
     *
     * Responsibility: Resolves a configured catalog entry into a navigable path.
     */
    private function resolveCatalogPath(string $entry): ?string
    {
        $path = AppEntryCatalog::resolvePath($entry);

        if (!is_string($path) || trim($path) === '' || $path === '/') {
            return null;
        }

        return $path;
    }
}
