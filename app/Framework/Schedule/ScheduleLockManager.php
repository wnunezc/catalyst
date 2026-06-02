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

use Catalyst\Helpers\Path\ProjectPath;
use RuntimeException;

/**
 * Defines the Schedule Lock Manager class contract.
 *
 * @package Catalyst\Framework\Schedule
 * Responsibility: Coordinates the schedule lock manager behavior within its module boundary.
 */
final class ScheduleLockManager
{
    /**
     * @template T
     * @param callable():T $callback
     * @return T
     */
    public function runWithLock(string $taskName, callable $callback): mixed
    {
        $path = $this->lockPath($taskName);
        $directory = dirname($path);

        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new RuntimeException('Unable to create scheduler lock directory: ' . $directory);
        }

        $handle = fopen($path, 'c+');

        if ($handle === false) {
            throw new RuntimeException('Unable to open scheduler lock file: ' . $path);
        }

        try {
            if (!flock($handle, LOCK_EX | LOCK_NB)) {
                throw new RuntimeException('Task is already locked: ' . $taskName);
            }

            fwrite($handle, getmypid() . '|' . gmdate('c'));

            return $callback();
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    /**
     * Handles the lock path workflow.
     */
    private function lockPath(string $taskName): string
    {
        $safe = preg_replace('/[^A-Za-z0-9_.-]+/', '-', strtolower($taskName)) ?? 'task';

        return ProjectPath::storage('locks', 'scheduler', $safe . '.lock');
    }
}
