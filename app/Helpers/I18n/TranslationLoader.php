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

namespace Catalyst\Helpers\I18n;

/**
 * TranslationLoader — file loading layer for the i18n system.
 *
 * Responsible for:
 * - Loading JSON translation files from registered lang/ directories
 * - Merging multiple paths (later paths override earlier ones)
 * - Flattening nested JSON to dot-notation keys
 * - In-memory cache to avoid re-reading files within the same request
 *
 * Key format after flatten():
 *   "validation.required"    → string value
 *   "form.gender_options"    → array value (for getList())
 *   "form.gender_options.male" → string value
 *
 * @package Catalyst\Helpers\I18n
 * Responsibility: Loads, flattens, merges and caches locale translation groups.
 */
class TranslationLoader
{
    /**
     * In-memory cache: "{locale}.{group}" → flat translations array
     *
     * @var array<string, array<string, mixed>>
     */
    private array $cache = [];

    /**
     * Load and merge translations for a group across all registered paths. Files are merged in order: later paths override earlier ones. This allows module lang files to override global ones when needed.
     *
     * Responsibility: Load and merge translations for a group across all registered paths. Files are merged in order: later paths override earlier ones. This allows module lang files to override global ones when needed.
     * @param string   $group  JSON file name without extension (e.g. 'validation')
     * @param string   $locale Language code (e.g. 'en', 'es')
     * @param string[] $paths  Ordered list of lang/ base directories
     * @return array<string, mixed> Flat dot-notation array (values may be string or array)
     */
    public function load(string $group, string $locale, array $paths): array
    {
        $cacheKey = $locale . '.' . $group;

        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        $merged = [];

        foreach ($paths as $basePath) {
            $file = $basePath . DIRECTORY_SEPARATOR . $locale . DIRECTORY_SEPARATOR . $group . '.json';

            if (!file_exists($file)) {
                continue;
            }

            $content = file_get_contents($file);
            if ($content === false) {
                continue;
            }

            $data = json_decode($content, true);
            if (!is_array($data)) {
                continue;
            }

            // Later paths override earlier ones (array_merge: later keys win)
            $merged = array_merge($merged, $this->flatten($data));
        }

        $this->cache[$cacheKey] = $merged;

        return $merged;
    }

    /**
     * Flatten a nested array to dot-notation keys. For each node that is an array, BOTH the array value (at the parent key) AND all its children (recursively) are stored. This allows: - get('form.gender_options.male') → string - getList('form.gender_options') → array.
     *
     * Responsibility: Flatten a nested array to dot-notation keys. For each node that is an array, BOTH the array value (at the parent key) AND all its children (recursively) are stored. This allows: - get('form.gender_options.male') → string - getList('form.gender_options') → array.
     * @param array<string, mixed> $array  Input (possibly nested)
     * @param string               $prefix Current prefix for recursion
     * @return array<string, mixed>
     */
    public function flatten(array $array, string $prefix = ''): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            $fullKey = $prefix !== '' ? $prefix . '.' . $key : (string)$key;

            if (is_array($value)) {
                // Store the array itself (enables getList() on this key)
                $result[$fullKey] = $value;
                // Also recurse so leaf strings are accessible via dot-notation
                $result += $this->flatten($value, $fullKey);
            } else {
                $result[$fullKey] = $value;
            }
        }

        return $result;
    }

    /**
     * Resolve a subkey within a flat translations array.
     *
     * Responsibility: Resolve a subkey within a flat translations array.
     * @param string               $subkey       Dot-notation key (e.g. 'required', 'formats.default')
     * @param array<string, mixed> $translations Flat translations for a group
     * @return string|array<string, mixed>|null  Found value, or null if not found
     */
    public function resolve(string $subkey, array $translations): string|array|null
    {
        $value = $translations[$subkey] ?? null;
        if ($value === null) {
            return null;
        }
        return is_string($value) || is_array($value) ? $value : null;
    }

    /**
     * Invalidate cache entries.
     *
     * Responsibility: Invalidate cache entries.
     * @param string|null $locale Clear only this locale (null = all)
     * @param string|null $group  Clear only this group (null = all for the locale)
     */
    public function clearCache(?string $locale = null, ?string $group = null): void
    {
        if ($locale === null) {
            $this->cache = [];
            return;
        }

        if ($group === null) {
            foreach (array_keys($this->cache) as $key) {
                if (str_starts_with($key, $locale . '.')) {
                    unset($this->cache[$key]);
                }
            }
            return;
        }

        unset($this->cache[$locale . '.' . $group]);
    }
}
