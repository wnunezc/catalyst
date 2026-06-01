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
use Catalyst\Repository\Roles\Requests\RolePayloadRequest;

class RolesController extends Controller
{
    use InteractsWithRecordClaimsTrait;

    public function __construct(
        private readonly RoleRepository $repo
    ) {
        parent::__construct();
    }

    public function index(Request $request): Response
    {
        $this->authorizeResource('view-any', 'roles');

        $gridBuilder = DataGrid::make()
            ->baseUrl('/users/roles')
            ->title(__('roles.roles.listing_title'), __('roles.roles.listing_description'))
            ->emptyState(
                (string) __('roles.roles.empty'),
                (string) __('roles.roles.empty_description'),
                [
                    'label' => (string) __('roles.roles.new'),
                    'href' => '/users/roles/create',
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
                        RbacLabelPresenter::roleName((string) ($row['name'] ?? ''), (string) ($row['slug'] ?? '')),
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
                    'empty' => (string) __('roles.common.no_description'),
                ],
            ])
            ->filters([
                [
                    'name' => 'description_state',
                    'label' => (string) __('roles.common.description'),
                    'type' => 'select',
                    'options' => [
                        'with' => (string) __('roles.roles.filters.with_description'),
                        'without' => (string) __('roles.roles.filters.without_description'),
                    ],
                ],
            ])
            ->actions([
                [
                    'label' => (string) __('roles.roles.permissions_title'),
                    'icon' => 'fa-solid fa-key',
                    'class' => 'btn btn-outline-secondary btn-sm',
                    'href' => '/users/roles/{id}/permissions',
                ],
                [
                    'label' => (string) __('roles.common.edit'),
                    'class' => 'btn btn-outline-primary btn-sm',
                    'href' => '/users/roles/{id}/edit',
                ],
                [
                    'label' => (string) __('roles.common.delete'),
                    'class' => 'btn btn-outline-danger btn-sm',
                    'method' => 'POST',
                    'href' => '/users/roles/{id}/delete',
                    'confirm' => static fn (array $row): string => sprintf(
                        (string) __('roles.roles.confirm_delete'),
                        RbacLabelPresenter::roleName((string) ($row['name'] ?? ''), (string) ($row['slug'] ?? ''))
                    ),
                ],
            ])
            ->bulkActions([
                [
                    'label' => (string) __('roles.roles.bulk.delete_selected'),
                    'class' => 'btn btn-outline-danger btn-sm',
                    'method' => 'POST',
                    'href' => '/users/roles/bulk-delete',
                    'icon' => 'fa-solid fa-trash',
                    'confirm' => (string) __('roles.roles.bulk.confirm_delete'),
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
            ], 'roles-catalog')
            ->printEnabled(true, (string) __('ui.datagrid.print'))
            ->defaultSort('name')
            ->pagination(10, [10, 25, 50])
            ->searchPlaceholder((string) __('roles.roles.search_placeholder'))
            ->provider(fn (array $state): array => $this->repo->searchRoles([
                'page' => $state['page'],
                'per_page' => $state['per_page'],
                'sort' => $state['sort'],
                'direction' => $state['direction'],
                'search' => $state['search'],
                'description_state' => $state['filters']['description_state'] ?? '',
            ]));

        if (in_array($gridBuilder->exportFormat($request), ['csv', 'xls'], true)) {
            $this->authorizeResource('export', 'roles');
            return $gridBuilder->export($request);
        }

        $grid = $gridBuilder->resolve($request);

        return $this->view('roles.index', [
            'title' => (string) __('roles.roles.title'),
            'pageTitle' => (string) __('roles.roles.title'),
            'grid' => $grid,
        ], 200, 'admin');
    }

    public function create(Request $request): Response
    {
        $this->authorizeResource('create', 'roles');
        return $this->renderForm((string) __('roles.roles.create_title'), null);
    }

    public function store(RolePayloadRequest $request): Response
    {
        $this->authorizeResource('create', 'roles');
        $payload = $request->validated();

        $this->repo->createRole(
            trim((string) ($payload['name'] ?? '')),
            trim((string) ($payload['slug'] ?? '')),
            $this->normalizeDescription($payload['description'] ?? null)
        );

        return $this->postActionSuccessRedirect('/users/roles', (string) __('roles.roles.created'));
    }

    public function edit(Request $request, string $id): Response
    {
        $role = $this->repo->findRole((int) $id);

        if ($role === null) {
            return $this->postActionErrorRedirect('/users/roles', (string) __('roles.roles.not_found'), 404);
        }

        $this->authorizeResource('view', 'roles', $role);

        try {
            $claim = $this->acquireRecordClaim('roles', (int) $id, [
                'surface' => 'roles.edit',
            ]);
        } catch (RuntimeException $e) {
            $this->flash()->error($e->getMessage());

            return $this->redirect('/users/roles');
        }

        return $this->renderForm((string) __('roles.roles.edit_title'), $role, $claim);
    }

    public function update(RolePayloadRequest $request, string $id): Response
    {
        $roleId = (int) $id;
        $role = $this->repo->findRole($roleId);

        if ($role === null) {
            $this->flash()->error((string) __('roles.roles.not_found'));

            return $this->redirect('/users/roles');
        }

        $this->authorizeResource('update', 'roles', $role);

        $payload = $request->validated();

        try {
            $this->assertRecordClaimAvailable('roles', $roleId, $request->request());
            $this->repo->updateRole(
                $roleId,
                trim((string) ($payload['name'] ?? '')),
                trim((string) ($payload['slug'] ?? '')),
                $this->normalizeDescription($payload['description'] ?? null)
            );
            $this->releaseRecordClaim('roles', $roleId, $request->request(), 'role updated');
        } catch (RuntimeException $e) {
            $this->rememberConcurrencyConflict($request->request(), $e);
            return $this->postActionErrorRedirect('/users/roles/' . $roleId . '/edit', $e->getMessage(), 409);
        }
        return $this->postActionSuccessRedirect('/users/roles', (string) __('roles.roles.updated'));
    }

    public function destroy(Request $request, string $id): Response
    {
        $role = $this->repo->findRole((int) $id);

        if ($role === null) {
            return $this->postActionErrorRedirect('/users/roles', (string) __('roles.roles.not_found'), 404);
        }

        $this->authorizeResource('delete', 'roles', $role);

        try {
            $this->assertRecordClaimAvailable('roles', (int) $id, $request);
            $this->repo->deleteRole((int) $id);
            $this->releaseRecordClaim('roles', (int) $id, $request, 'role deleted');
        } catch (RuntimeException $e) {
            return $this->postActionErrorRedirect('/users/roles', $e->getMessage(), 409);
        }
        return $this->postActionSuccessRedirect('/users/roles', (string) __('roles.roles.deleted'));
    }

    public function bulkDestroy(Request $request): Response
    {
        $this->authorizeResource('bulk-delete', 'roles');
        $ids = array_values(array_filter(
            array_map('intval', (array) ($request->input('selected') ?? [])),
            static fn (int $id): bool => $id > 0
        ));

        if ($ids === []) {
            return $this->postActionErrorRedirect('/users/roles', (string) __('roles.roles.bulk.select_one'));
        }

        foreach ($ids as $roleId) {
            $this->assertRecordClaimAvailable('roles', $roleId, $request);
            $this->repo->deleteRole($roleId);
            $this->releaseRecordClaim('roles', $roleId, $request, 'role bulk deleted');
        }

        return $this->postActionSuccessRedirect('/users/roles', count($ids) . ' ' . (string) __('roles.roles.bulk.deleted_suffix'));
    }

    public function permissions(Request $request, string $id): Response
    {
        $role = $this->repo->findRole((int) $id);

        if ($role === null) {
            return $this->postActionErrorRedirect('/users/roles', (string) __('roles.roles.not_found'), 404);
        }

        $this->authorizeResource('view', 'roles', $role);

        try {
            $claim = $this->acquireRecordClaim('roles', (int) $id, [
                'surface' => 'roles.permissions',
            ]);
        } catch (RuntimeException) {
            $claim = \Catalyst\Framework\Concurrency\RecordClaimManager::getInstance()->snapshot('roles', (int) $id);
        }

        return $this->view('roles.permissions', [
            'title' => (string) __('roles.roles.permissions_for') . ' — ' . e(RbacLabelPresenter::roleName((string) ($role['name'] ?? ''), (string) ($role['slug'] ?? ''))),
            'pageTitle' => (string) __('roles.roles.permissions_page_title'),
            'role' => $role,
            'allPermissions' => $this->repo->allPermissions(),
            'rolePermissions' => array_column($this->repo->getRolePermissions((int) $id), null, 'id'),
            'claimContext' => $this->buildRecordClaimContext($claim),
        ], 200, 'admin');
    }

    public function syncPermissions(Request $request, string $id): Response
    {
        $roleId = (int) $id;
        $role = $this->repo->findRole($roleId);

        if ($role === null) {
            $this->flash()->error((string) __('roles.roles.not_found'));

            return $this->redirect('/users/roles');
        }

        $this->authorizeResource('sync', 'roles', $role);
        $selectedIds = (array) ($request->input('permissions') ?? []);
        $allPermissions = $this->repo->allPermissions();

        try {
            $this->assertRecordClaimAvailable('roles', $roleId, $request);

            foreach ($allPermissions as $permission) {
                if (in_array((string) $permission['id'], $selectedIds, true)) {
                    $this->repo->assignPermissionToRole($roleId, (int) $permission['id']);
                } else {
                    $this->repo->removePermissionFromRole($roleId, (int) $permission['id']);
                }
            }

        } catch (RuntimeException $e) {
            return $this->postActionErrorRedirect('/users/roles/' . $roleId . '/permissions', $e->getMessage(), 409);
        }
        return $this->postActionSuccessRedirect('/users/roles/' . $roleId . '/permissions', (string) __('roles.permissions.updated'));
    }

    private function renderForm(string $title, ?array $role, ?array $claim = null): Response
    {
        $action = $role === null
            ? '/users/roles'
            : '/users/roles/' . (int) $role['id'];

        $fields = array_merge(
            $this->concurrencyHiddenFields($claim),
            [
                'name' => [
                    'label' => (string) __('roles.common.name'),
                    'required' => true,
                    'section' => 'identity',
                    'placeholder' => (string) __('roles.form.name_placeholder'),
                    'attributes' => ['maxlength' => 50],
                ],
                'slug' => [
                    'label' => (string) __('roles.common.slug'),
                    'required' => true,
                    'section' => 'identity',
                    'placeholder' => (string) __('roles.form.slug_placeholder'),
                    'help' => (string) __('roles.common.slug_hint_role'),
                    'attributes' => ['maxlength' => 50],
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
            ->model($role)
            ->sections([
                'identity' => [
                    'title' => (string) __('roles.form.sections.identity.title'),
                    'description' => (string) __('roles.form.sections.identity.description'),
                ],
                'details' => [
                    'title' => (string) __('roles.form.sections.details.title'),
                    'description' => (string) __('roles.form.sections.details.description'),
                ],
            ])
            ->autosave()
            ->fields($fields)
            ->actions([
                [
                    'type' => 'submit',
                    'label' => $role === null
                        ? (string) __('roles.form.submit_create')
                        : (string) __('roles.form.submit_update'),
                    'class' => 'btn btn-primary',
                ],
                [
                    'type' => 'link',
                    'label' => (string) __('roles.common.cancel'),
                    'href' => '/users/roles',
                    'class' => 'btn btn-outline-secondary',
                ],
            ])
            ->toArray();

        return $this->view('roles.form', [
            'title' => $title,
            'pageTitle' => $title,
            'role' => $role,
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
