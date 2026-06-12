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

namespace Catalyst\Repository\Configuration\Support;

use Catalyst\Helpers\Config\ConfigManager;

/**
 * Writes logging and rotation settings.
 *
 * @package Catalyst\Repository\Configuration\Support
 * Responsibility: Persists the selected logging channel, severity and bounded rotation limits.
 */
final class LoggingConfigWriter
{
    /**
     * Saves normalized logging settings.
     *
     * Responsibility: Saves normalized logging settings.
     * @param array<string, mixed> $data
     */
    public function save(array $data): void
    {
        ConfigManager::getInstance()->writeSection('logging', [
            'logging' => [
                'log_channel' => (string) ($data['log_channel'] ?? 'single'),
                'log_level' => (string) ($data['log_level'] ?? 'warning'),
                'display_logs' => (bool) ($data['display_logs'] ?? false),
                'log_rotation_enabled' => (bool) ($data['log_rotation_enabled'] ?? true),
                'log_max_file_size_mb' => min(50, max(1, (int) ($data['log_max_file_size_mb'] ?? 2))),
                'log_max_rotated_files' => min(10, max(1, (int) ($data['log_max_rotated_files'] ?? 5))),
            ],
        ]);
    }
}
