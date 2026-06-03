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

namespace Catalyst\Framework\Cli\Commands;

use Catalyst\Framework\Argument\ArgumentBag;
use Catalyst\Framework\Argument\Option;
use Catalyst\Framework\Cli\AbstractCommand;
use Catalyst\Framework\Localization\LocalizationManager;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * i18n:usage-lint CLI command.
 *
 * @package Catalyst\Framework\Cli\Commands
 * Responsibility: Cross-checks translation keys used by PHP, PHTML and JS against locale JSON catalogs without mutating runtime state.
 */
final class I18nUsageLintCommand extends AbstractCommand
{
    /**
     * @var array<string, string> Translation keys intentionally allowed to miss exact catalog matches.
     */
    private const KNOWN_ALLOWED = [
        'nonexistent.key' => 'DevTools intentional missing-key probe.',
        'devtools.module_designer.form.options.surface_' => 'Dynamic Module Designer prefix completed with a runtime suffix.',
    ];

    /**
     * Defines the accepted option schema for this command.
     *
     * Responsibility: Exposes CLI parser metadata only; command behavior stays inside execute().
     * @return Option[]
     */
    public function getOptions(): array
    {
        return [
            new Option(null, 'locale', null, false, 'Locale to inspect. Defaults to en,es.', true),
            new Option(null, 'json', false, false, 'Render JSON output.', false),
        ];
    }

    /**
     * Returns the command name registered in the CLI registry.
     *
     * Responsibility: Provides the stable command identifier consumed by CommandRegistry.
     */
    public function getName(): string
    {
        return 'i18n:usage-lint';
    }

    /**
     * Returns the short help text shown for this command.
     *
     * Responsibility: Keeps command discovery text separate from execution logic.
     */
    public function getDescription(): string
    {
        return 'Verify used translation keys against locale JSON catalogs';
    }

    /**
     * Runs the i18n usage lint without writing files, cache or configuration.
     *
     * Responsibility: Checks translation key coverage as a read-only quality gate for release validation.
     */
    public function execute(ArgumentBag $args): int
    {
        $locale = strtolower(trim((string) ($args->getOptionValue('locale') ?? '')));
        $locales = $locale !== '' ? [$locale] : ['en', 'es'];
        $usedKeys = $this->usedKeys();
        $reports = [];

        foreach ($locales as $targetLocale) {
            $reports[$targetLocale] = $this->reportLocale($targetLocale, $usedKeys);
        }

        $ok = true;
        foreach ($reports as $report) {
            $ok = $ok && count((array) ($report['missing'] ?? [])) === 0;
        }

        if ((bool) ($args->getOptionValue('json') ?? false)) {
            $this->line((string) json_encode([
                'ok' => $ok,
                'used_keys' => count($usedKeys),
                'locales' => $reports,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

            return $ok ? 0 : 1;
        }

        $this->line('');
        $this->info('I18n Usage Lint');
        $this->line(str_repeat('-', 74));

        foreach ($reports as $targetLocale => $report) {
            $missing = (array) ($report['missing'] ?? []);
            $tolerated = (array) ($report['tolerated'] ?? []);

            $this->line(sprintf(
                '  %-8s missing: %d  tolerated: %d',
                strtoupper((string) $targetLocale),
                count($missing),
                count($tolerated)
            ));

            foreach ($missing as $entry) {
                $this->line(sprintf('    - %s :: %s', (string) ($entry['key'] ?? ''), (string) ($entry['file'] ?? '')));
            }
        }

        $this->line(str_repeat('-', 74));
        $this->line('Used keys: ' . count($usedKeys));
        $ok
            ? $this->success('Used translation keys have matching EN/ES catalog entries.')
            : $this->error('Used translation keys are missing from one or more catalogs.');
        $this->line('');

        return $ok ? 0 : 1;
    }

    /**
     * Builds the locale report for all used keys.
     *
     * Responsibility: Compares extracted key usage against one locale catalog and classifies missing coverage.
     * @param array<string, array<string, true>> $usedKeys
     * @return array{missing: array<int, array{key: string, file: string}>, tolerated: array<int, array{key: string, file: string, reason: string}>}
     */
    private function reportLocale(string $locale, array $usedKeys): array
    {
        $catalog = $this->catalog($locale);
        $missing = [];
        $tolerated = [];

        foreach ($usedKeys as $key => $files) {
            if (isset($catalog[$key])) {
                continue;
            }

            $file = (string) array_key_first($files);
            if (isset(self::KNOWN_ALLOWED[$key])) {
                $tolerated[] = ['key' => $key, 'file' => $file, 'reason' => self::KNOWN_ALLOWED[$key]];
                continue;
            }

            if (str_ends_with($key, '.') && $this->catalogHasPrefix($catalog, $key)) {
                $tolerated[] = ['key' => $key, 'file' => $file, 'reason' => 'Dynamic catalog prefix with existing children.'];
                continue;
            }

            $missing[] = ['key' => $key, 'file' => $file];
        }

        return [
            'missing' => $missing,
            'tolerated' => $tolerated,
        ];
    }

    /**
     * Extracts translation keys from source files.
     *
     * Responsibility: Discovers literal translation key references while ignoring generated/vendor surfaces.
     * @return array<string, array<string, true>>
     */
    private function usedKeys(): array
    {
        $used = [];
        $patterns = [
            '/__\(\s*[\'"]([a-zA-Z0-9_.:-]+)[\'"]/',
            '/\bt\(\s*[\'"]([a-zA-Z0-9_.:-]+)[\'"]/',
            '/\{\{\s*t:([a-zA-Z0-9_.:-]+)\s*\}\}/',
        ];

        foreach ($this->sourceFiles() as $file) {
            $text = (string) file_get_contents($file);

            foreach ($patterns as $pattern) {
                if (!preg_match_all($pattern, $text, $matches)) {
                    continue;
                }

                foreach ($matches[1] as $key) {
                    $used[$key][$this->relativePath($file)] = true;
                }
            }
        }

        ksort($used);

        return $used;
    }

    /**
     * Loads and flattens all catalogs for one locale.
     *
     * Responsibility: Builds the lookup table used to verify locale coverage across module and core dictionaries.
     * @return array<string, true>
     */
    private function catalog(string $locale): array
    {
        $catalog = [];

        foreach (LocalizationManager::getInstance()->languageRoots() as $root) {
            $path = rtrim((string) ($root['path'] ?? ''), DS);
            $localePath = $path . DS . $locale;

            if (!is_dir($localePath)) {
                continue;
            }

            foreach (glob($localePath . DS . '*.json') ?: [] as $jsonFile) {
                $domain = basename($jsonFile, '.json');
                $data = json_decode((string) file_get_contents($jsonFile), true);

                if (!is_array($data)) {
                    continue;
                }

                foreach ($this->flatten($data) as $key => $_) {
                    $catalog[$domain . '.' . $key] = true;
                }
            }
        }

        ksort($catalog);

        return $catalog;
    }

    /**
     * Recursively flattens a translation array.
     *
     * Responsibility: Converts nested JSON language trees into dotted keys for deterministic comparison.
     * @param array<string, mixed> $data
     * @return array<string, true>
     */
    private function flatten(array $data, string $prefix = ''): array
    {
        $out = [];

        foreach ($data as $key => $value) {
            $path = $prefix === '' ? (string) $key : $prefix . '.' . $key;

            if (is_array($value)) {
                $out += $this->flatten($value, $path);
                continue;
            }

            $out[$path] = true;
        }

        return $out;
    }

    /**
     * Returns source files that may contain translation key usage.
     *
     * Responsibility: Defines the repository scan boundary for i18n usage linting.
     * @return array<int, string>
     */
    private function sourceFiles(): array
    {
        $files = [];

        foreach (['app', 'boot-core', 'Repository/Framework', 'Repository/App'] as $root) {
            $base = PD . DS . str_replace('/', DS, $root);
            if (!is_dir($base)) {
                continue;
            }

            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base));
            foreach ($iterator as $entry) {
                if (!$entry instanceof SplFileInfo || !$entry->isFile()) {
                    continue;
                }

                $path = $entry->getPathname();
                $normalized = str_replace('\\', '/', $path);
                $extension = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));

                if (str_contains($normalized, '/vendor/') || str_contains($normalized, '/lang/')) {
                    continue;
                }

                if (in_array($extension, ['php', 'phtml', 'js'], true)) {
                    $files[] = $path;
                }
            }
        }

        sort($files);

        return $files;
    }

    /**
     * Checks whether a flattened catalog contains children for a dynamic prefix.
     *
     * Responsibility: Allows dynamic translation groups without requiring every runtime-expanded child key to appear in source.
     * @param array<string, true> $catalog
     */
    private function catalogHasPrefix(array $catalog, string $prefix): bool
    {
        foreach ($catalog as $key => $_) {
            if (str_starts_with($key, $prefix)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Converts an absolute path to a stable project-relative path.
     *
     * Responsibility: Normalizes lint output paths so reports remain portable across workstations.
     */
    private function relativePath(string $path): string
    {
        $relative = str_replace('\\', '/', str_replace(PD . DS, '', $path));

        return ltrim($relative, '/');
    }
}