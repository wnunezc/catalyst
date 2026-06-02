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

namespace Catalyst\Repository\Operations\Requests;

use Catalyst\Framework\Http\Request;
use Catalyst\Repository\Operations\Requests\Concerns\NormalizesCheckboxValues;

/**
 * Defines the Appearance Update Request class contract.
 *
 * @package Catalyst\Repository\Operations\Requests
 * Responsibility: Coordinates the appearance update request behavior within its module boundary.
 */
final class AppearanceUpdateRequest
{
    use NormalizesCheckboxValues;

    /**
     * Initializes the Appearance Update Request instance.
     */
    public function __construct(private readonly Request $request)
    {
    }

    /**
     * Handles the admin customizer enabled workflow.
     */
    public function adminCustomizerEnabled(): bool
    {
        return $this->checkboxValue($this->request->input('admin_customizer_enabled'));
    }

    /**
     * Handles the pdf watermark enabled workflow.
     */
    public function pdfWatermarkEnabled(): bool
    {
        return $this->checkboxValue($this->request->input('pdf_watermark_enabled'));
    }

    /**
     * @return array<string, string>
     */
    public function platformTheme(): array
    {
        return [
            'skin' => trim((string) $this->request->input('platform_skin', 'default')),
            'theme' => trim((string) $this->request->input('platform_theme', 'light')),
            'topbar-color' => trim((string) $this->request->input('platform_topbar_color', 'gray')),
            'sidenav-color' => trim((string) $this->request->input('platform_sidenav_color', 'dark')),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function branding(): array
    {
        return [
            'theme_family' => trim((string) $this->request->input('theme_family', 'inspinia')),
            'default_variant' => trim((string) $this->request->input('default_variant', 'light')),
            'brand_name' => trim((string) $this->request->input('brand_name', '')),
            'brand_short_name' => trim((string) $this->request->input('brand_short_name', '')),
            'brand_tagline' => trim((string) $this->request->input('brand_tagline', '')),
            'logo_primary_path' => trim((string) $this->request->input('logo_primary_path', '')),
            'logo_dark_path' => trim((string) $this->request->input('logo_dark_path', '')),
            'favicon_path' => trim((string) $this->request->input('favicon_path', '')),
            'pdf_watermark_text' => trim((string) $this->request->input('pdf_watermark_text', '')),
            'pdf_watermark_font_size' => (string) $this->request->input('pdf_watermark_font_size', '46'),
            'pdf_watermark_color' => strtoupper(trim((string) $this->request->input('pdf_watermark_color', '#CBD5E1'))),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function files(): array
    {
        return [
            'logo_primary_file' => $this->request->file('logo_primary_file'),
            'logo_dark_file' => $this->request->file('logo_dark_file'),
            'favicon_file' => $this->request->file('favicon_file'),
        ];
    }

    /**
     * Handles the reset requested workflow.
     */
    public function resetRequested(string $asset): bool
    {
        return !empty($this->request->input('reset_' . $asset));
    }
}
