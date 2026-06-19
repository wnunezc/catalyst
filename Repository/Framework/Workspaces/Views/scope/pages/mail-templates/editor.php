<?php

declare(strict_types=1);

use Catalyst\Framework\View\InlineJson;
use Catalyst\Framework\View\TrustedHtml;
use Catalyst\Helpers\Security\CsrfProtection;

return static function (array $scope): array {
    $template = (array) ($scope['template'] ?? []);
    $manifest = (array) ($template['manifest'] ?? []);
    $key = (string) ($scope['templateKey'] ?? '');
    $locale = (string) ($scope['selectedLocale'] ?? 'en');
    $csrf = TrustedHtml::fromString(CsrfProtection::getInstance()->getTokenField());
    $localeOptions = [];
    $labels = (array) ($scope['localeLabels'] ?? []);
    foreach ((array) ($scope['availableLocales'] ?? []) as $item) {
        $item = (string) $item;
        $localeOptions[] = [
            'value' => $item,
            'label' => (string) ($labels[$item] ?? strtoupper($item)),
            'selected' => $item === $locale,
            'href' => $key !== ''
                ? '/workspaces/mail-templates/' . rawurlencode($key) . '?locale=' . rawurlencode($item)
                : '/workspaces/mail-templates/create?locale=' . rawurlencode($item),
        ];
    }
    $preview = (array) ($scope['preview'] ?? []);

    return [
        'page_header' => [
            'eyebrow' => __('workspaces.mail_templates.eyebrow'),
            'title' => (string) ($scope['title'] ?? __('workspaces.mail_templates.title')),
            'description' => __('workspaces.mail_templates.editor_description'),
            'actions' => [[
                'label' => __('workspaces.mail_templates.actions.back'),
                'href' => '/workspaces/mail-templates',
                'class' => 'btn btn-sm btn-outline-secondary',
                'icon' => 'fa-solid fa-arrow-left',
            ]],
        ],
        'csrfField' => $csrf,
        'creating' => $key === '',
        'key' => $key !== '' ? $key : (string) ($manifest['key'] ?? ''),
        'name' => (string) ($manifest['name'] ?? ''),
        'origin' => (string) ($template['origin'] ?? 'managed'),
        'translation_catalog' => (string) ($manifest['translation_catalog'] ?? ''),
        'translation_namespace' => (string) ($manifest['translation_namespace'] ?? ''),
        'required_placeholders_json' => InlineJson::encode(
            (array) ($manifest['required_placeholders'] ?? []),
            InlineJson::DEFAULT_OPTIONS | JSON_PRETTY_PRINT
        ),
        'sample_payload_json' => is_string($scope['previewPayloadJson'] ?? null)
            ? (string) $scope['previewPayloadJson']
            : InlineJson::encode(
                (array) ($manifest['sample_payload'] ?? []),
                InlineJson::DEFAULT_OPTIONS | JSON_PRETTY_PRINT
            ),
        'catalog_json' => InlineJson::encode(
            (array) ($scope['catalog'] ?? []),
            InlineJson::DEFAULT_OPTIONS | JSON_PRETTY_PRINT
        ),
        'html' => TrustedHtml::fromString(htmlspecialchars(
            (string) ($template['html'] ?? ''),
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        )),
        'text' => TrustedHtml::fromString(htmlspecialchars(
            (string) ($template['text'] ?? ''),
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        )),
        'locale' => $locale,
        'locale_options' => $localeOptions,
        'form_action' => $key === '' ? '/workspaces/mail-templates' : '/workspaces/mail-templates/' . rawurlencode($key),
        'preview_action' => '/workspaces/mail-templates/' . rawurlencode($key) . '/preview',
        'test_action' => '/workspaces/mail-templates/' . rawurlencode($key) . '/test',
        'restore_action' => '/workspaces/mail-templates/' . rawurlencode($key) . '/restore',
        'delete_action' => '/workspaces/mail-templates/' . rawurlencode($key) . '/delete',
        'has_system' => !empty($template['has_system']),
        'has_override' => !empty($template['has_override']),
        'can_restore' => !empty($template['has_system']) && !empty($template['has_override']),
        'can_delete' => $key !== '' && empty($template['has_system']),
        'has_preview' => $preview !== [],
        'preview_subject' => (string) ($preview['subject'] ?? ''),
        'preview_html' => (string) ($preview['html'] ?? ''),
        'preview_text' => (string) ($preview['text'] ?? ''),
    ];
};
