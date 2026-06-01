<?php

declare(strict_types=1);

namespace Catalyst\Repository\Operations\Controllers;

use Catalyst\Framework\Appearance\PlatformAppearanceManager;
use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;

final class AppearanceController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorizeResource('manage', 'operations');

        $manager = PlatformAppearanceManager::getInstance();

        return $this->view('operations.appearance', [
            'title' => __('operations.title'),
            'pageTitle' => __('operations.appearance.page_title'),
            'activeSection' => 'appearance',
            'settings' => $manager->settings(),
            'themeCatalog' => $manager->themeCatalog(),
            'branding' => $manager->brandingViewModel(),
            'runtime' => $manager->runtimeViewModel(),
            'customizerAllowedValues' => $manager->customizerAllowedValues(),
        ], 200, 'admin');
    }

    public function update(Request $request): Response
    {
        $this->authorizeResource('manage', 'operations');

        $manager = PlatformAppearanceManager::getInstance();
        $themeCatalog = $manager->themeCatalog();

        $adminCustomizerEnabled = $this->checkboxValue($request->input('admin_customizer_enabled'));
        $platformTheme = $manager->sanitizeThemeConfig([
            'skin' => trim((string) $request->input('platform_skin', 'default')),
            'theme' => trim((string) $request->input('platform_theme', 'light')),
            'topbar-color' => trim((string) $request->input('platform_topbar_color', 'gray')),
            'sidenav-color' => trim((string) $request->input('platform_sidenav_color', 'dark')),
        ]);

        $data = [
            'theme_family' => trim((string) $request->input('theme_family', 'inspinia')),
            'default_variant' => trim((string) $request->input('default_variant', 'light')),
            'brand_name' => trim((string) $request->input('brand_name', '')),
            'brand_short_name' => trim((string) $request->input('brand_short_name', '')),
            'brand_tagline' => trim((string) $request->input('brand_tagline', '')),
            'logo_primary_path' => trim((string) $request->input('logo_primary_path', '')),
            'logo_dark_path' => trim((string) $request->input('logo_dark_path', '')),
            'favicon_path' => trim((string) $request->input('favicon_path', '')),
            'pdf_watermark_text' => trim((string) $request->input('pdf_watermark_text', '')),
            'pdf_watermark_font_size' => (string) $request->input('pdf_watermark_font_size', '46'),
            'pdf_watermark_color' => strtoupper(trim((string) $request->input('pdf_watermark_color', '#CBD5E1'))),
        ];

        if ($data['theme_family'] === '') {
            $data['theme_family'] = 'inspinia';
        }

        if (!array_key_exists($data['theme_family'], $themeCatalog)) {
            return $this->postActionErrorRedirect('/configuration/platform-appearance', __('operations.appearance.messages.invalid_theme'), 422);
        }

        $currentThemeFamily = $manager->themeFamily();
        $currentBranding = $manager->brandingViewModel();
        $nextThemeDefaults = $manager->themeBrandDefaults($data['theme_family']);

        if ($data['theme_family'] !== $currentThemeFamily) {
            if ($data['brand_name'] === '' || $data['brand_name'] === (string) ($currentBranding['brand_name'] ?? '')) {
                $data['brand_name'] = $nextThemeDefaults['brand_name'];
            }

            if ($data['brand_short_name'] === '' || strtoupper($data['brand_short_name']) === strtoupper((string) ($currentBranding['brand_short_name'] ?? ''))) {
                $data['brand_short_name'] = $nextThemeDefaults['brand_short_name'];
            }
        }

        if ($data['brand_name'] === '') {
            $data['brand_name'] = $nextThemeDefaults['brand_name'];
        }

        if ($data['brand_short_name'] === '') {
            $data['brand_short_name'] = $nextThemeDefaults['brand_short_name'];
        }

        $validator = $this->validate($data, [
            'theme_family' => 'required|max:64',
            'default_variant' => 'required|in:light,dark',
            'brand_name' => 'required|max:120',
            'brand_short_name' => 'max:12',
            'brand_tagline' => 'max:120',
            'logo_primary_path' => 'max:255',
            'logo_dark_path' => 'max:255',
            'favicon_path' => 'max:255',
            'pdf_watermark_text' => 'max:80',
            'pdf_watermark_font_size' => 'required|integer|min_value:24|max_value:96',
            'pdf_watermark_color' => 'required|max:7',
        ]);

        if ($validator->fails()) {
            return $this->postActionErrorRedirect('/configuration/platform-appearance', $this->firstValidationError($validator->errors()), 422);
        }

        if (preg_match('/^#[0-9A-F]{6}$/', $data['pdf_watermark_color']) !== 1) {
            return $this->postActionErrorRedirect('/configuration/platform-appearance', __('operations.appearance.messages.invalid_watermark_color'), 422);
        }

        $files = [
            'logo_primary_file' => $request->file('logo_primary_file'),
            'logo_dark_file' => $request->file('logo_dark_file'),
            'favicon_file' => $request->file('favicon_file'),
        ];

        foreach ($files as $field => $file) {
            if ($file === null) {
                continue;
            }

            $fileValidator = $this->validate(
                [$field => $file],
                [$field => 'file|mimes:png,jpg,jpeg,svg,webp|max_size:4096'],
                [$field => ucfirst(str_replace('_', ' ', $field))]
            );

            if ($fileValidator->fails()) {
                return $this->postActionErrorRedirect('/configuration/platform-appearance', $this->firstValidationError($fileValidator->errors()), 422);
            }
        }

        if (!empty($request->input('reset_logo_primary'))) {
            $data['logo_primary_path'] = '';
        }
        if (!empty($request->input('reset_logo_dark'))) {
            $data['logo_dark_path'] = '';
        }
        if (!empty($request->input('reset_favicon'))) {
            $data['favicon_path'] = '';
        }

        if ($files['logo_primary_file'] !== null) {
            $data['logo_primary_path'] = $manager->storeBrandAsset($files['logo_primary_file'], 'logo-primary');
        }
        if ($files['logo_dark_file'] !== null) {
            $data['logo_dark_path'] = $manager->storeBrandAsset($files['logo_dark_file'], 'logo-dark');
        }
        if ($files['favicon_file'] !== null) {
            $data['favicon_path'] = $manager->storeBrandAsset($files['favicon_file'], 'favicon');
        }

        $manager->writeSettings([
            'ui' => [
                'admin_customizer_enabled' => $adminCustomizerEnabled,
                'mode' => $adminCustomizerEnabled ? 'user' : 'locked',
                'locked_theme' => $platformTheme,
            ],
            'branding' => [
                'theme_family' => $data['theme_family'],
                'default_variant' => $data['default_variant'],
                'allow_user_variant_override' => false,
                'brand_name' => $data['brand_name'],
                'brand_short_name' => $data['brand_short_name'],
                'brand_tagline' => $data['brand_tagline'],
                'logo_primary_path' => $data['logo_primary_path'],
                'logo_dark_path' => $data['logo_dark_path'],
                'favicon_path' => $data['favicon_path'],
                'pdf_watermark_enabled' => $this->checkboxValue($request->input('pdf_watermark_enabled')),
                'pdf_watermark_text' => $data['pdf_watermark_text'],
                'pdf_watermark_font_size' => (int) $data['pdf_watermark_font_size'],
                'pdf_watermark_color' => $data['pdf_watermark_color'],
            ],
        ]);

        return $this->postActionSuccessRedirect('/configuration/platform-appearance', 'Platform appearance updated.');
    }

    /**
     * @param array<string, string[]|string> $errors
     */
    private function firstValidationError(array $errors): string
    {
        foreach ($errors as $messages) {
            if (is_array($messages) && $messages !== []) {
                return (string) $messages[0];
            }

            if (is_string($messages) && trim($messages) !== '') {
                return $messages;
            }
        }

        return 'Invalid appearance payload.';
    }

    private function checkboxValue(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return in_array(strtolower((string) $value), ['1', 'true', 'on', 'yes'], true);
    }
}
