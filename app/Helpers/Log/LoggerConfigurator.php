<?php

declare(strict_types=1);

namespace Catalyst\Helpers\Log;

final class LoggerConfigurator
{
    public function ensureLogDirectory(string $directory): void
    {
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }
    }

    public function applyRuntimeOptions(LoggerSettings $settings, array $config): void
    {
        if (isset($config['logAssetErrors'])) {
            $settings->logAssetErrors = (bool) $config['logAssetErrors'];
        }
    }

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