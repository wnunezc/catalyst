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

namespace Catalyst\Repository\Workspaces\Catalogs\Support;

use Catalyst\Framework\DataGrid\DataGrid;
use Catalyst\Framework\Catalog\CatalogManager;
use Catalyst\Framework\Catalog\CatalogRepository;

/**
 * Builds the administrative catalog definition data grid.
 *
 * @package Catalyst\Repository\Workspaces\Catalogs\Support
 * Responsibility: Configure catalog columns, filters, row actions and repository-backed pagination.
 */
final class CatalogGridFactory
{
    /**
     * Builds the catalog definition listing grid backed by repository search.
     *
     * Responsibility: Builds the catalog definition listing grid backed by repository search.
     */
    public function buildIndexGrid(CatalogRepository $repository): DataGrid
    {
        return DataGrid::make()
            ->baseUrl('/workspaces/catalogs')
            ->title(__('catalogs.index.title'), __('catalogs.index.description'))
            ->emptyState(
                __('catalogs.index.empty.title'),
                __('catalogs.index.empty.description'),
                [
                    'label' => __('catalogs.index.empty.action'),
                    'href' => '/workspaces/catalogs/create',
                    'class' => 'btn btn-sm btn-primary',
                    'icon' => 'fa-solid fa-plus',
                ]
            )
            ->columns([
                [
                    'key' => 'label',
                    'label' => __('catalogs.index.columns.catalog'),
                    'sortable' => true,
                    'value' => static fn (array $row): array => DataGrid::stack(
                        (string) ($row['label'] ?? ''),
                        (string) ($row['catalog_key'] ?? ''),
                        ['secondary_is_code' => true]
                    ),
                ],
                [
                    'key' => 'current_state',
                    'label' => __('catalogs.index.columns.workflow'),
                    'value' => static fn (array $row): array => DataGrid::badge((string) ($row['current_state'] ?? 'draft')),
                ],
                [
                    'key' => 'item_count',
                    'label' => __('catalogs.index.columns.items'),
                    'sortable' => true,
                    'value' => static fn (array $row): string => (int) ($row['enabled_item_count'] ?? 0)
                        . ' / ' . (int) ($row['item_count'] ?? 0),
                ],
                [
                    'key' => 'updated_at',
                    'label' => __('catalogs.index.columns.updated'),
                    'sortable' => true,
                ],
            ])
            ->filters([
                [
                    'name' => 'state',
                    'label' => __('catalogs.index.filters.workflow_state'),
                    'type' => 'select',
                    'options' => [
                        'draft' => __('catalogs.index.states.draft'),
                        'active' => __('catalogs.index.states.active'),
                        'archived' => __('catalogs.index.states.archived'),
                    ],
                ],
            ])
            ->actions([
                [
                    'label' => __('catalogs.index.actions.view'),
                    'class' => 'btn btn-outline-secondary btn-sm',
                    'href' => '/workspaces/catalogs/{id}',
                ],
                [
                    'label' => __('catalogs.index.actions.edit'),
                    'class' => 'btn btn-outline-primary btn-sm',
                    'href' => '/workspaces/catalogs/{id}/edit',
                ],
                [
                    'label' => __('catalogs.index.actions.delete'),
                    'class' => 'btn btn-outline-danger btn-sm',
                    'method' => 'POST',
                    'href' => '/workspaces/catalogs/{id}/delete',
                    'confirm' => static fn (array $row): string => sprintf(
                        __('catalogs.index.actions.confirm_delete'),
                        (string) ($row['label'] ?? __('catalogs.show.catalog_fallback'))
                    ),
                ],
            ])
            ->resourceKey(CatalogManager::RESOURCE_KEY)
            ->defaultSort('updated_at', 'desc')
            ->pagination(15, [15, 30, 60])
            ->searchPlaceholder(__('catalogs.index.search_placeholder'))
            ->provider(fn (array $state): array => $repository->searchDefinitions([
                'page' => $state['page'],
                'per_page' => $state['per_page'],
                'sort' => $state['sort'],
                'direction' => $state['direction'],
                'search' => $state['search'],
                'state' => $state['filters']['state'] ?? '',
            ]));
    }
}
