<?php

declare(strict_types=1);

namespace Catalyst\Helpers\Log;

final class LoggerEntryFormatter
{
    public function format(string $level, string $message, array $context, string $requestId): string
    {
        if (!IS_CLI && !isset($context['request_metadata'])) {
            $context['request_metadata'] = [
                'uri' => $_SERVER['REQUEST_URI'] ?? 'unknown',
                'method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
                'referer' => $_SERVER['HTTP_REFERER'] ?? 'direct',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                'request_id' => $requestId,
            ];
        }

        return $this->buildEntry($level, $message, $context);
    }

    public function formatEmail(string $message, array $context): string
    {
        return $this->buildEntry('EMAIL', $message, $context);
    }

    private function buildEntry(string $level, string $message, array $context): string
    {
        $logEntry = sprintf(
            '[%s] [%s] [%s] [User:%s] %s',
            date('Y-m-d H:i:s'),
            $level,
            $this->getClientIp(),
            $this->getCurrentUserId(),
            $message
        );

        if (!empty($context)) {
            $logEntry .= ' ' . json_encode($context);
        }

        return $logEntry;
    }

    private function getCurrentUserId(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return 'guest';
        }

        return (string) ($_SESSION['user_id'] ?? 'guest');
    }

    private function getClientIp(): string
    {
        if (IS_CLI) {
            return 'CLI';
        }

        return (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    }
}
