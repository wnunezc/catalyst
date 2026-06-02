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
use Catalyst\Helpers\Security\CsrfProtection;

return static function (array $scope): array {
    $passwordError = validation_error('password');
    $codeError = validation_error('code');
    $backupCodes = $_SESSION['_mfa_backup_codes_display'] ?? [];
    if ($backupCodes !== []) {
        unset($_SESSION['_mfa_backup_codes_display']);
    }
    $continueRedirect = (string) ($_SESSION['_mfa_setup_continue_redirect'] ?? '');
    if ($continueRedirect !== '') {
        unset($_SESSION['_mfa_setup_continue_redirect']);
    }

    return [
        'title' => (string) ($scope['title'] ?? __('auth.mfa.setup_title')),
        'csrf_token_field' => TrustedHtml::fromString(CsrfProtection::getInstance()->getTokenField()),
        'qr_uri' => (string) ($scope['qrUri'] ?? $scope['qr_uri'] ?? ''),
        'secret' => (string) ($scope['secret'] ?? ''),
        'mfa_active' => (bool) ($scope['mfaActive'] ?? $scope['mfa_active'] ?? false),
        'forced_setup' => (bool) ($scope['forcedSetup'] ?? $scope['forced_setup'] ?? false),
        'password_error' => (string) ($passwordError ?? ''),
        'password_invalid_class' => $passwordError !== null ? ' is-invalid' : '',
        'code_error' => (string) ($codeError ?? ''),
        'code_invalid_class' => $codeError !== null ? ' is-invalid' : '',
        'backup_codes' => is_array($backupCodes) ? array_values($backupCodes) : [],
        'has_backup_codes' => is_array($backupCodes) && $backupCodes !== [],
        'continue_redirect' => $continueRedirect,
    ];
};
