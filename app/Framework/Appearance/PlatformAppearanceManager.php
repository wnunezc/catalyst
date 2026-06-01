<?php

declare(strict_types=1);

namespace Catalyst\Framework\Appearance;

use Catalyst\Framework\Http\UploadedFile;
use Catalyst\Framework\Storage\StorageManager;
use Catalyst\Framework\Traits\SingletonTrait;
use Catalyst\Helpers\Config\ConfigManager;
use InvalidArgumentException;

final class PlatformAppearanceManager
{
    use SingletonTrait;

    public const SECTION = 'appearance';
    public const ENTRY = 'platform';

    /** @var string[] */
    private const BRANDING_KEYS = [
        'theme_family',
        'default_variant',
        'allow_user_variant_override',
        'brand_name',
        'brand_short_name',
        'brand_tagline',
        'logo_primary_path',
        'logo_dark_path',
        'favicon_path',
        'pdf_watermark_enabled',
        'pdf_watermark_text',
        'pdf_watermark_font_size',
        'pdf_watermark_color',
    ];

    private ConfigManager $config;
    private StorageManager $storage;

    protected function __construct()
    {
        $this->config = ConfigManager::getInstance();
        $this->storage = StorageManager::getInstance();
    }

    /**
     * @return array<string, mixed>
     */
    public function settings(): array
    {
        return $this->withLegacyAliases(
            $this->normalizeSettings($this->config->entry(self::SECTION, self::ENTRY, []))
        );
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function themeCatalog(): array
    {
        return [
            'inspinia' => [
                'label' => 'Catalyst / Inspinia',
                'description' => 'Tema canónico neutro. La apariencia visual se controla por el customizer de Inspinia, no por familias institucionales.',
                'brand_name' => $this->defaultBrandName(),
                'brand_short_name' => 'CF',
                'primary' => '#727cf5',
                'secondary' => '#6c757d',
                'accent' => '#39afd1',
                'logos' => [
                    'light' => '/assets/vendor/inspinia/images/logo.png',
                    'dark' => '/assets/vendor/inspinia/images/logo-black.png',
                ],
                'favicon' => '/assets/vendor/inspinia/images/favicon.ico',
                'pdf_watermark_logo' => '',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function themeDefinition(?string $family = null): array
    {
        $catalog = $this->themeCatalog();
        $family = $family !== null && array_key_exists($family, $catalog)
            ? $family
            : $this->themeFamily();

        return $catalog[$family] ?? $catalog['inspinia'];
    }

    /**
     * @return array{brand_name: string, brand_short_name: string, brand_tagline: string}
     */
    public function themeBrandDefaults(?string $family = null): array
    {
        $theme = $this->themeDefinition($family);
        $brandName = trim((string) ($theme['brand_name'] ?? '')) ?: $this->defaultBrandName();
        $brandShortName = trim((string) ($theme['brand_short_name'] ?? '')) ?: $this->initials($brandName);

        return [
            'brand_name' => $brandName,
            'brand_short_name' => strtoupper(substr($brandShortName, 0, 12)),
            'brand_tagline' => trim((string) ($theme['brand_tagline'] ?? '')),
        ];
    }

    public function themeFamily(): string
    {
        $settings = $this->settings();
        $branding = is_array($settings['branding'] ?? null) ? $settings['branding'] : [];
        $family = (string) ($branding['theme_family'] ?? $settings['theme_family'] ?? 'inspinia');

        return array_key_exists($family, $this->themeCatalog()) ? $family : 'inspinia';
    }

    public function defaultVariant(): string
    {
        $settings = $this->settings();
        $branding = is_array($settings['branding'] ?? null) ? $settings['branding'] : [];
        $variant = strtolower(trim((string) ($branding['default_variant'] ?? $settings['default_variant'] ?? 'light')));

        return in_array($variant, ['light', 'dark'], true) ? $variant : 'light';
    }

    public function allowUserVariantOverride(): bool
    {
        return false;
    }

    public function isAdminCustomizerEnabled(): bool
    {
        $settings = $this->settings();
        $ui = is_array($settings['ui'] ?? null) ? $settings['ui'] : [];

        return $this->booleanValue($ui['admin_customizer_enabled'] ?? true, true);
    }

    /**
     * @return array<string, string>
     */
    public function platformThemeConfig(): array
    {
        $settings = $this->settings();
        $ui = is_array($settings['ui'] ?? null) ? $settings['ui'] : [];
        $lockedTheme = is_array($ui['locked_theme'] ?? null) ? $ui['locked_theme'] : [];

        return $this->sanitizeThemeConfig($lockedTheme);
    }

    /**
     * @return array<string, mixed>
     */
    public function runtimeViewModel(): array
    {
        $enabled = $this->isAdminCustomizerEnabled();
        $lockedConfig = $this->platformThemeConfig();

        return [
            'admin_customizer_enabled' => $enabled,
            'adminCustomizerEnabled' => $enabled,
            'mode' => $enabled ? 'user' : 'locked',
            'locked_config' => $lockedConfig,
            'lockedConfig' => $lockedConfig,
            'defaults' => $this->themeConfigDefaults(),
            'closed_skins' => $this->closedSkinPresets(),
            'closedSkins' => $this->closedSkinPresets(),
        ];
    }

    /**
     * @return array<string, list<string>|array<string, array<string, string>>>
     */
    public function customizerAllowedValues(): array
    {
        return [
            'skin' => ['default', 'minimal', 'modern', 'material', 'pixel', 'luxe', 'flat', 'red-cross', 'civil-protection', 'firefighters', 'grempa'],
            'theme' => ['light', 'dark', 'system'],
            'topbar-color' => ['gray', 'light', 'dark'],
            'sidenav-color' => ['dark', 'light', 'gray'],
            'closed-skins' => $this->closedSkinPresets(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function sanitizeThemeConfig(array $config): array
    {
        $defaults = $this->themeConfigDefaults();
        $allowed = $this->customizerAllowedValues();

        $skin = $this->pickAllowed((string) ($config['skin'] ?? $defaults['skin']), $defaults['skin'], $allowed['skin']);
        $theme = $this->pickAllowed((string) ($config['theme'] ?? $defaults['theme']), $defaults['theme'], $allowed['theme']);
        $topbarColor = $this->pickAllowed((string) ($config['topbar-color'] ?? $defaults['topbar-color']), $defaults['topbar-color'], $allowed['topbar-color']);
        $sidenavColor = $this->pickAllowed((string) ($config['sidenav-color'] ?? $defaults['sidenav-color']), $defaults['sidenav-color'], $allowed['sidenav-color']);

        $sanitized = [
            'skin' => $skin,
            'theme' => $theme,
            'topbar-color' => $topbarColor,
            'sidenav-color' => $sidenavColor,
            'sidenav-size' => $defaults['sidenav-size'],
            'position' => $defaults['position'],
            'width' => $defaults['width'],
            'dir' => $defaults['dir'],
        ];

        $closed = $this->closedSkinPresets();
        if (isset($closed[$skin])) {
            $sanitized['theme'] = $closed[$skin]['theme'];
            $sanitized['topbar-color'] = $closed[$skin]['topbar-color'];
            $sanitized['sidenav-color'] = $closed[$skin]['sidenav-color'];
        }

        return $sanitized;
    }

    /**
     * @return array<string, mixed>
     */
    public function brandingViewModel(): array
    {
        $settings = $this->settings();
        $brandingSettings = is_array($settings['branding'] ?? null) ? $settings['branding'] : [];
        $family = $this->themeFamily();
        $catalog = $this->themeDefinition($family);
        $brandDefaults = $this->themeBrandDefaults($family);
        $brandName = trim((string) ($brandingSettings['brand_name'] ?? '')) ?: $brandDefaults['brand_name'];
        $brandShortName = trim((string) ($brandingSettings['brand_short_name'] ?? '')) ?: $brandDefaults['brand_short_name'];
        $brandTaglineSource = trim((string) ($brandingSettings['brand_tagline'] ?? ''));
        if ($brandTaglineSource === '') {
            $brandTaglineSource = $brandDefaults['brand_tagline'];
        }
        $brandTagline = $this->resolveBrandTagline($brandTaglineSource);

        $lightLogo = $this->normalizeAssetPath((string) ($brandingSettings['logo_primary_path'] ?? ''))
            ?: (string) ($catalog['logos']['light'] ?? '');
        $darkLogo = $this->normalizeAssetPath((string) ($brandingSettings['logo_dark_path'] ?? ''))
            ?: (string) ($catalog['logos']['dark'] ?? $lightLogo);
        $favicon = $this->normalizeAssetPath((string) ($brandingSettings['favicon_path'] ?? ''))
            ?: (string) ($catalog['favicon'] ?? $lightLogo);

        return [
            'theme_family' => $family,
            'theme_label' => (string) ($catalog['label'] ?? $family),
            'brand_name' => $brandName,
            'brand_short_name' => strtoupper(substr($brandShortName, 0, 12)),
            'brand_tagline' => $brandTagline,
            'has_brand_tagline' => $brandTagline !== '',
            'logo_light_url' => $lightLogo,
            'logo_dark_url' => $darkLogo,
            'favicon_url' => $favicon,
            'default_variant' => $this->defaultVariant(),
            'allow_user_variant_override' => $this->allowUserVariantOverride(),
            'admin_customizer_enabled' => $this->isAdminCustomizerEnabled(),
            'pdf_watermark_enabled' => (bool) ($brandingSettings['pdf_watermark_enabled'] ?? false),
            'pdf_watermark_text' => trim((string) ($brandingSettings['pdf_watermark_text'] ?? '')),
            'pdf_watermark_font_size' => (int) ($brandingSettings['pdf_watermark_font_size'] ?? 46),
            'pdf_watermark_color' => (string) ($brandingSettings['pdf_watermark_color'] ?? '#cbd5e1'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function headBootstrapPayload(): array
    {
        $branding = $this->brandingViewModel();
        $runtime = $this->runtimeViewModel();

        return [
            'themeFamily' => $branding['theme_family'],
            'defaultVariant' => $branding['default_variant'],
            'allowUserVariantOverride' => $branding['allow_user_variant_override'],
            'brandName' => $branding['brand_name'],
            'brandShortName' => $branding['brand_short_name'],
            'brandTagline' => $branding['brand_tagline'],
            'adminCustomizerEnabled' => $runtime['adminCustomizerEnabled'],
            'mode' => $runtime['mode'],
            'lockedConfig' => $runtime['lockedConfig'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function pdfWatermarkSettings(): array
    {
        $branding = $this->brandingViewModel();
        $settings = $this->settings();
        $brandingSettings = is_array($settings['branding'] ?? null) ? $settings['branding'] : [];
        $theme = $this->themeDefinition((string) ($branding['theme_family'] ?? $this->themeFamily()));
        $customLogoPath = $this->resolveLocalPublicAssetPath((string) ($brandingSettings['logo_primary_path'] ?? ''));
        $themeWatermarkLogoPath = $this->resolveLocalPublicAssetPath((string) ($theme['pdf_watermark_logo'] ?? ''));
        $watermarkLogoPath = $this->isSupportedPdfWatermarkLogo($customLogoPath)
            ? $customLogoPath
            : $themeWatermarkLogoPath;

        return [
            'enabled' => (bool) $branding['pdf_watermark_enabled'],
            'text' => trim((string) $branding['pdf_watermark_text']),
            'font_size' => max(24, min(96, (int) $branding['pdf_watermark_font_size'])),
            'color' => $this->normalizeHexColor((string) $branding['pdf_watermark_color']),
            'brand_name' => (string) $branding['brand_name'],
            'logo_path' => $watermarkLogoPath,
            'logo_opacity' => 0.08,
            'logo_max_width' => 280,
        ];
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function writeSettings(array $payload): void
    {
        $settings = $this->normalizeSettings(
            $this->mergeRecursiveDistinct($this->settings(), $payload)
        );

        $this->config->writeSection(self::SECTION, [
            self::ENTRY => [
                'ui' => $settings['ui'],
                'branding' => $settings['branding'],
            ],
        ]);
    }

    public function storeBrandAsset(UploadedFile $file, string $slot): string
    {
        if (!in_array($slot, ['logo-primary', 'logo-dark', 'favicon'], true)) {
            throw new InvalidArgumentException('Invalid branding asset slot.');
        }

        return $file->store('branding/' . $slot);
    }

    /**
     * @return array<string, mixed>
     */
    private function defaults(): array
    {
        return [
            'ui' => [
                'admin_customizer_enabled' => true,
                'mode' => 'user',
                'locked_theme' => $this->themeConfigDefaults(),
            ],
            'branding' => [
                'theme_family' => 'inspinia',
                'default_variant' => 'light',
                'allow_user_variant_override' => false,
                'brand_name' => '',
                'brand_short_name' => '',
                'brand_tagline' => '',
                'logo_primary_path' => '',
                'logo_dark_path' => '',
                'favicon_path' => '',
                'pdf_watermark_enabled' => false,
                'pdf_watermark_text' => 'INTERNAL USE',
                'pdf_watermark_font_size' => 46,
                'pdf_watermark_color' => '#cbd5e1',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function themeConfigDefaults(): array
    {
        return [
            'skin' => 'default',
            'theme' => 'light',
            'topbar-color' => 'gray',
            'sidenav-color' => 'dark',
            'sidenav-size' => 'default',
            'position' => 'fixed',
            'width' => 'fluid',
            'dir' => 'ltr',
        ];
    }

    /**
     * @return array<string, array{theme: string, topbar-color: string, sidenav-color: string}>
     */
    private function closedSkinPresets(): array
    {
        return [
            'red-cross' => [
                'theme' => 'light',
                'topbar-color' => 'light',
                'sidenav-color' => 'light',
            ],
            'civil-protection' => [
                'theme' => 'light',
                'topbar-color' => 'dark',
                'sidenav-color' => 'light',
            ],
            'firefighters' => [
                'theme' => 'light',
                'topbar-color' => 'dark',
                'sidenav-color' => 'dark',
            ],
            'grempa' => [
                'theme' => 'dark',
                'topbar-color' => 'dark',
                'sidenav-color' => 'dark',
            ],
        ];
    }

    /**
     * @param array<string, mixed> $raw
     * @return array<string, mixed>
     */
    private function normalizeSettings(array $raw): array
    {
        $settings = $this->defaults();

        if (is_array($raw['ui'] ?? null)) {
            $settings['ui'] = $this->mergeRecursiveDistinct($settings['ui'], $raw['ui']);
        }

        if (is_array($raw['branding'] ?? null)) {
            $settings['branding'] = $this->mergeRecursiveDistinct($settings['branding'], $raw['branding']);
        }

        foreach (self::BRANDING_KEYS as $key) {
            if (array_key_exists($key, $raw)) {
                $settings['branding'][$key] = $raw[$key];
            }
        }

        if (array_key_exists('admin_customizer_enabled', $raw)) {
            $settings['ui']['admin_customizer_enabled'] = $raw['admin_customizer_enabled'];
        }

        if (array_key_exists('customizer_policy', $raw)) {
            $settings['ui']['mode'] = $raw['customizer_policy'];
        }

        foreach (['locked_theme', 'platform_theme'] as $legacyThemeKey) {
            if (is_array($raw[$legacyThemeKey] ?? null)) {
                $settings['ui']['locked_theme'] = $raw[$legacyThemeKey];
            }
        }

        $settings['ui']['admin_customizer_enabled'] = $this->booleanValue($settings['ui']['admin_customizer_enabled'] ?? true, true);
        $settings['ui']['mode'] = $settings['ui']['admin_customizer_enabled'] ? 'user' : 'locked';
        $settings['ui']['locked_theme'] = $this->sanitizeThemeConfig(
            is_array($settings['ui']['locked_theme'] ?? null) ? $settings['ui']['locked_theme'] : []
        );

        $settings['branding']['theme_family'] = array_key_exists((string) ($settings['branding']['theme_family'] ?? ''), $this->themeCatalog())
            ? (string) $settings['branding']['theme_family']
            : 'inspinia';
        $settings['branding']['default_variant'] = in_array((string) ($settings['branding']['default_variant'] ?? ''), ['light', 'dark'], true)
            ? (string) $settings['branding']['default_variant']
            : 'light';
        $settings['branding']['allow_user_variant_override'] = false;
        $settings['branding']['pdf_watermark_enabled'] = $this->booleanValue($settings['branding']['pdf_watermark_enabled'] ?? false, false);
        $settings['branding']['pdf_watermark_font_size'] = max(24, min(96, (int) ($settings['branding']['pdf_watermark_font_size'] ?? 46)));
        $settings['branding']['pdf_watermark_color'] = $this->normalizeHexColor((string) ($settings['branding']['pdf_watermark_color'] ?? '#CBD5E1'));

        foreach (['brand_name', 'brand_short_name', 'brand_tagline', 'logo_primary_path', 'logo_dark_path', 'favicon_path', 'pdf_watermark_text'] as $key) {
            $settings['branding'][$key] = trim((string) ($settings['branding'][$key] ?? ''));
        }

        return $settings;
    }

    /**
     * @param array<string, mixed> $settings
     * @return array<string, mixed>
     */
    private function withLegacyAliases(array $settings): array
    {
        $branding = is_array($settings['branding'] ?? null) ? $settings['branding'] : [];
        foreach (self::BRANDING_KEYS as $key) {
            $settings[$key] = $branding[$key] ?? null;
        }

        return $settings;
    }

    /**
     * @param list<string> $choices
     */
    private function pickAllowed(string $value, string $fallback, array $choices): string
    {
        return in_array($value, $choices, true) ? $value : $fallback;
    }

    private function booleanValue(mixed $value, bool $default): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return $value === 1;
        }

        $value = strtolower(trim((string) $value));
        if ($value === '') {
            return $default;
        }

        if (in_array($value, ['1', 'true', 'on', 'yes', 'enabled'], true)) {
            return true;
        }

        if (in_array($value, ['0', 'false', 'off', 'no', 'disabled'], true)) {
            return false;
        }

        return $default;
    }

    private function defaultBrandName(): string
    {
        $project = $this->config->entry('app', 'project');

        return trim((string) ($project['project_name'] ?? 'Catalyst Framework')) ?: 'Catalyst Framework';
    }

    private function initials(string $value): string
    {
        $parts = preg_split('/\s+/', trim($value)) ?: [];
        $letters = '';

        foreach ($parts as $part) {
            if ($part === '') {
                continue;
            }

            $letters .= strtoupper(substr($part, 0, 1));
        }

        return $letters !== '' ? substr($letters, 0, 12) : 'CF';
    }

    private function resolveBrandTagline(string $value): string
    {
        $value = trim($value);

        if ($value === '' || strcasecmp($value, 'Administration') === 0) {
            return __('ui.shell.administration_tagline');
        }

        return $value;
    }

    private function normalizeAssetPath(string $path): string
    {
        $path = trim($path);

        if ($path === '') {
            return '';
        }

        if (preg_match('#^(https?:)?//#i', $path) === 1) {
            return $path;
        }

        if (str_starts_with($path, '/')) {
            return $path;
        }

        return $this->storage->url($path, 'local');
    }

    private function normalizeHexColor(string $value): string
    {
        $value = strtoupper(trim($value));

        if (preg_match('/^#[0-9A-F]{6}$/', $value) === 1) {
            return $value;
        }

        return '#CBD5E1';
    }

    private function resolveLocalPublicAssetPath(string $path): string
    {
        $path = trim($path);

        if ($path === '' || preg_match('#^(https?:)?//#i', $path) === 1) {
            return '';
        }

        $relativePath = str_starts_with($path, '/')
            ? ltrim($path, '/')
            : ltrim(str_replace('\\', '/', $path), '/');
        $absolutePath = implode(DS, array_merge([PD, 'public'], explode('/', $relativePath)));

        return is_file($absolutePath) ? $absolutePath : '';
    }

    private function isSupportedPdfWatermarkLogo(string $path): bool
    {
        return $path !== '' && preg_match('/\.(jpe?g)$/i', $path) === 1;
    }

    /**
     * @param array<string, mixed> $base
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function mergeRecursiveDistinct(array $base, array $overrides): array
    {
        foreach ($overrides as $key => $value) {
            if (is_array($value) && is_array($base[$key] ?? null)) {
                $base[$key] = $this->mergeRecursiveDistinct($base[$key], $value);
                continue;
            }

            $base[$key] = $value;
        }

        return $base;
    }
}
