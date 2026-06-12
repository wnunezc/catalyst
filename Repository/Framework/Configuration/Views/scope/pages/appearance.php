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

use Catalyst\Framework\View\InlineJson;
use Catalyst\Framework\View\TrustedHtml;
use Catalyst\Helpers\Security\CsrfProtection;

return static function (array $scope): array {
    $settings = is_array($scope['settings'] ?? null) ? $scope['settings'] : [];
    $brandingSettings = is_array($settings['branding'] ?? null) ? $settings['branding'] : $settings;
    $runtime = is_array($scope['runtime'] ?? null) ? $scope['runtime'] : [];
    $lockedConfig = is_array($runtime['lockedConfig'] ?? null) ? $runtime['lockedConfig'] : [];
    $customizerEnabled = !array_key_exists('customizerEnabled', $runtime) || (bool) $runtime['customizerEnabled'];

    $themeCatalog = is_array($scope['themeCatalog'] ?? null) ? $scope['themeCatalog'] : [];
    $selectedTheme = (string) ($brandingSettings['theme_family'] ?? 'inspinia');
    if ($themeCatalog !== [] && !array_key_exists($selectedTheme, $themeCatalog)) {
        $selectedTheme = (string) array_key_first($themeCatalog);
        $brandingSettings['theme_family'] = $selectedTheme;
    }

    $themeCards = [];
    foreach ($themeCatalog as $themeKey => $theme) {
        $themeKey = (string) $themeKey;
        $cssKey = preg_replace('/[^a-z0-9_-]+/i', '-', strtolower($themeKey));
        $cssKey = trim((string) $cssKey, '-');

        $themeCards[] = [
            'key' => $themeKey,
            'css_key' => $cssKey !== '' ? $cssKey : 'default',
            'label' => (string) ($theme['label'] ?? $themeKey),
            'description' => (string) ($theme['description'] ?? ''),
            'is_selected' => $selectedTheme === $themeKey,
        ];
    }

    $defaultVariant = (string) ($brandingSettings['default_variant'] ?? 'light');
    $brandingSettings['is_default_variant_light'] = $defaultVariant === 'light';
    $brandingSettings['is_default_variant_dark'] = $defaultVariant === 'dark';
    $brandingSettings['allow_user_variant_override_checked'] = false;
    $brandingSettings['pdf_watermark_enabled_checked'] = !empty($brandingSettings['pdf_watermark_enabled']);
    $brandingSettings['pdf_watermark_font_size'] = (string) ($brandingSettings['pdf_watermark_font_size'] ?? '46');
    $brandingSettings['pdf_watermark_color'] = (string) ($brandingSettings['pdf_watermark_color'] ?? '#CBD5E1');

    $skinOptions = [
        ['value' => 'default', 'label' => __('settings.appearance.options.default'), 'image_url' => '/assets/vendor/inspinia/images/layouts/skin-default.png', 'preview_class' => '', 'is_closed' => false],
        ['value' => 'minimal', 'label' => 'Minimal', 'image_url' => '/assets/vendor/inspinia/images/layouts/skin-minimal.png', 'preview_class' => '', 'is_closed' => false],
        ['value' => 'modern', 'label' => 'Modern', 'image_url' => '/assets/vendor/inspinia/images/layouts/skin-modern.png', 'preview_class' => '', 'is_closed' => false],
        ['value' => 'material', 'label' => 'Material', 'image_url' => '/assets/vendor/inspinia/images/layouts/skin-material.png', 'preview_class' => '', 'is_closed' => false],
        ['value' => 'pixel', 'label' => 'Pixel', 'image_url' => '/assets/vendor/inspinia/images/layouts/skin-pixel.png', 'preview_class' => '', 'is_closed' => false],
        ['value' => 'luxe', 'label' => 'Luxe', 'image_url' => '/assets/vendor/inspinia/images/layouts/skin-luxe.png', 'preview_class' => '', 'is_closed' => false],
        ['value' => 'flat', 'label' => 'Flat', 'image_url' => '/assets/vendor/inspinia/images/layouts/skin-flat.png', 'preview_class' => '', 'is_closed' => false],
        ['value' => 'red-cross', 'label' => 'Red Cross', 'image_url' => '', 'preview_class' => 'catalyst-red-cross-skin-preview', 'is_closed' => true],
        ['value' => 'civil-protection', 'label' => 'Civil Protection', 'image_url' => '', 'preview_class' => 'catalyst-response-skin-preview catalyst-response-skin-preview--civil-protection', 'is_closed' => true],
        ['value' => 'firefighters', 'label' => 'Firefighters', 'image_url' => '', 'preview_class' => 'catalyst-response-skin-preview catalyst-response-skin-preview--firefighters', 'is_closed' => true],
        ['value' => 'grempa', 'label' => 'GREMPA', 'image_url' => '', 'preview_class' => 'catalyst-response-skin-preview catalyst-response-skin-preview--grempa', 'is_closed' => true],
    ];

    foreach ($skinOptions as &$option) {
        $option['is_selected'] = (string) ($lockedConfig['skin'] ?? 'default') === $option['value'];
        $option['has_image'] = (string) $option['image_url'] !== '';
        $option['closed_badge'] = $option['is_closed'] ? __('settings.appearance.options.fixed_preset') : '';
        $option['fixed_label'] = __('settings.appearance.options.fixed');
        $option['preview_aria_label'] = __('settings.appearance.preview.theme_preview', ['theme' => (string) $option['label']]);
        $option['is_red_cross_preview'] = $option['value'] === 'red-cross';
        $option['is_response_preview'] = !$option['has_image'] && $option['value'] !== 'red-cross';
    }
    unset($option);

    $schemeOptions = [
        ['value' => 'light', 'label' => __('settings.appearance.options.light'), 'image_url' => '/assets/vendor/inspinia/images/layouts/theme-light.png'],
        ['value' => 'dark', 'label' => __('settings.appearance.options.dark'), 'image_url' => '/assets/vendor/inspinia/images/layouts/theme-dark.png'],
        ['value' => 'system', 'label' => __('settings.appearance.options.system'), 'image_url' => '/assets/vendor/inspinia/images/layouts/theme-system.png'],
    ];
    foreach ($schemeOptions as &$option) {
        $option['is_selected'] = (string) ($lockedConfig['theme'] ?? 'light') === $option['value'];
        $option['preview_aria_label'] = __('settings.appearance.preview.theme_preview', ['theme' => (string) $option['label']]);
    }
    unset($option);

    $topbarOptions = [
        ['value' => 'light', 'label' => __('settings.appearance.options.light'), 'image_url' => '/assets/vendor/inspinia/images/layouts/topbar-color-light.png'],
        ['value' => 'dark', 'label' => __('settings.appearance.options.dark'), 'image_url' => '/assets/vendor/inspinia/images/layouts/topbar-color-dark.png'],
        ['value' => 'gray', 'label' => __('settings.appearance.options.gray'), 'image_url' => '/assets/vendor/inspinia/images/layouts/topbar-color-gray.png'],
    ];
    foreach ($topbarOptions as &$option) {
        $option['is_selected'] = (string) ($lockedConfig['topbar-color'] ?? 'gray') === $option['value'];
        $option['preview_aria_label'] = __('settings.appearance.preview.topbar_preview', ['color' => (string) $option['label']]);
    }
    unset($option);

    $sidenavOptions = [
        ['value' => 'light', 'label' => __('settings.appearance.options.light'), 'image_url' => '/assets/vendor/inspinia/images/layouts/sidenav-color-light.png'],
        ['value' => 'dark', 'label' => __('settings.appearance.options.dark'), 'image_url' => '/assets/vendor/inspinia/images/layouts/sidenav-color-dark.png'],
        ['value' => 'gray', 'label' => __('settings.appearance.options.gray'), 'image_url' => '/assets/vendor/inspinia/images/layouts/sidenav-color-gray.png'],
    ];
    foreach ($sidenavOptions as &$option) {
        $option['is_selected'] = (string) ($lockedConfig['sidenav-color'] ?? 'dark') === $option['value'];
        $option['preview_aria_label'] = __('settings.appearance.preview.sidenav_preview', ['color' => (string) $option['label']]);
    }
    unset($option);

    $policySummary = $customizerEnabled
        ? __('settings.appearance.policy.enabled_summary')
        : __('settings.appearance.policy.locked_summary');

    return [
        'page_header' => [
            'eyebrow' => __('settings.appearance.hero_eyebrow'),
            'title' => (string) ($scope['pageTitle'] ?? __('settings.appearance.page_title')),
            'description' => __('settings.appearance.hero_lede'),
        ],

        'settings' => $brandingSettings,
        'themeCards' => $themeCards,
        'skinOptions' => $skinOptions,
        'schemeOptions' => $schemeOptions,
        'topbarOptions' => $topbarOptions,
        'sidenavOptions' => $sidenavOptions,
        'customizer_enabled_checked' => $customizerEnabled,
        'customizer_disabled_checked' => !$customizerEnabled,
        'platform_policy_summary' => $policySummary,
        'platform_locked_panel_class' => $customizerEnabled ? 'd-none' : '',
        'platform_runtime_json' => TrustedHtml::fromString(InlineJson::encode($runtime)),
        'csrfField' => TrustedHtml::fromString(CsrfProtection::getInstance()->getTokenField()),
    ];
};
