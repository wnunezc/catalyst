<?php

declare(strict_types=1);

use Catalyst\Framework\View\TrustedHtml;
use Catalyst\Helpers\Security\CsrfProtection;

return static function (array $scope): array {
    $csrf = TrustedHtml::fromString(CsrfProtection::getInstance()->getTokenField());
    $templates = [];
    foreach ((array) ($scope['templates'] ?? []) as $template) {
        $template = (array) $template;
        $key = (string) ($template['key'] ?? '');
        $templates[] = [
            'key' => $key,
            'name' => (string) ($template['name'] ?? $key),
            'domain' => (string) ($template['domain'] ?? ''),
            'origin' => (string) ($template['origin'] ?? ''),
            'has_override' => !empty($template['has_override']),
            'locale_count' => count((array) ($template['locales'] ?? [])),
            'href' => '/workspaces/mail-templates/' . rawurlencode($key),
        ];
    }

    $assets = [];
    foreach ((array) ($scope['assets'] ?? []) as $asset) {
        $asset = (array) $asset;
        $name = (string) ($asset['name'] ?? '');
        $assets[] = [
            'name' => $name,
            'origin' => (string) ($asset['origin'] ?? ''),
            'size' => number_format(((int) ($asset['size'] ?? 0)) / 1024, 1) . ' KB',
            'url' => (string) ($asset['url'] ?? ''),
            'managed' => ($asset['origin'] ?? '') === 'managed',
            'delete_action' => '/workspaces/mail-templates/assets/' . rawurlencode($name) . '/delete',
            'csrfField' => $csrf,
        ];
    }

    return [
        'page_header' => [
            'eyebrow' => __('workspaces.mail_templates.eyebrow'),
            'title' => (string) ($scope['title'] ?? __('workspaces.mail_templates.title')),
            'description' => __('workspaces.mail_templates.description'),
            'actions' => [[
                'label' => __('workspaces.mail_templates.actions.create'),
                'href' => '/workspaces/mail-templates/create',
                'class' => 'btn btn-sm btn-primary',
                'icon' => 'fa-solid fa-plus',
            ]],
        ],
        'csrfField' => $csrf,
        'templates' => $templates,
        'assets' => $assets,
        'has_templates' => $templates !== [],
        'has_assets' => $assets !== [],
        'origin_filter' => (string) ($scope['originFilter'] ?? ''),
        'origin_options' => [
            [
                'value' => '',
                'label' => __('workspaces.mail_templates.filters.all'),
                'selected' => (string) ($scope['originFilter'] ?? '') === '',
            ],
            [
                'value' => 'system',
                'label' => 'system',
                'selected' => (string) ($scope['originFilter'] ?? '') === 'system',
            ],
            [
                'value' => 'managed',
                'label' => 'managed',
                'selected' => (string) ($scope['originFilter'] ?? '') === 'managed',
            ],
        ],
        'domain_filter' => (string) ($scope['domainFilter'] ?? ''),
    ];
};
