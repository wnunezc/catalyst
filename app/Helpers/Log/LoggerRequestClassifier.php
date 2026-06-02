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
 * Defines the Logger Request Classifier class contract.
 *
 * @package Catalyst\Helpers\Log
 * Responsibility: Coordinates the logger request classifier behavior within its module boundary.
 */
final class LoggerRequestClassifier
{
    /**
     * Handles the classify workflow.
     */
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
