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

use Catalyst\Framework\Traits\SingletonTrait;
use DateTimeInterface;
use DateTime;
use InvalidArgumentException;

/**
 * Translator — i18n / Translation system for Catalyst Framework.
 *
 * Features:
 * - Dot-notation keys: '{group}.{subkey}' maps to '{group}.json' → 'subkey'
 * - Multi-locale: loads JSON files from boot-core/lang/{locale}/ and module lang/ dirs
 * - Lazy loading: files loaded on first access, cached in memory for the request
 * - Placeholder replacement: :key syntax (__('validation.required', ['field' => 'Email']))
 * - Fallback chain: requested locale → default locale → key itself (no exception)
 * - User preference: locale stored in session ($_SESSION['__catalyst_locale'])
 * - Select options: getList() returns array values for building <select> inputs
 * - Date formatting: formatDate() applies locale-aware format patterns and name translations
 *
 * Initialization (Kernel::bootstrap, after SessionManager::init):
 *   Translator::getInstance()->init(GET_ENV_VAR['APP_LANG'] ?? 'en', PD . DS . 'boot-core' . DS . 'lang');
 *
 * Module registration (module routes.php):
 *   Translator::getInstance()->addPath(PD . DS . 'Repository' . DS . 'Framework' . DS . 'DevTools' . DS . 'lang');
 *
 * @package Catalyst\Helpers\I18n
 * Responsibility: Resolves localized strings, lists and dates across global and module paths.
 */
class Translator
{
    use SingletonTrait;

    /**
     * Default locale from .env APP_LANG (fallback when no session preference)
     */
    private string $defaultLocale = 'en';

    /**
     * Ordered list of lang/ directories.
     * Global path is added first; module paths are appended via addPath().
     * Later paths override earlier ones on key collision.
     *
     * @var string[]
     */
    private array $paths = [];

    /**
     * File loader (handles JSON reading, flattening, and in-memory caching)
     */
    private TranslationLoader $loader;

    /**
     * Initializes the Translator instance.
     *
     * Responsibility: Initializes the Translator instance.
     */
    protected function __construct()
    {
        $this->loader = new TranslationLoader();
    }

    // -------------------------------------------------------------------------
    // Initialization & configuration
    // -------------------------------------------------------------------------

    /**
     * Initialize the translation system. Must be called once in Kernel::bootstrap(), after SessionManager::init() so that getLocale() can read the session.
     *
     * Responsibility: Initialize the translation system. Must be called once in Kernel::bootstrap(), after SessionManager::init() so that getLocale() can read the session.
     * @param string $defaultLocale Language code from .env APP_LANG (e.g. 'en')
     * @param string $globalLangPath Absolute path to boot-core/lang/
     */
    public function init(string $defaultLocale, string $globalLangPath): void
    {
        $this->defaultLocale = $defaultLocale;
        $this->paths         = [$globalLangPath];
    }

    /**
     * Register an additional lang/ directory (called from module routes.php). Paths registered later override earlier ones on key collision, so modules can override global translations for their own group files.
     *
     * Responsibility: Register an additional lang/ directory (called from module routes.php). Paths registered later override earlier ones on key collision, so modules can override global translations for their own group files.
     * @param string $path Absolute path to a lang/ directory
     */
    public function addPath(string $path): void
    {
        if (!in_array($path, $this->paths, true)) {
            $this->paths[] = $path;
            $this->clearCache();
        }
    }

    // -------------------------------------------------------------------------
    // Locale management
    // -------------------------------------------------------------------------

    /**
     * Set the user's locale preference (stored in session). Does not require the user to be logged in. The locale persists for the session duration regardless of authentication state.
     *
     * Responsibility: Stores the session locale preference used to resolve translations for the active visitor.
     * @param string $locale Language code (e.g. 'en', 'es')
     */
    public function setLocale(string $locale): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['__catalyst_locale'] = $locale;
        }
    }

    /**
     * Get the current effective locale. Resolution order: session → default locale from .env.
     *
     * Responsibility: Resolves the effective locale from the session preference and configured application default.
     * @return string Language code
     */
    public function getLocale(): string
    {
        if (
            session_status() === PHP_SESSION_ACTIVE
            && isset($_SESSION['__catalyst_locale'])
            && is_string($_SESSION['__catalyst_locale'])
            && $_SESSION['__catalyst_locale'] !== ''
        ) {
            return $_SESSION['__catalyst_locale'];
        }

        return $this->defaultLocale;
    }

    /**
     * Get the default locale (from .env APP_LANG).
     *
     * Responsibility: Exposes the configured fallback locale used when no session preference exists.
     * @return string Language code
     */
    public function getDefaultLocale(): string
    {
        return $this->defaultLocale;
    }

    // -------------------------------------------------------------------------
    // String translation
    // -------------------------------------------------------------------------

    /**
     * Get a translated string by dot-notation key. Key format: '{group}.{subkey}' where group = JSON file name. 'validation.required' → loads validation.json → key 'required' 'messages.save_success' → loads messages.json → key 'save_success' 'dates.formats.default' → loads dates.json → nested 'formats.default' Fallback chain: 1. Requested locale 2. Default locale (if different from requested) 3. The key itself (no exception thrown).
     *
     * Responsibility: Get a translated string by dot-notation key. Key format: '{group}.{subkey}' where group = JSON file name. 'validation.required' → loads validation.json → key 'required' 'messages.save_success' → loads messages.json → key 'save_success' 'dates.formats.default' → loads dates.json → nested 'formats.default' Fallback chain: 1. Requested locale 2. Default locale (if different from requested) 3. The key itself (no exception thrown).
     * @param string      $key          Dot-notation key
     * @param array<string, scalar> $replacements Placeholder values (['field' => 'Email'])
     * @param string|null $locale       Override locale for this single call
     * @return string Translated string, or $key if not found
     */
    public function get(string $key, array $replacements = [], ?string $locale = null): string
    {
        $locale ??= $this->getLocale();

        $value = $this->resolveValue($key, $locale);

        if ($value === null && $locale !== $this->defaultLocale) {
            $value = $this->resolveValue($key, $this->defaultLocale);
        }

        if ($value === null || is_array($value)) {
            return $key;
        }

        return $this->applyReplacements($value, $replacements);
    }

    /**
     * Check if a translation key exists for the given (or current) locale.
     *
     * Responsibility: Check if a translation key exists for the given (or current) locale.
     * @param string      $key
     * @param string|null $locale
     * @return bool
     */
    public function has(string $key, ?string $locale = null): bool
    {
        $locale ??= $this->getLocale();
        return $this->resolveValue($key, $locale) !== null;
    }

    // -------------------------------------------------------------------------
    // List / select options support
    // -------------------------------------------------------------------------

    /**
     * Get an array value for use as select options or enumeration lists. Only returns entries where the value is a string (skips nested arrays), making it safe to pass directly to a <select> builder. JSON example (form.json): "gender_options": { "male": "Male", "female": "Female", "other": "Other" } "gender_default": "male" Usage: $options = Translator::getInstance()->getList('form.gender_options'); // → ['male' => 'Male', 'female' => 'Female', 'other' => 'Other'] $default = __('form.gender_default'); // → 'male'.
     *
     * Responsibility: Get an array value for use as select options or enumeration lists. Only returns entries where the value is a string (skips nested arrays), making it safe to pass directly to a <select> builder. JSON example (form.json): "gender_options": { "male": "Male", "female": "Female", "other": "Other" } "gender_default": "male" Usage: $options = Translator::getInstance()->getList('form.gender_options'); // → ['male' => 'Male', 'female' => 'Female', 'other' => 'Other'] $default = __('form.gender_default'); // → 'male'.
     * @param string      $key    Dot-notation key pointing to a JSON object
     * @param string|null $locale Override locale for this call
     * @return array<string, string> String-valued entries only
     */
    public function getList(string $key, ?string $locale = null): array
    {
        $locale ??= $this->getLocale();

        $value = $this->resolveValue($key, $locale);

        if ($value === null && $locale !== $this->defaultLocale) {
            $value = $this->resolveValue($key, $this->defaultLocale);
        }

        if (!is_array($value)) {
            return [];
        }

        return array_filter($value, fn($v) => is_string($v));
    }

    // -------------------------------------------------------------------------
    // Date formatting
    // -------------------------------------------------------------------------

    /**
     * Format a date using a locale-aware format pattern. Format patterns are defined in dates.json under 'formats.*'. Month and day names are translated using the dates.json name tables. Built-in format keys (defined in boot-core/lang/{locale}/dates.json): 'default' → locale default (e.g. 'm/d/Y' for en, 'd/m/Y' for es) 'long' → full month name (e.g. 'January 15, 2026') 'short' → abbreviated (e.g. '01/15/26') 'full' → day name + full date 'time' → time only 'datetime' → date + time 'iso' → Y-m-d (locale-independent) You can also pass a literal PHP date format string as $format (e.g. 'Y-m-d', 'd/m/Y H:i') when no translation key is needed.
     *
     * Responsibility: Format a date using a locale-aware format pattern. Format patterns are defined in dates.json under 'formats.*'. Month and day names are translated using the dates.json name tables. Built-in format keys (defined in boot-core/lang/{locale}/dates.json): 'default' → locale default (e.g. 'm/d/Y' for en, 'd/m/Y' for es) 'long' → full month name (e.g. 'January 15, 2026') 'short' → abbreviated (e.g. '01/15/26') 'full' → day name + full date 'time' → time only 'datetime' → date + time 'iso' → Y-m-d (locale-independent) You can also pass a literal PHP date format string as $format (e.g. 'Y-m-d', 'd/m/Y H:i') when no translation key is needed.
     * @param DateTimeInterface|string|int $date    DateTime, date string, or Unix timestamp
     * @param string                       $format  Named format key or PHP date() format string
     * @param string|null                  $locale  Override locale for this call
     * @return string Formatted, localized date string
     */
    public function formatDate(
        DateTimeInterface|string|int $date,
        string $format = 'default',
        ?string $locale = null
    ): string {
        $locale ??= $this->getLocale();
        $dt      = $this->toDateTime($date);

        // Resolve format pattern: try translation key first, fall back to literal
        $formatKey     = 'dates.formats.' . $format;
        $formatPattern = $this->get($formatKey, [], $locale);

        // If the key was not found, get() returns the key itself — use $format as literal pattern
        if ($formatPattern === $formatKey) {
            $formatPattern = $format;
        }

        // Format with PHP's date engine (outputs English month/day names by default)
        $formatted = $dt->format($formatPattern);

        // Translate full month name (F → "January") if present in output
        $englishFullMonth = $dt->format('F');
        if (str_contains($formatted, $englishFullMonth)) {
            $translated = $this->get('dates.months.' . $dt->format('n'), [], $locale);
            if ($translated !== 'dates.months.' . $dt->format('n')) {
                $formatted = str_replace($englishFullMonth, $translated, $formatted);
            }
        } else {
            // Translate short month name (M → "Jan") only if full not present
            $englishShortMonth = $dt->format('M');
            if (str_contains($formatted, $englishShortMonth)) {
                $translated = $this->get('dates.months_short.' . $dt->format('n'), [], $locale);
                if ($translated !== 'dates.months_short.' . $dt->format('n')) {
                    $formatted = str_replace($englishShortMonth, $translated, $formatted);
                }
            }
        }

        // Translate full day name (l → "Wednesday") if present in output
        $englishFullDay = $dt->format('l');
        if (str_contains($formatted, $englishFullDay)) {
            $translated = $this->get('dates.days.' . $dt->format('w'), [], $locale);
            if ($translated !== 'dates.days.' . $dt->format('w')) {
                $formatted = str_replace($englishFullDay, $translated, $formatted);
            }
        } else {
            // Translate short day name (D → "Wed") only if full not present
            $englishShortDay = $dt->format('D');
            if (str_contains($formatted, $englishShortDay)) {
                $translated = $this->get('dates.days_short.' . $dt->format('w'), [], $locale);
                if ($translated !== 'dates.days_short.' . $dt->format('w')) {
                    $formatted = str_replace($englishShortDay, $translated, $formatted);
                }
            }
        }

        return $formatted;
    }

    // -------------------------------------------------------------------------
    // Cache management
    // -------------------------------------------------------------------------

    /**
     * Invalidate loaded translation cache.
     *
     * Responsibility: Invalidate loaded translation cache.
     * @param string|null $locale Clear only this locale (null = all)
     * @param string|null $group  Clear only this group within the locale (null = all)
     */
    public function clearCache(?string $locale = null, ?string $group = null): void
    {
        $this->loader->clearCache($locale, $group);
    }

    // -------------------------------------------------------------------------
    // Internal helpers
    // -------------------------------------------------------------------------

    /**
     * Parse a dot-notation key into [group, subkey]. 'validation.required' → ['validation', 'required'] 'dates.formats.default' → ['dates', 'formats.default'] 'standalone' → ['standalone', ''].
     *
     * Responsibility: Parse a dot-notation key into [group, subkey]. 'validation.required' → ['validation', 'required'] 'dates.formats.default' → ['dates', 'formats.default'] 'standalone' → ['standalone', ''].
     * @param string $key
     * @return array{0: string, 1: string}
     */
    private function parseKey(string $key): array
    {
        $dotPos = strpos($key, '.');
        if ($dotPos === false) {
            return [$key, ''];
        }
        return [substr($key, 0, $dotPos), substr($key, $dotPos + 1)];
    }

    /**
     * Load the group for a locale and resolve the subkey.
     *
     * Responsibility: Load the group for a locale and resolve the subkey.
     * @param string $key    Full dot-notation key
     * @param string $locale Language code
     * @return string|array<string, mixed>|null
     */
    private function resolveValue(string $key, string $locale): string|array|null
    {
        [$group, $subkey] = $this->parseKey($key);

        if ($subkey === '') {
            return null;
        }

        $translations = $this->loader->load($group, $locale, $this->paths);

        return $this->loader->resolve($subkey, $translations);
    }

    /**
     * Replace :placeholder tokens in a translated string.
     *
     * Responsibility: Replace :placeholder tokens in a translated string.
     * @param string                $value        Template with :placeholders
     * @param array<string, scalar> $replacements ['field' => 'Email', 'min' => 8]
     * @return string
     */
    private function applyReplacements(string $value, array $replacements): string
    {
        foreach ($replacements as $placeholder => $replacement) {
            $value = str_replace(':' . $placeholder, (string)$replacement, $value);
        }
        return $value;
    }

    /**
     * Normalize input to a DateTimeInterface.
     *
     * Responsibility: Normalize input to a DateTimeInterface.
     * @param DateTimeInterface|string|int $date
     * @return DateTimeInterface
     */
    private function toDateTime(DateTimeInterface|string|int $date): DateTimeInterface
    {
        if ($date instanceof DateTimeInterface) {
            return $date;
        }

        if (is_int($date)) {
            return new DateTime('@' . $date);
        }

        return new DateTime($date);
    }
}
