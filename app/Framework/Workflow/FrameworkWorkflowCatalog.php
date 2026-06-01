<?php

declare(strict_types=1);

namespace Catalyst\Framework\Workflow;

use Catalyst\Entities\AutomationRule;

final class FrameworkWorkflowCatalog
{
    private static bool $registered = false;

    public static function registerDefaults(WorkflowDefinitionRegistry $registry): void
    {
        if (self::$registered) {
            return;
        }

        $registry->register(new WorkflowDefinition(
            key: 'document-templates.lifecycle',
            resourceKey: 'document-templates',
            label: 'Document template lifecycle',
            initialState: 'draft',
            states: [
                'draft' => 'Draft',
                'in_review' => 'In review',
                'approved' => 'Approved',
                'archived' => 'Archived',
            ],
            transitions: [
                [
                    'key' => 'submit-review',
                    'label' => 'Submit for review',
                    'from' => ['draft'],
                    'to' => 'in_review',
                    'ability' => 'submit-review',
                ],
                [
                    'key' => 'approve',
                    'label' => 'Approve',
                    'from' => ['in_review'],
                    'to' => 'approved',
                    'ability' => 'approve',
                ],
                [
                    'key' => 'reject',
                    'label' => 'Reject back to draft',
                    'from' => ['in_review'],
                    'to' => 'draft',
                    'ability' => 'reject',
                ],
                [
                    'key' => 'archive',
                    'label' => 'Archive',
                    'from' => ['draft', 'approved'],
                    'to' => 'archived',
                    'ability' => 'archive',
                ],
                [
                    'key' => 'reopen',
                    'label' => 'Reopen draft',
                    'from' => ['archived'],
                    'to' => 'draft',
                    'ability' => 'restore',
                ],
            ]
        ));

        $registry->register(new WorkflowDefinition(
            key: 'automation-rules.lifecycle',
            resourceKey: 'automation-rules',
            label: 'Automation rule lifecycle',
            initialState: 'draft',
            states: [
                'draft' => 'Draft',
                'active' => 'Active',
                'paused' => 'Paused',
                'archived' => 'Archived',
            ],
            transitions: [
                [
                    'key' => 'activate',
                    'label' => 'Activate',
                    'from' => ['draft', 'paused'],
                    'to' => 'active',
                    'ability' => 'activate',
                    'after' => static function (mixed $record): void {
                        self::syncRuleEnabledState($record, true);
                    },
                ],
                [
                    'key' => 'pause',
                    'label' => 'Pause',
                    'from' => ['active'],
                    'to' => 'paused',
                    'ability' => 'pause',
                    'after' => static function (mixed $record): void {
                        self::syncRuleEnabledState($record, false);
                    },
                ],
                [
                    'key' => 'archive',
                    'label' => 'Archive',
                    'from' => ['draft', 'active', 'paused'],
                    'to' => 'archived',
                    'ability' => 'archive',
                    'after' => static function (mixed $record): void {
                        self::syncRuleEnabledState($record, false);
                    },
                ],
                [
                    'key' => 'restore',
                    'label' => 'Restore to draft',
                    'from' => ['archived'],
                    'to' => 'draft',
                    'ability' => 'restore',
                    'after' => static function (mixed $record): void {
                        self::syncRuleEnabledState($record, false);
                    },
                ],
            ]
        ));

        $registry->register(new WorkflowDefinition(
            key: 'catalogs.lifecycle',
            resourceKey: 'catalogs',
            label: 'Catalog lifecycle',
            initialState: 'draft',
            states: [
                'draft' => 'Draft',
                'active' => 'Active',
                'archived' => 'Archived',
            ],
            transitions: [
                [
                    'key' => 'activate',
                    'label' => 'Activate',
                    'from' => ['draft'],
                    'to' => 'active',
                    'ability' => 'activate',
                ],
                [
                    'key' => 'archive',
                    'label' => 'Archive',
                    'from' => ['draft', 'active'],
                    'to' => 'archived',
                    'ability' => 'archive',
                ],
                [
                    'key' => 'restore',
                    'label' => 'Restore to draft',
                    'from' => ['archived'],
                    'to' => 'draft',
                    'ability' => 'restore',
                ],
            ]
        ));

        self::$registered = true;
    }

    private static function syncRuleEnabledState(mixed $record, bool $enabled): void
    {
        if ($record instanceof AutomationRule) {
            $record->fill(['is_enabled' => $enabled ? '1' : '0']);
            $record->save();
        }
    }
}
