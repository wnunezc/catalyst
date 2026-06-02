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

namespace Catalyst\Framework\Queue;

use Catalyst\Helpers\Config\ConfigManager;

/**
 * Defines the Queue Settings class contract.
 *
 * @package Catalyst\Framework\Queue
 * Responsibility: Coordinates the queue settings behavior within its module boundary.
 */
final class QueueSettings
{
    /**
     * @return array<string, mixed>
     */
    public static function defaults(): array
    {
        return [
            'enabled' => true,
            'connection' => 'db1',
            'default_queue' => 'default',
            'jobs_table' => 'queue_jobs',
            'failed_jobs_table' => 'failed_jobs',
            'stale_after_seconds' => 300,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function current(): array
    {
        $config = ConfigManager::getInstance()->entry('queue', 'queue', self::defaults());

        $config['enabled'] = (bool) ($config['enabled'] ?? true);
        $config['connection'] = trim((string) ($config['connection'] ?? 'db1')) ?: 'db1';
        $config['default_queue'] = trim((string) ($config['default_queue'] ?? 'default')) ?: 'default';
        $config['jobs_table'] = trim((string) ($config['jobs_table'] ?? 'queue_jobs')) ?: 'queue_jobs';
        $config['failed_jobs_table'] = trim((string) ($config['failed_jobs_table'] ?? 'failed_jobs')) ?: 'failed_jobs';
        $config['stale_after_seconds'] = max(30, (int) ($config['stale_after_seconds'] ?? 300));

        return $config;
    }
}
