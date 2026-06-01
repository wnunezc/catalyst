<?php

declare(strict_types=1);

namespace Catalyst\Repository\Operations\Controllers;

use Catalyst\Framework\Admin\Form\FormBuilder;
use Catalyst\Framework\Admin\Grid\DataGrid;
use Catalyst\Framework\Deployment\DeploymentManager;
use Catalyst\Framework\Deployment\DeploymentRunRepository;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Catalyst\Repository\Operations\Requests\DeploymentRunRequest;

final class DeploymentsController extends AbstractOperationsController
{
    public function deployments(Request $request): Response
    {
        $this->authorizeResource('manage', 'operations');

        $profiles = DeploymentManager::getInstance()->profiles();
        $form = FormBuilder::make()
            ->action('/operations/deployments/runs')
            ->method('POST')
            ->sections([
                'release' => [
                    'title' => __('operations.deployments.form.title'),
                    'description' => __('operations.deployments.form.description'),
                ],
            ])
            ->fields([
                'profile_key' => [
                    'label' => __('operations.deployments.form.fields.profile'),
                    'type' => 'select',
                    'required' => true,
                    'section' => 'release',
                    'empty_option_label' => __('operations.deployments.form.fields.select_profile'),
                    'options' => array_map(
                        static fn (string $key, array $profile): array => [
                            'value' => $key,
                            'label' => $key . ' — ' . (string) ($profile['description'] ?? ''),
                        ],
                        array_keys($profiles),
                        array_values($profiles)
                    ),
                ],
                'dry_run' => [
                    'label' => __('operations.deployments.form.fields.dry_run'),
                    'type' => 'checkbox',
                    'section' => 'release',
                    'help' => __('operations.deployments.form.fields.dry_run_help'),
                ],
            ])
            ->actions([
                [
                    'type' => 'submit',
                    'label' => __('operations.deployments.form.actions.run'),
                    'class' => 'btn btn-primary',
                ],
            ])
            ->toArray();

        $grid = DataGrid::make()
            ->baseUrl('/operations/deployments')
            ->title(__('operations.deployments.grid.title'), __('operations.deployments.grid.description'))
            ->emptyState(__('operations.deployments.grid.empty.title'), __('operations.deployments.grid.empty.description'))
            ->columns([
                [
                    'key' => 'release_id',
                    'label' => __('operations.deployments.grid.columns.release'),
                    'sortable' => true,
                    'value' => static fn (array $row): array => DataGrid::code((string) ($row['release_id'] ?? '')),
                ],
                [
                    'key' => 'profile_key',
                    'label' => __('operations.deployments.grid.columns.profile'),
                    'sortable' => true,
                ],
                [
                    'key' => 'status',
                    'label' => __('operations.deployments.grid.columns.status'),
                    'sortable' => true,
                    'value' => static fn (array $row): array => DataGrid::badge(
                        (string) ($row['status'] ?? __('operations.index.common.unknown')),
                        'text-bg-' . (match ((string) ($row['status'] ?? '')) {
                            'completed' => 'success',
                            'failed' => 'danger',
                            'dry-run' => 'warning',
                            default => 'secondary',
                        })
                    ),
                ],
                [
                    'key' => 'environment',
                    'label' => __('operations.deployments.grid.columns.environment'),
                    'sortable' => true,
                ],
                [
                    'key' => 'started_at',
                    'label' => __('operations.deployments.grid.columns.started'),
                    'sortable' => true,
                    'empty' => '—',
                ],
                [
                    'key' => 'artifact_path',
                    'label' => __('operations.deployments.grid.columns.artifact'),
                    'value' => static fn (array $row): array => DataGrid::code((string) ($row['artifact_path'] ?? '—')),
                ],
            ])
            ->filters([
                [
                    'name' => 'status',
                    'label' => __('operations.deployments.grid.filters.status'),
                    'type' => 'select',
                    'options' => [
                        'completed' => __('operations.deployments.states.completed'),
                        'failed' => __('operations.deployments.states.failed'),
                        'dry-run' => __('operations.deployments.states.dry_run'),
                        'running' => __('operations.deployments.states.running'),
                    ],
                ],
            ])
            ->defaultSort('started_at', 'desc')
            ->pagination(10, [10, 25, 50])
            ->searchPlaceholder(__('operations.deployments.grid.search_placeholder'))
            ->provider(fn (array $state): array => DeploymentRunRepository::getInstance()->search([
                'page' => $state['page'],
                'per_page' => $state['per_page'],
                'search' => $state['search'],
                'status' => $state['filters']['status'] ?? '',
            ]))
            ->resolve($request);

        return $this->view('operations.deployments', [
            'title' => __('operations.title'),
            'pageTitle' => __('operations.deployments.title'),
            'activeSection' => 'deployments',
            'deploymentForm' => $form,
            'deploymentProfiles' => $profiles,
            'deploymentGrid' => $grid,
        ], 200, 'admin');
    }

    public function runDeployment(DeploymentRunRequest $request): Response
    {
        $this->authorizeResource('manage', 'operations');

        $payload = $request->validated();
        $profileKey = trim((string) ($payload['profile_key'] ?? ''));

        if (!array_key_exists($profileKey, DeploymentManager::getInstance()->profiles())) {
            return $this->postActionErrorRedirect('/operations/deployments', __('operations.deployments.messages.profile_not_found'), 404);
        }

        try {
            $result = DeploymentManager::getInstance()->run(
                $profileKey,
                $this->checkboxValue($payload['dry_run'] ?? null)
            );
        } catch (\Throwable $e) {
            return $this->postActionErrorRedirect('/operations/deployments', $e->getMessage(), 422);
        }

        return $this->postActionSuccessRedirect(
            '/operations/deployments',
            __('operations.deployments.messages.finished') . ' ' . (string) ($result['release_id'] ?? $profileKey) . '.'
        );
    }
}
