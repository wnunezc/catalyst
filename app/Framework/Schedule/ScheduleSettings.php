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

namespace Catalyst\Framework\Schedule;

use Catalyst\Helpers\Config\ConfigManager;

/**
 * Resolves scheduler defaults and normalized runtime configuration.
 *
 * @package Catalyst\Framework\Schedule
 * Responsibility: Provides the scheduler enabled flag and history-table setting.
 */
final class ScheduleSettings
{
    /**
     * Returns the default scheduler configuration.
     *
     * @return array<string, mixed>
     */
    public static function defaults(): array
    {
        return [
            'enabled' => true,
            'history_table' => 'scheduler_runs',
        ];
    }

    /**
     * Returns the normalized active scheduler configuration.
     *
     * @return array<string, mixed>
     */
    public static function current(): array
    {
        $config = ConfigManager::getInstance()->entry('schedule', 'schedule', self::defaults());

        $config['enabled'] = (bool) ($config['enabled'] ?? true);
        $config['history_table'] = trim((string) ($config['history_table'] ?? 'scheduler_runs')) ?: 'scheduler_runs';

        return $config;
    }
}
