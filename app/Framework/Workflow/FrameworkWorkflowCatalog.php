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

namespace Catalyst\Framework\Workflow;

use Catalyst\Entities\AutomationRule;

/**
 * Registers the framework's built-in workflow definitions.
 *
 * @package Catalyst\Framework\Workflow
 * Responsibility: Supplies lifecycle definitions and built-in transition side effects.
 */
final class FrameworkWorkflowCatalog
{
    private static bool $registered = false;

    /**
     * Registers built-in definitions once per runtime.
     *
     * Responsibility: Seeds reusable framework workflow examples without creating application-specific workflows.
     */
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
                    'kind' => 'submit',
                    'ability' => 'submit-review',
                ],
                [
                    'key' => 'approve',
                    'label' => 'Approve',
                    'from' => ['in_review'],
                    'to' => 'approved',
                    'kind' => 'approve',
                    'ability' => 'approve',
                ],
                [
                    'key' => 'reject',
                    'label' => 'Reject back to draft',
                    'from' => ['in_review'],
                    'to' => 'draft',
                    'kind' => 'reject',
                    'ability' => 'reject',
                ],
                [
                    'key' => 'archive',
                    'label' => 'Archive',
                    'from' => ['draft', 'approved'],
                    'to' => 'archived',
                    'kind' => 'archive',
                    'ability' => 'archive',
                ],
                [
                    'key' => 'reopen',
                    'label' => 'Reopen draft',
                    'from' => ['archived'],
                    'to' => 'draft',
                    'kind' => 'resubmit',
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
                    'kind' => 'approve',
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
                    'kind' => 'return',
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
                    'kind' => 'archive',
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
                    'kind' => 'resubmit',
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
                    'kind' => 'approve',
                    'ability' => 'activate',
                ],
                [
                    'key' => 'archive',
                    'label' => 'Archive',
                    'from' => ['draft', 'active'],
                    'to' => 'archived',
                    'kind' => 'archive',
                    'ability' => 'archive',
                ],
                [
                    'key' => 'restore',
                    'label' => 'Restore to draft',
                    'from' => ['archived'],
                    'to' => 'draft',
                    'kind' => 'resubmit',
                    'ability' => 'restore',
                ],
            ]
        ));

        self::$registered = true;
    }

    /**
     * Synchronizes an automation rule enabled flag with its workflow state.
     *
     * Responsibility: Keeps the framework automation fixture consistent after lifecycle transitions.
     */
    private static function syncRuleEnabledState(mixed $record, bool $enabled): void
    {
        if ($record instanceof AutomationRule) {
            $record->fill(['is_enabled' => $enabled ? '1' : '0']);
            $record->save();
        }
    }
}
