<?php

declare(strict_types=1);

namespace Catalyst\Helpers\Log;

final class LoggerSettings
{
    public function __construct(
        public string $logDirectory,
        public int $minimumLogLevel,
        public string $logChannel = 'single',
        public bool $displayLogs = false,
        public bool $logAssetErrors = false,
        public bool $logRotationEnabled = true,
        public int $maxFileSizeBytes = 10485760,
        public int $maxRotatedFiles = 7,
    ) {
    }
}