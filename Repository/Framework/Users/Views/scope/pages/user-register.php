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
use Catalyst\Repository\Users\Support\RbacLabelPresenter;

return static function (array $scope): array {
    $roles = array_values((array) ($scope['roles'] ?? []));
    $selectedRole = (string) old('role', 'user');
    $selectedEmailVerified = (string) old('email_verified', '1');
    $roleOptions = [];

    if ($roles === []) {
        $roleOptions[] = [
            'value' => 'user',
            'label' => __('roles.users.form.default_role_label'),
            'selected' => true,
        ];
    } else {
        foreach ($roles as $role) {
            $role = is_array($role) ? $role : [];
            $slug = (string) ($role['slug'] ?? '');
            $roleOptions[] = [
                'value' => $slug,
                'label' => RbacLabelPresenter::roleName((string) ($role['name'] ?? ''), $slug) . ' — ' . $slug,
                'selected' => $slug === $selectedRole,
            ];
        }
    }

    return [
        'page_header' => [
            'eyebrow' => __('roles.users.eyebrow'),
            'title' => (string) ($scope['title'] ?? __('roles.users.register_title')),
            'description' => __('roles.users.register_description'),
            'actions' => [
                ['label' => __('roles.users.back_to_users'), 'href' => '/users', 'class' => 'btn btn-sm btn-outline-secondary', 'icon' => 'fa-solid fa-arrow-left'],
            ],
        ],

        'csrfField' => TrustedHtml::fromString(CsrfProtection::getInstance()->getTokenField()),
        'errors' => [
            'name' => (string) (validation_error('name') ?? ''),
            'email' => (string) (validation_error('email') ?? ''),
            'password' => (string) (validation_error('password') ?? ''),
            'password_confirm' => (string) (validation_error('password_confirm') ?? ''),
            'role' => (string) (validation_error('role') ?? ''),
            'email_verified' => (string) (validation_error('email_verified') ?? ''),
        ],
        'old' => [
            'name' => (string) old('name', ''),
            'email' => (string) old('email', ''),
        ],
        'role_options' => $roleOptions,
        'email_verified_options' => [
            [
                'value' => '1',
                'label' => __('roles.users.form.options.email_verified_yes'),
                'selected' => $selectedEmailVerified === '1',
            ],
            [
                'value' => '0',
                'label' => __('roles.users.form.options.email_verified_no'),
                'selected' => $selectedEmailVerified === '0',
            ],
        ],
    ];
};
