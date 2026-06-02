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

    $errorArray = is_array($scope['errorArray'] ?? null) ? $scope['errorArray'] : [];

    return [
        'locale' => (string) ($_SESSION['app_locale'] ?? 'en'),
        'error_class' => (string) ($errorArray['class'] ?? ''),
        'control_title' => $translate('ui.errors.control_title', 'Error Control Software'),
        'description_label' => $translate('ui.errors.description', 'Description:'),
        'handler_message_prefix' => $translate('ui.errors.handler_no.message_prefix', 'Errors have been detected, error code:'),
        'error_code' => (string) ($errorArray['micro_time'] ?? ''),
        'return_hint' => $translate('ui.errors.return_hint', 'Please try to Go Back'),
        'go_back_label' => $translate('ui.errors.go_back', 'Go Back'),
        'style_nonce_attr' => TrustedHtml::fromString($nonceAttr),
        'script_nonce_attr' => TrustedHtml::fromString($nonceAttr),
    ];
};
