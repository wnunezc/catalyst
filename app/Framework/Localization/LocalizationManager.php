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
namespace Catalyst\Framework\Localization;

use Catalyst\Framework\Traits\SingletonTrait;
use Catalyst\Helpers\Config\ConfigManager;
use RuntimeException;

/**
 * Manages runtime localization settings and locale catalogs.
 *
 * @package Catalyst\Framework\Localization
 * Responsibility: Resolves supported locales, writes runtime language configuration and reports, initializes or synchronizes translation catalogs.
 */
final class LocalizationManager
{
    use SingletonTrait;

    public const SECTION = 'localization';
    public const ENTRY = 'runtime';
    public const BASE_LOCALE = 'en';

    private ConfigManager $config;

    /**
     * Initializes configuration access for localization settings.
     *
     * Responsibility: Initializes configuration access for localization settings.
     */
    protected function __construct()
    {
        $this->config = ConfigManager::getInstance();
    }

    /**
     * Returns runtime localization settings merged with defaults.
     *
     * Responsibility: Returns runtime localization settings merged with defaults.
     * @return array<string, mixed>
     */
    public function settings(): array
    {
        return $this->mergeRecursiveDistinct(
            $this->defaults(),
            $this->config->entry(self::SECTION, self::ENTRY, [])
        );
    }

    /**
     * Returns the configured default locale with base fallback.
     *
     * Responsibility: Returns the configured default locale with base fallback.
     */
    public function defaultLocale(): string
    {
        $settings = $this->settings();
        $default = strtolower(trim((string) ($settings['default_locale'] ?? self::BASE_LOCALE)));

        return $default !== '' ? $default : self::BASE_LOCALE;
    }

    /**
     * Returns labels for all available locales.
     *
     * Responsibility: Returns labels for all available locales.
     * @return array<string, string>
     */
    public function localeLabels(): array
    {
        $settings = $this->settings();
        $labels = is_array($settings['locale_labels'] ?? null) ? $settings['locale_labels'] : [];
        $locales = $this->availableLocales();

        foreach ($locales as $locale) {
            if (!isset($labels[$locale]) || trim((string) $labels[$locale]) === '') {
                $labels[$locale] = $this->guessLocaleLabel($locale);
            }
        }

        ksort($labels);

        return array_map(static fn (mixed $value): string => trim((string) $value), $labels);
    }

    /**
     * Discovers locales from configured support and language catalog roots.
     *
     * Responsibility: Discovers locales from configured support and language catalog roots.
     * @return array<int, string>
     */
    public function availableLocales(): array
    {
        $locales = [];

        foreach ($this->languageRoots() as $root) {
            if (!is_dir($root['path'])) {
                continue;
            }

            $directories = glob($root['path'] . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR) ?: [];
            foreach ($directories as $directory) {
                $locales[] = strtolower((string) basename($directory));
            }
        }

        $settings = $this->settings();
        foreach ((array) ($settings['supported_locales'] ?? []) as $locale) {
            $locales[] = strtolower(trim((string) $locale));
        }

        $locales[] = self::BASE_LOCALE;
        $locales[] = $this->defaultLocale();
        $locales = array_values(array_unique(array_filter(
            $locales,
            static fn (string $locale): bool => $locale !== ''
        )));
        sort($locales);

        return $locales;
    }

    /**
     * Persists runtime localization settings and updates the app language setting.
     *
     * Responsibility: Persists runtime localization settings and updates the app language setting.
     * @param array<string, mixed> $payload
     */
    public function writeRuntimeSettings(array $payload): void
    {
        $settings = $this->mergeRecursiveDistinct($this->settings(), $payload);
        $defaultLocale = strtolower(trim((string) ($settings['default_locale'] ?? self::BASE_LOCALE))) ?: self::BASE_LOCALE;
        $supportedLocales = array_values(array_unique(array_filter(array_map(
            static fn (mixed $locale): string => strtolower(trim((string) $locale)),
            (array) ($settings['supported_locales'] ?? [])
        ))));

        if (!in_array(self::BASE_LOCALE, $supportedLocales, true)) {
            $supportedLocales[] = self::BASE_LOCALE;
        }

        if (!in_array($defaultLocale, $supportedLocales, true)) {
            $supportedLocales[] = $defaultLocale;
        }

        sort($supportedLocales);
        $settings['default_locale'] = $defaultLocale;
        $settings['supported_locales'] = $supportedLocales;
        $settings['locale_labels'] = $this->normalizeLocaleLabels((array) ($settings['locale_labels'] ?? []), $supportedLocales);

        $this->config->writeSection(self::SECTION, [
            self::ENTRY => $settings,
        ]);

        $app = $this->config->section('app')['project'] ?? [];
        $app['project_lang'] = $defaultLocale;
        $this->config->writeSection('app', [
            'project' => $app,
        ]);
    }

    /**
     * Returns all language catalog root directories managed by the framework.
     *
     * Responsibility: Returns all language catalog root directories managed by the framework.
     * @return array<int, array<string, mixed>>
     */
    public function languageRoots(): array
    {
        $roots = [
            [
                'scope' => 'boot-core',
                'path' => implode(DIRECTORY_SEPARATOR, [PD, 'boot-core', 'lang']),
                'label' => 'boot-core',
            ],
        ];

        foreach ([
            [PD, 'Repository', 'Framework', '*', 'lang'],
            [PD, 'Repository', 'App', '*', 'lang'],
            [PD, 'Repository', 'App', 'Surface', '*', 'lang'],
        ] as $patternParts) {
            $pattern = implode(DIRECTORY_SEPARATOR, $patternParts);
            $directories = glob($pattern, GLOB_ONLYDIR) ?: [];

            foreach ($directories as $directory) {
                $relative = str_replace(PD . DIRECTORY_SEPARATOR, '', $directory);
                $scope = strtolower(str_replace(DIRECTORY_SEPARATOR, '.', preg_replace('/\\\\+/', DIRECTORY_SEPARATOR, $relative) ?: $relative));
                $roots[] = [
                    'scope' => $scope,
                    'path' => $directory,
                    'label' => $relative,
                ];
            }
        }

        return $roots;
    }

    /**
     * Builds catalog coverage and missing-key details for a locale.
     *
     * Responsibility: Builds catalog coverage and missing-key details for a locale.
     * @return array<string, mixed>
     */
    public function localeReport(string $locale): array
    {
        $locale = strtolower(trim($locale));
        $baseLocale = self::BASE_LOCALE;
        $catalogs = [];
        $totalKeys = 0;
        $translatedKeys = 0;
        $missingCatalogs = 0;
        $missingKeys = 0;
        $extraKeys = 0;

        foreach ($this->languageRoots() as $root) {
            $baseDirectory = $root['path'] . DIRECTORY_SEPARATOR . $baseLocale;
            if (!is_dir($baseDirectory)) {
                continue;
            }

            $baseFiles = glob($baseDirectory . DIRECTORY_SEPARATOR . '*.json') ?: [];
            sort($baseFiles);

            foreach ($baseFiles as $baseFile) {
                $filename = (string) basename($baseFile);
                $targetFile = $root['path'] . DIRECTORY_SEPARATOR . $locale . DIRECTORY_SEPARATOR . $filename;
                $baseJson = $this->readJsonFile($baseFile);
                $targetJson = is_file($targetFile) ? $this->readJsonFile($targetFile) : [];
                $baseLeaves = $this->flattenLeafValues($baseJson);
                $targetLeaves = $this->flattenLeafValues($targetJson);
                $catalogMissingKeys = array_values(array_diff(array_keys($baseLeaves), array_keys($targetLeaves)));
                $catalogExtraKeys = array_values(array_diff(array_keys($targetLeaves), array_keys($baseLeaves)));

                $catalogs[] = [
                    'scope' => (string) $root['scope'],
                    'label' => (string) $root['label'],
                    'catalog' => pathinfo($filename, PATHINFO_FILENAME),
                    'base_file' => $baseFile,
                    'target_file' => $targetFile,
                    'catalog_exists' => is_file($targetFile),
                    'missing_keys' => $catalogMissingKeys,
                    'extra_keys' => $catalogExtraKeys,
                    'base_key_count' => count($baseLeaves),
                    'translated_key_count' => count($baseLeaves) - count($catalogMissingKeys),
                ];

                if (!is_file($targetFile)) {
                    $missingCatalogs++;
                }

                $missingKeys += count($catalogMissingKeys);
                $extraKeys += count($catalogExtraKeys);
                $totalKeys += count($baseLeaves);
                $translatedKeys += max(0, count($baseLeaves) - count($catalogMissingKeys));
            }
        }

        usort($catalogs, static function (array $left, array $right): int {
            return [$left['label'], $left['catalog']] <=> [$right['label'], $right['catalog']];
        });

        return [
            'locale' => $locale,
            'label' => $this->localeLabels()[$locale] ?? $this->guessLocaleLabel($locale),
            'base_locale' => $baseLocale,
            'catalogs' => $catalogs,
            'summary' => [
                'catalog_count' => count($catalogs),
                'missing_catalogs' => $missingCatalogs,
                'missing_keys' => $missingKeys,
                'extra_keys' => $extraKeys,
                'total_keys' => $totalKeys,
                'translated_keys' => $translatedKeys,
                'coverage_percent' => $totalKeys > 0 ? round(($translatedKeys / $totalKeys) * 100, 2) : 100.0,
            ],
        ];
    }

    /**
     * Initializes missing locale catalog files from the base locale.
     *
     * Responsibility: Initializes missing locale catalog files from the base locale.
     * @return array<string, mixed>
     */
    public function initializeLocale(string $locale, string $label, bool $dryRun = false): array
    {
        $locale = $this->normalizeLocaleCode($locale);
        $actions = [];

        foreach ($this->languageRoots() as $root) {
            $baseDirectory = $root['path'] . DIRECTORY_SEPARATOR . self::BASE_LOCALE;
            if (!is_dir($baseDirectory)) {
                continue;
            }

            $targetDirectory = $root['path'] . DIRECTORY_SEPARATOR . $locale;
            $baseFiles = glob($baseDirectory . DIRECTORY_SEPARATOR . '*.json') ?: [];
            sort($baseFiles);

            foreach ($baseFiles as $baseFile) {
                $targetFile = $targetDirectory . DIRECTORY_SEPARATOR . basename($baseFile);
                $exists = is_file($targetFile);
                $actions[] = [
                    'action' => $exists ? 'kept' : 'create',
                    'target' => $targetFile,
                ];

                if ($dryRun || $exists) {
                    continue;
                }

                if (!is_dir($targetDirectory) && !mkdir($targetDirectory, 0755, true) && !is_dir($targetDirectory)) {
                    throw new RuntimeException('Unable to create locale directory: ' . $targetDirectory);
                }

                $contents = file_get_contents($baseFile);
                if ($contents === false || file_put_contents($targetFile, $contents) === false) {
                    throw new RuntimeException('Unable to initialize locale file: ' . $targetFile);
                }
            }
        }

        if (!$dryRun) {
            $labels = $this->localeLabels();
            $labels[$locale] = trim($label) !== '' ? trim($label) : $this->guessLocaleLabel($locale);
            $supported = $this->availableLocales();
            if (!in_array($locale, $supported, true)) {
                $supported[] = $locale;
            }

            $this->writeRuntimeSettings([
                'default_locale' => $this->defaultLocale(),
                'supported_locales' => $supported,
                'locale_labels' => $labels,
            ]);
        }

        return [
            'locale' => $locale,
            'label' => trim($label) !== '' ? trim($label) : $this->guessLocaleLabel($locale),
            'dry_run' => $dryRun,
            'actions' => $actions,
        ];
    }

    /**
     * Synchronizes a locale catalog set with missing base-locale keys.
     *
     * Responsibility: Synchronizes a locale catalog set with missing base-locale keys.
     * @return array<string, mixed>
     */
    public function synchronizeLocale(string $locale, bool $dryRun = false): array
    {
        $locale = $this->normalizeLocaleCode($locale);
        $updatedCatalogs = [];
        $missingKeys = 0;

        foreach ($this->languageRoots() as $root) {
            $baseDirectory = $root['path'] . DIRECTORY_SEPARATOR . self::BASE_LOCALE;
            if (!is_dir($baseDirectory)) {
                continue;
            }

            $targetDirectory = $root['path'] . DIRECTORY_SEPARATOR . $locale;
            $baseFiles = glob($baseDirectory . DIRECTORY_SEPARATOR . '*.json') ?: [];
            sort($baseFiles);

            foreach ($baseFiles as $baseFile) {
                $targetFile = $targetDirectory . DIRECTORY_SEPARATOR . basename($baseFile);
                $baseJson = $this->readJsonFile($baseFile);
                $targetJson = is_file($targetFile) ? $this->readJsonFile($targetFile) : [];
                $merge = $this->mergeMissingValues($targetJson, $baseJson);
                $missingKeys += count($merge['missing_keys']);

                if ($merge['missing_keys'] === [] && is_file($targetFile)) {
                    continue;
                }

                $updatedCatalogs[] = [
                    'target' => $targetFile,
                    'missing_keys' => $merge['missing_keys'],
                    'created' => !is_file($targetFile),
                ];

                if ($dryRun) {
                    continue;
                }

                if (!is_dir($targetDirectory) && !mkdir($targetDirectory, 0755, true) && !is_dir($targetDirectory)) {
                    throw new RuntimeException('Unable to create locale directory: ' . $targetDirectory);
                }

                $encoded = json_encode($merge['merged'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                if ($encoded === false || file_put_contents($targetFile, $encoded . PHP_EOL) === false) {
                    throw new RuntimeException('Unable to synchronize locale file: ' . $targetFile);
                }
            }
        }

        return [
            'locale' => $locale,
            'dry_run' => $dryRun,
            'updated_catalogs' => $updatedCatalogs,
            'missing_key_count' => $missingKeys,
        ];
    }

    /**
     * Normalizes and validates a locale code.
     *
     * Responsibility: Normalizes and validates a locale code.
     */
    private function normalizeLocaleCode(string $locale): string
    {
        $locale = strtolower(trim($locale));

        if (preg_match('/^[a-z]{2,3}(?:-[a-z0-9]{2,8})?$/', $locale) !== 1) {
            throw new RuntimeException('Locale code must look like en, es or pt-br.');
        }

        return $locale;
    }

    /**
     * Returns default localization runtime settings.
     *
     * Responsibility: Returns default localization runtime settings.
     * @return array<string, mixed>
     */
    private function defaults(): array
    {
        $project = $this->config->entry('app', 'project');

        return [
            'base_locale' => self::BASE_LOCALE,
            'fallback_locale' => self::BASE_LOCALE,
            'default_locale' => strtolower(trim((string) ($project['project_lang'] ?? self::BASE_LOCALE))) ?: self::BASE_LOCALE,
            'supported_locales' => [self::BASE_LOCALE, 'es'],
            'locale_labels' => [
                'en' => 'English',
                'es' => 'Español',
            ],
        ];
    }

    /**
     * Flattens nested locale catalog values into dotted leaf keys.
     *
     * Responsibility: Flattens nested locale catalog values into dotted leaf keys.
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function flattenLeafValues(array $data, string $prefix = ''): array
    {
        $result = [];

        foreach ($data as $key => $value) {
            $path = $prefix === '' ? (string) $key : $prefix . '.' . $key;

            if (is_array($value)) {
                $result += $this->flattenLeafValues($value, $path);
                continue;
            }

            $result[$path] = $value;
        }

        return $result;
    }

    /**
     * Reads and decodes a locale JSON file.
     *
     * Responsibility: Reads and decodes a locale JSON file.
     * @return array<string, mixed>
     */
    private function readJsonFile(string $path): array
    {
        if (!is_file($path)) {
            return [];
        }

        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new RuntimeException('Unable to read locale file: ' . $path);
        }

        $decoded = json_decode($contents, true);
        if (!is_array($decoded)) {
            throw new RuntimeException('Invalid locale JSON: ' . $path);
        }

        return $decoded;
    }

    /**
     * Recursively merges missing base values into the target catalog.
     *
     * Responsibility: Recursively merges missing base values into the target catalog.
     * @param array<string, mixed> $target
     * @param array<string, mixed> $base
     * @return array{merged: array<string, mixed>, missing_keys: array<int, string>}
     */
    private function mergeMissingValues(array $target, array $base, string $prefix = ''): array
    {
        $merged = $target;
        $missingKeys = [];

        foreach ($base as $key => $value) {
            $path = $prefix === '' ? (string) $key : $prefix . '.' . $key;

            if (!array_key_exists($key, $merged)) {
                $merged[$key] = $value;
                $missingKeys[] = $path;
                continue;
            }

            if (is_array($value) && is_array($merged[$key])) {
                $child = $this->mergeMissingValues($merged[$key], $value, $path);
                $merged[$key] = $child['merged'];
                $missingKeys = array_merge($missingKeys, $child['missing_keys']);
            }
        }

        return [
            'merged' => $merged,
            'missing_keys' => $missingKeys,
        ];
    }

    /**
     * Recursively merges override values without replacing unrelated nested keys.
     *
     * Responsibility: Recursively merges override values without replacing unrelated nested keys.
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

    /**
     * Normalizes labels for all supported locales.
     *
     * Responsibility: Normalizes labels for all supported locales.
     * @param array<string, mixed> $labels
     * @param array<int, string> $supportedLocales
     * @return array<string, string>
     */
    private function normalizeLocaleLabels(array $labels, array $supportedLocales): array
    {
        $normalized = [];

        foreach ($supportedLocales as $locale) {
            $normalized[$locale] = trim((string) ($labels[$locale] ?? $this->guessLocaleLabel($locale)));
        }

        ksort($normalized);

        return $normalized;
    }

    /**
     * Returns a readable label for a locale code.
     *
     * Responsibility: Returns a readable label for a locale code.
     */
    private function guessLocaleLabel(string $locale): string
    {
        return match ($locale) {
            'en' => 'English',
            'es' => 'Español',
            'fr' => 'Français',
            'pt', 'pt-br' => 'Português',
            default => strtoupper($locale),
        };
    }
}
