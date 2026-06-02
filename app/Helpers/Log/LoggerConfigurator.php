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
 * Defines the Logger Configurator class contract.
 *
 * @package Catalyst\Helpers\Log
 * Responsibility: Coordinates the logger configurator behavior within its module boundary.
 */
final class LoggerConfigurator
{
    /**
     * Handles the ensure log directory workflow.
     */
    public function ensureLogDirectory(string $directory): void
    {
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }
    }

    /**
     * Handles the apply runtime options workflow.
     */
    public function applyRuntimeOptions(LoggerSettings $settings, array $config): void
    {
        if (isset($config['logAssetErrors'])) {
            $settings->logAssetErrors = (bool) $config['logAssetErrors'];
        }
    }

    /**
     * Handles the apply initial options workflow.
     */
    public function applyInitialOptions(LoggerSettings $settings, array $config): void
    {
        if (isset($config['logDirectory'])) {
            $settings->logDirectory = (string) $config['logDirectory'];
            $this->ensureLogDirectory($settings->logDirectory);
        }

        if (isset($config['minimumLogLevel'])) {
            $minimumLogLevel = LoggerLevelMap::normalize((string) $config['minimumLogLevel']);
            if ($minimumLogLevel !== null) {
                $settings->minimumLogLevel = LoggerLevelMap::PRIORITIES[$minimumLogLevel];
            }
        }

        if (isset($config['logChannel'])) {
            $logChannel = strtolower((string) $config['logChannel']);
            if (in_array($logChannel, LoggerLevelMap::channels(), true)) {
                $settings->logChannel = $logChannel;
            }
        }

        if (isset($config['displayLogs'])) {
            $settings->displayLogs = (bool) $config['displayLogs'];
        }

        if (isset($config['logRotationEnabled'])) {
            $settings->logRotationEnabled = (bool) $config['logRotationEnabled'];
        }

        if (isset($config['maxFileSizeBytes'])) {
            $maxFileSizeBytes = (int) $config['maxFileSizeBytes'];

            if ($maxFileSizeBytes > 0) {
                $settings->maxFileSizeBytes = min($maxFileSizeBytes, 50 * 1024 * 1024);
            }
        }

        if (isset($config['maxRotatedFiles'])) {
            $maxRotatedFiles = (int) $config['maxRotatedFiles'];

            if ($maxRotatedFiles > 0) {
                $settings->maxRotatedFiles = min($maxRotatedFiles, 10);
            }
        }
    }
}