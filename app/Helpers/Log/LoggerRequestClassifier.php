<?php

declare(strict_types=1);

namespace Catalyst\Helpers\Log;

final class LoggerRequestClassifier
{
    public function classify(): string
    {
        if (IS_CLI) {
            return 'cli';
        }

        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        if (str_starts_with($uri, '/api/') || str_contains($accept, 'application/json')) {
            return 'api';
        }

        if (preg_match('/\.(js|css|jpg|jpeg|png|gif|svg|woff|woff2|ttf|ico)$/i', $uri)) {
            return 'asset';
        }

        if (preg_match('/(bot|crawler|spider|slurp|yahoo|bingbot|googlebot)/i', $userAgent)) {
            return 'bot';
        }

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
        ) {
            return 'ajax';
        }

        return 'page';
    }
}
