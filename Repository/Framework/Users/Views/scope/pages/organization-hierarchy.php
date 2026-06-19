<?php

declare(strict_types=1);

use Catalyst\Framework\View\TrustedHtml;
use Catalyst\Helpers\Security\CsrfProtection;

return static function (array $scope): array {
    $organizations = array_values((array)($scope['organizations'] ?? []));
    $scopes = array_values((array)($scope['scopes'] ?? []));
    $csrfField = TrustedHtml::fromString(CsrfProtection::getInstance()->getTokenField());
    $decorate = static function (array $rows, string $type, string $basePath) use ($csrfField): array {
        return array_map(static function (array $row) use ($type, $basePath, $csrfField): array {
            $dependencies = array_filter((array)($row['dependencies'] ?? []), static fn (int $count): bool => $count > 0);
            $row['record_type'] = $type;
            $row['delete_action'] = $basePath . '/' . (int)($row['id'] ?? 0) . '/delete';
            $row['csrfField'] = $csrfField;
            $row['is_locked'] = $dependencies !== [];
            $row['lock_reason'] = $dependencies === []
                ? ''
                : sprintf((string)__('roles.organization_hierarchy.messages.delete_blocked'), implode(', ', array_keys($dependencies)));

            return $row;
        }, $rows);
    };

    $organizations = $decorate($organizations, 'organization', '/users/organization-hierarchy/organizations');
    $units = $decorate(array_values((array)($scope['units'] ?? [])), 'unit', '/users/organization-hierarchy/units');
    $scopes = $decorate($scopes, 'scope', '/users/organization-hierarchy/scopes');
    $levels = $decorate(array_values((array)($scope['levels'] ?? [])), 'level', '/users/organization-hierarchy/levels');

    $organizationOptions = array_map(
        static fn (array $row): array => [
            'value' => (string)($row['id'] ?? ''),
            'label' => (string)($row['name'] ?? $row['slug'] ?? ''),
        ],
        $organizations
    );

    $scopeOptions = array_map(
        static fn (array $row): array => [
            'value' => (string)($row['id'] ?? ''),
            'label' => (string)($row['label'] ?? $row['scope_key'] ?? ''),
        ],
        $scopes
    );

    return [
        'page_header' => [
            'eyebrow' => __('roles.organization_hierarchy.eyebrow'),
            'title' => (string)($scope['title'] ?? __('roles.organization_hierarchy.title')),
            'description' => __('roles.organization_hierarchy.description'),
            'actions' => [
                ['label' => __('roles.common.back_to_roles'), 'href' => '/users/roles', 'class' => 'btn btn-sm btn-outline-secondary', 'icon' => 'fa-solid fa-arrow-left'],
            ],
        ],
        'organizationRows' => $organizations,
        'unitRows' => $units,
        'scopeRows' => $scopes,
        'levelRows' => $levels,
        'organizationOptions' => $organizationOptions,
        'scopeOptions' => $scopeOptions,
        'organizationCountLabel' => sprintf((string)__('roles.organization_hierarchy.organization_count'), count($organizations)),
        'csrfField' => $csrfField,
    ];
};
