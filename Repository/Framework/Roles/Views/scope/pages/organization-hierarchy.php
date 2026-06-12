<?php

declare(strict_types=1);

use Catalyst\Framework\View\TrustedHtml;
use Catalyst\Helpers\Security\CsrfProtection;

return static function (array $scope): array {
    $organizations = array_values((array)($scope['organizations'] ?? []));
    $scopes = array_values((array)($scope['scopes'] ?? []));

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
            'eyebrow' => __('roles.organization_admin.eyebrow'),
            'title' => (string)($scope['title'] ?? __('roles.organization_admin.title')),
            'description' => __('roles.organization_admin.description'),
            'actions' => [
                ['label' => __('roles.common.back_to_roles'), 'href' => '/users/roles', 'class' => 'btn btn-sm btn-outline-secondary', 'icon' => 'fa-solid fa-arrow-left'],
            ],
        ],
        'organizationRows' => $organizations,
        'unitRows' => array_values((array)($scope['units'] ?? [])),
        'scopeRows' => $scopes,
        'levelRows' => array_values((array)($scope['levels'] ?? [])),
        'organizationOptions' => $organizationOptions,
        'scopeOptions' => $scopeOptions,
        'organizationCountLabel' => sprintf((string)__('roles.organization_admin.organization_count'), count($organizations)),
        'csrfField' => TrustedHtml::fromString(CsrfProtection::getInstance()->getTokenField()),
    ];
};
