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

return static function (array $scope): array {
    $ticket = trim((string) ($scope['error_ticket'] ?? ''));

    return [
        'error_status' => (string) ($scope['error_status'] ?? '500'),
        'error_title' => (string) ($scope['error_title'] ?? __('ui.errors.500_title')),
        'error_message' => (string) ($scope['error_message'] ?? __('ui.errors.500_message')),
        'error_ticket' => $ticket,
        'has_error_ticket' => $ticket !== '',
        'request_path' => (string) ($scope['request_path'] ?? '/'),
        'show_login_action' => !empty($scope['show_login_action']),
    ];
};
