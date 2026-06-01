<?php

declare(strict_types=1);

namespace Catalyst\Framework\Metadata;

use Catalyst\Framework\Traits\SingletonTrait;

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
     * @return array<string, array<string, string>>
     */
    public function all(): array
    {
        ksort($this->resources);

        return $this->resources;
    }

    /**
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

    public function exists(string $key): bool
    {
        return $this->find($key) !== null;
    }

    private function normalizeKey(string $key): string
    {
        return trim(strtolower($key));
    }

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
