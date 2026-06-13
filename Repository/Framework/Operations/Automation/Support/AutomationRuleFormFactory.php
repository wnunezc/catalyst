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

namespace Catalyst\Repository\Operations\Automation\Support;

use Catalyst\Framework\Form\FormBuilder;

/**
 * Builds the privileged automation rule form schema.
 *
 * @package Catalyst\Repository\Operations\Automation\Support
 * Responsibility: Define automation rule form sections, fields, defaults and actions for create and edit screens.
 */
final class AutomationRuleFormFactory
{
    /**
     * Builds the form schema for a new or existing automation rule.
     *
     * Responsibility: Builds the form schema for a new or existing automation rule.
     * @param array<string, mixed>|null $rule
     * @param array<string, array<string, mixed>> $hiddenFields
     * @return array<string, mixed>
     */
    public function build(?array $rule, array $hiddenFields): array
    {
        $fields = array_merge($hiddenFields, [
            'name' => [
                'label' => __('automation.form_page.labels.rule_name'),
                'required' => true,
                'section' => 'identity',
                'placeholder' => __('automation.form_page.placeholders.rule_name'),
                'attributes' => ['maxlength' => 150],
            ],
            'slug' => [
                'label' => __('automation.form_page.labels.rule_slug'),
                'required' => true,
                'section' => 'identity',
                'placeholder' => __('automation.form_page.placeholders.rule_slug'),
                'attributes' => ['maxlength' => 150],
            ],
            'description' => [
                'label' => __('automation.form_page.labels.description'),
                'section' => 'identity',
                'type' => 'textarea',
                'html_attributes' => 'rows="3"',
            ],
            'trigger_type' => [
                'label' => __('automation.form_page.labels.trigger_type'),
                'required' => true,
                'section' => 'identity',
                'type' => 'select',
                'options' => [
                    ['value' => 'event', 'label' => __('automation.index.triggers.event')],
                    ['value' => 'schedule', 'label' => __('automation.index.triggers.schedule')],
                ],
                'empty_option_label' => '',
                'value' => $rule['trigger_type'] ?? 'event',
            ],
            'event_name' => [
                'label' => __('automation.form_page.labels.event_name'),
                'section' => 'identity',
                'placeholder' => __('automation.form_page.placeholders.event_name'),
                'help' => __('automation.form_page.help.event_name'),
            ],
            'cron_expression' => [
                'label' => __('automation.form_page.labels.cron_expression'),
                'section' => 'identity',
                'placeholder' => __('automation.form_page.placeholders.cron_expression'),
                'help' => __('automation.form_page.help.cron_expression'),
            ],
            'condition_json' => [
                'label' => __('automation.form_page.labels.condition_json'),
                'required' => true,
                'section' => 'conditions',
                'type' => 'textarea',
                'html_attributes' => 'rows="8" spellcheck="false"',
                'help' => __('automation.form_page.help.condition_json'),
                'value' => $this->jsonField($rule['condition_json'] ?? new \stdClass()),
            ],
            'action_type' => [
                'label' => __('automation.form_page.labels.action_type'),
                'required' => true,
                'section' => 'action',
                'type' => 'select',
                'options' => [
                    ['value' => 'notification', 'label' => __('automation.form_page.action_types.notification')],
                    ['value' => 'workflow_transition', 'label' => __('automation.form_page.action_types.workflow_transition')],
                    ['value' => 'render_document', 'label' => __('automation.form_page.action_types.render_document')],
                ],
                'empty_option_label' => '',
                'value' => $rule['action_type'] ?? 'notification',
            ],
            'action_payload_json' => [
                'label' => __('automation.form_page.labels.action_payload_json'),
                'required' => true,
                'section' => 'action',
                'type' => 'textarea',
                'html_attributes' => 'rows="12" spellcheck="false"',
                'help' => __('automation.form_page.help.action_payload_json'),
                'value' => $this->jsonField($rule['action_payload_json'] ?? [
                    'title' => __('automation.form_page.defaults.action_title'),
                    'body' => __('automation.form_page.defaults.action_body'),
                    'target_path' => 'payload.actor_id',
                ]),
            ],
            'valid_from' => [
                'label' => __('automation.form_page.labels.valid_from'),
                'section' => 'identity',
                'placeholder' => __('automation.form_page.placeholders.valid_from'),
                'help' => __('automation.form_page.help.valid_from'),
            ],
            'valid_to' => [
                'label' => __('automation.form_page.labels.valid_to'),
                'section' => 'identity',
                'placeholder' => __('automation.form_page.placeholders.valid_to'),
                'help' => __('automation.form_page.help.valid_to'),
            ],
        ]);

        return FormBuilder::make()
            ->action($rule === null ? '/operations/automation-rules' : '/operations/automation-rules/' . (int) ($rule['id'] ?? 0))
            ->method('POST')
            ->model($rule)
            ->sections([
                'identity' => [
                    'title' => __('automation.form_page.sections.identity.title'),
                    'description' => __('automation.form_page.sections.identity.description'),
                ],
                'conditions' => [
                    'title' => __('automation.form_page.sections.conditions.title'),
                    'description' => __('automation.form_page.sections.conditions.description'),
                ],
                'action' => [
                    'title' => __('automation.form_page.sections.action.title'),
                    'description' => __('automation.form_page.sections.action.description'),
                ],
            ])
            ->fields($fields)
            ->actions([
                [
                    'type' => 'submit',
                    'label' => $rule === null ? __('automation.form_page.actions.create') : __('automation.form_page.actions.save'),
                    'class' => 'btn btn-primary',
                ],
                [
                    'type' => 'link',
                    'label' => __('automation.form_page.actions.back'),
                    'href' => '/operations/automation-rules',
                    'class' => 'btn btn-outline-secondary',
                ],
            ])
            ->toArray();
    }

    /**
     * Formats structured defaults as editable JSON while preserving existing JSON strings.
     *
     * Responsibility: Formats structured defaults as editable JSON while preserving existing JSON strings.
     */
    private function jsonField(mixed $value): string
    {
        if (is_string($value) && trim($value) !== '') {
            return $value;
        }

        return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}';
    }
}
