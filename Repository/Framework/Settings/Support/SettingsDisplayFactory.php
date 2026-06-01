<?php

declare(strict_types=1);

namespace Catalyst\Repository\Settings\Support;

final class SettingsDisplayFactory
{
    public function displayRow(
        string $label,
        string $id,
        mixed $value,
        bool $isPassword = false,
        bool $isBoolean = false
    ): array {
        if ($isBoolean) {
            $boolValue = (bool) $value;

            return [
                'label' => $label,
                'id' => $id,
                'text' => $boolValue ? __('ui.common.yes') : __('ui.common.no'),
                'text_class' => '',
                'is_boolean' => true,
                'badge_class' => $boolValue ? 'text-bg-success' : 'text-bg-secondary',
            ];
        }

        if ($isPassword) {
            $hasValue = $value !== null && (string) $value !== '';

            return [
                'label' => $label,
                'id' => $id,
                'text' => $hasValue ? '••••••••' : '—',
                'text_class' => $hasValue ? ' text-secondary font-monospace' : ' text-muted',
                'is_boolean' => false,
                'badge_class' => '',
            ];
        }

        $stringValue = trim((string) $value);

        return [
            'label' => $label,
            'id' => $id,
            'text' => $stringValue !== '' ? $stringValue : '—',
            'text_class' => $stringValue !== '' ? '' : ' text-muted',
            'is_boolean' => false,
            'badge_class' => '',
        ];
    }

    public function alertField(string $message): array
    {
        return [
            'type' => 'alert',
            'is_alert' => true,
            'is_checkbox' => false,
            'is_password' => false,
            'is_select' => false,
            'message' => $message,
            'label' => '',
            'name' => '',
            'value' => '',
            'input_type' => 'text',
            'hint' => '',
            'placeholder' => '',
            'checked_attr' => '',
            'disabled_attr' => '',
            'options' => [],
        ];
    }

    public function checkboxField(string $name, string $label, bool $checked, bool $disabled = false): array
    {
        return [
            'type' => 'checkbox',
            'is_alert' => false,
            'is_checkbox' => true,
            'is_password' => false,
            'is_select' => false,
            'message' => '',
            'label' => $label,
            'name' => $name,
            'value' => '',
            'input_type' => 'text',
            'hint' => '',
            'placeholder' => '',
            'checked_attr' => $checked ? ' checked' : '',
            'disabled_attr' => $disabled ? ' disabled' : '',
            'options' => [],
        ];
    }

    public function inputField(
        string $section,
        string $name,
        string $label,
        mixed $value,
        string $inputType = 'text',
        string $hint = '',
        bool $disabled = false
    ): array {
        return [
            'type' => 'input',
            'is_alert' => false,
            'is_checkbox' => false,
            'is_password' => false,
            'is_select' => false,
            'message' => '',
            'section' => $section,
            'label' => $label,
            'name' => $name,
            'value' => (string) $value,
            'input_type' => $inputType,
            'hint' => $hint,
            'placeholder' => '',
            'checked_attr' => '',
            'disabled_attr' => $disabled ? ' disabled' : '',
            'options' => [],
        ];
    }

    public function passwordField(string $section, string $name, string $label, bool $hasValue): array
    {
        return [
            'type' => 'password',
            'is_alert' => false,
            'is_checkbox' => false,
            'is_password' => true,
            'is_select' => false,
            'message' => '',
            'section' => $section,
            'label' => $label,
            'name' => $name,
            'value' => '',
            'input_type' => 'password',
            'hint' => '',
            'placeholder' => $hasValue ? __('settings.common.leave_empty_keep_current') : '',
            'checked_attr' => '',
            'disabled_attr' => '',
            'options' => [],
        ];
    }

    /**
     * @param array<string, string> $options
     */
    public function selectField(
        string $section,
        string $name,
        string $label,
        mixed $current,
        array $options,
        string $hint = '',
        bool $disabled = false
    ): array {
        return [
            'type' => 'select',
            'is_alert' => false,
            'is_checkbox' => false,
            'is_password' => false,
            'is_select' => true,
            'message' => '',
            'section' => $section,
            'label' => $label,
            'name' => $name,
            'value' => '',
            'input_type' => 'text',
            'hint' => $hint,
            'placeholder' => '',
            'checked_attr' => '',
            'disabled_attr' => $disabled ? ' disabled' : '',
            'options' => $this->normalizeOptions($options, $current),
        ];
    }

    public function modal(
        string $id,
        string $icon,
        string $title,
        string $section,
        array $fields,
        string $size = '',
        string $pretestAction = '',
        bool $saveDisabled = false
    ): array {
        return [
            'id' => $id,
            'icon' => $icon,
            'title' => $title,
            'section' => $section,
            'fields' => $fields,
            'size_class' => $size !== '' ? ' modal-' . $size : '',
            'action' => '/configuration/environment-setup/' . $section,
            'pretestAction' => $pretestAction,
            'save_disabled_attr' => $saveDisabled ? ' disabled' : '',
        ];
    }

    /**
     * @param array<string, string> $options
     * @return array<int, array<string, string>>
     */
    private function normalizeOptions(array $options, mixed $current): array
    {
        $currentValue = (string) $current;
        $normalized = [];

        foreach ($options as $value => $label) {
            $normalized[] = [
                'value' => (string) $value,
                'label' => (string) $label,
                'selected_attr' => (string) (((string) $value === $currentValue) ? ' selected' : ''),
            ];
        }

        return $normalized;
    }
}
