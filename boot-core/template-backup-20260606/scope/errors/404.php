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

use Catalyst\Framework\View\TrustedHtml;

return static function (array $scope): array {
    $translate = static function (string $key, ?string $default = null): string {
        if (function_exists('__')) {
            return (string) __($key);
        }

        return $default ?? $key;
    };

    $nonceAttr = '';
    if (class_exists(\Catalyst\Helpers\Security\CspNonce::class)) {
        $nonce = \Catalyst\Helpers\Security\CspNonce::get();
        if ($nonce !== '') {
            $nonceAttr = ' nonce="' . htmlspecialchars($nonce, ENT_QUOTES, 'UTF-8') . '"';
        }
    }

    return [
        'locale' => (string) ($_SESSION['app_locale'] ?? 'en'),
        'title' => $translate('ui.errors.404.title', 'Page Not Found'),
        'message' => (string) ($scope['message'] ?? $translate('ui.errors.404.message', 'Page Not Found')),
        'requested_url_label' => $translate('ui.errors.404.requested_url', 'The requested URL was:'),
        'uri' => (string) ($scope['uri'] ?? $_SERVER['REQUEST_URI'] ?? $translate('ui.common.unknown', 'Unknown')),
        'go_back_label' => $translate('ui.errors.go_back', 'Go Back'),
        'go_home_label' => $translate('ui.errors.go_home', 'Go to Homepage'),
        'suggestions_label' => $translate('ui.errors.404.suggestions', 'Suggestions:'),
        'suggestion_url' => $translate('ui.errors.404.suggestion_url', 'Check that the URL is correct'),
        'suggestion_moved' => $translate('ui.errors.404.suggestion_moved', 'The page might have been moved or deleted'),
        'suggestion_permission' => $translate('ui.errors.404.suggestion_permission', 'You might not have permission to view this page'),
        'style_nonce_attr' => TrustedHtml::fromString($nonceAttr),
        'script_nonce_attr' => TrustedHtml::fromString($nonceAttr),
    ];
};
