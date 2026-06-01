<?php

declare(strict_types=1);

namespace Catalyst\Repository\Roles\Controllers;

use Catalyst\Framework\Admin\Grid\DataGrid;
use Catalyst\Framework\Authorization\RoleRepository;
use Catalyst\Framework\Auth\UserDirectoryRepository;
use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;

class UserRolesController extends Controller
{
    private RoleRepository $repo;
    private UserDirectoryRepository $users;

    public function __construct(RoleRepository $repo, UserDirectoryRepository $users)
    {
        parent::__construct();
        $this->repo = $repo;
        $this->users = $users;
    }

    public function index(Request $request, string $userId): Response
    {
        $uid  = (int) $userId;
        $user = $this->findUser($uid);

        if ($user === null) {
            $this->flash()->error(__('roles.users.messages.user_not_found'));
            return $this->redirect('/users');
        }

        $this->authorizeResource('view', 'users', $user);

        $gridBuilder = DataGrid::make()
            ->baseUrl('/users/' . $uid . '/roles')
            ->resourceKey('user-roles')
            ->title(
                (string) __('roles.user_roles.title'),
                (string) __('roles.users.listing_description')
            )
            ->emptyState(
                (string) __('roles.user_roles.empty'),
                (string) __('roles.users.empty_description'),
                null
            )
            ->columns([
                [
                    'key' => 'name',
                    'label' => (string) __('roles.user_roles.role'),
                    'sortable' => true,
                    'value' => static fn (array $row): array => DataGrid::stack(
                        (string) ($row['name'] ?? ''),
                        (string) ($row['slug'] ?? ''),
                        ['secondary_is_code' => true]
                    ),
                ],
                [
                    'key' => 'assigned',
                    'label' => (string) __('roles.user_roles.status'),
                    'sortable' => true,
                    'value' => static fn (array $row): array => DataGrid::badge(
                        (bool) ($row['assigned'] ?? false)
                            ? (string) __('roles.user_roles.assigned')
                            : (string) __('roles.user_roles.not_assigned'),
                        (bool) ($row['assigned'] ?? false) ? 'text-bg-success' : 'text-bg-secondary'
                    ),
                ],
            ])
            ->filters([
                [
                    'name' => 'assigned',
                    'label' => (string) __('roles.user_roles.status'),
                    'type' => 'select',
                    'options' => [
                        'assigned' => (string) __('roles.user_roles.assigned'),
                        'available' => (string) __('roles.user_roles.not_assigned'),
                    ],
                ],
            ])
            ->actions([
                [
                    'label' => static fn (array $row): string => (bool) ($row['assigned'] ?? false)
                        ? (string) __('roles.user_roles.remove')
                        : (string) __('roles.user_roles.assign'),
                    'class' => static fn (array $row): string => (bool) ($row['assigned'] ?? false)
                        ? 'btn btn-outline-danger btn-sm'
                        : 'btn btn-outline-primary btn-sm',
                    'method' => 'POST',
                    'href' => static fn (array $row): string => (bool) ($row['assigned'] ?? false)
                        ? '/users/' . $uid . '/roles/' . (int) ($row['id'] ?? 0) . '/remove'
                        : '/users/' . $uid . '/roles/' . (int) ($row['id'] ?? 0),
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
            ], 'user-role-assignments')
            ->printEnabled(true, (string) __('ui.datagrid.print'))
            ->defaultSort('name')
            ->pagination(10, [10, 25, 50])
            ->searchPlaceholder((string) __('ui.datagrid.search'))
            ->provider(fn (array $state): array => $this->searchRoleAssignments($uid, $state));

        if (in_array($gridBuilder->exportFormat($request), ['csv', 'xls'], true)) {
            $this->authorizeResource('export', 'user-roles', $user);

            return $gridBuilder->export($request);
        }

        return $this->view('roles.user-roles', [
            'title' => __('roles.user_roles.title_for', ['name' => (string) $user['name']]),
            'pageTitle' => __('roles.user_roles.title'),
            'user' => $user,
            'grid' => $gridBuilder->resolve($request),
        ], 200, 'admin');
    }

    public function assign(Request $request, string $userId, string $roleId): Response
    {
        $user = $this->findUser((int) $userId);

        if ($user === null) {
            return $this->postActionErrorRedirect('/users', __('roles.users.messages.user_not_found'), 404);
        }

        $this->authorizeResource('assign', 'users', $user);
        $this->repo->assignRoleToUser((int) $userId, (int) $roleId);

        return $this->postActionSuccessRedirect('/users/' . (int) $userId . '/roles', __('roles.user_roles.messages.assigned'));
    }

    public function remove(Request $request, string $userId, string $roleId): Response
    {
        $user = $this->findUser((int) $userId);

        if ($user === null) {
            return $this->postActionErrorRedirect('/users', __('roles.users.messages.user_not_found'), 404);
        }

        $this->authorizeResource('assign', 'users', $user);
        $this->repo->removeRoleFromUser((int) $userId, (int) $roleId);

        return $this->postActionSuccessRedirect('/users/' . (int) $userId . '/roles', __('roles.user_roles.messages.removed'));
    }

    private function findUser(int $id): ?array
    {
        try {
            return $this->users->findActiveSummary($id);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @param array<string, mixed> $state
     * @return array{rows: array<int, array<string, mixed>>, total: int}
     */
    private function searchRoleAssignments(int $userId, array $state): array
    {
        $rows = [];
        $assigned = array_column($this->repo->getUserRoles($userId), null, 'id');

        foreach ($this->repo->allRoles() as $role) {
            $roleId = (int) ($role['id'] ?? 0);
            $rows[] = [
                'id' => $roleId,
                'name' => (string) ($role['name'] ?? ''),
                'slug' => (string) ($role['slug'] ?? ''),
                'assigned' => isset($assigned[$roleId]),
            ];
        }

        $search = strtolower(trim((string) ($state['search'] ?? '')));
        $assignedFilter = trim((string) ($state['filters']['assigned'] ?? ''));

        $rows = array_values(array_filter($rows, static function (array $row) use ($search, $assignedFilter): bool {
            if ($assignedFilter === 'assigned' && !($row['assigned'] ?? false)) {
                return false;
            }

            if ($assignedFilter === 'available' && ($row['assigned'] ?? false)) {
                return false;
            }

            if ($search === '') {
                return true;
            }

            $haystack = strtolower(trim((string) ($row['name'] ?? '') . ' ' . (string) ($row['slug'] ?? '')));

            return str_contains($haystack, $search);
        }));

        $sort = trim((string) ($state['sort'] ?? 'name'));
        $direction = strtolower(trim((string) ($state['direction'] ?? 'asc'))) === 'desc' ? -1 : 1;

        usort($rows, static function (array $left, array $right) use ($sort, $direction): int {
            $value = match ($sort) {
                'assigned' => ((int) ($left['assigned'] ?? false)) <=> ((int) ($right['assigned'] ?? false)),
                'slug' => strcmp((string) ($left['slug'] ?? ''), (string) ($right['slug'] ?? '')),
                default => strcmp((string) ($left['name'] ?? ''), (string) ($right['name'] ?? '')),
            };

            return $value * $direction;
        });

        $total = count($rows);
        $page = max(1, (int) ($state['page'] ?? 1));
        $perPage = max(1, (int) ($state['per_page'] ?? 10));
        $offset = ($page - 1) * $perPage;

        return [
            'rows' => array_slice($rows, $offset, $perPage),
            'total' => $total,
        ];
    }
}
