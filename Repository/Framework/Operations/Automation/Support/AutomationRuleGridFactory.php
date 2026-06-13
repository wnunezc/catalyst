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

use Catalyst\Framework\DataGrid\DataGrid;
use Catalyst\Framework\Automation\AutomationManager;
use Catalyst\Framework\Automation\AutomationRuleRepository;
use Catalyst\Framework\Idempotency\IdempotencyManager;
use Catalyst\Framework\Temporal\EffectiveWindow;

/**
 * Builds the administrative automation rule data grid.
 *
 * @package Catalyst\Repository\Operations\Automation\Support
 * Responsibility: Configure rule columns, filters, row actions and repository-backed pagination.
 */
final class AutomationRuleGridFactory
{
    /**
     * Builds the automation rule listing grid backed by repository search.
     *
     * Responsibility: Builds the automation rule listing grid backed by repository search.
     */
    public function build(AutomationRuleRepository $repository): DataGrid
    {
        return DataGrid::make()
            ->baseUrl('/operations/automation-rules')
            ->title(__('automation.index.title'), __('automation.index.description'))
            ->emptyState(
                __('automation.index.empty.title'),
                __('automation.index.empty.description'),
                [
                    'label' => __('automation.index.empty.action'),
                    'href' => '/operations/automation-rules/create',
                    'class' => 'btn btn-sm btn-primary',
                    'icon' => 'fa-solid fa-plus',
                ]
            )
            ->columns([
                [
                    'key' => 'name',
                    'label' => __('automation.index.columns.rule'),
                    'sortable' => true,
                    'value' => static fn (array $row): array => DataGrid::stack(
                        (string) ($row['name'] ?? ''),
                        (string) ($row['slug'] ?? '')
                    ),
                ],
                [
                    'key' => 'trigger_type',
                    'label' => __('automation.index.columns.trigger'),
                    'value' => static fn (array $row): array => DataGrid::stack(
                        (string) ($row['trigger_type'] ?? 'event'),
                        (string) (($row['event_name'] ?? '') ?: ($row['cron_expression'] ?? ''))
                    ),
                ],
                ['key' => 'action_type', 'label' => __('automation.index.columns.action')],
                [
                    'key' => 'current_state',
                    'label' => __('automation.index.columns.workflow'),
                    'value' => static fn (array $row): array => DataGrid::badge((string) ($row['current_state'] ?? 'draft')),
                ],
                [
                    'key' => 'temporal_state',
                    'label' => __('automation.index.columns.validity'),
                    'value' => static fn (array $row): array => DataGrid::stack(
                        (string) ($row['temporal_state'] ?? 'active'),
                        (string) (($row['valid_from'] ?? null) ?: __('automation.show.common.now'))
                            . " \u{2192} "
                            . (string) (($row['valid_to'] ?? null) ?: __('automation.show.common.open'))
                    ),
                ],
                ['key' => 'last_run_at', 'label' => __('automation.index.columns.last_run'), 'sortable' => true],
                ['key' => 'updated_at', 'label' => __('automation.index.columns.updated'), 'sortable' => true],
            ])
            ->filters([
                [
                    'name' => 'trigger_type',
                    'label' => __('automation.index.filters.trigger'),
                    'type' => 'select',
                    'options' => [
                        'event' => __('automation.index.triggers.event'),
                        'schedule' => __('automation.index.triggers.schedule'),
                    ],
                ],
                [
                    'name' => 'state',
                    'label' => __('automation.index.filters.workflow_state'),
                    'type' => 'select',
                    'options' => [
                        'draft' => __('automation.index.states.draft'),
                        'active' => __('automation.index.states.active'),
                        'paused' => __('automation.index.states.paused'),
                        'archived' => __('automation.index.states.archived'),
                    ],
                ],
                [
                    'name' => 'temporal_state',
                    'label' => __('automation.index.filters.validity'),
                    'type' => 'select',
                    'options' => [
                        EffectiveWindow::STATE_ACTIVE => __('automation.index.validity.active'),
                        EffectiveWindow::STATE_SCHEDULED => __('automation.index.validity.scheduled'),
                        EffectiveWindow::STATE_EXPIRED => __('automation.index.validity.expired'),
                    ],
                ],
            ])
            ->actions([
                ['label' => __('automation.index.actions.view'), 'class' => 'btn btn-outline-secondary btn-sm', 'href' => '/operations/automation-rules/{id}'],
                ['label' => __('automation.index.actions.edit'), 'class' => 'btn btn-outline-primary btn-sm', 'href' => '/operations/automation-rules/{id}/edit'],
                [
                    'label' => __('automation.index.actions.run'),
                    'class' => 'btn btn-outline-success btn-sm',
                    'method' => 'POST',
                    'href' => static fn (array $row): string => '/operations/automation-rules/' . (int) ($row['id'] ?? 0)
                        . '/run?_idempotency_key=' . rawurlencode(IdempotencyManager::getInstance()->generateKey()),
                ],
                [
                    'label' => __('automation.index.actions.delete'),
                    'class' => 'btn btn-outline-danger btn-sm',
                    'method' => 'POST',
                    'href' => '/operations/automation-rules/{id}/delete',
                    'confirm' => static fn (array $row): string => sprintf(
                        __('automation.index.actions.confirm_delete'),
                        (string) ($row['name'] ?? __('automation.show.rule_fallback'))
                    ),
                ],
            ])
            ->resourceKey(AutomationManager::RESOURCE_KEY)
            ->defaultSort('updated_at', 'desc')
            ->pagination(15, [15, 30, 60])
            ->searchPlaceholder(__('automation.index.search_placeholder'))
            ->provider(fn (array $state): array => $repository->search([
                'page' => $state['page'],
                'per_page' => $state['per_page'],
                'search' => $state['search'],
                'trigger_type' => $state['filters']['trigger_type'] ?? '',
                'state' => $state['filters']['state'] ?? '',
                'temporal_state' => $state['filters']['temporal_state'] ?? '',
            ]));
    }
}
