<?php

declare(strict_types=1);

use Catalyst\Framework\View\InlineJson;
use Catalyst\Framework\View\TrustedHtml;
use Catalyst\Helpers\Security\CsrfProtection;

return static function (array $scope): array {
    $settings = (array) ($scope['settings'] ?? []);
    $labels = (array) ($scope['localeLabels'] ?? []);
    $selected = (string) ($scope['selectedLocale'] ?? 'en');
    $report = (array) ($scope['selectedReport'] ?? []);
    $summary = (array) ($report['summary'] ?? []);
    $options = [];
    $syncOptions = [];
    $cards = [];

    foreach ((array) ($scope['availableLocales'] ?? []) as $locale) {
        $locale = (string) $locale;
        $option = [
            'value' => $locale,
            'label' => (string) ($labels[$locale] ?? strtoupper($locale)),
            'selected' => $locale === (string) ($settings['default_locale'] ?? 'en'),
        ];
        $options[] = $option;
        if ($locale !== 'en') {
            $syncOptions[] = [...$option, 'selected' => $locale === $selected];
        }
    }

    foreach ((array) ($scope['localeReports'] ?? []) as $localeReport) {
        $localeReport = (array) $localeReport;
        $localeSummary = (array) ($localeReport['summary'] ?? []);
        $locale = (string) ($localeReport['locale'] ?? '');
        $cards[] = [
            'href' => '/workspaces/locale-tools?locale=' . rawurlencode($locale),
            'locale' => $locale,
            'label' => (string) ($labels[$locale] ?? strtoupper($locale)),
            'coverage' => number_format((float) ($localeSummary['coverage_percent'] ?? 0), 2),
            'missing' => (int) ($localeSummary['missing_keys'] ?? 0),
            'active' => $locale === $selected,
        ];
    }

    $catalogs = [];
    foreach ((array) ($report['catalogs'] ?? []) as $catalog) {
        $catalog = (array) $catalog;
        $baseCount = (int) ($catalog['base_key_count'] ?? 0);
        $translated = (int) ($catalog['translated_key_count'] ?? 0);
        $catalogs[] = [
            'name' => (string) ($catalog['catalog'] ?? ''),
            'scope' => (string) ($catalog['label'] ?? ''),
            'coverage' => number_format($baseCount > 0 ? ($translated / $baseCount) * 100 : 100, 2),
            'missing' => count((array) ($catalog['missing_keys'] ?? [])),
            'exists' => !empty($catalog['catalog_exists']),
        ];
    }

    $operation = (array) ($scope['operation'] ?? []);
    $operationItems = [];
    foreach ((array) ($operation['items'] ?? []) as $item) {
        $item = (array) $item;
        $operationItems[] = [
            'action' => (string) ($item['action'] ?? (!empty($item['created']) ? 'create' : 'update')),
            'target' => (string) ($item['target'] ?? ''),
        ];
    }

    return [
        'page_header' => [
            'eyebrow' => __('workspaces.localization.eyebrow'),
            'title' => (string) ($scope['title'] ?? __('workspaces.localization.title')),
            'description' => __('workspaces.localization.description'),
        ],
        'csrfField' => TrustedHtml::fromString(CsrfProtection::getInstance()->getTokenField()),
        'locale_cards' => $cards,
        'default_locale_options' => $options,
        'sync_locale_options' => $syncOptions,
        'locale_labels_json' => InlineJson::encode($labels, InlineJson::DEFAULT_OPTIONS | JSON_PRETTY_PRINT),
        'selected_locale' => $selected,
        'coverage' => number_format((float) ($summary['coverage_percent'] ?? 0), 2),
        'missing_keys' => (int) ($summary['missing_keys'] ?? 0),
        'catalogs' => $catalogs,
        'has_operation' => $operation !== [],
        'operation_message' => (string) ($operation['message'] ?? ''),
        'operation_items' => $operationItems,
        'localization_error' => (string) ($scope['localizationError'] ?? ''),
    ];
};
