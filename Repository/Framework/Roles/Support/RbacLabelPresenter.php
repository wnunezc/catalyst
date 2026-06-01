<?php

declare(strict_types=1);

namespace Catalyst\Repository\Roles\Support;

final class RbacLabelPresenter
{
    public static function roleName(string $name, ?string $slug = null): string
    {
        return match (self::normalizeKey($slug ?: $name)) {
            'admin', 'administrator' => __('roles.common.role_labels.administrator'),
            'user' => __('roles.common.role_labels.user'),
            'guest' => __('roles.common.role_labels.guest'),
            default => $name,
        };
    }

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

    private static function normalizeKey(string $value): string
    {
        $normalized = strtolower(trim($value));
        $normalized = str_replace(['_', ' '], '-', $normalized);

        return preg_replace('/-+/', '-', $normalized) ?: $normalized;
    }
}
