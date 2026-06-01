<?php

declare(strict_types=1);

namespace Catalyst\Framework\Metadata;

use Catalyst\Framework\Catalog\CatalogManager;
use Catalyst\Framework\Catalog\CatalogRepository;
use Catalyst\Framework\Database\DatabaseManager;
use Catalyst\Framework\Tenancy\TenancyManager;
use Catalyst\Framework\Traits\SingletonTrait;

final class MetadataManager
{
    use SingletonTrait;

    public const MEDIA_LIBRARY_RESOURCE = 'media-library';
    public const INPUT_PREFIX = 'meta__';
    public const FILTER_PREFIX = 'meta_filter__';

    private MetadataFieldRepository $fields;
    private MetadataValueRepository $values;
    private MetadataResourceRegistry $resources;
    private CatalogRepository $catalogs;
    private DatabaseManager $db;

    protected function __construct()
    {
        $this->fields = MetadataFieldRepository::getInstance();
        $this->values = MetadataValueRepository::getInstance();
        $this->resources = MetadataResourceRegistry::getInstance();
        $this->catalogs = CatalogRepository::getInstance();
        $this->db = DatabaseManager::getInstance();
    }

    /**
     * @return array<string, string>
     */
    public function supportedTypes(): array
    {
        return [
            'text' => __('ui.metadata.types.text'),
            'textarea' => __('ui.metadata.types.textarea'),
            'number' => __('ui.metadata.types.number'),
            'boolean' => __('ui.metadata.types.boolean'),
            'select' => __('ui.metadata.types.select'),
            'catalog' => __('ui.metadata.types.catalog'),
            'date' => __('ui.metadata.types.date'),
            'datetime' => __('ui.metadata.types.datetime'),
            'media' => __('ui.metadata.types.media'),
        ];
    }

    public function supportsResource(string $resourceKey): bool
    {
        return $this->resources->exists($resourceKey);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function definitionsFor(string $resourceKey): array
    {
        return $this->fields->activeForResource($resourceKey);
    }

    /**
     * @param array<int, array<string, mixed>> $definitions
     * @param array<string, mixed> $currentValues
     * @return array<int|string, array<string, mixed>>
     */
    public function formSections(array $definitions, array $currentValues = []): array
    {
        $sections = [];
        $grouped = [];

        foreach ($definitions as $definition) {
            $sectionKey = trim((string) ($definition['section_key'] ?? ''));
            if ($sectionKey === '') {
                $sectionKey = 'extended-metadata';
            }

            $grouped[$sectionKey][] = $definition;
        }

        foreach ($grouped as $sectionKey => $items) {
            $sections[$sectionKey] = [
                'title' => $sectionKey === 'extended-metadata'
                    ? __('ui.metadata.sections.extended.title')
                    : $this->humanize($sectionKey),
                'description' => $sectionKey === 'extended-metadata'
                    ? __('ui.metadata.sections.extended.description')
                    : '',
            ];
        }

        return $sections;
    }

    /**
     * @param array<int, array<string, mixed>> $definitions
     * @param array<string, mixed> $currentValues
     * @return array<string, array<string, mixed>>
     */
    public function formFields(array $definitions, array $currentValues = []): array
    {
        $fields = [];

        foreach ($definitions as $definition) {
            $fieldKey = (string) ($definition['field_key'] ?? '');
            if ($fieldKey === '') {
                continue;
            }

            $type = (string) ($definition['type'] ?? 'text');
            $inputKey = self::inputKey($fieldKey);
            $currentValue = $currentValues[$fieldKey]['value'] ?? $this->defaultValue($definition);
            $sectionKey = trim((string) ($definition['section_key'] ?? ''));
            if ($sectionKey === '') {
                $sectionKey = 'extended-metadata';
            }

            $fields[$inputKey] = [
                'label' => (string) ($definition['label'] ?? $this->humanize($fieldKey)),
                'type' => $this->formFieldType($type),
                'required' => (bool) ($definition['is_required'] ?? false),
                'section' => $sectionKey,
                'help' => (string) ($definition['help_text'] ?? ''),
                'placeholder' => (string) ($definition['placeholder'] ?? ''),
                'options' => $this->fieldOptions($definition, $currentValue),
                'empty_option_label' => in_array($type, ['select', 'catalog', 'media'], true)
                    ? __('ui.metadata.empty_option_label')
                    : '',
                'value' => $currentValue,
                'attributes' => $this->fieldAttributes($definition),
            ];
        }

        return $fields;
    }

    /**
     * @return array<string, string>
     */
    public function validationRules(string $resourceKey): array
    {
        $rules = [];

        foreach ($this->definitionsFor($resourceKey) as $definition) {
            $fieldKey = (string) ($definition['field_key'] ?? '');
            if ($fieldKey === '') {
                continue;
            }

            $inputKey = self::inputKey($fieldKey);
            $parts = [];

            if ((bool) ($definition['is_required'] ?? false)) {
                $parts[] = 'required';
            }

            $parts = array_merge($parts, $this->typeRules($definition));

            $extra = trim((string) ($definition['rules_extra'] ?? ''));
            if ($extra !== '') {
                $parts[] = $extra;
            }

            $rules[$inputKey] = implode('|', array_values(array_filter($parts, static fn (string $part): bool => trim($part) !== '')));
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function validationLabels(string $resourceKey): array
    {
        $labels = [];

        foreach ($this->definitionsFor($resourceKey) as $definition) {
            $fieldKey = (string) ($definition['field_key'] ?? '');
            if ($fieldKey === '') {
                continue;
            }

            $labels[self::inputKey($fieldKey)] = (string) ($definition['label'] ?? $this->humanize($fieldKey));
        }

        return $labels;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, string[]>
     */
    public function validateFieldPayload(string $resourceKey, array $data): array
    {
        $errors = [];

        foreach ($this->definitionsFor($resourceKey) as $definition) {
            $inputKey = self::inputKey((string) ($definition['field_key'] ?? ''));
            $type = (string) ($definition['type'] ?? 'text');

            if ($type === 'media') {
                $mediaId = (int) ($data[$inputKey] ?? 0);
                if ($mediaId <= 0) {
                    continue;
                }

                if ($this->mediaLabel($mediaId) === null) {
                    $errors[$inputKey][] = __('ui.metadata.validation.media_not_available');
                }

                continue;
            }

            if ($type === 'catalog') {
                $value = trim((string) ($data[$inputKey] ?? ''));
                if ($value === '') {
                    continue;
                }

                $catalogKey = trim((string) ($definition['catalog_key'] ?? ''));
                if ($catalogKey === '' || !CatalogManager::getInstance()->hasOption($catalogKey, $value)) {
                    $errors[$inputKey][] = __('ui.metadata.validation.catalog_option_not_available');
                }
            }
        }

        return $errors;
    }

    /**
     * @param array<int, array<string, mixed>> $definitions
     * @return array<int, array<string, mixed>>
     */
    public function gridColumns(array $definitions): array
    {
        $columns = [];

        foreach ($definitions as $definition) {
            if (!(bool) ($definition['is_listed'] ?? false)) {
                continue;
            }

            $fieldKey = (string) ($definition['field_key'] ?? '');
            if ($fieldKey === '') {
                continue;
            }

            $columns[] = [
                'key' => self::gridColumnKey($fieldKey),
                'label' => (string) ($definition['label'] ?? $this->humanize($fieldKey)),
                'sortable' => false,
                'class' => 'small',
                'empty' => '—',
                'value' => static fn (array $row): string => (string) (($row['metadata_display'][$fieldKey] ?? '') ?: ''),
            ];
        }

        return $columns;
    }

    /**
     * @param array<int, array<string, mixed>> $definitions
     * @return array<int, array<string, mixed>>
     */
    public function gridFilters(array $definitions): array
    {
        $filters = [];

        foreach ($definitions as $definition) {
            if (!(bool) ($definition['is_filterable'] ?? false)) {
                continue;
            }

            $fieldKey = (string) ($definition['field_key'] ?? '');
            $type = (string) ($definition['type'] ?? 'text');
            if ($fieldKey === '' || !$this->supportsFilterType($type)) {
                continue;
            }

            $filter = [
                'name' => self::filterKey($fieldKey),
                'label' => (string) ($definition['label'] ?? $this->humanize($fieldKey)),
                'type' => 'text',
                'placeholder' => __('ui.metadata.filters.value'),
            ];

            if (in_array($type, ['select', 'catalog', 'boolean', 'media'], true)) {
                $filter['type'] = 'select';
                $filter['options'] = $this->filterOptions($definition);
            } elseif ($type === 'date') {
                $filter['placeholder'] = __('ui.metadata.filters.date');
            } elseif ($type === 'datetime') {
                $filter['placeholder'] = __('ui.metadata.filters.datetime');
            } elseif ($type === 'number') {
                $filter['placeholder'] = __('ui.metadata.filters.number');
            }

            $filters[] = $filter;
        }

        return $filters;
    }

    /**
     * @param array<string, mixed> $filters
     * @return array<string, string>
     */
    public function extractGridFilters(array $filters): array
    {
        $resolved = [];

        foreach ($filters as $key => $value) {
            if (!is_string($key) || !str_starts_with($key, self::FILTER_PREFIX)) {
                continue;
            }

            $fieldKey = substr($key, strlen(self::FILTER_PREFIX));
            $fieldKey = trim(strtolower($fieldKey));
            if ($fieldKey === '') {
                continue;
            }

            $resolved[$fieldKey] = trim((string) ($value ?? ''));
        }

        return $resolved;
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function normalizeDefinitionPayload(array $payload): array
    {
        $type = trim(strtolower((string) ($payload['type'] ?? 'text')));

        return [
            'resource_key' => trim(strtolower((string) ($payload['resource_key'] ?? ''))),
            'field_key' => trim(strtolower((string) ($payload['field_key'] ?? ''))),
            'label' => trim((string) ($payload['label'] ?? '')),
            'type' => $type,
            'section_key' => trim(strtolower((string) ($payload['section_key'] ?? ''))),
            'help_text' => $this->nullableString($payload['help_text'] ?? null),
            'placeholder' => $this->nullableString($payload['placeholder'] ?? null),
            'default_value' => $this->nullableString($payload['default_value'] ?? null),
            'options_json' => $type === 'select' ? $this->parseSelectOptions((string) ($payload['select_options'] ?? '')) : [],
            'catalog_key' => $type === 'catalog'
                ? $this->nullableLowerString($payload['catalog_key'] ?? null)
                : null,
            'rules_extra' => $this->nullableString($payload['rules_extra'] ?? null),
            'is_required' => filter_var($payload['is_required'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'is_filterable' => filter_var($payload['is_filterable'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'is_listed' => filter_var($payload['is_listed'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'sort_order' => max(0, (int) ($payload['sort_order'] ?? 0)),
            'max_length' => $this->nullableInt($payload['max_length'] ?? null),
            'min_value' => $this->nullableFloat($payload['min_value'] ?? null),
            'max_value' => $this->nullableFloat($payload['max_value'] ?? null),
        ];
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, string[]>
     */
    public function validateDefinitionPayload(array $payload, ?int $ignoreId = null): array
    {
        $errors = [];
        $resourceKey = trim(strtolower((string) ($payload['resource_key'] ?? '')));
        $fieldKey = trim(strtolower((string) ($payload['field_key'] ?? '')));
        $type = trim(strtolower((string) ($payload['type'] ?? 'text')));
        $minValue = $payload['min_value'] ?? null;
        $maxValue = $payload['max_value'] ?? null;

        if (!$this->supportsResource($resourceKey)) {
            $errors['resource_key'][] = __('ui.metadata.validation.resource_supported');
        }

        if ($fieldKey !== '' && $this->fields->existsFieldKey($resourceKey, $fieldKey, $ignoreId)) {
            $errors['field_key'][] = __('ui.metadata.validation.field_key_exists');
        }

        if ($type === 'select' && $this->parseSelectOptions((string) ($payload['select_options'] ?? '')) === []) {
            $errors['select_options'][] = __('ui.metadata.validation.select_options_required');
        }

        if ($type === 'catalog') {
            $catalogKey = $this->nullableLowerString($payload['catalog_key'] ?? null);

            if ($catalogKey === null) {
                $errors['catalog_key'][] = __('ui.metadata.validation.catalog_required');
            } elseif ($this->catalogs->findDefinitionByKey($catalogKey) === null) {
                $errors['catalog_key'][] = __('ui.metadata.validation.catalog_not_available');
            }
        }

        if (!$this->supportsFilterType($type) && filter_var($payload['is_filterable'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            $errors['is_filterable'][] = __('ui.metadata.validation.filter_type_not_supported');
        }

        if ($minValue !== null && $maxValue !== null && $minValue !== '' && $maxValue !== '' && (float) $minValue > (float) $maxValue) {
            $errors['max_value'][] = __('ui.metadata.validation.max_gte_min');
        }

        return $errors;
    }

    /**
     * @return array<int, array<string, string>>
     */
    public function parseSelectOptions(string $raw): array
    {
        $options = [];

        foreach (preg_split('/\r\n|\r|\n/', $raw) ?: [] as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $parts = array_map('trim', explode('|', $line, 2));
            $value = trim((string) ($parts[0] ?? ''));
            $label = trim((string) ($parts[1] ?? $value));

            if ($value === '') {
                continue;
            }

            $options[] = [
                'value' => $value,
                'label' => $label === '' ? $value : $label,
            ];
        }

        return $options;
    }

    public function selectOptionsText(array $definition): string
    {
        $lines = [];

        foreach ((array) ($definition['options_json'] ?? []) as $option) {
            $value = trim((string) ($option['value'] ?? ''));
            if ($value === '') {
                continue;
            }

            $label = trim((string) ($option['label'] ?? $value));
            $lines[] = $label === $value
                ? $value
                : $value . ' | ' . $label;
        }

        return implode(PHP_EOL, $lines);
    }

    public function mediaLibraryLabel(int $mediaId): ?string
    {
        return $this->mediaLabel($mediaId);
    }

    /**
     * @return array<string, string>
     */
    public function catalogDefinitionOptions(bool $includeState = true): array
    {
        return $this->catalogs->definitionOptionMap($includeState);
    }

    public static function inputKey(string $fieldKey): string
    {
        return self::INPUT_PREFIX . trim(strtolower($fieldKey));
    }

    public static function filterKey(string $fieldKey): string
    {
        return self::FILTER_PREFIX . trim(strtolower($fieldKey));
    }

    public static function gridColumnKey(string $fieldKey): string
    {
        return 'meta_' . trim(strtolower($fieldKey));
    }

    /**
     * @param array<string, mixed> $definition
     * @return string[]
     */
    private function typeRules(array $definition): array
    {
        $type = (string) ($definition['type'] ?? 'text');
        $rules = [];

        if (in_array($type, ['text', 'textarea'], true) && $definition['max_length'] !== null) {
            $rules[] = 'max:' . (int) $definition['max_length'];
        }

        if ($type === 'number') {
            $rules[] = 'numeric';

            if ($definition['min_value'] !== null) {
                $rules[] = 'min_value:' . (string) $definition['min_value'];
            }

            if ($definition['max_value'] !== null) {
                $rules[] = 'max_value:' . (string) $definition['max_value'];
            }
        }

        if ($type === 'boolean') {
            $rules[] = 'boolean';
        }

        if ($type === 'select') {
            $values = array_map(
                static fn (array $option): string => (string) ($option['value'] ?? ''),
                (array) ($definition['options_json'] ?? [])
            );

            if ($values !== []) {
                $rules[] = 'in:' . implode(',', $values);
            }
        }

        if (in_array($type, ['date', 'datetime'], true)) {
            $rules[] = 'date';
        }

        if ($type === 'media') {
            $rules[] = 'integer';
        }

        return $rules;
    }

    /**
     * @param array<string, mixed> $definition
     * @return array<int|string, string|array<string, string>>
     */
    private function fieldOptions(array $definition, mixed $currentValue = null): array
    {
        $type = (string) ($definition['type'] ?? 'text');

        if ($type === 'select') {
            $options = [];

            foreach ((array) ($definition['options_json'] ?? []) as $option) {
                $value = (string) ($option['value'] ?? '');
                if ($value === '') {
                    continue;
                }

                $options[$value] = (string) ($option['label'] ?? $value);
            }

            return $options;
        }

        if ($type === 'catalog') {
            $selectedKeys = [];
            $selected = trim((string) ($currentValue ?? ''));
            if ($selected !== '') {
                $selectedKeys[] = $selected;
            }

            return CatalogManager::getInstance()->options((string) ($definition['catalog_key'] ?? ''), $selectedKeys);
        }

        if ($type === 'media') {
            return $this->mediaOptions();
        }

        return [];
    }

    /**
     * @param array<string, mixed> $definition
     * @return array<string, scalar|array<int, scalar>>
     */
    private function fieldAttributes(array $definition): array
    {
        $type = (string) ($definition['type'] ?? 'text');
        $attributes = [];

        if (in_array($type, ['text', 'textarea'], true) && $definition['max_length'] !== null) {
            $attributes['maxlength'] = (int) $definition['max_length'];
        }

        if ($type === 'number') {
            $attributes['step'] = '0.01';

            if ($definition['min_value'] !== null) {
                $attributes['min'] = (string) $definition['min_value'];
            }

            if ($definition['max_value'] !== null) {
                $attributes['max'] = (string) $definition['max_value'];
            }
        }

        return $attributes;
    }

    /**
     * @param array<string, mixed> $definition
     * @return array<string, string>
     */
    private function filterOptions(array $definition): array
    {
        $type = (string) ($definition['type'] ?? 'text');

        if ($type === 'boolean') {
            return [
                '1' => __('ui.common.yes'),
                '0' => __('ui.common.no'),
            ];
        }

        if ($type === 'media') {
            return $this->mediaOptions();
        }

        if ($type === 'catalog') {
            return CatalogManager::getInstance()->options((string) ($definition['catalog_key'] ?? ''));
        }

        $options = [];

        foreach ((array) ($definition['options_json'] ?? []) as $option) {
            $value = (string) ($option['value'] ?? '');
            if ($value === '') {
                continue;
            }

            $options[$value] = (string) ($option['label'] ?? $value);
        }

        return $options;
    }

    /**
     * @param array<string, mixed> $definition
     */
    private function defaultValue(array $definition): mixed
    {
        $default = $definition['default_value'] ?? null;
        $type = (string) ($definition['type'] ?? 'text');

        return match ($type) {
            'boolean' => $default === null || $default === '' ? '0' : (string) $default,
            'media' => $default === null ? '' : (string) $default,
            default => $default,
        };
    }

    private function supportsFilterType(string $type): bool
    {
        return in_array($type, ['text', 'number', 'boolean', 'select', 'catalog', 'date', 'datetime', 'media'], true);
    }

    private function formFieldType(string $type): string
    {
        return match ($type) {
            'textarea' => 'textarea',
            'number' => 'number',
            'boolean' => 'checkbox',
            'select', 'catalog', 'media' => 'select',
            'date' => 'date',
            'datetime' => 'datetime-local',
            default => 'text',
        };
    }

    /**
     * @return array<string, string>
     */
    private function mediaOptions(): array
    {
        $options = [];

        try {
            $rows = $this->db->connection()->select(
                'SELECT id, name
                 FROM media_library
                 WHERE tenant_id = ?
                 ORDER BY name ASC
                 LIMIT 250',
                [$this->currentTenantId()]
            ) ?: [];
        } catch (\Throwable) {
            return [];
        }

        foreach ($rows as $row) {
            $id = (int) ($row['id'] ?? 0);
            if ($id <= 0) {
                continue;
            }

            $options[(string) $id] = (string) ($row['name'] ?? ('#' . $id));
        }

        return $options;
    }

    private function mediaLabel(int $mediaId): ?string
    {
        if ($mediaId <= 0) {
            return null;
        }

        try {
            $row = $this->db->connection()->selectOne(
                'SELECT name
                 FROM media_library
                 WHERE id = ?
                   AND tenant_id = ?',
                [$mediaId, $this->currentTenantId()]
            );
        } catch (\Throwable) {
            return null;
        }

        $name = trim((string) ($row['name'] ?? ''));

        return $name === '' ? null : $name;
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }

    private function nullableLowerString(mixed $value): ?string
    {
        $value = trim(strtolower((string) ($value ?? '')));

        return $value === '' ? null : $value;
    }

    private function nullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    private function nullableFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (float) $value;
    }

    private function humanize(string $value): string
    {
        $value = trim(str_replace(['_', '-'], ' ', $value));

        return $value === '' ? '' : ucwords($value);
    }

    private function currentTenantId(): int
    {
        return TenancyManager::getInstance()->requireCurrentTenantId();
    }
}
