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

namespace Catalyst\Framework\Metadata;

use Catalyst\Framework\Traits\SingletonTrait;

/**
 * Registry of resources that support dynamic metadata fields.
 *
 * @package Catalyst\Framework\Metadata
 * Responsibility: Register and resolve metadata-enabled resource definitions.
 */
final class MetadataResourceRegistry
{
    use SingletonTrait;

    /**
     * @var array<string, array<string, string>>
     */
    private const BASE_RESOURCES = [
        'media-library' => [
            'label' => 'media.module.library_navigation_label',
            'description' => 'media.library.index.description',
            'route' => '/workspaces/media-library',
            'fields_route' => '/workspaces/media-fields',
        ],
    ];

    /**
     * @var array<string, array<string, string>>
     */
    private array $resources = self::BASE_RESOURCES;

    /**
     * Return all registered metadata resources sorted by key.
     *
     * Responsibility: Return all registered metadata resources sorted by key.
     * @return array<string, array<string, string>>
     */
    public function all(): array
    {
        ksort($this->resources);

        return $this->resources;
    }

    /**
     * Return resource option labels keyed by resource key.
     *
     * Responsibility: Return resource option labels keyed by resource key.
     * @return array<string, string>
     */
    public function options(): array
    {
        $options = [];

        foreach ($this->all() as $key => $definition) {
            $options[$key] = $this->resolveDefinitionValue($definition, 'label', $key);
        }

        return $options;
    }

    /**
     * Register or replace one metadata resource definition.
     *
     * Responsibility: Register or replace one metadata resource definition.
     * @param array<string, string> $definition
     */
    public function register(string $key, array $definition): void
    {
        $normalized = $this->normalizeKey($key);

        if ($normalized === '') {
            return;
        }

        $this->resources[$normalized] = [
            'label' => trim((string) ($definition['label'] ?? $normalized)),
            'description' => trim((string) ($definition['description'] ?? '')),
            'route' => trim((string) ($definition['route'] ?? '')),
            'fields_route' => trim((string) ($definition['fields_route'] ?? '')),
        ];
    }

    /**
     * Resolve one registered resource definition with translated labels.
     *
     * Responsibility: Resolve one registered resource definition with translated labels.
     * @return array<string, string>|null
     */
    public function find(string $key): ?array
    {
        $normalized = $this->normalizeKey($key);

        $definition = $this->resources[$normalized] ?? null;

        if ($definition === null) {
            return null;
        }

        $definition['label'] = $this->resolveDefinitionValue($definition, 'label', $normalized);
        $definition['description'] = $this->resolveDefinitionValue($definition, 'description');

        return $definition;
    }

    /**
     * Determine whether a metadata resource key is registered.
     *
     * Responsibility: Determine whether a metadata resource key is registered.
     */
    public function exists(string $key): bool
    {
        return $this->find($key) !== null;
    }

    /**
     * Normalize a metadata resource key.
     *
     * Responsibility: Normalize a metadata resource key.
     */
    private function normalizeKey(string $key): string
    {
        return trim(strtolower($key));
    }

    /**
     * Resolve a translated definition field with fallback.
     *
     * Responsibility: Resolve a translated definition field with fallback.
     */
    private function resolveDefinitionValue(array $definition, string $field, string $fallback = ''): string
    {
        $value = trim((string) ($definition[$field] ?? ''));

        if ($value === '') {
            return $fallback;
        }

        if (str_contains($value, '.')) {
            $translated = __($value);

            if ($translated !== $value) {
                return $translated;
            }
        }

        return $value;
    }
}
