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

use Catalyst\Helpers\Config\ConfigManager;

/**
 * Defines the App Config Writer class contract.
 *
 * @package Catalyst\Repository\Settings\Support
 * Responsibility: Coordinates the app config writer behavior within its module boundary.
 */
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
