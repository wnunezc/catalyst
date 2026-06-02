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

namespace Catalyst\Framework\Http;

use Catalyst\Framework\View\View;
use Throwable;

/**
 * Creates framework error responses with HTML fallback rendering.
 *
 * @package Catalyst\Framework\Http
 * Responsibility: Renders localized error surfaces and falls back to escaped inline HTML when the view layer is unavailable.
 */
final class ErrorResponseFactory
{
    /**
     * Renders an error response for the provided status and message.
     *
     * @param array<string, string|array<string>> $headers
     */
    public static function render(int $status, string $title, string $message, array $headers = [], ?string $ticket = null): Response
    {
        $path = (string) ($_SERVER['REQUEST_URI'] ?? '/');
        $requestPath = (string) (parse_url($path, PHP_URL_PATH) ?: '/');

        try {
            $response = View::getInstance()->render('error.surface', [
                'title' => $title,
                'error_status' => $status,
                'error_title' => $title,
                'error_message' => $message,
                'error_ticket' => $ticket ?? '',
                'request_path' => $requestPath,
                'show_login_action' => $status === 401,
            ], $status, 'error');

            foreach ($headers as $name => $value) {
                $response->setHeader((string) $name, $value);
            }

            return $response;
        } catch (Throwable) {
            return new Response(self::fallbackHtml($status, $title, $message, $ticket), $status, $headers);
        }
    }

    /**
     * Renders a forbidden response.
     *
     * @param array<string, string|array<string>> $headers
     */
    public static function forbidden(string $message = '', array $headers = []): Response
    {
        return self::render(
            403,
            __('ui.errors.403_title'),
            $message !== '' ? $message : __('ui.errors.403_message'),
            $headers
        );
    }

    /**
     * Renders an unauthorized response.
     *
     * @param array<string, string|array<string>> $headers
     */
    public static function unauthorized(string $message = '', array $headers = []): Response
    {
        return self::render(
            401,
            __('ui.errors.401_title'),
            $message !== '' ? $message : __('ui.errors.401_message'),
            $headers
        );
    }

    /**
     * Renders a throttling response with an optional retry header.
     *
     * @param array<string, string|array<string>> $headers
     */
    public static function tooManyRequests(string $message = '', int $retryAfter = 0, array $headers = []): Response
    {
        if ($retryAfter > 0) {
            $headers['Retry-After'] = (string) $retryAfter;
        }

        return self::render(
            429,
            __('ui.errors.429_title'),
            $message !== '' ? $message : __('ui.errors.429_message'),
            $headers
        );
    }

    /**
     * Builds escaped fallback HTML for error responses when views cannot render.
     */
    private static function fallbackHtml(int $status, string $title, string $message, ?string $ticket): string
    {
        $safeStatus = htmlspecialchars((string) $status, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeTitle = htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeMessage = htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeTicket = htmlspecialchars((string) ($ticket ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $ticketHtml = $safeTicket !== '' ? '<p>Error ticket: ' . $safeTicket . '</p>' : '';

        return '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>' . $safeTitle . '</title><link href="/assets/css/catalyst/error-surface.css" rel="stylesheet"></head><body class="catalyst-error-shell-body"><main class="catalyst-error-shell"><section class="catalyst-error-card"><span class="catalyst-error-card__code">' . $safeStatus . '</span><h1>' . $safeTitle . '</h1><p>' . $safeMessage . '</p>' . $ticketHtml . '<a href="/">Home</a></section></main></body></html>';
    }
}
