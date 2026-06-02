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

namespace Catalyst\Framework\Cli;

use InvalidArgumentException;
use RuntimeException;

/**
 * Scaffolding helper for CLI generators.
 *
 * Responsibility: Normalizes names, renders stubs and writes generated framework artifacts.
 *
 * @package Catalyst\Framework\Cli
 */
class ScaffoldManager
{
    private string $stubPath;

    /**
     * Initializes dependencies required by this CLI component.
     *
     * Responsibility: Initializes dependencies required by this CLI component.
     */
    public function __construct(?string $stubPath = null)
    {
        $this->stubPath = $stubPath ?? implode(DS, [PD, 'app', 'Framework', 'Cli', 'Stubs']);
    }

    /**
     * Normalizes generated class names and enforces the expected suffix.
     *
     * Responsibility: Normalizes generated class names and enforces the expected suffix.
     */
    public function normalizeClassName(string $name, string $suffix = ''): string
    {
        $name = trim($name);
        $name = preg_replace('/\.php$/i', '', $name) ?? $name;

        if ($name === '') {
            throw new InvalidArgumentException('The class name cannot be empty.');
        }

        $normalized = $this->normalizeSegment($name, 'class name');

        if ($suffix !== '' && !str_ends_with($normalized, $suffix)) {
            $normalized .= $suffix;
        }

        return $normalized;
    }

    /**
     * Normalizes an application surface module name for generated artifacts.
     *
     * Responsibility: Normalizes an application surface module name for generated artifacts.
     */
    public function normalizeAppModule(string $module): string
    {
        $module = trim(str_replace('\\', '/', $module), " \t\n\r\0\x0B/");

        if ($module === '') {
            throw new InvalidArgumentException('The module name cannot be empty.');
        }

        $segments = array_values(array_filter(explode('/', $module), static fn (string $segment): bool => $segment !== ''));

        if (count($segments) === 2 && strcasecmp($segments[0], 'App') === 0) {
            $segments = [$segments[1]];
        }

        if (count($segments) !== 1) {
            throw new InvalidArgumentException('The module must be a single App module (e.g. Catalog or App/Catalog).');
        }

        return $this->normalizeSegment($segments[0], 'module name');
    }

    /**
     * Normalizes a module name into namespace-safe segments.
     *
     * Responsibility: Normalizes a module name into namespace-safe segments.
     */
    public function normalizeModuleName(string $module): string
    {
        $module = trim(str_replace('\\', '/', $module), " \t\n\r\0\x0B/");

        if ($module === '') {
            throw new InvalidArgumentException('The module name cannot be empty.');
        }

        $segments = array_values(array_filter(explode('/', $module), static fn (string $segment): bool => $segment !== ''));

        if (count($segments) !== 1) {
            throw new InvalidArgumentException('The module name must be a single segment (e.g. Catalog).');
        }

        return $this->normalizeSegment($segments[0], 'module name');
    }

    /**
     * Normalizes the target repository space for module scaffolding.
     *
     * Responsibility: Normalizes the target repository space for module scaffolding.
     */
    public function normalizeSpace(?string $space): string
    {
        $space = trim((string) $space);

        if ($space === '') {
            return 'App';
        }

        $normalized = ucfirst(strtolower($space));

        if (!in_array($normalized, ['App', 'Framework'], true)) {
            throw new InvalidArgumentException('The --space option only accepts App or Framework.');
        }

        return $normalized;
    }

    /**
     * Renders a named stub with resolved template variables.
     *
     * Responsibility: Renders a named stub with resolved template variables.
     */
    public function renderStub(string $stubName, array $variables): string
    {
        $path = $this->stubPath . DS . $stubName;

        if (!is_file($path)) {
            throw new RuntimeException('Stub file not found: ' . $path);
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new RuntimeException('Unable to read stub file: ' . $path);
        }

        $replacements = [];
        foreach ($variables as $key => $value) {
            $replacements['{{' . $key . '}}'] = (string) $value;
        }

        return strtr($contents, $replacements);
    }

    /**
     * Creates a target directory when it does not already exist.
     *
     * Responsibility: Creates a target directory when it does not already exist.
     */
    public function ensureDirectory(string $directory): void
    {
        if (is_dir($directory)) {
            return;
        }

        if (!mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new RuntimeException('Cannot create directory: ' . $directory);
        }
    }

    /**
     * Writes generated file contents after guarding against existing paths.
     *
     * Responsibility: Writes generated file contents after guarding against existing paths.
     */
    public function writeFile(string $path, string $contents): void
    {
        $directory = dirname($path);
        $this->ensureDirectory($directory);

        if (file_exists($path)) {
            throw new RuntimeException('File already exists: ' . $path);
        }

        if (file_put_contents($path, $contents) === false) {
            throw new RuntimeException('Failed to write file: ' . $path);
        }
    }

    /**
     * Builds the base directory path for a generated module.
     *
     * Responsibility: Builds the base directory path for a generated module.
     */
    public function moduleBaseDirectory(string $space, string $module): string
    {
        if ($space === 'Framework') {
            return implode(DS, [PD, 'Repository', 'Framework', $module]);
        }

        return implode(DS, [PD, 'Repository', 'App', 'Surface', $module]);
    }

    /**
     * Builds the namespace root for a generated module.
     *
     * Responsibility: Builds the namespace root for a generated module.
     */
    public function moduleNamespaceRoot(string $space, string $module): string
    {
        if ($space === 'Framework') {
            return 'Catalyst\\Repository\\' . $module;
        }

        return 'App\\Surface\\' . $module;
    }

    /**
     * Returns the repository directory for a normalized space.
     *
     * Responsibility: Returns the repository directory for a normalized space.
     */
    public function spaceDirectory(string $space): string
    {
        return $space === 'Framework' ? 'Framework' : 'App';
    }

    /**
     * Builds the view namespace used by a generated module.
     *
     * Responsibility: Builds the view namespace used by a generated module.
     */
    public function moduleViewNamespace(string $module): string
    {
        return strtolower($module);
    }

    /**
     * Builds the default route URI prefix for a generated module.
     *
     * Responsibility: Builds the default route URI prefix for a generated module.
     */
    public function moduleRouteUri(string $module): string
    {
        return $this->toKebabCase($module);
    }

    /**
     * Derives the default table name for a generated model class.
     *
     * Responsibility: Derives the default table name for a generated model class.
     */
    public function defaultTableName(string $className): string
    {
        $baseName = preg_replace('/Model$/', '', $className) ?: $className;
        $snake    = $this->toSnakeCase($baseName);

        return $this->pluralize($snake);
    }

    /**
     * Normalizes one namespace or path segment for scaffolding.
     *
     * Responsibility: Normalizes one namespace or path segment for scaffolding.
     */
    private function normalizeSegment(string $value, string $label): string
    {
        $value = trim($value);
        $value = preg_replace('/[^A-Za-z0-9]+/', ' ', $value) ?? $value;

        $parts = preg_split('/\s+/', $value, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $parts = array_map(
            static function (string $part): string {
                return ucfirst($part);
            },
            $parts
        );

        $value = implode('', $parts);

        if ($value === '' || !preg_match('/^[A-Z][A-Za-z0-9]*$/', $value)) {
            throw new InvalidArgumentException('Invalid ' . $label . '. Use letters and numbers only.');
        }

        return $value;
    }

    /**
     * Converts a name to snake_case.
     *
     * Responsibility: Converts a name to snake_case.
     */
    private function toSnakeCase(string $value): string
    {
        $snake = preg_replace('/(?<!^)[A-Z]/', '_$0', $value) ?? $value;

        return strtolower($snake);
    }

    /**
     * Converts a name to kebab-case.
     *
     * Responsibility: Converts a name to kebab-case.
     */
    private function toKebabCase(string $value): string
    {
        $kebab = preg_replace('/(?<!^)[A-Z]/', '-$0', $value) ?? $value;

        return strtolower($kebab);
    }

    /**
     * Applies the framework default plural form for generated table names.
     *
     * Responsibility: Applies the framework default plural form for generated table names.
     */
    private function pluralize(string $value): string
    {
        if (preg_match('/[^aeiou]y$/i', $value) === 1) {
            return substr($value, 0, -1) . 'ies';
        }

        if (preg_match('/(s|x|z|ch|sh)$/i', $value) === 1) {
            return $value . 'es';
        }

        return $value . 's';
    }
}
