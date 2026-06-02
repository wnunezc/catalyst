<?php

declare(strict_types=1);

namespace Catalyst\Repository\Operations\Requests;

use Catalyst\Framework\Http\Request;
use Catalyst\Repository\Operations\Requests\Concerns\NormalizesCheckboxValues;

final class AppearanceUpdateRequest
{
    use NormalizesCheckboxValues;

    public function __construct(private readonly Request $request)
    {
    }

    public function adminCustomizerEnabled(): bool
    {
        return $this->checkboxValue($this->request->input('admin_customizer_enabled'));
    }

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

    public function resetRequested(string $asset): bool
    {
        return !empty($this->request->input('reset_' . $asset));
    }
}
