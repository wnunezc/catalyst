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

namespace Catalyst\Repository\Operations\Controllers;

use Catalyst\Framework\Appearance\PlatformAppearanceManager;
use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Catalyst\Repository\Operations\Requests\AppearanceUpdateRequest;

/**
 * Defines the Appearance Controller class contract.
 *
 * @package Catalyst\Repository\Operations\Controllers
 * Responsibility: Coordinates the appearance controller behavior within its module boundary.
 */
final class AppearanceController extends Controller
{
    /**
     * Handles the index workflow.
     */
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

    /**
     * Handles the update workflow.
     */
    public function update(Request $request): Response
    {
        $this->authorizeResource('manage', 'operations');

        $manager = PlatformAppearanceManager::getInstance();
        $payload = new AppearanceUpdateRequest($request);
        $themeCatalog = $manager->themeCatalog();

        $adminCustomizerEnabled = $payload->adminCustomizerEnabled();
        $platformTheme = $manager->sanitizeThemeConfig($payload->platformTheme());
        $data = $payload->branding();

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

        $files = $payload->files();

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

        if ($payload->resetRequested('logo_primary')) {
            $data['logo_primary_path'] = '';
        }
        if ($payload->resetRequested('logo_dark')) {
            $data['logo_dark_path'] = '';
        }
        if ($payload->resetRequested('favicon')) {
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
                'pdf_watermark_enabled' => $payload->pdfWatermarkEnabled(),
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

}
