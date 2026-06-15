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

namespace Catalyst\Framework\Appearance;

use Catalyst\Framework\Http\UploadedFile;
use Catalyst\Framework\Storage\StorageManager;
use Catalyst\Framework\Traits\SingletonTrait;
use Catalyst\Helpers\Config\ConfigManager;
use InvalidArgumentException;

/**
 * Manages platform appearance settings, branding assets, and runtime theme payloads.
 *
 * @package Catalyst\Framework\Appearance
 * Responsibility: Normalizes appearance configuration, exposes view models, stores brand assets, and constrains customizer values.
 */
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

    /**
     * Initializes configuration and storage services used by appearance workflows.
     *
     * Responsibility: Initializes configuration and storage services used by appearance workflows.
     */
    protected function __construct()
    {
        $this->config = ConfigManager::getInstance();
        $this->storage = StorageManager::getInstance();
    }

    /**
     * Returns normalized platform appearance settings with legacy top-level aliases.
     *
     * Responsibility: Returns normalized platform appearance settings with legacy top-level aliases.
     * @return array<string, mixed>
     */
    public function settings(): array
    {
        return $this->withLegacyAliases(
            $this->normalizeSettings($this->config->entry(self::SECTION, self::ENTRY, []))
        );
    }

    /**
     * Returns the available theme families and their default branding metadata.
     *
     * Responsibility: Returns the available theme families and their default branding metadata.
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
     * Resolves the requested theme family or falls back to the active configured theme.
     *
     * Responsibility: Resolves the requested theme family or falls back to the active configured theme.
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
     * Builds default brand labels from the selected theme definition.
     *
     * Responsibility: Builds default brand labels from the selected theme definition.
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

    /**
     * Resolves the active theme family from branding settings and validates it against the catalog.
     *
     * Responsibility: Resolves the active theme family from branding settings and validates it against the catalog.
     */
    public function themeFamily(): string
    {
        $settings = $this->settings();
        $branding = is_array($settings['branding'] ?? null) ? $settings['branding'] : [];
        $family = (string) ($branding['theme_family'] ?? $settings['theme_family'] ?? 'inspinia');

        return array_key_exists($family, $this->themeCatalog()) ? $family : 'inspinia';
    }

    /**
     * Resolves the configured light or dark default variant for the platform shell.
     *
     * Responsibility: Resolves the configured light or dark default variant for the platform shell.
     */
    public function defaultVariant(): string
    {
        $settings = $this->settings();
        $branding = is_array($settings['branding'] ?? null) ? $settings['branding'] : [];
        $variant = strtolower(trim((string) ($branding['default_variant'] ?? $settings['default_variant'] ?? 'light')));

        return in_array($variant, ['light', 'dark'], true) ? $variant : 'light';
    }

    /**
     * Reports whether end users may override the platform color variant.
     *
     * Responsibility: Reports whether end users may override the platform color variant.
     */
    public function allowUserVariantOverride(): bool
    {
        return false;
    }

    /**
     * Determines whether the shared Inspinia customizer is exposed to users.
     *
     * Responsibility: Determines whether the shared Inspinia customizer is exposed to users.
     */
    public function isCustomizerEnabled(): bool
    {
        $settings = $this->settings();
        $ui = is_array($settings['ui'] ?? null) ? $settings['ui'] : [];

        return $this->booleanValue($ui['customizer_enabled'] ?? true, true);
    }

    /**
     * Returns the locked platform theme configuration used when the customizer is disabled.
     *
     * Responsibility: Returns the locked platform theme configuration used when the customizer is disabled.
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
     * Builds the runtime payload consumed by the shell and customizer front-end.
     *
     * Responsibility: Builds the runtime payload consumed by the shell and customizer front-end.
     * @return array<string, mixed>
     */
    public function runtimeViewModel(): array
    {
        $enabled = $this->isCustomizerEnabled();
        $lockedConfig = $this->platformThemeConfig();

        return [
            'customizer_enabled' => $enabled,
            'customizerEnabled' => $enabled,
            'mode' => $enabled ? 'user' : 'locked',
            'locked_config' => $lockedConfig,
            'lockedConfig' => $lockedConfig,
            'defaults' => $this->themeConfigDefaults(),
            'closed_skins' => $this->closedSkinPresets(),
            'closedSkins' => $this->closedSkinPresets(),
        ];
    }

    /**
     * Lists the allowed customizer values accepted by the platform appearance contract.
     *
     * Responsibility: Lists the allowed customizer values accepted by the platform appearance contract.
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
     * Sanitizes an incoming theme configuration and applies closed-skin presets when selected.
     *
     * Responsibility: Sanitizes an incoming theme configuration and applies closed-skin presets when selected.
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
     * Builds the branding view model used by layouts, logos, favicon, and PDF settings.
     *
     * Responsibility: Builds the branding view model used by layouts, logos, favicon, and PDF settings.
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
        $smallLogo = $this->normalizeAssetPath((string) ($brandingSettings['favicon_path'] ?? ''))
            ?: '/assets/vendor/inspinia/images/logo-sm.png';

        return [
            'theme_family' => $family,
            'theme_label' => (string) ($catalog['label'] ?? $family),
            'brand_name' => $brandName,
            'brand_short_name' => strtoupper(substr($brandShortName, 0, 12)),
            'brand_tagline' => $brandTagline,
            'has_brand_tagline' => $brandTagline !== '',
            'logo_light_url' => $lightLogo,
            'logo_dark_url' => $darkLogo,
            'logo_small_url' => $smallLogo,
            'favicon_url' => $favicon,
            'default_variant' => $this->defaultVariant(),
            'allow_user_variant_override' => $this->allowUserVariantOverride(),
            'customizer_enabled' => $this->isCustomizerEnabled(),
            'pdf_watermark_enabled' => (bool) ($brandingSettings['pdf_watermark_enabled'] ?? false),
            'pdf_watermark_text' => trim((string) ($brandingSettings['pdf_watermark_text'] ?? '')),
            'pdf_watermark_font_size' => (int) ($brandingSettings['pdf_watermark_font_size'] ?? 46),
            'pdf_watermark_color' => (string) ($brandingSettings['pdf_watermark_color'] ?? '#cbd5e1'),
        ];
    }

    /**
     * Builds the compact appearance payload injected during document head bootstrap.
     *
     * Responsibility: Builds the compact appearance payload injected during document head bootstrap.
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
            'customizerEnabled' => $runtime['customizerEnabled'],
            'mode' => $runtime['mode'],
            'lockedConfig' => $runtime['lockedConfig'],
        ];
    }

    /**
     * Resolves document branding and watermark settings for PDF rendering.
     *
     * Responsibility: Resolves watermark text, color, size, and supported local logo path for PDF rendering.
     * @return array<string, mixed>
     */
    public function pdfWatermarkSettings(): array
    {
        $branding = $this->brandingViewModel();
        $settings = $this->settings();
        $brandingSettings = is_array($settings['branding'] ?? null) ? $settings['branding'] : [];
        $theme = $this->themeDefinition((string) ($branding['theme_family'] ?? $this->themeFamily()));
        $customLogoPath = $this->resolveLocalPublicAssetPath((string) ($brandingSettings['logo_primary_path'] ?? ''));
        $themeDocumentLogoPath = $this->resolveLocalPublicAssetPath((string) ($theme['pdf_watermark_logo'] ?? ''));
        $documentLogoPath = $this->isSupportedPdfWatermarkLogo($customLogoPath)
            ? $customLogoPath
            : $themeDocumentLogoPath;

        return [
            'enabled' => (bool) $branding['pdf_watermark_enabled'],
            'text' => trim((string) $branding['pdf_watermark_text']),
            'font_size' => max(24, min(96, (int) $branding['pdf_watermark_font_size'])),
            'color' => $this->normalizeHexColor((string) $branding['pdf_watermark_color']),
            'brand_name' => (string) $branding['brand_name'],
            'brand_logo_path' => $documentLogoPath,
            'brand_logo_max_width' => 120,
        ];
    }

    /**
     * Merges and persists normalized appearance settings into the platform configuration section.
     *
     * Responsibility: Merges and persists normalized appearance settings into the platform configuration section.
     * @param array<string, mixed> $payload
     */
    public function writeSettings(array $payload): void
    {
        $settings = $this->normalizeSettings(
            $this->mergeRecursiveDistinct($this->rawSettings(), $payload)
        );

        $this->config->writeSection(self::SECTION, [
            self::ENTRY => [
                'ui' => $settings['ui'],
                'branding' => $settings['branding'],
            ],
        ]);
    }

    /**
     * Stores an uploaded branding asset in the requested logo or favicon slot.
     *
     * Responsibility: Stores an uploaded branding asset in the requested logo or favicon slot.
     */
    public function storeBrandAsset(UploadedFile $file, string $slot): string
    {
        if (!in_array($slot, ['logo-primary', 'logo-dark', 'favicon'], true)) {
            throw new InvalidArgumentException('Invalid branding asset slot.');
        }

        $path = $file->store('branding/' . $slot);
        $this->normalizeStoredBrandAsset($path, $slot);

        return $path;
    }

    /**
     * Removes excessive transparent canvas from stored raster branding assets.
     *
     * Responsibility: Normalizes reusable branding geometry without changing its visual proportions.
     */
    private function normalizeStoredBrandAsset(string $path, string $slot): void
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (!function_exists('imagecreatefromstring') || !in_array($extension, ['png', 'webp'], true)) {
            return;
        }

        $contents = $this->storage->get($path, 'local');
        $source = @imagecreatefromstring($contents);

        if ($source === false) {
            return;
        }

        if (function_exists('imagepalettetotruecolor')) {
            imagepalettetotruecolor($source);
        }

        $bounds = $this->visibleImageBounds($source);
        if ($bounds === null) {
            imagedestroy($source);
            return;
        }

        $normalized = $slot === 'favicon'
            ? $this->normalizeFaviconCanvas($source, $bounds)
            : $this->cropTransparentCanvas($source, $bounds);
        imagedestroy($source);

        if ($normalized === false) {
            return;
        }

        $normalizedContents = $this->encodeNormalizedBrandAsset($normalized, $extension);
        imagedestroy($normalized);

        if ($normalizedContents !== '') {
            $this->storage->put($path, $normalizedContents, 'local');
        }
    }

    /**
     * Encodes a normalized branding image using its stored file format.
     */
    private function encodeNormalizedBrandAsset(\GdImage $image, string $extension): string
    {
        ob_start();

        if ($extension === 'webp' && function_exists('imagewebp')) {
            imagewebp($image, null, 90);
        } elseif ($extension === 'png') {
            imagepng($image, null, 9);
        } else {
            ob_end_clean();
            return '';
        }

        $contents = ob_get_clean();

        return is_string($contents) ? $contents : '';
    }

    /**
     * Finds the visible pixel bounds of an image using its alpha channel.
     *
     * @return array{x: int, y: int, width: int, height: int}|null
     */
    private function visibleImageBounds(\GdImage $image): ?array
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $left = $width;
        $top = $height;
        $right = -1;
        $bottom = -1;

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $alpha = (imagecolorat($image, $x, $y) >> 24) & 0x7F;
                if ($alpha >= 120) {
                    continue;
                }

                $left = min($left, $x);
                $top = min($top, $y);
                $right = max($right, $x);
                $bottom = max($bottom, $y);
            }
        }

        if ($right < $left || $bottom < $top) {
            return null;
        }

        return [
            'x' => $left,
            'y' => $top,
            'width' => $right - $left + 1,
            'height' => $bottom - $top + 1,
        ];
    }

    /**
     * Crops a wordmark to its visible content while preserving a small breathing area.
     *
     * @param array{x: int, y: int, width: int, height: int} $bounds
     */
    private function cropTransparentCanvas(\GdImage $source, array $bounds): \GdImage|false
    {
        $padding = max(2, (int) round($bounds['height'] * 0.04));
        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);
        $x = max(0, $bounds['x'] - $padding);
        $y = max(0, $bounds['y'] - $padding);
        $width = min($sourceWidth - $x, $bounds['width'] + ($padding * 2));
        $height = min($sourceHeight - $y, $bounds['height'] + ($padding * 2));
        $target = $this->transparentCanvas($width, $height);

        if ($target === false) {
            return false;
        }

        imagecopy($target, $source, 0, 0, $x, $y, $width, $height);

        return $target;
    }

    /**
     * Centers a favicon's visible symbol on a compact square canvas.
     *
     * @param array{x: int, y: int, width: int, height: int} $bounds
     */
    private function normalizeFaviconCanvas(\GdImage $source, array $bounds): \GdImage|false
    {
        $size = 256;
        $padding = 20;
        $available = $size - ($padding * 2);
        $scale = min($available / $bounds['width'], $available / $bounds['height']);
        $width = max(1, (int) round($bounds['width'] * $scale));
        $height = max(1, (int) round($bounds['height'] * $scale));
        $target = $this->transparentCanvas($size, $size);

        if ($target === false) {
            return false;
        }

        imagecopyresampled(
            $target,
            $source,
            (int) floor(($size - $width) / 2),
            (int) floor(($size - $height) / 2),
            $bounds['x'],
            $bounds['y'],
            $width,
            $height,
            $bounds['width'],
            $bounds['height']
        );

        return $target;
    }

    /**
     * Creates a fully transparent image canvas.
     */
    private function transparentCanvas(int $width, int $height): \GdImage|false
    {
        $canvas = imagecreatetruecolor($width, $height);
        if ($canvas === false) {
            return false;
        }

        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefill($canvas, 0, 0, $transparent);

        return $canvas;
    }

    /**
     * Returns a translation key when an uploaded asset violates a slot-specific contract.
     *
     * Responsibility: Validates reusable branding asset geometry before storage.
     */
    public function brandAssetValidationError(UploadedFile $file, string $slot): ?string
    {
        $mime = strtolower($file->getMimeType());
        $rasterMimes = ['image/png', 'image/jpeg', 'image/jpg', 'image/webp'];

        if ($slot === 'logo-primary' && !in_array($mime, $rasterMimes, true)) {
            return 'settings.appearance.messages.primary_logo_raster';
        }

        if ($slot !== 'favicon') {
            return null;
        }

        if (!in_array($mime, $rasterMimes, true)) {
            return 'settings.appearance.messages.favicon_raster';
        }

        $dimensions = @getimagesize($file->getPath());
        if (!is_array($dimensions)) {
            return 'settings.appearance.messages.invalid_brand_asset';
        }

        return (int) ($dimensions[0] ?? 0) === (int) ($dimensions[1] ?? 0)
            ? null
            : 'settings.appearance.messages.favicon_square';
    }

    /**
     * Returns stored appearance settings without compatibility aliases.
     *
     * Responsibility: Prevents legacy aliases from overriding current nested settings during writes.
     * @return array<string, mixed>
     */
    private function rawSettings(): array
    {
        $settings = $this->config->entry(self::SECTION, self::ENTRY, []);
        $settings = is_array($settings) ? $settings : [];

        foreach (self::BRANDING_KEYS as $key) {
            unset($settings[$key]);
        }

        return $settings;
    }

    /**
     * Provides the baseline appearance configuration used before stored overrides are applied.
     *
     * Responsibility: Provides the baseline appearance configuration used before stored overrides are applied.
     * @return array<string, mixed>
     */
    private function defaults(): array
    {
        return [
            'ui' => [
                'customizer_enabled' => true,
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
     * Provides default Inspinia customizer values for a locked platform theme.
     *
     * Responsibility: Provides default Inspinia customizer values for a locked platform theme.
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
     * Lists skin presets whose palette values are fixed by the platform.
     *
     * Responsibility: Lists skin presets whose palette values are fixed by the platform.
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
     * Normalizes stored appearance settings, legacy keys, booleans, theme values, and branding fields.
     *
     * Responsibility: Normalizes stored appearance settings, legacy keys, booleans, theme values, and branding fields.
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

        if (array_key_exists('customizer_enabled', $raw)) {
            $settings['ui']['customizer_enabled'] = $raw['customizer_enabled'];
        }

        if (array_key_exists('customizer_policy', $raw)) {
            $settings['ui']['mode'] = $raw['customizer_policy'];
        }

        foreach (['locked_theme', 'platform_theme'] as $legacyThemeKey) {
            if (is_array($raw[$legacyThemeKey] ?? null)) {
                $settings['ui']['locked_theme'] = $raw[$legacyThemeKey];
            }
        }

        $settings['ui']['customizer_enabled'] = $this->booleanValue($settings['ui']['customizer_enabled'] ?? true, true);
        $settings['ui']['mode'] = $settings['ui']['customizer_enabled'] ? 'user' : 'locked';
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
     * Adds legacy top-level branding keys to the normalized settings array.
     *
     * Responsibility: Adds legacy top-level branding keys to the normalized settings array.
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
     * Selects a value when it belongs to the allowed list or returns the fallback.
     *
     * Responsibility: Selects a value when it belongs to the allowed list or returns the fallback.
     * @param list<string> $choices
     */
    private function pickAllowed(string $value, string $fallback, array $choices): string
    {
        return in_array($value, $choices, true) ? $value : $fallback;
    }

    /**
     * Coerces common scalar boolean representations while preserving a default for unknown values.
     *
     * Responsibility: Coerces common scalar boolean representations while preserving a default for unknown values.
     */
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

    /**
     * Resolves the default brand name from project configuration.
     *
     * Responsibility: Resolves the default brand name from project configuration.
     */
    private function defaultBrandName(): string
    {
        $project = $this->config->entry('app', 'project');

        return trim((string) ($project['project_name'] ?? 'Catalyst Framework')) ?: 'Catalyst Framework';
    }

    /**
     * Derives an uppercase short brand label from the words in a display name.
     *
     * Responsibility: Derives an uppercase short brand label from the words in a display name.
     */
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

    /**
     * Resolves blank or legacy privileged tagline text through translation.
     *
     * Responsibility: Resolves blank or legacy privileged tagline text through translation.
     */
    private function resolveBrandTagline(string $value): string
    {
        $value = trim($value);

        if ($value === '' || strcasecmp($value, 'Privileged') === 0) {
            return __('ui.shell.privileged_tagline');
        }

        return $value;
    }

    /**
     * Normalizes a stored asset reference into an external URL, absolute public path, or storage URL.
     *
     * Responsibility: Normalizes a stored asset reference into an external URL, absolute public path, or storage URL.
     */
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

    /**
     * Normalizes a hex color to uppercase six-digit form or returns the PDF-safe default.
     *
     * Responsibility: Normalizes a hex color to uppercase six-digit form or returns the PDF-safe default.
     */
    private function normalizeHexColor(string $value): string
    {
        $value = strtoupper(trim($value));

        if (preg_match('/^#[0-9A-F]{6}$/', $value) === 1) {
            return $value;
        }

        return '#CBD5E1';
    }

    /**
     * Resolves a public asset reference to a local filesystem path when the file exists.
     *
     * Responsibility: Resolves a public asset reference to a local filesystem path when the file exists.
     */
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

    /**
     * Determines whether a local asset path can be used as a PDF watermark logo.
     *
     * Responsibility: Determines whether a local asset path can be used as a PDF watermark logo.
     */
    private function isSupportedPdfWatermarkLogo(string $path): bool
    {
        return $path !== '' && preg_match('/\.(jpe?g|png|webp)$/i', $path) === 1;
    }

    /**
     * Recursively merges override values into a base settings array without numeric append semantics.
     *
     * Responsibility: Recursively merges override values into a base settings array without numeric append semantics.
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
