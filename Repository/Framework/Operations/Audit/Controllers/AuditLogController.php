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

namespace Catalyst\Repository\Operations\Audit\Controllers;

use Catalyst\Framework\DataGrid\DataGrid;
use Catalyst\Framework\Audit\AuditLogRepository;
use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;

/**
 * Admin controller for browsing and inspecting audit log entries.
 *
 * @package Catalyst\Repository\Operations\Audit\Controllers
 * Responsibility: Builds the audit log data grid, handles CSV/XLS exports,
 * and renders individual audit entry details.
 */
final class AuditLogController extends Controller
{
    /**
     * Receives the audit repository used by list, export, and detail workflows.
     *
     * Responsibility: Receives the audit repository used by list, export, and detail workflows.
     */
    public function __construct(
        private readonly AuditLogRepository $repository
    ) {
        parent::__construct();
    }

    /**
     * Builds the searchable audit grid and exports audit rows when an export format is requested.
     *
     * Responsibility: Builds the searchable audit grid and exports audit rows when an export format is requested.
     */
    public function index(Request $request): Response
    {
        $this->authorizeResource('view-any', 'operations-audit-log');

        $gridBuilder = DataGrid::make()
            ->baseUrl('/operations/audit-log')
            ->title(__('audit.index.title'), __('audit.index.description'))
            ->emptyState(
                __('audit.index.empty.title'),
                __('audit.index.empty.description'),
                null
            )
            ->columns([
                [
                    'key' => 'occurred_at',
                    'label' => __('audit.index.columns.occurred'),
                    'sortable' => true,
                ],
                [
                    'key' => 'tenant_key',
                    'label' => __('audit.index.columns.tenant'),
                    'sortable' => false,
                    'value' => static fn (array $row): array => DataGrid::stack(
                        (string) ($row['tenant_key'] ?? __('audit.show.common.default_tenant')),
                        '#' . (string) ($row['tenant_id'] ?? '0')
                    ),
                ],
                [
                    'key' => 'resource',
                    'label' => __('audit.index.columns.resource'),
                    'sortable' => true,
                    'value' => static fn (array $row): array => DataGrid::stack(
                        (string) (($row['resource_label'] ?? '') !== '' ? $row['resource_label'] : ($row['resource'] ?? '')),
                        (string) ($row['resource'] ?? '')
                    ),
                ],
                [
                    'key' => 'action',
                    'label' => __('audit.index.columns.action'),
                    'sortable' => true,
                ],
                [
                    'key' => 'channel',
                    'label' => __('audit.index.columns.channel'),
                    'sortable' => true,
                ],
                [
                    'key' => 'actor_id',
                    'label' => __('audit.index.columns.actor'),
                    'sortable' => true,
                    'value' => static fn (array $row): array => DataGrid::stack(
                        (string) ($row['actor_type'] ?? __('audit.show.common.system')),
                        (string) (($row['actor_id'] ?? '') !== '' ? '#' . $row['actor_id'] : __('audit.show.common.not_available')),
                        ['primary_class' => '']
                    ),
                ],
                [
                    'key' => 'request_uri',
                    'label' => __('audit.index.columns.request'),
                    'value' => static fn (array $row): array => DataGrid::stack(
                        (string) ($row['request_method'] ?? ''),
                        (string) ($row['request_uri'] ?? ($row['event_name'] ?? '')),
                        ['primary_class' => 'small', 'secondary_class' => 'small text-muted text-break']
                    ),
                ],
            ])
            ->filters([
                [
                    'name' => 'channel',
                    'label' => __('audit.index.columns.channel'),
                    'type' => 'select',
                    'options' => $this->optionsFromValues($this->repository->distinctChannels()),
                ],
                [
                    'name' => 'action',
                    'label' => __('audit.index.columns.action'),
                    'type' => 'select',
                    'options' => $this->optionsFromValues($this->repository->distinctActions()),
                ],
                [
                    'name' => 'resource',
                    'label' => __('audit.index.columns.resource'),
                    'type' => 'select',
                    'options' => $this->optionsFromValues($this->repository->distinctResources()),
                ],
            ])
            ->actions([
                [
                    'label' => __('audit.index.actions.view'),
                    'class' => 'btn btn-outline-primary btn-sm',
                    'href' => '/operations/audit-log/{id}',
                ],
            ])
            ->exportFormats([
                'csv' => [
                    'label' => (string) __('ui.datagrid.export_csv'),
                    'icon' => 'fa-solid fa-file-csv',
                ],
                'xls' => [
                    'label' => (string) __('ui.datagrid.export_xls'),
                    'icon' => 'fa-solid fa-file-excel',
                ],
            ], 'audit-log')
            ->printEnabled(true, (string) __('ui.datagrid.print'))
            ->defaultSort('occurred_at')
            ->pagination(20, [20, 50, 100])
            ->searchPlaceholder(__('audit.index.search_placeholder'))
            ->provider(fn (array $state): array => $this->repository->search([
                'page' => $state['page'],
                'per_page' => $state['per_page'],
                'sort' => $state['sort'],
                'direction' => $state['direction'],
                'search' => $state['search'],
                'channel' => $state['filters']['channel'] ?? '',
                'action' => $state['filters']['action'] ?? '',
                'resource' => $state['filters']['resource'] ?? '',
            ]));

        if (in_array($gridBuilder->exportFormat($request), ['csv', 'xls'], true)) {
            $this->authorizeResource('export', 'operations-audit-log');
            return $gridBuilder->export($request);
        }

        $grid = $gridBuilder->resolve($request);

        return $this->view('audit.index', [
            'title' => __('audit.index.title'),
            'pageTitle' => __('audit.index.title'),
            'grid' => $grid,
        ]);
    }

    /**
     * Loads a single audit entry, enforces detail authorization, and renders its detail view.
     *
     * Responsibility: Loads a single audit entry, enforces detail authorization, and renders its detail view.
     */
    public function show(Request $request, string $id): Response
    {
        $entry = $this->repository->find((int) $id);

        if ($entry === null) {
            $this->flash()->error(__('audit.messages.not_found'));

            return $this->redirect('/operations/audit-log');
        }

        $this->authorizeResource('view', 'operations-audit-log', $entry);

        return $this->view('audit.show', [
            'title' => __('audit.show.title_prefix') . ' #' . (int) $entry['id'],
            'pageTitle' => __('audit.module.breadcrumb_show'),
            'entry' => $entry,
        ]);
    }

    /**
     * Converts distinct repository values into select filter options with readable labels.
     *
     * Responsibility: Converts distinct repository values into select filter options with readable labels.
     * @param string[] $values
     * @return array<string, string>
     */
    private function optionsFromValues(array $values): array
    {
        $options = [];

        foreach ($values as $value) {
            $options[$value] = strtoupper(str_replace('-', ' ', $value));
        }

        return $options;
    }
}
