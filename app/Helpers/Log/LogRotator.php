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
 * Defines the Log Rotator class contract.
 *
 * @package Catalyst\Helpers\Log
 * Responsibility: Coordinates the log rotator behavior within its module boundary.
 */
final class LogRotator
{
    /**
     * Handles the rotate if needed workflow.
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
     * Handles the rotate workflow.
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
     * Determines whether is Stream.
     */
    private function isStream(string $path): bool
    {
        return str_contains($path, '://');
    }
}