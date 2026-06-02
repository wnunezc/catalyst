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

namespace Catalyst\Repository\Roles\Support;

/**
 * Translates known RBAC labels while preserving custom catalog values.
 *
 * @package Catalyst\Repository\Roles\Support
 * Responsibility: Presents role and permission names, descriptions and lists with stable normalized lookup keys.
 */
final class RbacLabelPresenter
{
    /**
     * Returns a translated label for a known role or the supplied name.
     */
    public static function roleName(string $name, ?string $slug = null): string
    {
        return match (self::normalizeKey($slug ?: $name)) {
            'admin', 'administrator' => __('roles.common.role_labels.administrator'),
            'user' => __('roles.common.role_labels.user'),
            'guest' => __('roles.common.role_labels.guest'),
            default => $name,
        };
    }

    /**
     * Returns a translated label for a known permission or the supplied name.
     */
    public static function permissionName(string $name, ?string $slug = null): string
    {
        return match (self::normalizeKey($slug ?: $name)) {
            'manage-users' => __('roles.module.manage_users_label'),
            'manage-roles' => __('roles.module.manage_roles_label'),
            'view-dashboard' => __('roles.common.permission_labels.view_dashboard'),
            'access-devtools' => __('roles.common.permission_labels.access_devtools'),
            default => $name,
        };
    }

    /**
     * Returns a translated description for a known permission or the supplied description.
     */
    public static function permissionDescription(?string $description, ?string $slug = null): ?string
    {
        if ($description === null || trim($description) === '') {
            return $description;
        }

        return match (self::normalizeKey((string) $slug)) {
            'manage-users' => __('roles.common.permission_descriptions.manage_users'),
            'manage-roles' => __('roles.common.permission_descriptions.manage_roles'),
            'view-dashboard' => __('roles.common.permission_descriptions.view_dashboard'),
            'access-devtools' => __('roles.common.permission_descriptions.access_devtools'),
            default => $description,
        };
    }

    /**
     * Presents a comma-separated role list using translated labels when available.
     */
    public static function roleList(string $csv): string
    {
        $items = array_filter(array_map(
            static fn (string $value): string => trim($value),
            explode(',', $csv)
        ));

        if ($items === []) {
            return $csv;
        }

        return implode(', ', array_map(
            static fn (string $value): string => self::roleName($value),
            $items
        ));
    }

    /**
     * Normalizes a role or permission lookup key.
     */
    private static function normalizeKey(string $value): string
    {
        $normalized = strtolower(trim($value));
        $normalized = str_replace(['_', ' '], '-', $normalized);

        return preg_replace('/-+/', '-', $normalized) ?: $normalized;
    }
}
