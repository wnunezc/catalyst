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

namespace Catalyst\Repository\Workspaces\Documents\Support;

use Catalyst\Framework\DataGrid\DataGrid;
use Catalyst\Framework\Document\DocumentTemplateManager;
use Catalyst\Framework\Document\DocumentTemplateRepository;

/**
 * Builds the administrative document template data grid.
 *
 * @package Catalyst\Repository\Workspaces\Documents\Support
 * Responsibility: Configure template columns, filters, row actions and repository-backed pagination.
 */
final class DocumentTemplateGridFactory
{
    /**
     * Builds the document template listing grid backed by repository search.
     *
     * Responsibility: Builds the document template listing grid backed by repository search.
     */
    public function build(DocumentTemplateRepository $repository): DataGrid
    {
        return DataGrid::make()
            ->baseUrl('/workspaces/document-templates')
            ->title(__('documents.index.title'), __('documents.index.description'))
            ->emptyState(
                __('documents.index.empty.title'),
                __('documents.index.empty.description'),
                [
                    'label' => __('documents.index.empty.action'),
                    'href' => '/workspaces/document-templates/create',
                    'class' => 'btn btn-sm btn-primary',
                    'icon' => 'fa-solid fa-plus',
                ]
            )
            ->columns([
                [
                    'key' => 'name',
                    'label' => __('documents.index.columns.template'),
                    'sortable' => true,
                    'value' => static fn (array $row): array => DataGrid::stack(
                        (string) ($row['name'] ?? ''),
                        (string) ($row['slug'] ?? '')
                    ),
                ],
                ['key' => 'format', 'label' => __('documents.index.columns.format'), 'sortable' => true],
                [
                    'key' => 'current_state',
                    'label' => __('documents.index.columns.workflow'),
                    'sortable' => false,
                    'value' => static fn (array $row): array => DataGrid::badge((string) ($row['current_state'] ?? 'draft')),
                ],
                ['key' => 'version_count', 'label' => __('documents.index.columns.versions')],
                ['key' => 'artifact_count', 'label' => __('documents.index.columns.artifacts')],
                ['key' => 'updated_at', 'label' => __('documents.index.columns.updated'), 'sortable' => true],
            ])
            ->filters([
                [
                    'name' => 'format',
                    'label' => __('documents.index.filters.format'),
                    'type' => 'select',
                    'options' => [
                        'html' => 'HTML',
                        'text' => __('documents.index.formats.text'),
                        'pdf' => 'PDF',
                    ],
                ],
                [
                    'name' => 'state',
                    'label' => __('documents.index.filters.workflow_state'),
                    'type' => 'select',
                    'options' => [
                        'draft' => __('documents.index.states.draft'),
                        'in_review' => __('documents.index.states.in_review'),
                        'approved' => __('documents.index.states.approved'),
                        'archived' => __('documents.index.states.archived'),
                    ],
                ],
            ])
            ->actions([
                ['label' => __('documents.index.actions.view'), 'class' => 'btn btn-outline-secondary btn-sm', 'href' => '/workspaces/document-templates/{id}'],
                ['label' => __('documents.index.actions.edit'), 'class' => 'btn btn-outline-primary btn-sm', 'href' => '/workspaces/document-templates/{id}/edit'],
                ['label' => __('documents.index.actions.export'), 'class' => 'btn btn-outline-success btn-sm', 'method' => 'POST', 'href' => '/workspaces/document-templates/{id}/export'],
                [
                    'label' => __('documents.index.actions.delete'),
                    'class' => 'btn btn-outline-danger btn-sm',
                    'method' => 'POST',
                    'href' => '/workspaces/document-templates/{id}/delete',
                    'confirm' => static fn (array $row): string => sprintf(
                        __('documents.index.actions.confirm_delete'),
                        (string) ($row['name'] ?? __('documents.show.template_fallback'))
                    ),
                ],
            ])
            ->defaultSort('updated_at', 'desc')
            ->pagination(15, [15, 30, 60])
            ->searchPlaceholder(__('documents.index.search_placeholder'))
            ->resourceKey(DocumentTemplateManager::RESOURCE_KEY)
            ->provider(fn (array $state): array => $repository->search([
                'page' => $state['page'],
                'per_page' => $state['per_page'],
                'search' => $state['search'],
                'format' => $state['filters']['format'] ?? '',
                'state' => $state['filters']['state'] ?? '',
            ]));
    }
}
