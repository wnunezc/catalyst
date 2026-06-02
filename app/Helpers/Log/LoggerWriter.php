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

use Catalyst\Helpers\Exceptions\FileSystemException;
use Exception;

/**
 * Writes formatted log entries to configured destinations.
 *
 * @package Catalyst\Helpers\Log
 * Responsibility: Resolves log paths, creates directories, rotates files and appends entries.
 */
final class LoggerWriter
{
    /**
     * Initializes the Logger Writer instance.
     *
     * Responsibility: Initializes the Logger Writer instance.
     */
    public function __construct(
        private readonly LogRotator $rotator = new LogRotator()
    ) {
    }

    /**
     * Writes a categorized application log entry.
     *
     * Responsibility: Writes a categorized application log entry.
     * @throws Exception
     */
    public function write(LoggerSettings $settings, string $level, string $logEntry): void
    {
        $this->appendToFile(
            $this->resolveLogFile($settings, $level),
            $logEntry,
            $settings
        );
    }

    /**
     * Writes an entry to the daily email log.
     *
     * Responsibility: Writes an entry to the daily email log.
     * @throws Exception
     */
    public function writeEmail(LoggerSettings $settings, string $logEntry): void
    {
        $emailDirectory = $settings->logDirectory . DS . 'email';
        $this->ensureDirectory($emailDirectory);

        $this->appendToFile(
            $emailDirectory . DS . date('Y-m-d') . '.log',
            $logEntry,
            $settings
        );
    }

    /**
     * Resolves the destination path for a log level and channel.
     *
     * Responsibility: Resolves the destination path for a log level and channel.
     */
    private function resolveLogFile(LoggerSettings $settings, string $level): string
    {
        if ($settings->logChannel === 'stderr') {
            return 'php://stderr';
        }

        $categoryDirectory = $settings->logDirectory . DS . LoggerLevelMap::categoryFor($level);
        $this->ensureDirectory($categoryDirectory);

        $filename = $settings->logChannel === 'daily'
            ? date('Y-m-d') . '.log'
            : LoggerLevelMap::categoryFor($level) . '.log';

        return $categoryDirectory . DS . $filename;
    }

    /**
     * Creates a log directory when missing.
     *
     * Responsibility: Creates a log directory when missing.
     */
    private function ensureDirectory(string $directory): void
    {
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }
    }

    /**
     * Rotates and appends one entry to a log destination.
     *
     * Responsibility: Rotates and appends one entry to a log destination.
     * @throws Exception
     */
    private function appendToFile(string $logFile, string $logEntry, LoggerSettings $settings): void
    {
        try {
            $this->rotator->rotateIfNeeded($logFile, $settings);

            $result = file_put_contents(
                $logFile,
                $logEntry . PHP_EOL,
                FILE_APPEND | LOCK_EX
            );

            if ($result === false) {
                throw FileSystemException::unableToWriteFile($logFile);
            }
        } catch (Exception $e) {
            error_log("Failed to write to log file '$logFile': " . $e->getMessage());

            if (IS_DEVELOPMENT) {
                throw $e;
            }
        }
    }
}
