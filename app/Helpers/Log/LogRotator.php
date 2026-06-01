<?php

declare(strict_types=1);

namespace Catalyst\Helpers\Log;

final class LogRotator
{
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

    private function isStream(string $path): bool
    {
        return str_contains($path, '://');
    }
}