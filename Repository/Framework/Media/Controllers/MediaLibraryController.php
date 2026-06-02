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

namespace Catalyst\Repository\Media\Controllers;

use Catalyst\Framework\Admin\Grid\DataGrid;
use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Catalyst\Framework\Media\MediaManager;
use Catalyst\Framework\Media\MediaRepository;
use Catalyst\Framework\Metadata\MetadataManager;
use Catalyst\Framework\Traits\InteractsWithRecordClaimsTrait;
use Catalyst\Helpers\Exceptions\OptimisticLockException;
use Catalyst\Repository\Media\Requests\MediaItemRequest;
use Catalyst\Repository\Media\Requests\MediaBulkSelectionRequest;
use Catalyst\Repository\Media\Support\MediaLibraryFormFactory;
use RuntimeException;

/**
 * Defines the Media Library Controller class contract.
 *
 * @package Catalyst\Repository\Media\Controllers
 * Responsibility: Coordinates the media library controller behavior within its module boundary.
 */
final class MediaLibraryController extends Controller
{
    use InteractsWithRecordClaimsTrait;

    /**
     * Initializes the Media Library Controller instance.
     */
    public function __construct(
        private readonly MediaRepository $repository,
        private readonly MediaManager $manager,
        private readonly MetadataManager $metadata,
        private readonly MediaLibraryFormFactory $formFactory
    ) {
        parent::__construct();
    }

    /**
     * Handles the index workflow.
     */
    public function index(Request $request): Response
    {
        $this->authorizeResource('view-any', MediaManager::RESOURCE_KEY);
        $t = static fn (string $key, array $replace = []): string => __($key, $replace);

        $definitions = $this->metadata->definitionsFor(MediaManager::RESOURCE_KEY);
        $mimeGroupOptions = [];
        foreach ($this->repository->distinctMimeGroups() as $group) {
            $mimeGroupOptions[$group] = strtoupper($group);
        }

        $gridBuilder = DataGrid::make()
            ->baseUrl('/workspaces/media-library')
            ->title($t('media.library.index.title'), $t('media.library.index.description'))
            ->emptyState(
                $t('media.library.index.empty.title'),
                $t('media.library.index.empty.description'),
                [
                    'label' => $t('media.library.index.empty.action'),
                    'href' => '/workspaces/media-library/upload',
                    'class' => 'btn btn-sm btn-primary',
                    'icon' => 'fa-solid fa-upload',
                ]
            )
            ->columns(array_merge([
                [
                    'key' => 'name',
                    'label' => $t('media.library.index.columns.asset'),
                    'sortable' => true,
                    'value' => static fn (array $row): array => DataGrid::stack(
                        (string) ($row['name'] ?? ''),
                        (string) ($row['original_name'] ?? '')
                    ),
                ],
                [
                    'key' => 'mime_type',
                    'label' => $t('media.library.index.columns.mime'),
                    'sortable' => true,
                    'value' => static fn (array $row): array => DataGrid::code((string) ($row['mime_type'] ?? '')),
                ],
                [
                    'key' => 'size_bytes',
                    'label' => $t('media.library.index.columns.size'),
                    'sortable' => true,
                    'value' => fn (array $row): string => $this->formatBytes((int) ($row['size_bytes'] ?? 0)),
                ],
                [
                    'key' => 'disk',
                    'label' => $t('media.library.index.columns.storage'),
                    'sortable' => true,
                    'value' => static fn (array $row): array => DataGrid::stack(
                        (string) ($row['disk'] ?? 'local'),
                        (string) ($row['path'] ?? ''),
                        ['secondary_class' => 'small text-muted text-break']
                    ),
                ],
                [
                    'key' => 'created_at',
                    'label' => $t('media.library.index.columns.uploaded'),
                    'sortable' => true,
                ],
            ], $this->metadata->gridColumns($definitions)))
            ->filters(array_merge([
                [
                    'name' => 'disk',
                    'label' => $t('media.library.index.filters.disk'),
                    'type' => 'select',
                    'options' => [
                        'local' => $t('media.library.common.local'),
                        'ftp' => $t('media.library.common.remote_storage'),
                    ],
                ],
                [
                    'name' => 'mime_group',
                    'label' => $t('media.library.index.filters.mime_group'),
                    'type' => 'select',
                    'options' => $mimeGroupOptions,
                ],
            ], $this->metadata->gridFilters($definitions)))
            ->actions([
                [
                    'label' => $t('media.library.index.actions.open'),
                    'class' => 'btn btn-outline-secondary btn-sm',
                    'href' => '{public_url}',
                ],
                [
                    'label' => $t('media.library.index.actions.edit'),
                    'class' => 'btn btn-outline-primary btn-sm',
                    'href' => '/workspaces/media-library/{id}/edit',
                ],
                [
                    'label' => $t('media.library.index.actions.delete'),
                    'class' => 'btn btn-outline-danger btn-sm',
                    'method' => 'POST',
                    'href' => '/workspaces/media-library/{id}/delete',
                    'confirm' => static fn (array $row): string => sprintf(
                        __('media.library.index.actions.confirm_delete'),
                        (string) ($row['name'] ?? __('media.library.common.asset_fallback'))
                    ),
                ],
            ])
            ->bulkActions([
                [
                    'label' => $t('media.library.index.bulk.delete_selected'),
                    'class' => 'btn btn-outline-danger btn-sm',
                    'method' => 'POST',
                    'href' => '/workspaces/media-library/bulk-delete',
                    'icon' => 'fa-solid fa-trash',
                    'confirm' => $t('media.library.index.bulk.confirm_delete'),
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
            ], 'media-library')
            ->resourceKey(MediaManager::RESOURCE_KEY)
            ->printEnabled(true, (string) __('ui.datagrid.print'))
            ->defaultSort('created_at', 'desc')
            ->pagination(15, [15, 30, 60])
            ->searchPlaceholder($t('media.library.index.search_placeholder'))
            ->provider(fn (array $state): array => $this->repository->search([
                'page' => $state['page'],
                'per_page' => $state['per_page'],
                'sort' => $state['sort'],
                'direction' => $state['direction'],
                'search' => $state['search'],
                'disk' => $state['filters']['disk'] ?? '',
                'mime_group' => $state['filters']['mime_group'] ?? '',
                'metadata_filters' => $this->metadata->extractGridFilters((array) ($state['filters'] ?? [])),
            ], $definitions));

        if (in_array($gridBuilder->exportFormat($request), ['csv', 'xls'], true)) {
            $this->authorizeResource('export', MediaManager::RESOURCE_KEY);
            return $gridBuilder->export($request);
        }

        return $this->view('media.index', [
            'title' => $t('media.library.index.title'),
            'pageTitle' => $t('media.library.index.title'),
            'grid' => $gridBuilder->resolve($request),
        ], 200, 'admin');
    }

    /**
     * Handles the create workflow.
     */
    public function create(Request $request): Response
    {
        $this->authorizeResource('create', MediaManager::RESOURCE_KEY);

        return $this->renderForm(__('media.library.form.create_title'), null);
    }

    /**
     * Handles the persistence workflow.
     */
    public function store(MediaItemRequest $request): Response
    {
        $this->authorizeResource('create', MediaManager::RESOURCE_KEY);

        try {
            $this->manager->create($request->validated());
        } catch (RuntimeException $e) {
            return $this->postActionErrorRedirect('/workspaces/media-library/upload', $e->getMessage(), 422);
        }

        return $this->postActionSuccessRedirect('/workspaces/media-library', __('media.library.messages.created'));
    }

    /**
     * Handles the edit workflow.
     */
    public function edit(Request $request, string $id): Response
    {
        $definitions = $this->metadata->definitionsFor(MediaManager::RESOURCE_KEY);
        $media = $this->repository->find((int) $id, $definitions);

        if ($media === null) {
            $this->flash()->error(__('media.library.messages.not_found'));

            return $this->redirect('/workspaces/media-library');
        }

        $this->authorizeResource('view', MediaManager::RESOURCE_KEY, $media);

        try {
            $claim = $this->acquireRecordClaim(MediaManager::RESOURCE_KEY, (int) $id, [
                'surface' => 'media.edit',
            ]);
        } catch (RuntimeException $e) {
            $this->flash()->error($e->getMessage());

            return $this->redirect('/workspaces/media-library');
        }

        return $this->renderForm(__('media.library.form.edit_title'), $media, $claim);
    }

    /**
     * Handles the update workflow.
     */
    public function update(MediaItemRequest $request, string $id): Response
    {
        $item = $this->repository->findModel((int) $id);

        if (!$item instanceof \Catalyst\Entities\MediaItem) {
            return $this->postActionErrorRedirect('/workspaces/media-library', __('media.library.messages.not_found'), 404);
        }

        $this->authorizeResource('update', MediaManager::RESOURCE_KEY, $item->toArray());

        try {
            $this->assertRecordClaimAvailable(MediaManager::RESOURCE_KEY, (int) $id, $request->request());
            $item->fill([
                'lock_version' => max(1, (int) $request->input('lock_version', $item->toArray()['lock_version'] ?? 1)),
            ]);
            $this->manager->update($item, $request->validated());
            $this->releaseRecordClaim(MediaManager::RESOURCE_KEY, (int) $id, $request->request(), __('media.library.messages.claim_updated'));
        } catch (OptimisticLockException|RuntimeException $e) {
            $this->rememberConcurrencyConflict($request->request(), $e);
            return $this->postActionErrorRedirect('/workspaces/media-library/' . (int) $id . '/edit', $e->getMessage(), 409);
        }

        return $this->postActionSuccessRedirect('/workspaces/media-library', __('media.library.messages.updated'));
    }

    /**
     * Handles the destroy workflow.
     */
    public function destroy(Request $request, string $id): Response
    {
        $item = $this->repository->findModel((int) $id);

        if (!$item instanceof \Catalyst\Entities\MediaItem) {
            return $this->postActionErrorRedirect('/workspaces/media-library', __('media.library.messages.not_found'), 404);
        }

        $this->authorizeResource('delete', MediaManager::RESOURCE_KEY, $item->toArray());

        try {
            $this->assertRecordClaimAvailable(MediaManager::RESOURCE_KEY, (int) $id, $request);
            $this->manager->delete($item);
            $this->releaseRecordClaim(MediaManager::RESOURCE_KEY, (int) $id, $request, __('media.library.messages.claim_deleted'));
        } catch (RuntimeException $e) {
            return $this->postActionErrorRedirect('/workspaces/media-library', $e->getMessage(), 409);
        }

        return $this->postActionSuccessRedirect('/workspaces/media-library', __('media.library.messages.deleted'));
    }

    /**
     * Handles the bulk destroy workflow.
     */
    public function bulkDestroy(Request $request): Response
    {
        $this->authorizeResource('bulk-delete', MediaManager::RESOURCE_KEY);

        $ids = (new MediaBulkSelectionRequest($request))->ids();

        if ($ids === []) {
            return $this->postActionErrorRedirect('/workspaces/media-library', __('media.library.index.bulk.select_one'));
        }

        foreach ($ids as $mediaId) {
            $item = $this->repository->findModel($mediaId);
            if ($item instanceof \Catalyst\Entities\MediaItem) {
                $this->assertRecordClaimAvailable(MediaManager::RESOURCE_KEY, $mediaId, $request);
                $this->manager->delete($item);
                $this->releaseRecordClaim(MediaManager::RESOURCE_KEY, $mediaId, $request, __('media.library.messages.claim_bulk_deleted'));
            }
        }

        return $this->postActionSuccessRedirect('/workspaces/media-library', sprintf(__('media.library.index.bulk.deleted_suffix'), count($ids)));
    }

    /**
     * @param array<string, mixed>|null $media
     */
    private function renderForm(string $title, ?array $media, ?array $claim = null): Response
    {
        $form = $this->formFactory->build(
            $media,
            $this->concurrencyHiddenFields(
                $claim,
                $media !== null ? (int) ($media['lock_version'] ?? 1) : null
            )
        );

        return $this->view('media.form', [
            'title' => $title,
            'pageTitle' => $title,
            'media' => $media,
            'form' => $form,
            'claimContext' => $this->buildRecordClaimContext($claim),
        ], 200, 'admin');
    }

    /**
     * Handles the format bytes workflow.
     */
    private function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . ' B';
        }

        $units = ['KB', 'MB', 'GB', 'TB'];
        $size = $bytes / 1024;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return number_format($size, 2) . ' ' . $units[$unit];
    }
}
