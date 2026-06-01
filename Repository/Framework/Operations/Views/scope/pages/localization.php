<?php

declare(strict_types=1);

use Catalyst\Framework\View\InlineJson;
use Catalyst\Framework\View\TrustedHtml;
use Catalyst\Helpers\Security\CsrfProtection;

return static function (array $scope): array {
    $localeLabel = static function (string $locale, array $localeLabels): string {
        $translated = __('ui.languages.' . $locale);

        if ($translated !== 'ui.languages.' . $locale) {
            return $translated;
        }

        return $localeLabels[$locale] ?? strtoupper($locale);
    };

    $settings = is_array($scope['settings'] ?? null) ? $scope['settings'] : [];
    $availableLocales = array_values((array) ($scope['availableLocales'] ?? []));
    $localeLabels = is_array($scope['localeLabels'] ?? null) ? $scope['localeLabels'] : [];
    $selectedLocale = (string) ($scope['selectedLocale'] ?? 'en');
    $selectedReport = is_array($scope['selectedReport'] ?? null) ? $scope['selectedReport'] : [];
    $localeReports = array_values((array) ($scope['localeReports'] ?? []));

    $localeCards = [];
    foreach ($localeReports as $report) {
        $report = is_array($report) ? $report : [];
        $summary = is_array($report['summary'] ?? null) ? $report['summary'] : [];
        $locale = (string) ($report['locale'] ?? 'en');
        $localeCards[] = [
            'href' => '/workspaces/locale-tools?locale=' . rawurlencode($locale),
            'locale' => $locale,
            'label' => $localeLabel($locale, $localeLabels),
            'coverage_percent' => number_format((float) ($summary['coverage_percent'] ?? 0), 2),
            'missing_summary' => sprintf(
                __('operations.localization.cards.missing_summary'),
                (int) ($summary['missing_keys'] ?? 0),
                (int) ($summary['missing_catalogs'] ?? 0)
            ),
            'is_active' => $locale === $selectedLocale,
        ];
    }

    $defaultLocale = (string) ($settings['default_locale'] ?? 'en');
    $defaultLocaleOptions = [];
    foreach ($availableLocales as $locale) {
        $defaultLocaleOptions[] = [
            'value' => $locale,
            'label' => $localeLabel($locale, $localeLabels) . ' (' . $locale . ')',
            'selected' => $locale === $defaultLocale,
        ];
    }

    $syncLocaleOptions = [];
    foreach ($availableLocales as $locale) {
        if ($locale === 'en') {
            continue;
        }

        $syncLocaleOptions[] = [
            'value' => $locale,
            'label' => $localeLabel($locale, $localeLabels) . ' (' . $locale . ')',
            'selected' => $locale === $selectedLocale,
        ];
    }

    $selectedSummary = is_array($selectedReport['summary'] ?? null) ? $selectedReport['summary'] : [];
    $selectedCatalogs = [];
    foreach ((array) ($selectedReport['catalogs'] ?? []) as $catalog) {
        $catalog = is_array($catalog) ? $catalog : [];
        $baseCount = (int) ($catalog['base_key_count'] ?? 0);
        $translatedCount = (int) ($catalog['translated_key_count'] ?? 0);
        $coverage = $baseCount > 0 ? round(($translatedCount / $baseCount) * 100, 2) : 100.0;
        $missingKeys = array_values((array) ($catalog['missing_keys'] ?? []));
        $missingPreview = implode(', ', array_slice($missingKeys, 0, 5));
        if (count($missingKeys) > 5) {
            $missingPreview .= '…';
        }

        $selectedCatalogs[] = [
            'catalog_name' => (string) ($catalog['catalog'] ?? 'catalog'),
            'present_label' => !empty($catalog['catalog_exists'])
                ? __('operations.localization.coverage_report.present')
                : __('operations.localization.coverage_report.missing_in_target'),
            'label' => (string) ($catalog['label'] ?? ''),
            'coverage_percent' => number_format($coverage, 2),
            'missing_count' => count($missingKeys),
            'has_missing_preview' => $missingPreview !== '',
            'missing_preview' => $missingPreview,
            'extra_count' => count((array) ($catalog['extra_keys'] ?? [])),
        ];
    }

    return [
        'admin_header' => [
            'eyebrow' => __('operations.localization.hero_eyebrow'),
            'title' => (string) ($scope['pageTitle'] ?? __('operations.localization.page_title')),
            'description' => __('operations.localization.hero_lede'),
        ],

        'csrfField' => TrustedHtml::fromString(CsrfProtection::getInstance()->getTokenField()),
        'locale_cards' => $localeCards,
        'default_locale_options' => $defaultLocaleOptions,
        'sync_locale_options' => $syncLocaleOptions,
        'locale_labels_json' => InlineJson::encode($localeLabels, InlineJson::DEFAULT_OPTIONS | JSON_PRETTY_PRINT),
        'coverage_report_title' => sprintf(
            __('operations.localization.coverage_report.title'),
            $localeLabel($selectedLocale, $localeLabels)
        ),
        'selected_report_base_locale' => (string) ($selectedReport['base_locale'] ?? 'en'),
        'selected_summary' => [
            'coverage_percent' => number_format((float) ($selectedSummary['coverage_percent'] ?? 0), 2),
            'missing_keys_badge' => sprintf(
                __('operations.localization.coverage_report.missing_keys_badge'),
                (int) ($selectedSummary['missing_keys'] ?? 0)
            ),
            'missing_catalogs_badge' => sprintf(
                __('operations.localization.coverage_report.missing_catalogs_badge'),
                (int) ($selectedSummary['missing_catalogs'] ?? 0)
            ),
        ],
        'selected_catalogs' => $selectedCatalogs,
    ];
};
