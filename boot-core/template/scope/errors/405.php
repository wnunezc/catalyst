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
        'title' => $translate('ui.errors.405.title', 'Method Not Allowed'),
        'message' => (string) ($scope['message'] ?? $translate('ui.errors.405.message', 'Method Not Allowed')),
        'requested_url_label' => $translate('ui.errors.405.requested_url', 'The requested URL was:'),
        'uri' => (string) ($scope['uri'] ?? $translate('ui.errors.405.unknown', 'Unknown')),
        'request_method_label' => $translate('ui.errors.405.request_method', 'Request method:'),
        'request_method' => (string) ($_SERVER['REQUEST_METHOD'] ?? $translate('ui.errors.405.unknown', 'Unknown')),
        'allowed_methods_label' => $translate('ui.errors.405.allowed_methods', 'Allowed methods:'),
        'allowed_methods' => array_values(is_array($scope['allowedMethods'] ?? null) ? $scope['allowedMethods'] : []),
        'unknown_label' => $translate('ui.errors.405.unknown', 'Unknown'),
        'go_back_label' => $translate('ui.errors.go_back', 'Go Back'),
        'go_home_label' => $translate('ui.errors.go_home', 'Go to Homepage'),
        'meaning_title' => $translate('ui.errors.405.meaning_title', 'What does this mean?'),
        'meaning_body' => $translate(
            'ui.errors.405.meaning_body',
            'The request method you used is not supported for this resource. Please use one of the allowed methods listed above.'
        ),
        'style_nonce_attr' => TrustedHtml::fromString($nonceAttr),
        'script_nonce_attr' => TrustedHtml::fromString($nonceAttr),
    ];
};
