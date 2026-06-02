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

namespace Catalyst\Helpers\Log;

/**
 * Rotates filesystem logs when they exceed configured size limits.
 *
 * @package Catalyst\Helpers\Log
 * Responsibility: Preserves bounded log history while excluding stream destinations.
 */
final class LogRotator
{
    /**
     * Rotates a log file when rotation is enabled and its size limit is reached.
     *
     * Responsibility: Rotates a log file when rotation is enabled and its size limit is reached.
     */
    public function rotateIfNeeded(string $logFile, LoggerSettings $settings): void
    {
        if (!$settings->logRotationEnabled) {
            return;
        }

        if ($settings->maxFileSizeBytes <= 0 || $settings->maxRotatedFiles <= 0) {
            return;
        }

        if ($this->isStream($logFile)) {
            return;
        }

        if (!is_file($logFile)) {
            return;
        }

        $currentSize = filesize($logFile);

        if ($currentSize === false || $currentSize < $settings->maxFileSizeBytes) {
            return;
        }

        $this->rotate($logFile, $settings->maxRotatedFiles);
    }

    /**
     * Shifts rotated files and reopens the active log path.
     *
     * Responsibility: Shifts rotated files and reopens the active log path.
     */
    private function rotate(string $logFile, int $maxFiles): void
    {
        $oldestFile = $logFile . '.' . $maxFiles;

        if (is_file($oldestFile)) {
            unlink($oldestFile);
        }

        for ($index = $maxFiles - 1; $index >= 1; $index--) {
            $source = $logFile . '.' . $index;
            $target = $logFile . '.' . ($index + 1);

            if (is_file($source)) {
                rename($source, $target);
            }
        }

        rename($logFile, $logFile . '.1');
        touch($logFile);
    }

    /**
     * Determines whether a path targets a PHP stream.
     *
     * Responsibility: Determines whether a path targets a PHP stream.
     */
    private function isStream(string $path): bool
    {
        return str_contains($path, '://');
    }
}
