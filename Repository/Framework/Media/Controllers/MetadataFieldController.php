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

use Catalyst\Framework\DataGrid\DataGrid;
use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Catalyst\Framework\Catalog\CatalogRepository;
use Catalyst\Framework\Metadata\MetadataFieldRepository;
use Catalyst\Framework\Metadata\MetadataManager;
use Catalyst\Framework\Metadata\MetadataResourceRegistry;
use Catalyst\Framework\Traits\InteractsWithRecordClaimsTrait;
use Catalyst\Helpers\Exceptions\OptimisticLockException;
use Catalyst\Repository\Media\Requests\MetadataFieldDefinitionRequest;
use Catalyst\Repository\Media\Support\MetadataFieldFormFactory;
use RuntimeException;

/**
 * Serves the administrative dynamic metadata field workflow.
 *
 * @package Catalyst\Repository\Media\Controllers
 * Responsibility: Render metadata field screens and coordinate authorized CRUD and export actions.
 */
final class MetadataFieldController extends Controller
{
    use InteractsWithRecordClaimsTrait;

    /**
     * Initializes the Metadata Field Controller instance.
     *
     * Responsibility: Initializes the Metadata Field Controller instance.
     */
    public function __construct(
        private readonly MetadataFieldRepository $repository,
        private readonly MetadataManager $metadata,
        private readonly MetadataResourceRegistry $resources,
        private readonly CatalogRepository $catalogs,
        private readonly MetadataFieldFormFactory $formFactory
    ) {
        parent::__construct();
    }

    /**
     * Renders or exports the searchable metadata field listing.
     *
     * Responsibility: Renders or exports the searchable metadata field listing.
     */
    public function index(Request $request): Response
    {
        $this->authorizeResource('view-any', 'metadata-fields');

        $gridBuilder = DataGrid::make()
            ->baseUrl('/workspaces/media-fields')
            ->resourceKey('metadata-fields')
            ->title(__('media.fields.index.title'), __('media.fields.index.description'))
            ->emptyState(
                __('media.fields.index.empty.title'),
                __('media.fields.index.empty.description'),
                [
                    'label' => __('media.fields.index.empty.action'),
                    'href' => '/workspaces/media-fields/create',
                    'class' => 'btn btn-sm btn-primary',
                    'icon' => 'fa-solid fa-plus',
                ]
            )
            ->columns([
                [
                    'key' => 'resource_key',
                    'label' => __('media.fields.index.columns.resource'),
                    'sortable' => true,
                ],
                [
                    'key' => 'label',
                    'label' => __('media.fields.index.columns.field'),
                    'sortable' => true,
                    'value' => static fn (array $row): array => DataGrid::stack(
                        (string) ($row['label'] ?? ''),
                        (string) ($row['field_key'] ?? ''),
                        ['secondary_is_code' => true]
                    ),
                ],
                [
                    'key' => 'type',
                    'label' => __('media.fields.index.columns.type'),
                    'sortable' => true,
                    'value' => static fn (array $row): array => DataGrid::stack(
                        (string) ($row['type'] ?? 'text'),
                        trim((string) ($row['catalog_key'] ?? '')),
                        ['secondary_is_code' => true]
                    ),
                ],
                [
                    'key' => 'section_key',
                    'label' => __('media.fields.index.columns.section'),
                    'sortable' => true,
                    'empty' => __('media.fields.index.columns.extended_metadata'),
                ],
                [
                    'key' => 'flags',
                    'label' => __('media.fields.index.columns.flags'),
                    'value' => static fn (array $row): array => DataGrid::badges(array_values(array_filter([
                        !empty($row['is_required']) ? ['label' => __('media.fields.index.flags.required'), 'class' => 'text-bg-primary'] : null,
                        !empty($row['is_listed']) ? ['label' => __('media.fields.index.flags.listed'), 'class' => 'text-bg-secondary'] : null,
                        !empty($row['is_filterable']) ? ['label' => __('media.fields.index.flags.filterable'), 'class' => 'text-bg-success'] : null,
                    ]))),
                ],
            ])
            ->filters([
                [
                    'name' => 'resource_key',
                    'label' => __('media.fields.index.columns.resource'),
                    'type' => 'select',
                    'options' => $this->resources->options(),
                ],
                [
                    'name' => 'type',
                    'label' => __('media.fields.index.columns.type'),
                    'type' => 'select',
                    'options' => $this->metadata->supportedTypes(),
                ],
            ])
            ->actions([
                [
                    'label' => __('media.fields.index.actions.edit'),
                    'class' => 'btn btn-outline-primary btn-sm',
                    'href' => '/workspaces/media-fields/{id}/edit',
                ],
                [
                    'label' => __('media.fields.index.actions.delete'),
                    'class' => 'btn btn-outline-danger btn-sm',
                    'method' => 'POST',
                    'href' => '/workspaces/media-fields/{id}/delete',
                    'confirm' => static fn (array $row): string => sprintf(
                        __('media.fields.index.actions.confirm_delete'),
                        (string) ($row['label'] ?? __('media.fields.form.labels.field_label'))
                    ),
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
            ], 'media-metadata-fields')
            ->printEnabled(true, (string) __('ui.datagrid.print'))
            ->defaultSort('sort_order')
            ->pagination(15, [15, 30, 60])
            ->searchPlaceholder(__('media.fields.index.search_placeholder'))
            ->provider(fn (array $state): array => $this->repository->search([
                'page' => $state['page'],
                'per_page' => $state['per_page'],
                'sort' => $state['sort'],
                'direction' => $state['direction'],
                'search' => $state['search'],
                'resource_key' => $state['filters']['resource_key'] ?? '',
                'type' => $state['filters']['type'] ?? '',
            ]));

        if (in_array($gridBuilder->exportFormat($request), ['csv', 'xls'], true)) {
            $this->authorizeResource('export', 'metadata-fields');
            return $gridBuilder->export($request);
        }

        return $this->view('media.fields-index', [
            'title' => __('media.fields.index.title'),
            'pageTitle' => __('media.fields.index.title'),
            'grid' => $gridBuilder->resolve($request),
        ]);
    }

    /**
     * Renders the metadata field creation form.
     *
     * Responsibility: Renders the metadata field creation form.
     */
    public function create(Request $request): Response
    {
        $this->authorizeResource('create', 'metadata-fields');

        return $this->renderForm(__('media.fields.form.create_title'), null);
    }

    /**
     * Normalizes and persists a validated metadata field definition.
     *
     * Responsibility: Normalizes and persists a validated metadata field definition.
     */
    public function store(MetadataFieldDefinitionRequest $request): Response
    {
        $this->authorizeResource('create', 'metadata-fields');
        $payload = $this->metadata->normalizeDefinitionPayload($request->validated());
        $this->repository->persist($payload);
        return $this->postActionSuccessRedirect('/workspaces/media-fields', __('media.fields.messages.created'));
    }

    /**
     * Acquires a record claim and renders the metadata field edit form.
     *
     * Responsibility: Acquires a record claim and renders the metadata field edit form.
     */
    public function edit(Request $request, string $id): Response
    {
        $field = $this->repository->find((int) $id);

        if ($field === null) {
            $this->flash()->error(__('media.fields.messages.not_found'));

            return $this->redirect('/workspaces/media-fields');
        }

        $this->authorizeResource('view', 'metadata-fields', $field);

        try {
            $claim = $this->acquireRecordClaim('metadata-fields', (int) $id, [
                'surface' => 'metadata-fields.edit',
            ]);
        } catch (RuntimeException $e) {
            $this->flash()->error($e->getMessage());

            return $this->redirect('/workspaces/media-fields');
        }

        return $this->renderForm(__('media.fields.form.edit_title'), $field, $claim);
    }

    /**
     * Updates a metadata field definition while handling concurrency conflicts.
     *
     * Responsibility: Updates a metadata field definition while handling concurrency conflicts.
     */
    public function update(MetadataFieldDefinitionRequest $request, string $id): Response
    {
        $model = $this->repository->findModel((int) $id);

        if (!$model instanceof \Catalyst\Entities\MetadataFieldDefinition) {
            return $this->postActionErrorRedirect('/workspaces/media-fields', __('media.fields.messages.not_found'), 404);
        }

        $this->authorizeResource('update', 'metadata-fields', $model->toArray());

        try {
            $this->assertRecordClaimAvailable('metadata-fields', (int) $id, $request->request());
            $model->fill([
                'lock_version' => max(1, (int) $request->input('lock_version', $model->toArray()['lock_version'] ?? 1)),
            ]);
            $payload = $this->metadata->normalizeDefinitionPayload($request->validated());
            $this->repository->persist($payload, $model);
            $this->releaseRecordClaim('metadata-fields', (int) $id, $request->request(), 'metadata field updated');
        } catch (OptimisticLockException|RuntimeException $e) {
            $this->rememberConcurrencyConflict($request->request(), $e);
            return $this->postActionErrorRedirect('/workspaces/media-fields/' . (int) $id . '/edit', $e->getMessage(), 409);
        }
        return $this->postActionSuccessRedirect('/workspaces/media-fields', __('media.fields.messages.updated'));
    }

    /**
     * Deletes one metadata field definition after claim validation.
     *
     * Responsibility: Deletes one metadata field definition after claim validation.
     */
    public function destroy(Request $request, string $id): Response
    {
        $model = $this->repository->findModel((int) $id);

        if (!$model instanceof \Catalyst\Entities\MetadataFieldDefinition) {
            return $this->postActionErrorRedirect('/workspaces/media-fields', __('media.fields.messages.not_found'), 404);
        }

        $this->authorizeResource('delete', 'metadata-fields', $model->toArray());

        try {
            $this->assertRecordClaimAvailable('metadata-fields', (int) $id, $request);
            $this->repository->delete($model);
            $this->releaseRecordClaim('metadata-fields', (int) $id, $request, 'metadata field deleted');
        } catch (RuntimeException $e) {
            return $this->postActionErrorRedirect('/workspaces/media-fields', $e->getMessage(), 409);
        }
        return $this->postActionSuccessRedirect('/workspaces/media-fields', __('media.fields.messages.deleted'));
    }

    /**
     * Builds and renders the create or edit form for a metadata field definition.
     *
     * Responsibility: Builds and renders the create or edit form for a metadata field definition.
     * @param array<string, mixed>|null $field
     */
    private function renderForm(string $title, ?array $field, ?array $claim = null): Response
    {
        $action = $field === null
            ? '/workspaces/media-fields'
            : '/workspaces/media-fields/' . (int) ($field['id'] ?? 0);
        $defaults = [
                'select_options' => $field === null ? '' : $this->metadata->selectOptionsText($field),
        ];
        $fields = array_merge(
            $this->concurrencyHiddenFields(
                $claim,
                $field !== null ? (int) ($field['lock_version'] ?? 1) : null
            ),
            [
                'resource_key' => [
                    'label' => __('media.fields.form.labels.resource'),
                    'required' => true,
                    'section' => 'identity',
                    'type' => 'select',
                    'options' => $this->resources->options(),
                ],
                'label' => [
                    'label' => __('media.fields.form.labels.field_label'),
                    'required' => true,
                    'section' => 'identity',
                    'placeholder' => __('media.fields.form.placeholders.field_label'),
                    'attributes' => ['maxlength' => 120],
                ],
                'field_key' => [
                    'label' => __('media.fields.form.labels.field_key'),
                    'required' => true,
                    'section' => 'identity',
                    'placeholder' => __('media.fields.form.placeholders.field_key'),
                    'help' => __('media.fields.form.help.field_key'),
                    'attributes' => ['maxlength' => 100],
                ],
                'type' => [
                    'label' => __('media.fields.form.labels.type'),
                    'required' => true,
                    'section' => 'identity',
                    'type' => 'select',
                    'options' => $this->metadata->supportedTypes(),
                ],
                'catalog_key' => [
                    'label' => __('media.fields.form.labels.catalog'),
                    'section' => 'identity',
                    'type' => 'select',
                    'options' => $this->metadata->catalogDefinitionOptions(),
                    'depends_on' => 'type',
                    'depends_values' => ['catalog'],
                    'help' => __('media.fields.form.help.catalog'),
                ],
                'section_key' => [
                    'label' => __('media.fields.form.labels.section_key'),
                    'section' => 'behavior',
                    'placeholder' => __('media.fields.form.placeholders.section_key'),
                    'help' => __('media.fields.form.help.section_key'),
                    'attributes' => ['maxlength' => 100],
                ],
                'help_text' => [
                    'label' => __('media.fields.form.labels.help_text'),
                    'section' => 'behavior',
                    'placeholder' => __('media.fields.form.placeholders.help_text'),
                    'attributes' => ['maxlength' => 255],
                ],
                'placeholder' => [
                    'label' => __('media.fields.form.labels.placeholder'),
                    'section' => 'behavior',
                    'placeholder' => __('media.fields.form.placeholders.placeholder'),
                    'attributes' => ['maxlength' => 255],
                ],
                'is_required' => [
                    'label' => __('media.fields.form.labels.required'),
                    'type' => 'checkbox',
                    'section' => 'behavior',
                    'help' => __('media.fields.form.help.required'),
                ],
                'is_listed' => [
                    'label' => __('media.fields.form.labels.listed'),
                    'type' => 'checkbox',
                    'section' => 'behavior',
                    'help' => __('media.fields.form.help.listed'),
                ],
                'is_filterable' => [
                    'label' => __('media.fields.form.labels.filterable'),
                    'type' => 'checkbox',
                    'section' => 'behavior',
                    'help' => __('media.fields.form.help.filterable'),
                ],
                'sort_order' => [
                    'label' => __('media.fields.form.labels.sort_order'),
                    'type' => 'number',
                    'section' => 'behavior',
                    'attributes' => ['min' => 0, 'step' => 1],
                    'value' => $field['sort_order'] ?? 100,
                ],
                'default_value' => [
                    'label' => __('media.fields.form.labels.default_value'),
                    'section' => 'type-configuration',
                    'placeholder' => __('media.fields.form.placeholders.default_value'),
                ],
                'max_length' => [
                    'label' => __('media.fields.form.labels.max_length'),
                    'type' => 'number',
                    'section' => 'type-configuration',
                    'depends_on' => 'type',
                    'depends_values' => ['text', 'textarea'],
                    'attributes' => ['min' => 1, 'step' => 1],
                ],
                'min_value' => [
                    'label' => __('media.fields.form.labels.min_value'),
                    'type' => 'number',
                    'section' => 'type-configuration',
                    'depends_on' => 'type',
                    'depends_values' => ['number'],
                    'attributes' => ['step' => '0.01'],
                ],
                'max_value' => [
                    'label' => __('media.fields.form.labels.max_value'),
                    'type' => 'number',
                    'section' => 'type-configuration',
                    'depends_on' => 'type',
                    'depends_values' => ['number'],
                    'attributes' => ['step' => '0.01'],
                ],
                'rules_extra' => [
                    'label' => __('media.fields.form.labels.extra_rules'),
                    'section' => 'type-configuration',
                    'placeholder' => __('media.fields.form.placeholders.extra_rules'),
                    'help' => __('media.fields.form.help.extra_rules'),
                ],
                'select_options' => [
                    'label' => __('media.fields.form.labels.select_options'),
                    'type' => 'textarea',
                    'section' => 'type-configuration',
                    'depends_on' => 'type',
                    'depends_values' => ['select'],
                    'placeholder' => __('media.fields.form.placeholders.select_options'),
                    'help' => __('media.fields.form.help.select_options'),
                ],
            ]
        );
        $form = $this->formFactory->build($action, $field, $defaults, $fields);

        return $this->view('media.field-form', [
            'title' => $title,
            'pageTitle' => $title,
            'field' => $field,
            'form' => $form,
            'recordPresence' => $this->buildRecordPresenceContext($claim),
        ]);
    }
}
