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

namespace Catalyst\Framework\Admin\Form;

/**
 * Defines the Form Builder class contract.
 *
 * @package Catalyst\Framework\Admin\Form
 * Responsibility: Coordinates the form builder behavior within its module boundary.
 */
final class FormBuilder
{
    /**
     * @var array<string, mixed>
     */
    private array $config = [
        'action' => '',
        'method' => 'POST',
        'attributes' => [],
        'fields' => [],
        'sections' => [],
        'actions' => [],
        'model' => [],
        'defaults' => [],
        'error_bag' => 'default',
        'multipart' => false,
        'wrapper_class' => 'row g-3',
        'autosave' => [
            'enabled' => false,
            'key' => null,
        ],
    ];

    /**
     * Creates the requested object.
     */
    public static function make(): self
    {
        return new self();
    }

    /**
     * Handles the action workflow.
     */
    public function action(string $action): self
    {
        $this->config['action'] = $action;

        return $this;
    }

    /**
     * Handles the method workflow.
     */
    public function method(string $method): self
    {
        $this->config['method'] = strtoupper(trim($method));

        return $this;
    }

    /**
     * @param array<string, scalar|array<int, scalar>> $attributes
     */
    public function attributes(array $attributes): self
    {
        $this->config['attributes'] = $attributes;

        return $this;
    }

    /**
     * @param array<int|string, array<string, mixed>> $fields
     */
    public function fields(array $fields): self
    {
        $this->config['fields'] = $fields;

        return $this;
    }

    /**
     * @param array<int|string, array<string, mixed>> $sections
     */
    public function sections(array $sections): self
    {
        $this->config['sections'] = $sections;

        return $this;
    }

    /**
     * @param array<int, array<string, mixed>> $actions
     */
    public function actions(array $actions): self
    {
        $this->config['actions'] = $actions;

        return $this;
    }

    /**
     * @param array<string, mixed>|object|null $model
     */
    public function model(array|object|null $model): self
    {
        $this->config['model'] = $this->normalizeModel($model);

        return $this;
    }

    /**
     * @param array<string, mixed> $defaults
     */
    public function defaults(array $defaults): self
    {
        $this->config['defaults'] = $defaults;

        return $this;
    }

    /**
     * Handles the error bag workflow.
     */
    public function errorBag(string $errorBag): self
    {
        $this->config['error_bag'] = $errorBag;

        return $this;
    }

    /**
     * Handles the multipart workflow.
     */
    public function multipart(bool $multipart = true): self
    {
        $this->config['multipart'] = $multipart;

        return $this;
    }

    /**
     * Handles the wrapper class workflow.
     */
    public function wrapperClass(string $wrapperClass): self
    {
        $this->config['wrapper_class'] = trim($wrapperClass);

        return $this;
    }

    /**
     * Handles the autosave workflow.
     */
    public function autosave(bool $enabled = true, ?string $key = null): self
    {
        $this->config['autosave'] = [
            'enabled' => $enabled,
            'key' => $key,
        ];

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $method = strtoupper((string) ($this->config['method'] ?? 'POST'));
        $multipart = (bool) ($this->config['multipart'] ?? false);
        $fields = [];
        $hasDependencies = false;
        $hasRepeaters = false;

        foreach ((array) ($this->config['fields'] ?? []) as $name => $field) {
            $normalized = $this->normalizeField(is_string($name) ? $name : null, (array) $field);
            $fields[] = $normalized;
            $multipart = $multipart || $normalized['type'] === 'file';
            $hasDependencies = $hasDependencies || $normalized['depends_on'] !== null;
            $hasRepeaters = $hasRepeaters || $normalized['type'] === 'repeater';
        }

        $sections = $this->normalizeSections($fields);

        return [
            'action' => (string) ($this->config['action'] ?? ''),
            'method' => $method,
            'http_method' => in_array($method, ['GET', 'POST'], true) ? $method : 'POST',
            'multipart' => $multipart,
            'attributes' => $this->normalizeAttributes((array) ($this->config['attributes'] ?? [])),
            'fields' => $fields,
            'sections' => $sections,
            'actions' => $this->normalizeActions((array) ($this->config['actions'] ?? [])),
            'wrapper_class' => (string) ($this->config['wrapper_class'] ?? 'row g-3'),
            'has_dependencies' => $hasDependencies,
            'has_repeaters' => $hasRepeaters,
            'autosave' => $this->normalizeAutosave(),
        ];
    }

    /**
     * @param array<string, mixed>|object|null $model
     * @return array<string, mixed>
     */
    private function normalizeModel(array|object|null $model): array
    {
        if ($model === null) {
            return [];
        }

        if (is_array($model)) {
            return $model;
        }

        if (method_exists($model, 'toArray')) {
            $result = $model->toArray();

            return is_array($result) ? $result : [];
        }

        return get_object_vars($model);
    }

    /**
     * @param array<string, mixed> $field
     * @return array<string, mixed>
     */
    private function normalizeField(?string $name, array $field): array
    {
        $fieldName = trim((string) ($field['name'] ?? $name ?? ''));
        $type = strtolower(trim((string) ($field['type'] ?? 'text')));
        $errorBag = (string) ($this->config['error_bag'] ?? 'default');
        $error = $fieldName !== '' ? validation_error($fieldName, $errorBag) : null;
        $options = $this->normalizeOptions((array) ($field['options'] ?? []));
        $attributes = $this->normalizeAttributes((array) ($field['attributes'] ?? []));
        $wrapperAttributes = $this->normalizeAttributes((array) ($field['wrapper_attributes'] ?? []));
        $dependsValues = $field['depends_values'] ?? $field['depends_value'] ?? null;

        if (!is_array($dependsValues)) {
            $dependsValues = $dependsValues === null || $dependsValues === ''
                ? []
                : [(string) $dependsValues];
        }

        $value = $this->resolveValue($fieldName, $type, $field);
        $repeaterFields = $type === 'repeater'
            ? $this->normalizeRepeaterFields((array) ($field['fields'] ?? []))
            : [];
        $repeaterItems = $type === 'repeater'
            ? $this->normalizeRepeaterItems($value, (int) ($field['min_items'] ?? 0))
            : [];

        return [
            'name' => $fieldName,
            'id' => (string) ($field['id'] ?? $fieldName),
            'type' => $type,
            'label' => (string) ($field['label'] ?? $this->humanize($fieldName)),
            'value' => $value,
            'required' => (bool) ($field['required'] ?? false),
            'help' => (string) ($field['help'] ?? $field['hint'] ?? ''),
            'placeholder' => (string) ($field['placeholder'] ?? ''),
            'options' => $options,
            'error' => $error,
            'attributes' => $attributes,
            'wrapper_attributes' => $wrapperAttributes,
            'col_class' => (string) ($field['col_class'] ?? $field['colClass'] ?? 'col-12'),
            'wrapper_class' => (string) ($field['wrapper_class'] ?? ''),
            'section' => trim((string) ($field['section'] ?? '')),
            'depends_on' => $field['depends_on'] ?? null,
            'depends_values' => array_values(array_map('strval', $dependsValues)),
            'hidden_value' => (string) ($field['hidden_value'] ?? '0'),
            'checked_value' => (string) ($field['checked_value'] ?? '1'),
            'empty_option_label' => (string) ($field['empty_option_label'] ?? ''),
            'add_label' => (string) ($field['add_label'] ?? 'Add item'),
            'remove_label' => (string) ($field['remove_label'] ?? 'Remove'),
            'empty_label' => (string) ($field['empty_label'] ?? 'No items added yet.'),
            'min_items' => max(0, (int) ($field['min_items'] ?? 0)),
            'max_items' => max(0, (int) ($field['max_items'] ?? 0)),
            'repeater_fields' => $repeaterFields,
            'repeater_items' => $repeaterItems,
            'html_attributes' => $this->stringifyAttributes($attributes),
            'html_wrapper_attributes' => $this->stringifyAttributes($wrapperAttributes),
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $actions
     * @return array<int, array<string, mixed>>
     */
    private function normalizeActions(array $actions): array
    {
        $normalized = [];

        foreach ($actions as $action) {
            $actionAttributes = $this->normalizeAttributes((array) ($action['attributes'] ?? []));

            $normalized[] = [
                'type' => strtoupper((string) ($action['type'] ?? 'submit')),
                'label' => (string) ($action['label'] ?? 'Save'),
                'class' => (string) ($action['class'] ?? 'btn btn-primary'),
                'href' => (string) ($action['href'] ?? ''),
                'icon' => (string) ($action['icon'] ?? ''),
                'attributes' => $actionAttributes,
                'html_attributes' => $this->stringifyAttributes($actionAttributes),
            ];
        }

        return $normalized;
    }

    /**
     * @param array<string, mixed> $field
     */
    private function resolveValue(string $fieldName, string $type, array $field): mixed
    {
        if ($fieldName === '') {
            return $field['value'] ?? null;
        }

        if ($type === 'file') {
            return null;
        }

        $model = (array) ($this->config['model'] ?? []);
        $defaults = (array) ($this->config['defaults'] ?? []);
        $default = $field['value']
            ?? $model[$fieldName]
            ?? $defaults[$fieldName]
            ?? ($type === 'checkbox' ? (string) ($field['hidden_value'] ?? '0') : '');

        return old($fieldName, $default);
    }

    /**
     * @param array<int|string, array<string, mixed>> $fields
     * @return array<int, array<string, mixed>>
     */
    private function normalizeRepeaterFields(array $fields): array
    {
        $normalized = [];

        foreach ($fields as $name => $field) {
            $fieldName = trim((string) ($field['name'] ?? (is_string($name) ? $name : '')));
            if ($fieldName === '') {
                continue;
            }

            $type = strtolower(trim((string) ($field['type'] ?? 'text')));
            $attributes = $this->normalizeAttributes((array) ($field['attributes'] ?? []));

            $normalized[] = [
                'name' => $fieldName,
                'id' => (string) ($field['id'] ?? $fieldName),
                'type' => $type,
                'label' => (string) ($field['label'] ?? $this->humanize($fieldName)),
                'required' => (bool) ($field['required'] ?? false),
                'help' => (string) ($field['help'] ?? ''),
                'placeholder' => (string) ($field['placeholder'] ?? ''),
                'options' => $this->normalizeOptions((array) ($field['options'] ?? [])),
                'attributes' => $attributes,
                'hidden_value' => (string) ($field['hidden_value'] ?? '0'),
                'checked_value' => (string) ($field['checked_value'] ?? '1'),
                'empty_option_label' => (string) ($field['empty_option_label'] ?? ''),
                'html_attributes' => $this->stringifyAttributes($attributes),
            ];
        }

        return $normalized;
    }

    /**
     * @param mixed $items
     * @return array<int, array<string, mixed>>
     */
    private function normalizeRepeaterItems(mixed $items, int $minItems): array
    {
        $normalized = [];

        if (is_array($items)) {
            foreach ($items as $item) {
                $normalized[] = is_array($item) ? $item : [];
            }
        }

        while (count($normalized) < $minItems) {
            $normalized[] = [];
        }

        return array_values($normalized);
    }

    /**
     * @param array<int, array<string, mixed>> $fields
     * @return array<int, array<string, mixed>>
     */
    private function normalizeSections(array $fields): array
    {
        $declared = (array) ($this->config['sections'] ?? []);

        if ($declared === []) {
            return [[
                'key' => 'default',
                'title' => '',
                'description' => '',
                'class' => '',
                'wrapper_class' => '',
                'fields' => $fields,
            ]];
        }

        $normalized = [];
        $indexes = [];

        foreach ($declared as $key => $section) {
            $section = (array) $section;
            $sectionKey = trim((string) ($section['key'] ?? (is_string($key) ? $key : '')));

            if ($sectionKey === '') {
                continue;
            }

            $indexes[$sectionKey] = count($normalized);
            $normalized[] = [
                'key' => $sectionKey,
                'title' => (string) ($section['title'] ?? $this->humanize($sectionKey)),
                'description' => (string) ($section['description'] ?? ''),
                'class' => (string) ($section['class'] ?? ''),
                'wrapper_class' => (string) ($section['wrapper_class'] ?? ''),
                'fields' => [],
            ];
        }

        $overflow = [];

        foreach ($fields as $field) {
            $sectionKey = trim((string) ($field['section'] ?? ''));

            if ($sectionKey !== '' && isset($indexes[$sectionKey])) {
                $normalized[$indexes[$sectionKey]]['fields'][] = $field;
                continue;
            }

            $overflow[] = $field;
        }

        if ($overflow !== []) {
            array_unshift($normalized, [
                'key' => 'default',
                'title' => '',
                'description' => '',
                'class' => '',
                'wrapper_class' => '',
                'fields' => $overflow,
            ]);
        }

        return array_values(array_filter(
            $normalized,
            static fn (array $section): bool => $section['fields'] !== [] || (string) ($section['title'] ?? '') !== ''
        ));
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeAutosave(): array
    {
        $autosave = (array) ($this->config['autosave'] ?? []);
        $enabled = (bool) ($autosave['enabled'] ?? false);
        $action = trim((string) ($this->config['action'] ?? ''));
        $key = trim((string) ($autosave['key'] ?? ''));

        if ($enabled && $key === '') {
            $key = 'form-builder:' . md5($action === '' ? json_encode($this->config['fields']) ?: '' : $action);
        }

        return [
            'enabled' => $enabled,
            'key' => $key,
        ];
    }

    /**
     * @param array<int|string, mixed> $options
     * @return array<int, array<string, string>>
     */
    private function normalizeOptions(array $options): array
    {
        $normalized = [];

        foreach ($options as $value => $label) {
            if (is_array($label)) {
                $normalized[] = [
                    'value' => (string) ($label['value'] ?? ''),
                    'label' => (string) ($label['label'] ?? $label['value'] ?? ''),
                ];

                continue;
            }

            $normalized[] = [
                'value' => (string) $value,
                'label' => (string) $label,
            ];
        }

        return $normalized;
    }

    /**
     * @param array<string, scalar|array<int, scalar>> $attributes
     * @return array<string, scalar|array<int, scalar>>
     */
    private function normalizeAttributes(array $attributes): array
    {
        $normalized = [];

        foreach ($attributes as $key => $value) {
            $attribute = trim((string) $key);

            if ($attribute === '') {
                continue;
            }

            $normalized[$attribute] = $value;
        }

        return $normalized;
    }

    /**
     * @param array<string, scalar|array<int, scalar>> $attributes
     */
    private function stringifyAttributes(array $attributes): string
    {
        $parts = [];

        foreach ($attributes as $key => $value) {
            if (is_bool($value)) {
                if ($value) {
                    $parts[] = $key;
                }

                continue;
            }

            if (is_array($value)) {
                $value = implode(' ', array_map('strval', $value));
            }

            $parts[] = sprintf('%s="%s"', $key, e((string) $value));
        }

        return implode(' ', $parts);
    }

    /**
     * Handles the humanize workflow.
     */
    private function humanize(string $value): string
    {
        $value = trim(str_replace(['_', '-'], ' ', $value));

        return $value === '' ? '' : ucwords($value);
    }
}
