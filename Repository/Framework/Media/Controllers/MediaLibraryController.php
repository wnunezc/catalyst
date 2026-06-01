<?php

declare(strict_types=1);

namespace Catalyst\Repository\Media\Controllers;

use Catalyst\Framework\Admin\Form\FormBuilder;
use Catalyst\Framework\Admin\Grid\DataGrid;
use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Catalyst\Framework\Media\MediaManager;
use Catalyst\Framework\Media\MediaRepository;
use Catalyst\Framework\Metadata\MetadataManager;
use Catalyst\Framework\Storage\StorageManager;
use Catalyst\Framework\Traits\InteractsWithRecordClaimsTrait;
use Catalyst\Helpers\Exceptions\OptimisticLockException;
use Catalyst\Repository\Media\Requests\MediaItemRequest;
use RuntimeException;

final class MediaLibraryController extends Controller
{
    use InteractsWithRecordClaimsTrait;

    public function __construct(
        private readonly MediaRepository $repository,
        private readonly MediaManager $manager,
        private readonly MetadataManager $metadata
    ) {
        parent::__construct();
    }

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

    public function create(Request $request): Response
    {
        $this->authorizeResource('create', MediaManager::RESOURCE_KEY);

        return $this->renderForm(__('media.library.form.create_title'), null);
    }

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

    public function bulkDestroy(Request $request): Response
    {
        $this->authorizeResource('bulk-delete', MediaManager::RESOURCE_KEY);

        $ids = array_values(array_filter(
            array_map('intval', (array) ($request->input('selected') ?? [])),
            static fn (int $id): bool => $id > 0
        ));

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
        $t = static fn (string $key, array $replace = []): string => __($key, $replace);
        $definitions = $this->metadata->definitionsFor(MediaManager::RESOURCE_KEY);
        $dynamicValues = $media !== null
            ? (array) ($media['metadata'] ?? [])
            : [];
        $action = $media === null
            ? '/workspaces/media-library'
            : '/workspaces/media-library/' . (int) ($media['id'] ?? 0);
        $storageSummary = StorageManager::getInstance()->summary();
        $diskOptions = ['local' => $t('media.library.common.local')];
        if ((bool) ($storageSummary['remote_ready'] ?? false)) {
            $diskOptions['ftp'] = $t('media.library.common.remote_storage');
        }

        $currentDisk = trim((string) ($media['disk'] ?? ''));
        if ($currentDisk !== '' && !array_key_exists($currentDisk, $diskOptions)) {
            $diskOptions[$currentDisk] = strtoupper($currentDisk);
        }

        $sections = array_merge([
            'asset-file' => [
                'title' => $t('media.library.form.sections.asset_file.title'),
                'description' => $t('media.library.form.sections.asset_file.description'),
            ],
            'asset-context' => [
                'title' => $t('media.library.form.sections.asset_context.title'),
                'description' => $t('media.library.form.sections.asset_context.description'),
            ],
        ], $this->metadata->formSections($definitions, $dynamicValues));

        $fields = array_merge([
            ...$this->concurrencyHiddenFields(
                $claim,
                $media !== null ? (int) ($media['lock_version'] ?? 1) : null
            ),
            'name' => [
                'label' => $t('media.library.form.labels.name'),
                'required' => true,
                'section' => 'asset-context',
                'placeholder' => $t('media.library.form.placeholders.name'),
                'attributes' => ['maxlength' => 150],
            ],
            'disk' => [
                'label' => $t('media.library.form.labels.disk'),
                'required' => true,
                'section' => 'asset-file',
                'type' => 'select',
                'options' => $diskOptions,
                'value' => $media['disk'] ?? 'local',
                'help' => $t('media.library.form.help.disk'),
            ],
            'asset_file' => [
                'label' => $media === null ? $t('media.library.form.labels.asset_file_create') : $t('media.library.form.labels.asset_file_edit'),
                'required' => $media === null,
                'section' => 'asset-file',
                'type' => 'file',
                'help' => $media === null
                    ? $t('media.library.form.help.asset_file_create')
                    : $t('media.library.form.help.asset_file_edit'),
            ],
        ], $this->metadata->formFields($definitions, $dynamicValues));

        $defaults = [];
        foreach ($dynamicValues as $fieldKey => $entry) {
            $defaults[MetadataManager::inputKey((string) $fieldKey)] = $entry['value'] ?? null;
        }

        $form = FormBuilder::make()
            ->action($action)
            ->method('POST')
            ->multipart()
            ->model($media)
            ->defaults($defaults)
            ->sections($sections)
            ->autosave()
            ->fields($fields)
            ->actions([
                [
                    'type' => 'submit',
                    'label' => $media === null ? $t('media.library.form.actions.create') : $t('media.library.form.actions.save'),
                    'class' => 'btn btn-primary',
                ],
                [
                    'type' => 'link',
                    'label' => $t('media.library.form.actions.back'),
                    'href' => '/workspaces/media-library',
                    'class' => 'btn btn-outline-secondary',
                ],
            ])
            ->toArray();

        return $this->view('media.form', [
            'title' => $title,
            'pageTitle' => $title,
            'media' => $media,
            'form' => $form,
            'claimContext' => $this->buildRecordClaimContext($claim),
        ], 200, 'admin');
    }

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
