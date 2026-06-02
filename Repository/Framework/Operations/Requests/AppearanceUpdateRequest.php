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
 * Reads platform appearance settings and uploaded branding assets from a request.
 *
 * @package Catalyst\Repository\Operations\Requests
 * Responsibility: Provides normalized appearance input to the operations controller.
 */
final class AppearanceUpdateRequest
{
    use NormalizesCheckboxValues;

    /**
     * Wraps the incoming HTTP request used to read appearance fields.
     *
     * Responsibility: Wraps the incoming HTTP request used to read appearance fields.
     */
    public function __construct(private readonly Request $request)
    {
    }

    /**
     * Returns whether the administrator customizer is enabled.
     *
     * Responsibility: Returns whether the administrator customizer is enabled.
     */
    public function adminCustomizerEnabled(): bool
    {
        return $this->checkboxValue($this->request->input('admin_customizer_enabled'));
    }

    /**
     * Returns whether generated PDFs should include a watermark.
     *
     * Responsibility: Returns whether generated PDFs should include a watermark.
     */
    public function pdfWatermarkEnabled(): bool
    {
        return $this->checkboxValue($this->request->input('pdf_watermark_enabled'));
    }

    /**
     * Returns normalized platform shell theme selections.
     *
     * Responsibility: Returns normalized platform shell theme selections.
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
     * Returns normalized branding and PDF watermark settings.
     *
     * Responsibility: Returns normalized branding and PDF watermark settings.
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
     * Returns uploaded branding assets keyed by form field.
     *
     * Responsibility: Returns uploaded branding assets keyed by form field.
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
     * Returns whether the specified branding asset should be reset.
     *
     * Responsibility: Returns whether the specified branding asset should be reset.
     */
    public function resetRequested(string $asset): bool
    {
        return !empty($this->request->input('reset_' . $asset));
    }
}
