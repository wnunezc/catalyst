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

namespace Catalyst\Repository\Users\Controllers;

use Catalyst\Framework\Form\FormBuilder;
use Catalyst\Framework\DataGrid\DataGrid;
use Catalyst\Framework\Authorization\RoleRepository;
use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Catalyst\Framework\Organization\OrganizationRepository;
use Catalyst\Framework\Traits\InteractsWithRecordClaimsTrait;
use Catalyst\Repository\Users\Support\RbacLabelPresenter;
use RuntimeException;
use Catalyst\Repository\Users\Requests\RolePayloadRequest;
use Catalyst\Repository\Users\Requests\RoleBulkSelectionRequest;
use Catalyst\Repository\Users\Requests\RolePermissionSyncRequest;

/**
 * Manages the privileged role catalog and role-permission assignments.
 *
 * @package Catalyst\Repository\Users\Controllers
 * Responsibility: Lists and mutates roles, renders role forms and synchronizes permission assignments with record-claim protection.
 */
class RolesController extends Controller
{
    use InteractsWithRecordClaimsTrait;

    /**
     * Initializes the Roles Controller instance.
     *
     * Responsibility: Initializes the Roles Controller instance.
     */
    public function __construct(
        private readonly RoleRepository $repo
    ) {
        parent::__construct();
    }

    /**
     * Displays the searchable role catalog and handles exports.
     *
     * Responsibility: Displays the searchable role catalog and handles exports.
     */
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

        return $this->view('users.index', [
            'title' => (string) __('roles.roles.title'),
            'pageTitle' => (string) __('roles.roles.title'),
            'grid' => $grid,
        ]);
    }

    /**
     * Displays the form for creating a role.
     *
     * Responsibility: Displays the form for creating a role.
     */
    public function create(Request $request): Response
    {
        $this->authorizeResource('create', 'roles');
        return $this->renderForm((string) __('roles.roles.create_title'), null);
    }

    /**
     * Creates a role from the validated request payload.
     *
     * Responsibility: Creates a role from the validated request payload.
     */
    public function store(RolePayloadRequest $request): Response
    {
        $this->authorizeResource('create', 'roles');
        $payload = $request->validated();

        $this->repo->createRole(
            trim((string) ($payload['name'] ?? '')),
            trim((string) ($payload['slug'] ?? '')),
            $this->normalizeDescription($payload['description'] ?? null),
            $this->nullablePositiveInt($payload['hierarchy_scope_id'] ?? null),
            $this->nullablePositiveInt($payload['hierarchy_level_id'] ?? null),
            $this->positiveIntList($payload['organization_unit_ids'] ?? [])
        );

        return $this->postActionSuccessRedirect('/users/roles', (string) __('roles.roles.created'));
    }

    /**
     * Acquires a record claim and displays the role edit form.
     *
     * Responsibility: Acquires a record claim and displays the role edit form.
     */
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

    /**
     * Updates a role while enforcing its active record claim.
     *
     * Responsibility: Updates a role while enforcing its active record claim.
     */
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
                $this->normalizeDescription($payload['description'] ?? null),
                $this->nullablePositiveInt($payload['hierarchy_scope_id'] ?? null),
                $this->nullablePositiveInt($payload['hierarchy_level_id'] ?? null),
                $this->positiveIntList($payload['organization_unit_ids'] ?? [])
            );
            $this->releaseRecordClaim('roles', $roleId, $request->request(), 'role updated');
        } catch (RuntimeException $e) {
            $this->rememberConcurrencyConflict($request->request(), $e);
            return $this->postActionErrorRedirect('/users/roles/' . $roleId . '/edit', $e->getMessage(), 409);
        }
        return $this->postActionSuccessRedirect('/users/roles', (string) __('roles.roles.updated'));
    }

    /**
     * Deletes a role when no competing record claim blocks the operation.
     *
     * Responsibility: Deletes a role when no competing record claim blocks the operation.
     */
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

    /**
     * Deletes the selected roles after validating concurrency claims.
     *
     * Responsibility: Deletes the selected roles after validating concurrency claims.
     */
    public function bulkDestroy(Request $request): Response
    {
        $this->authorizeResource('bulk-delete', 'roles');
        $payload = new RoleBulkSelectionRequest($request);
        $ids = $payload->ids();

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

    /**
     * Displays the permission assignment surface for a role.
     *
     * Responsibility: Displays the permission assignment surface for a role.
     */
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

        return $this->view('users.permissions', [
            'title' => (string) __('roles.roles.permissions_for') . ' — ' . e(RbacLabelPresenter::roleName((string) ($role['name'] ?? ''), (string) ($role['slug'] ?? ''))),
            'pageTitle' => (string) __('roles.roles.permissions_page_title'),
            'role' => $role,
            'allPermissions' => $this->repo->allPermissions(),
            'rolePermissions' => array_column($this->repo->getRolePermissions((int) $id), null, 'id'),
            'recordPresence' => $this->buildRecordPresenceContext($claim),
        ]);
    }

    /**
     * Synchronizes the selected permissions for a role.
     *
     * Responsibility: Synchronizes the selected permissions for a role.
     */
    public function syncPermissions(Request $request, string $id): Response
    {
        $roleId = (int) $id;
        $role = $this->repo->findRole($roleId);

        if ($role === null) {
            $this->flash()->error((string) __('roles.roles.not_found'));

            return $this->redirect('/users/roles');
        }

        $this->authorizeResource('sync', 'roles', $role);
        $payload = new RolePermissionSyncRequest($request);
        $selectedIds = $payload->selectedIds();
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

    /**
     * Builds and renders the role create or edit form.
     *
     * Responsibility: Builds and renders the role create or edit form.
     */
    private function renderForm(string $title, ?array $role, ?array $claim = null): Response
    {
        $action = $role === null
            ? '/users/roles'
            : '/users/roles/' . (int) $role['id'];
        $model = $role ?? [];

        if ($role !== null) {
            $model['organization_unit_ids'] = $this->repo->getRoleOrganizationUnitIds((int) $role['id']);
        }

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
                'hierarchy_scope_id' => [
                    'type' => 'select',
                    'label' => (string) __('roles.organization.scope') . ' (' . (string) __('roles.common.optional') . ')',
                    'section' => 'organization',
                    'options' => $this->organizationScopeOptions(),
                    'empty_option_label' => (string) __('roles.organization.no_scope'),
                    'help' => (string) __('roles.organization.scope_help'),
                ],
                'hierarchy_level_id' => [
                    'type' => 'select',
                    'label' => (string) __('roles.organization.level') . ' (' . (string) __('roles.common.optional') . ')',
                    'section' => 'organization',
                    'options' => $this->organizationLevelOptions(),
                    'empty_option_label' => (string) __('roles.organization.no_level'),
                    'help' => (string) __('roles.organization.level_help'),
                ],
                'organization_unit_ids' => [
                    'type' => 'select',
                    'multiple' => true,
                    'label' => (string) __('roles.organization.units') . ' (' . (string) __('roles.common.optional') . ')',
                    'section' => 'organization',
                    'options' => $this->organizationUnitOptions(),
                    'help' => (string) __('roles.organization.units_help'),
                    'attributes' => ['size' => 6],
                ],
            ]
        );

        $form = FormBuilder::make()
            ->action($action)
            ->method('POST')
            ->model($model)
            ->sections([
                'identity' => [
                    'title' => (string) __('roles.form.sections.identity.title'),
                    'description' => (string) __('roles.form.sections.identity.description'),
                ],
                'details' => [
                    'title' => (string) __('roles.form.sections.details.title'),
                    'description' => (string) __('roles.form.sections.details.description'),
                ],
                'organization' => [
                    'title' => (string) __('roles.organization.section_title'),
                    'description' => (string) __('roles.organization.section_description'),
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

        return $this->view('users.form', [
            'title' => $title,
            'pageTitle' => $title,
            'role' => $role,
            'form' => $form,
            'recordPresence' => $this->buildRecordPresenceContext($claim),
        ]);
    }

    /**
     * Trims an optional description and converts an empty value to null.
     *
     * Responsibility: Trims an optional description and converts an empty value to null.
     */
    private function normalizeDescription(mixed $description): ?string
    {
        $value = trim((string) ($description ?? ''));

        return $value === '' ? null : $value;
    }

    /**
     * Converts optional positive integer payload values.
     *
     * Responsibility: Keeps blank organization classification selectors as null before repository persistence.
     */
    private function nullablePositiveInt(mixed $value): ?int
    {
        $id = (int) $value;

        return $id > 0 ? $id : null;
    }

    /**
     * Converts submitted positive integer lists.
     *
     * Responsibility: Keeps optional horizontal organization unit links normalized before repository sync.
     * @return array<int, int>
     */
    private function positiveIntList(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $ids = [];
        foreach ($value as $candidate) {
            $id = (int) $candidate;
            if ($id > 0) {
                $ids[] = $id;
            }
        }

        return array_values(array_unique($ids));
    }

    /**
     * Builds hierarchy scope select options.
     *
     * Responsibility: Reads configured organization scopes for role classification without affecting RBAC behavior.
     * @return array<int, array{value:string,label:string}>
     */
    private function organizationScopeOptions(): array
    {
        try {
            return array_map(
                static fn (array $row): array => [
                    'value' => (string) ($row['id'] ?? ''),
                    'label' => (string) ($row['label'] ?? $row['scope_key'] ?? ''),
                ],
                (new OrganizationRepository())->scopeOptions()
            );
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Builds hierarchy level select options.
     *
     * Responsibility: Reads configured organization levels for role classification without affecting RBAC behavior.
     * @return array<int, array{value:string,label:string}>
     */
    private function organizationLevelOptions(): array
    {
        try {
            return array_map(
                static fn (array $row): array => [
                    'value' => (string) ($row['id'] ?? ''),
                    'label' => (string) ($row['label'] ?? $row['code'] ?? ''),
                ],
                (new OrganizationRepository())->levelOptions()
            );
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Builds horizontal organization unit multi-select options.
     *
     * Responsibility: Reads configured organization units for role metadata without affecting RBAC behavior.
     * @return array<int, array{value:string,label:string}>
     */
    private function organizationUnitOptions(): array
    {
        try {
            return array_map(
                static function (array $row): array {
                    $label = (string) ($row['label'] ?? $row['code'] ?? '');
                    $code = trim((string) ($row['code'] ?? ''));

                    return [
                        'value' => (string) ($row['id'] ?? ''),
                        'label' => $code === '' ? $label : trim($label . ' (' . $code . ')'),
                    ];
                },
                (new OrganizationRepository())->unitOptions()
            );
        } catch (\Throwable) {
            return [];
        }
    }
}
