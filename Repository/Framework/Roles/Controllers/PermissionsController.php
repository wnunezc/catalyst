<?php

declare(strict_types=1);

namespace Catalyst\Repository\Roles\Controllers;

use Catalyst\Framework\Admin\Form\FormBuilder;
use Catalyst\Framework\Admin\Grid\DataGrid;
use Catalyst\Framework\Authorization\RoleRepository;
use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Catalyst\Framework\Traits\InteractsWithRecordClaimsTrait;
use Catalyst\Repository\Roles\Support\RbacLabelPresenter;
use RuntimeException;
use Catalyst\Repository\Roles\Requests\PermissionPayloadRequest;

class PermissionsController extends Controller
{
    use InteractsWithRecordClaimsTrait;

    public function __construct(
        private readonly RoleRepository $repo
    ) {
        parent::__construct();
    }

    public function index(Request $request): Response
    {
        $this->authorizeResource('view-any', 'permissions');

        $prefixOptions = [];
        foreach ($this->repo->permissionPrefixes() as $prefix) {
            $prefixOptions[$prefix] = strtoupper($prefix);
        }

        $gridBuilder = DataGrid::make()
            ->baseUrl('/users/permissions')
            ->title((string) __('roles.permissions.listing_title'), (string) __('roles.permissions.listing_description'))
            ->emptyState(
                (string) __('roles.permissions.empty'),
                (string) __('roles.permissions.empty_description'),
                [
                    'label' => (string) __('roles.permissions.new'),
                    'href' => '/users/permissions/create',
                    'class' => 'btn btn-sm btn-primary',
                    'icon' => 'fa-solid fa-plus',
                ]
            )
            ->columns([
                [
                    'key' => 'name',
                    'label' => (string) __('roles.common.name'),
                    'sortable' => true,
                    'value' => static fn (array $row): array => DataGrid::stack(
                        RbacLabelPresenter::permissionName((string) ($row['name'] ?? ''), (string) ($row['slug'] ?? '')),
                        '#' . (int) ($row['id'] ?? 0)
                    ),
                ],
                [
                    'key' => 'slug',
                    'label' => (string) __('roles.common.slug'),
                    'sortable' => true,
                    'value' => static fn (array $row): array => DataGrid::code((string) ($row['slug'] ?? '')),
                ],
                [
                    'key' => 'description',
                    'label' => (string) __('roles.common.description'),
                    'sortable' => true,
                    'class' => 'text-muted small',
                    'value' => static fn (array $row): ?string => RbacLabelPresenter::permissionDescription(
                        isset($row['description']) ? (string) $row['description'] : null,
                        (string) ($row['slug'] ?? '')
                    ),
                    'empty' => (string) __('roles.common.no_description'),
                ],
            ])
            ->filters([
                [
                    'name' => 'slug_prefix',
                    'label' => (string) __('roles.permissions.filters.prefix'),
                    'type' => 'select',
                    'options' => $prefixOptions,
                ],
            ])
            ->actions([
                [
                    'label' => (string) __('roles.common.edit'),
                    'class' => 'btn btn-outline-primary btn-sm',
                    'href' => '/users/permissions/{id}/edit',
                ],
                [
                    'label' => (string) __('roles.common.delete'),
                    'class' => 'btn btn-outline-danger btn-sm',
                    'method' => 'POST',
                    'href' => '/users/permissions/{id}/delete',
                    'confirm' => static fn (array $row): string => sprintf(
                        (string) __('roles.permissions.confirm_delete'),
                        RbacLabelPresenter::permissionName((string) ($row['name'] ?? ''), (string) ($row['slug'] ?? ''))
                    ),
                ],
            ])
            ->bulkActions([
                [
                    'label' => (string) __('roles.permissions.bulk.delete_selected'),
                    'class' => 'btn btn-outline-danger btn-sm',
                    'method' => 'POST',
                    'href' => '/users/permissions/bulk-delete',
                    'icon' => 'fa-solid fa-trash',
                    'confirm' => (string) __('roles.permissions.bulk.confirm_delete'),
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
            ], 'permissions-catalog')
            ->printEnabled(true, (string) __('ui.datagrid.print'))
            ->defaultSort('name')
            ->pagination(10, [10, 25, 50])
            ->searchPlaceholder((string) __('roles.permissions.search_placeholder'))
            ->provider(fn (array $state): array => $this->repo->searchPermissions([
                'page' => $state['page'],
                'per_page' => $state['per_page'],
                'sort' => $state['sort'],
                'direction' => $state['direction'],
                'search' => $state['search'],
                'slug_prefix' => $state['filters']['slug_prefix'] ?? '',
            ]));

        if (in_array($gridBuilder->exportFormat($request), ['csv', 'xls'], true)) {
            $this->authorizeResource('export', 'permissions');
            return $gridBuilder->export($request);
        }

        $grid = $gridBuilder->resolve($request);

        return $this->view('roles.permissions-list', [
            'title' => (string) __('roles.permissions.title'),
            'pageTitle' => (string) __('roles.permissions.title'),
            'grid' => $grid,
        ], 200, 'admin');
    }

    public function create(Request $request): Response
    {
        $this->authorizeResource('create', 'permissions');
        return $this->renderForm((string) __('roles.permissions.create_title'), null);
    }

    public function store(PermissionPayloadRequest $request): Response
    {
        $this->authorizeResource('create', 'permissions');
        $payload = $request->validated();

        $this->repo->createPermission(
            trim((string) ($payload['name'] ?? '')),
            trim((string) ($payload['slug'] ?? '')),
            $this->normalizeDescription($payload['description'] ?? null)
        );
        return $this->postActionSuccessRedirect('/users/permissions', (string) __('roles.permissions.created'));
    }

    public function edit(Request $request, string $id): Response
    {
        $permission = $this->repo->findPermission((int) $id);

        if ($permission === null) {
            return $this->postActionErrorRedirect('/users/permissions', (string) __('roles.permissions.not_found'), 404);
        }

        $this->authorizeResource('view', 'permissions', $permission);

        try {
            $claim = $this->acquireRecordClaim('permissions', (int) $id, [
                'surface' => 'permissions.edit',
            ]);
        } catch (RuntimeException $e) {
            $this->flash()->error($e->getMessage());

            return $this->redirect('/users/permissions');
        }

        return $this->renderForm((string) __('roles.permissions.edit_title'), $permission, $claim);
    }

    public function update(PermissionPayloadRequest $request, string $id): Response
    {
        $permissionId = (int) $id;
        $permission = $this->repo->findPermission($permissionId);

        if ($permission === null) {
            $this->flash()->error((string) __('roles.permissions.not_found'));

            return $this->redirect('/users/permissions');
        }

        $this->authorizeResource('update', 'permissions', $permission);

        $payload = $request->validated();

        try {
            $this->assertRecordClaimAvailable('permissions', $permissionId, $request->request());
            $this->repo->updatePermission(
                $permissionId,
                trim((string) ($payload['name'] ?? '')),
                trim((string) ($payload['slug'] ?? '')),
                $this->normalizeDescription($payload['description'] ?? null)
            );
            $this->releaseRecordClaim('permissions', $permissionId, $request->request(), 'permission updated');
        } catch (RuntimeException $e) {
            $this->rememberConcurrencyConflict($request->request(), $e);
            return $this->postActionErrorRedirect('/users/permissions/' . $permissionId . '/edit', $e->getMessage(), 409);
        }
        return $this->postActionSuccessRedirect('/users/permissions', (string) __('roles.permissions.updated'));
    }

    public function destroy(Request $request, string $id): Response
    {
        $permission = $this->repo->findPermission((int) $id);

        if ($permission === null) {
            return $this->postActionErrorRedirect('/users/permissions', (string) __('roles.permissions.not_found'), 404);
        }

        $this->authorizeResource('delete', 'permissions', $permission);

        try {
            $this->assertRecordClaimAvailable('permissions', (int) $id, $request);
            $this->repo->deletePermission((int) $id);
            $this->releaseRecordClaim('permissions', (int) $id, $request, 'permission deleted');
        } catch (RuntimeException $e) {
            return $this->postActionErrorRedirect('/users/permissions', $e->getMessage(), 409);
        }
        return $this->postActionSuccessRedirect('/users/permissions', (string) __('roles.permissions.deleted'));
    }

    public function bulkDestroy(Request $request): Response
    {
        $this->authorizeResource('bulk-delete', 'permissions');
        $ids = array_values(array_filter(
            array_map('intval', (array) ($request->input('selected') ?? [])),
            static fn (int $id): bool => $id > 0
        ));

        if ($ids === []) {
            return $this->postActionErrorRedirect('/users/permissions', (string) __('roles.permissions.bulk.select_one'));
        }

        foreach ($ids as $permissionId) {
            $this->assertRecordClaimAvailable('permissions', $permissionId, $request);
            $this->repo->deletePermission($permissionId);
            $this->releaseRecordClaim('permissions', $permissionId, $request, 'permission bulk deleted');
        }

        return $this->postActionSuccessRedirect('/users/permissions', count($ids) . ' ' . (string) __('roles.permissions.bulk.deleted_suffix'));
    }

    private function renderForm(string $title, ?array $permission, ?array $claim = null): Response
    {
        $action = $permission === null
            ? '/users/permissions'
            : '/users/permissions/' . (int) $permission['id'];

        $fields = array_merge(
            $this->concurrencyHiddenFields($claim),
            [
                'name' => [
                    'label' => (string) __('roles.common.name'),
                    'required' => true,
                    'section' => 'identity',
                    'placeholder' => (string) __('roles.permission_form.name_placeholder'),
                    'attributes' => ['maxlength' => 100],
                ],
                'slug' => [
                    'label' => (string) __('roles.common.slug'),
                    'required' => true,
                    'section' => 'identity',
                    'placeholder' => (string) __('roles.permission_form.slug_placeholder'),
                    'help' => (string) __('roles.common.slug_hint_permission'),
                    'attributes' => ['maxlength' => 100],
                ],
                'description' => [
                    'label' => (string) __('roles.common.description') . ' (' . (string) __('roles.common.optional') . ')',
                    'section' => 'details',
                    'attributes' => ['maxlength' => 255],
                ],
            ]
        );

        $form = FormBuilder::make()
            ->action($action)
            ->method('POST')
            ->model($permission)
            ->sections([
                'identity' => [
                    'title' => (string) __('roles.permission_form.sections.identity.title'),
                    'description' => (string) __('roles.permission_form.sections.identity.description'),
                ],
                'details' => [
                    'title' => (string) __('roles.permission_form.sections.details.title'),
                    'description' => (string) __('roles.permission_form.sections.details.description'),
                ],
            ])
            ->autosave()
            ->fields($fields)
            ->actions([
                [
                    'type' => 'submit',
                    'label' => $permission === null
                        ? (string) __('roles.permission_form.submit')
                        : (string) __('roles.form.submit_update'),
                    'class' => 'btn btn-primary',
                ],
                [
                    'type' => 'link',
                    'label' => (string) __('roles.common.cancel'),
                    'href' => '/users/permissions',
                    'class' => 'btn btn-outline-secondary',
                ],
            ])
            ->toArray();

        return $this->view('roles.permission-form', [
            'title' => $title,
            'pageTitle' => $title,
            'permission' => $permission,
            'form' => $form,
            'claimContext' => $this->buildRecordClaimContext($claim),
        ], 200, 'admin');
    }

    private function normalizeDescription(mixed $description): ?string
    {
        $value = trim((string) ($description ?? ''));

        return $value === '' ? null : $value;
    }
}
