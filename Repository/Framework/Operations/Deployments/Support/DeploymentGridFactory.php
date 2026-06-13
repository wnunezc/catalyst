<?php

declare(strict_types=1);

namespace Catalyst\Repository\Operations\Deployments\Support;

use Catalyst\Framework\DataGrid\DataGrid;
use Catalyst\Framework\Deployment\DeploymentRunRepository;

/**
 * Builds the safe deployment history grid without exposing process errors or local paths.
 */
final class DeploymentGridFactory
{
    public function build(DeploymentRunRepository $repository): DataGrid
    {
        return DataGrid::make()
            ->baseUrl('/operations/deployments')
            ->title(__('operations.deployments.grid.title'), __('operations.deployments.grid.description'))
            ->emptyState(__('operations.deployments.grid.empty.title'), __('operations.deployments.grid.empty.description'))
            ->columns([
                ['key' => 'release_id', 'label' => __('operations.deployments.grid.columns.release'), 'sortable' => true],
                ['key' => 'profile_key', 'label' => __('operations.deployments.grid.columns.profile'), 'sortable' => true],
                [
                    'key' => 'status',
                    'label' => __('operations.deployments.grid.columns.status'),
                    'sortable' => true,
                    'value' => static fn (array $row): array => DataGrid::badge(
                        (string) ($row['status'] ?? ''),
                        'text-bg-' . match ((string) ($row['status'] ?? '')) {
                            'completed' => 'success',
                            'failed' => 'danger',
                            'dry-run' => 'warning',
                            default => 'secondary',
                        }
                    ),
                ],
                ['key' => 'environment', 'label' => __('operations.deployments.grid.columns.environment'), 'sortable' => true],
                ['key' => 'started_at', 'label' => __('operations.deployments.grid.columns.started'), 'sortable' => true],
                ['key' => 'finished_at', 'label' => __('operations.deployments.grid.columns.finished'), 'sortable' => true],
            ])
            ->filters([[
                'name' => 'status',
                'label' => __('operations.deployments.grid.filters.status'),
                'type' => 'select',
                'options' => [
                    'completed' => __('operations.deployments.states.completed'),
                    'failed' => __('operations.deployments.states.failed'),
                    'dry-run' => __('operations.deployments.states.dry_run'),
                    'running' => __('operations.deployments.states.running'),
                ],
            ]])
            ->defaultSort('started_at', 'desc')
            ->pagination(10, [10, 25, 50])
            ->searchPlaceholder(__('operations.deployments.grid.search_placeholder'))
            ->provider(fn (array $state): array => $repository->search([
                'page' => $state['page'],
                'per_page' => $state['per_page'],
                'search' => $state['search'],
                'status' => $state['filters']['status'] ?? '',
            ]));
    }
}
