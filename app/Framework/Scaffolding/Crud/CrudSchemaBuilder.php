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

namespace Catalyst\Framework\Scaffolding\Crud;

/**
 * Builder for generated CRUD schema metadata.
 *
 * @package Catalyst\Framework\Scaffolding\Crud
 * Responsibility: Converts parsed field definitions into form, grid, validation, migration, and search metadata.
 */
final class CrudSchemaBuilder
{
    /**
     * Builds all schema fragments needed by CRUD scaffold templates.
     *
     * Responsibility: Builds all schema fragments needed by CRUD scaffold templates.
     * @param array<int, array<string, mixed>> $fields
     * @return array<string, mixed>
     */
    public function build(array $fields, string $table, bool $softDeletes, bool $optimisticLocking): array
    {
        $fillable = array_map(static fn (array $field): string => (string) $field['name'], $fields);
        if ($optimisticLocking) {
            $fillable[] = 'lock_version';
        }

        $requestLabels = $this->buildRequestLabels($fields);
        if ($optimisticLocking) {
            $requestLabels['lock_version'] = 'Lock version';
        }

        return [
            'fillable' => $fillable,
            'form_fields' => $this->buildFormFields($fields, $optimisticLocking),
            'form_sections' => $this->buildFormSections($fields),
            'grid_columns' => $this->buildGridColumns($fields),
            'grid_filters' => $this->buildGridFilters($fields, $softDeletes),
            'upload_fields' => $this->resolveUploadFields($fields),
            'filter_fields' => array_map(
                static fn (array $field): string => (string) $field['name'],
                array_values(array_filter($fields, static fn (array $field): bool => !empty($field['filterable'])))
            ),
            'search_field' => $this->resolveSearchField($fields),
            'default_sort' => $this->resolveDefaultSort($fields),
            'request_rules' => $this->buildRequestRules($fields, $table, $optimisticLocking),
            'request_labels' => $requestLabels,
            'migration_columns' => $this->buildMigrationColumns($fields),
        ];
    }

    /**
     * Builds form field definitions from parsed CRUD fields.
     *
     * Responsibility: Builds form field definitions from parsed CRUD fields.
     * @param array<int, array<string, mixed>> $fields
     * @return array<int, array<string, mixed>>
     */
    private function buildFormFields(array $fields, bool $optimisticLocking = false): array
    {
        $schema = [];

        if ($optimisticLocking) {
            $schema[] = [
                'name' => 'lock_version',
                'type' => 'hidden',
                'value' => 1,
            ];
        }

        foreach ($fields as $index => $field) {
            $entry = [
                'name' => $field['name'],
                'label' => $field['label'],
                'required' => $field['required'],
                'section' => $index < 2 ? 'primary' : 'secondary',
            ];

            if ($field['type'] === 'textarea') {
                $entry['type'] = 'textarea';
            } elseif ($field['type'] === 'checkbox') {
                $entry['type'] = 'checkbox';
            } elseif ($field['type'] === 'file') {
                $entry['type'] = 'file';
            } elseif ($field['type'] === 'integer') {
                $entry['type'] = 'number';
                $entry['attributes'] = ['step' => 1];
            } else {
                $entry['type'] = $field['type'];
            }

            $schema[] = $entry;
        }

        return $schema;
    }

    /**
     * Builds form sections for generated CRUD forms.
     *
     * Responsibility: Builds form sections for generated CRUD forms.
     * @param array<int, array<string, mixed>> $fields
     * @return array<int, array<string, string>>
     */
    private function buildFormSections(array $fields): array
    {
        $sections = [[
            'key' => 'primary',
            'title' => 'Primary fields',
            'description' => 'Core record data required to create and maintain this resource.',
        ]];

        if (count($fields) > 2) {
            $sections[] = [
                'key' => 'secondary',
                'title' => 'Additional fields',
                'description' => 'Secondary details, uploads or optional metadata.',
            ];
        }

        return $sections;
    }

    /**
     * Builds grid column definitions for generated CRUD lists.
     *
     * Responsibility: Builds grid column definitions for generated CRUD lists.
     * @param array<int, array<string, mixed>> $fields
     * @return array<int, array<string, mixed>>
     */
    private function buildGridColumns(array $fields): array
    {
        $columns = [[
            'key' => 'id',
            'label' => 'ID',
            'sortable' => true,
        ]];

        foreach ($fields as $field) {
            if ($field['type'] === 'file') {
                continue;
            }

            $columns[] = [
                'key' => $field['name'],
                'label' => $field['label'],
                'sortable' => true,
            ];
        }

        return $columns;
    }

    /**
     * Builds filter definitions for generated CRUD lists.
     *
     * Responsibility: Builds filter definitions for generated CRUD lists.
     * @param array<int, array<string, mixed>> $fields
     * @return array<int, array<string, mixed>>
     */
    private function buildGridFilters(array $fields, bool $softDeletes): array
    {
        $filters = [];

        foreach ($fields as $field) {
            if (empty($field['filterable'])) {
                continue;
            }

            $filters[] = [
                'name' => $field['name'],
                'label' => $field['label'],
                'type' => 'text',
                'placeholder' => 'Filter by ' . strtolower((string) $field['label']),
            ];
        }

        if ($softDeletes) {
            $filters[] = [
                'name' => 'trashed',
                'label' => 'Records',
                'type' => 'select',
                'options' => [
                    'with' => 'Include deleted',
                    'only' => 'Only deleted',
                ],
            ];
        }

        return $filters;
    }

    /**
     * Resolves generated form fields that require file upload handling.
     *
     * Responsibility: Resolves generated form fields that require file upload handling.
     * @param array<int, array<string, mixed>> $fields
     * @return array<int, string>
     */
    private function resolveUploadFields(array $fields): array
    {
        return array_values(array_map(
            static fn (array $field): string => (string) $field['name'],
            array_filter($fields, static fn (array $field): bool => (string) ($field['type'] ?? 'text') === 'file')
        ));
    }

    /**
     * Builds validation rules for the generated request class.
     *
     * Responsibility: Builds validation rules for the generated request class.
     * @param array<int, array<string, mixed>> $fields
     * @return array<string, string>
     */
    private function buildRequestRules(array $fields, string $table, bool $optimisticLocking = false): array
    {
        $rules = [];

        foreach ($fields as $field) {
            $rule = $field['required'] ? 'required' : 'nullable';
            $name = (string) $field['name'];

            $rule .= match ($field['type']) {
                'textarea' => '|max:5000',
                'email' => '|email|max:255',
                'number' => '|numeric',
                'integer' => '|integer',
                'checkbox' => '|boolean',
                'file' => '|file',
                'date' => '|date',
                default => '|max:255',
            };

            if ($name === 'slug') {
                $rule .= '|unique:' . $table . ',slug';
            }

            $rules[$name] = $rule;
        }

        if ($optimisticLocking) {
            $rules['lock_version'] = 'nullable|integer|min:1';
        }

        return $rules;
    }

    /**
     * Builds validation labels for the generated request class.
     *
     * Responsibility: Builds validation labels for the generated request class.
     * @param array<int, array<string, mixed>> $fields
     * @return array<string, string>
     */
    private function buildRequestLabels(array $fields): array
    {
        $labels = [];

        foreach ($fields as $field) {
            $labels[(string) $field['name']] = (string) $field['label'];
        }

        return $labels;
    }

    /**
     * Builds SQL column declarations for the generated migration.
     *
     * Responsibility: Builds SQL column declarations for the generated migration.
     * @param array<int, array<string, mixed>> $fields
     */
    private function buildMigrationColumns(array $fields): string
    {
        $lines = [];

        foreach ($fields as $field) {
            $name = (string) ($field['name'] ?? '');
            if ($name === '') {
                continue;
            }

            $required = !empty($field['required']) ? 'NOT NULL' : 'NULL DEFAULT NULL';
            $type = match ((string) ($field['type'] ?? 'text')) {
                'textarea' => 'TEXT',
                'email', 'file', 'text' => 'VARCHAR(255)',
                'number' => 'DECIMAL(12,2)',
                'integer' => 'INT',
                'checkbox' => 'TINYINT(1)',
                'date' => 'DATE',
                default => 'VARCHAR(255)',
            };

            if ($type === 'TEXT' && $required !== 'NOT NULL') {
                $required = 'NULL';
            }

            $lines[] = sprintf("    `%s` %s %s,", $name, $type, $required);
        }

        return $lines === [] ? '' : implode("\n", $lines) . "\n";
    }

    /**
     * Resolves the default search field for generated grids.
     *
     * Responsibility: Resolves the default search field for generated grids.
     * @param array<int, array<string, mixed>> $fields
     */
    private function resolveSearchField(array $fields): string
    {
        foreach ($fields as $field) {
            if (!empty($field['searchable'])) {
                return (string) $field['name'];
            }
        }

        return (string) $fields[0]['name'];
    }

    /**
     * Resolves the default sort field for generated grids.
     *
     * Responsibility: Resolves the default sort field for generated grids.
     * @param array<int, array<string, mixed>> $fields
     */
    private function resolveDefaultSort(array $fields): string
    {
        foreach ($fields as $field) {
            if ((string) $field['name'] === 'name') {
                return 'name';
            }
        }

        return (string) $fields[0]['name'];
    }
}
