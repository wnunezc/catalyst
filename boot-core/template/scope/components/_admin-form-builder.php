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

use Catalyst\Framework\View\TrustedHtml;
use Catalyst\Helpers\Security\CsrfProtection;

return static function (array $scope): array {
    $form = is_array($scope['form'] ?? null) ? $scope['form'] : [];
    $method = strtoupper((string) ($form['method'] ?? 'POST'));
    $httpMethod = strtoupper((string) ($form['http_method'] ?? 'POST'));
    $hasDependencies = !empty($form['has_dependencies']);
    $hasRepeaters = !empty($form['has_repeaters']);
    $autosave = is_array($form['autosave'] ?? null) ? $form['autosave'] : [];
    $sections = array_values((array) ($form['sections'] ?? []));

    $stringifyAttributes = static function (array $attributes): string {
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

        return $parts !== [] ? ' ' . implode(' ', $parts) : '';
    };

    $normalizeFields = null;

    $normalizeField = static function (
        array $field,
        ?string $nameOverride = null,
        mixed $valueOverride = null,
        ?string $idOverride = null,
        bool $renderWithoutWrapper = false,
        bool $hasValueOverride = false
    ) use (&$normalizeField, &$normalizeFields): array {
        $fieldType = (string) ($field['type'] ?? 'text');
        $fieldName = $nameOverride ?? (string) ($field['name'] ?? '');
        $fieldId = $idOverride ?? (string) ($field['id'] ?? $fieldName);
        $fieldValue = $hasValueOverride ? $valueOverride : ($field['value'] ?? null);
        $isMultipleSelect = $fieldType === 'select' && !empty($field['multiple']);
        $renderedFieldName = $isMultipleSelect && !str_ends_with($fieldName, '[]') ? $fieldName . '[]' : $fieldName;
        $error = $nameOverride === null ? (string) ($field['error'] ?? '') : '';
        $help = (string) ($field['help'] ?? '');
        $wrapperClasses = trim((string) (($field['col_class'] ?? 'col-12') . ' ' . ($field['wrapper_class'] ?? '')));
        $dependencyValues = implode(' ', (array) ($field['depends_values'] ?? []));
        $dependencyAttrs = '';

        if (!empty($field['depends_on'])) {
            $dependencyAttrs = sprintf(
                ' data-depends-on="%s" data-depends-values="%s"',
                e((string) $field['depends_on']),
                e($dependencyValues)
            );
        }

        $wrapperAttributes = trim((string) ($field['html_wrapper_attributes'] ?? ''));
        if ($dependencyAttrs !== '') {
            $wrapperAttributes = trim($wrapperAttributes . $dependencyAttrs);
        }

        if ($isMultipleSelect) {
            $selectedValues = is_array($fieldValue)
                ? array_values(array_map('strval', $fieldValue))
                : (($fieldValue === null || $fieldValue === '') ? [] : [(string) $fieldValue]);
        } else {
            $selectedValues = [(string) ($fieldValue ?? '')];
        }

        $scalarFieldValue = is_array($fieldValue) ? '' : (string) ($fieldValue ?? '');

        $normalized = [
            'name' => $renderedFieldName,
            'base_name' => $fieldName,
            'id' => $fieldId,
            'label' => (string) ($field['label'] ?? __('ui.form_builder.field')),
            'help' => $help,
            'has_help' => $help !== '',
            'error' => $error,
            'has_error' => $error !== '',
            'wrapper_class' => $wrapperClasses,
            'wrapper_attributes_html' => TrustedHtml::fromString($wrapperAttributes !== '' ? ' ' . $wrapperAttributes : ''),
            'render_without_wrapper' => $renderWithoutWrapper,
            'show_label' => !in_array($fieldType, ['checkbox', 'repeater', 'hidden'], true),
            'show_help_after' => $help !== '' && !in_array($fieldType, ['checkbox', 'repeater', 'hidden'], true),
            'required' => !empty($field['required']),
            'type' => $fieldType,
            'is_hidden' => $fieldType === 'hidden',
            'is_repeater' => $fieldType === 'repeater',
            'is_textarea' => $fieldType === 'textarea',
            'is_select' => $fieldType === 'select',
            'is_multiple' => $isMultipleSelect,
            'is_checkbox' => $fieldType === 'checkbox',
            'is_file' => $fieldType === 'file',
            'input_type' => $fieldType,
            'invalid_class' => $error !== '' ? ' is-invalid' : '',
            'placeholder' => (string) ($field['placeholder'] ?? ''),
            'attributes_html' => TrustedHtml::fromString((string) ($field['html_attributes'] ?? '')),
            'value' => $fieldType === 'file' ? '' : $scalarFieldValue,
            'textarea_value' => $scalarFieldValue,
            'checkbox_hidden_value' => (string) ($field['hidden_value'] ?? '0'),
            'checkbox_checked_value' => (string) ($field['checked_value'] ?? '1'),
            'checkbox_checked' => (string) ($fieldValue ?? '') === (string) ($field['checked_value'] ?? '1'),
            'checkbox_label' => (string) ($field['help'] ?? $field['label'] ?? ''),
            'options' => [],
            'has_items' => false,
            'items' => [],
            'template_fields' => [],
            'repeater_label' => (string) ($field['label'] ?? __('ui.form_builder.items')),
            'add_label' => (string) ($field['add_label'] ?? __('ui.form_builder.add_item')),
            'remove_label' => (string) ($field['remove_label'] ?? __('ui.form_builder.remove')),
            'empty_label' => (string) ($field['empty_label'] ?? __('ui.form_builder.empty_items')),
            'min_items' => (int) ($field['min_items'] ?? 0),
            'max_items' => (int) ($field['max_items'] ?? 0),
        ];

        if ($fieldType === 'select') {
            $options = [];
            foreach ((array) ($field['options'] ?? []) as $option) {
                $option = is_array($option) ? $option : [];
                $optionValue = (string) ($option['value'] ?? '');
                $options[] = [
                    'value' => $optionValue,
                    'label' => (string) ($option['label'] ?? ''),
                    'selected' => in_array($optionValue, $selectedValues, true),
                ];
            }

            $normalized['options'] = $options;
            $normalized['has_empty_option'] = (string) ($field['empty_option_label'] ?? '') !== '';
            $normalized['empty_option_label'] = (string) ($field['empty_option_label'] ?? '');
        }

        if ($fieldType === 'repeater') {
            $items = is_array($fieldValue) ? array_values($fieldValue) : [];
            $minItems = max(0, (int) ($field['min_items'] ?? 0));
            while (count($items) < $minItems) {
                $items[] = [];
            }

            $normalizedItems = [];
            foreach ($items as $index => $itemValues) {
                $itemValues = is_array($itemValues) ? $itemValues : [];
                $subFields = [];

                foreach ((array) ($field['repeater_fields'] ?? []) as $subField) {
                    $subField = is_array($subField) ? $subField : [];
                    $subName = (string) ($subField['name'] ?? '');
                    $nestedName = $fieldName . '[' . $index . '][' . $subName . ']';
                    $nestedId = (string) ($subField['id'] ?? $subName) . '-' . $index;
                    $nestedValue = $itemValues[$subName]
                        ?? (($subField['type'] ?? 'text') === 'checkbox'
                            ? (string) ($subField['hidden_value'] ?? '0')
                            : '');
                    $subFields[] = $normalizeField($subField, $nestedName, $nestedValue, $nestedId, false, true);
                }

                $normalizedItems[] = [
                    'index_label' => __('ui.form_builder.item_number', ['index' => (int) $index + 1]),
                    'remove_label' => (string) ($field['remove_label'] ?? __('ui.form_builder.remove')),
                    'fields' => $subFields,
                ];
            }

            $templateFields = [];
            foreach ((array) ($field['repeater_fields'] ?? []) as $subField) {
                $subField = is_array($subField) ? $subField : [];
                $subName = (string) ($subField['name'] ?? '');
                $nestedName = $fieldName . '[__INDEX__][' . $subName . ']';
                $nestedId = (string) ($subField['id'] ?? $subName) . '-__INDEX__';
                $defaultValue = ($subField['type'] ?? 'text') === 'checkbox'
                    ? (string) ($subField['hidden_value'] ?? '0')
                    : '';
                $templateFields[] = $normalizeField($subField, $nestedName, $defaultValue, $nestedId, false, true);
            }

            $normalized['has_items'] = $normalizedItems !== [];
            $normalized['items'] = $normalizedItems;
            $normalized['template_fields'] = $templateFields;
            $normalized['template_index_label'] = __('ui.form_builder.item_number', ['index' => '__INDEX_LABEL__']);
        }

        return $normalized;
    };

    $normalizeFields = static function (array $fields, bool $flat = false) use (&$normalizeField): array {
        $normalized = [];

        foreach ($fields as $field) {
            if (!is_array($field)) {
                continue;
            }

            $renderWithoutWrapper = $flat && (($field['type'] ?? 'text') === 'hidden');
            $normalized[] = $normalizeField($field, null, null, null, $renderWithoutWrapper, false);
        }

        return $normalized;
    };

    $normalizedSections = [];
    foreach ($sections as $section) {
        $section = is_array($section) ? $section : [];
        $sectionTitle = trim((string) ($section['title'] ?? ''));
        $sectionDescription = trim((string) ($section['description'] ?? ''));
        $sectionClass = trim((string) ($section['class'] ?? ''));
        $sectionWrapper = trim((string) ($section['wrapper_class'] ?? ''));
        $fields = array_values((array) ($section['fields'] ?? []));
        $hasHeader = $sectionTitle !== '' || $sectionDescription !== '';

        $normalizedSections[] = [
            'has_header' => $hasHeader,
            'title' => $sectionTitle,
            'description' => $sectionDescription,
            'section_class' => $sectionClass,
            'fields_wrapper_class' => trim('row g-3 ' . $sectionWrapper),
            'fields' => $normalizeFields($fields, !$hasHeader),
        ];
    }

    $normalizedActions = [];
    foreach ((array) ($form['actions'] ?? []) as $action) {
        $action = is_array($action) ? $action : [];
        $type = strtoupper((string) ($action['type'] ?? 'submit'));
        $normalizedActions[] = [
            'is_link' => $type === 'LINK',
            'href' => (string) ($action['href'] ?? '#'),
            'type' => strtolower((string) ($action['type'] ?? 'submit')),
            'class' => (string) ($action['class'] ?? ($type === 'LINK' ? 'btn btn-outline-secondary' : 'btn btn-primary')),
            'attributes_html' => TrustedHtml::fromString((string) ($action['html_attributes'] ?? '')),
            'has_icon' => (string) ($action['icon'] ?? '') !== '',
            'icon' => (string) ($action['icon'] ?? ''),
            'label' => (string) ($action['label'] ?? ($type === 'LINK' ? __('ui.form_builder.back') : __('ui.form_builder.save'))),
        ];
    }

    $formAttrs = '';
    if (!empty($form['multipart'])) {
        $formAttrs .= ' enctype="multipart/form-data"';
    }
    if ($hasDependencies) {
        $formAttrs .= ' data-admin-form-dependencies="1"';
    }
    if ($hasRepeaters) {
        $formAttrs .= ' data-admin-form-repeaters="1"';
    }
    if (!empty($autosave['enabled'])) {
        $formAttrs .= ' data-admin-form-autosave="1"';
    }
    if (!empty($autosave['key'])) {
        $formAttrs .= ' data-admin-form-autosave-key="' . e((string) $autosave['key']) . '"';
    }
    if (isset($form['attributes'])) {
        $formAttrs .= $stringifyAttributes((array) ($form['attributes'] ?? []));
    }

    return [
        'method' => $method,
        'http_method' => $httpMethod,
        'action' => (string) ($form['action'] ?? ''),
        'wrapper_class' => (string) ($form['wrapper_class'] ?? 'row g-3'),
        'form_attributes_html' => TrustedHtml::fromString($formAttrs),
        'include_csrf' => $httpMethod !== 'GET',
        'csrfField' => TrustedHtml::fromString(CsrfProtection::getInstance()->getTokenField()),
        'include_method_override' => !in_array($method, ['GET', 'POST'], true),
        'sections' => $normalizedSections,
        'has_actions' => $normalizedActions !== [],
        'actions' => $normalizedActions,
    ];
};
