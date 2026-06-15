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

use Catalyst\Framework\DataGrid\DataGrid;
use Catalyst\Framework\Auth\UserDirectoryRepository;
use Catalyst\Framework\Auth\UserProvider;
use Catalyst\Framework\Authorization\RoleRepository;
use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Catalyst\Framework\Session\SessionManager;
use Catalyst\Repository\Users\Requests\UserEnrollmentRequest;
use Catalyst\Repository\Users\Support\RbacLabelPresenter;
use Catalyst\Repository\Users\Support\UserEnrollmentFormFactory;
use Exception;

/**
 * Provides the privileged user directory and enrollment workflow.
 *
 * @package Catalyst\Repository\Users\Controllers
 * Responsibility: Lists users, handles exports and enrolls new accounts with validated role assignment.
 */
class UserManagementController extends Controller
{
    private RoleRepository $roles;
    private UserProvider $users;
    private UserDirectoryRepository $userDirectory;

    /**
     * Initializes the User Management Controller instance.
     *
     * Responsibility: Initializes the User Management Controller instance.
     */
    public function __construct(
        RoleRepository $roles,
        UserProvider $users,
        UserDirectoryRepository $userDirectory,
        private readonly UserEnrollmentFormFactory $enrollmentFormFactory
    ) {
        parent::__construct();

        $this->roles = $roles;
        $this->users = $users;
        $this->userDirectory = $userDirectory;
    }

    /**
     * Displays the searchable privileged user directory and handles exports.
     *
     * Responsibility: Displays the searchable privileged user directory and handles exports.
     */
    public function index(Request $request): Response
    {
        $this->authorizeResource('view-any', 'users');
        $t = static fn (string $key, array $replace = []): string => __($key, $replace);

        $gridBuilder = DataGrid::make()
            ->baseUrl('/users')
            ->resourceKey('users')
            ->title($t('roles.users.listing_title'), $t('roles.users.listing_description'))
            ->emptyState(
                $t('roles.users.empty'),
                $t('roles.users.empty_description'),
                [
                    'label' => $t('roles.users.register_title'),
                    'href' => '/users/enroll',
                    'class' => 'btn btn-sm btn-primary',
                    'icon' => 'fa-solid fa-user-plus',
                ]
            )
            ->columns([
                [
                    'key' => 'name',
                    'label' => $t('roles.users.columns.user'),
                    'sortable' => true,
                    'value' => static fn (array $row): array => DataGrid::stack(
                        (string) ($row['name'] ?? ''),
                        '#' . (int) ($row['id'] ?? 0)
                    ),
                ],
                [
                    'key' => 'email',
                    'label' => $t('roles.users.columns.email'),
                    'sortable' => true,
                    'value' => static fn (array $row): array => DataGrid::code((string) ($row['email'] ?? '')),
                ],
                [
                    'key' => 'roles',
                    'label' => $t('roles.users.columns.roles'),
                    'value' => static fn (array $row): array => DataGrid::badge(
                        ($row['roles'] ?? '') !== ''
                            ? RbacLabelPresenter::roleList((string) ($row['roles'] ?? ''))
                            : (string) __('roles.users.no_role'),
                        ($row['roles'] ?? '') !== '' ? 'text-bg-light border' : 'text-bg-warning'
                    ),
                ],
                [
                    'key' => 'active',
                    'label' => $t('roles.users.columns.status'),
                    'sortable' => true,
                    'value' => static fn (array $row): array => DataGrid::badge(
                        (int) ($row['active'] ?? 0) === 1
                            ? (string) __('roles.users.status.active')
                            : (string) __('roles.users.status.inactive'),
                        (int) ($row['active'] ?? 0) === 1 ? 'text-bg-success' : 'text-bg-secondary'
                    ),
                ],
                [
                    'key' => 'email_verified',
                    'label' => $t('roles.users.columns.verification'),
                    'sortable' => true,
                    'value' => static fn (array $row): array => DataGrid::badge(
                        (int) ($row['email_verified'] ?? 0) === 1
                            ? (string) __('roles.users.verification.verified')
                            : (string) __('roles.users.verification.pending'),
                        (int) ($row['email_verified'] ?? 0) === 1 ? 'text-bg-success' : 'text-bg-warning'
                    ),
                ],
                [
                    'key' => 'created_at',
                    'label' => $t('roles.users.columns.created'),
                    'sortable' => true,
                    'class' => 'small text-muted',
                ],
            ])
            ->filters([
                [
                    'name' => 'active',
                    'label' => $t('roles.users.columns.status'),
                    'type' => 'select',
                    'options' => [
                        '1' => (string) __('roles.users.status.active'),
                        '0' => (string) __('roles.users.status.inactive'),
                    ],
                ],
                [
                    'name' => 'email_verified',
                    'label' => $t('roles.users.columns.verification'),
                    'type' => 'select',
                    'options' => [
                        '1' => (string) __('roles.users.verification.verified'),
                        '0' => (string) __('roles.users.verification.pending'),
                    ],
                ],
                [
                    'name' => 'role_state',
                    'label' => $t('roles.users.columns.roles'),
                    'type' => 'select',
                    'options' => [
                        'with' => (string) __('roles.roles.filters.with_description'),
                        'without' => (string) __('roles.roles.filters.without_description'),
                    ],
                ],
            ])
            ->actions([
                [
                    'label' => (string) __('roles.user_roles.title'),
                    'icon' => 'fa-solid fa-shield-halved',
                    'class' => 'btn btn-outline-primary btn-sm',
                    'href' => '/users/{id}/roles',
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
            ], 'users-catalog')
            ->printEnabled(true, (string) __('ui.datagrid.print'))
            ->defaultSort('created_at', 'desc')
            ->pagination(10, [10, 25, 50])
            ->searchPlaceholder((string) __('ui.datagrid.search'))
            ->provider(fn (array $state): array => $this->searchUsers($state));

        if (in_array($gridBuilder->exportFormat($request), ['csv', 'xls'], true)) {
            $this->authorizeResource('export', 'users');

            return $gridBuilder->export($request);
        }

        return $this->view('users.users-index', [
            'title' => $t('roles.users.title'),
            'pageTitle' => $t('roles.users.title'),
            'grid' => $gridBuilder->resolve($request),
        ]);
    }

    /**
     * Displays the user enrollment form.
     *
     * Responsibility: Displays the user enrollment form.
     */
    public function create(Request $request): Response
    {
        $this->authorizeResource('create', 'users');

        return $this->view('users.user-register', [
            'title' => __('roles.users.register_title'),
            'pageTitle' => __('roles.users.register_title'),
            'form' => $this->enrollmentFormFactory->build($this->roles),
        ]);
    }

    /**
     * Validates and creates a new user account with its initial role.
     *
     * Responsibility: Validates and creates a new user account with its initial role.
     */
    public function store(Request $request): Response
    {
        $this->authorizeResource('create', 'users');

        $enrollment = new UserEnrollmentRequest($request);
        $payload = $enrollment->payload();
        $errors = $enrollment->errors($payload);
        if ($errors !== []) {
            if ($this->expectsJson()) {
                return $this->jsonValidationError($errors);
            }

            $this->rememberValidationState($enrollment->replayableInput($payload), $errors);
            $this->flash()->error(implode(' ', array_map(static fn (array $messages): string => (string) ($messages[0] ?? ''), $errors)));
            return $this->redirect('/users/enroll');
        }

        if ($this->users->findByEmailAny($payload['email']) !== null) {
            if ($this->expectsJson()) {
                return $this->jsonValidationError([
                    'email' => [__('roles.users.messages.email_exists')],
                ], __('roles.users.messages.validation_failed'), 409);
            }

            $this->rememberValidationState($enrollment->replayableInput($payload), [
                'email' => [__('roles.users.messages.email_exists')],
            ]);
            $this->flash()->error(__('roles.users.messages.email_exists'));
            return $this->redirect('/users/enroll');
        }

        $roleSlug = $this->normalizeRoleSlug($payload['role']);
        $verified = $payload['email_verified'] === '1';

        try {
            $userId = $this->users->create(
                $payload['name'],
                $payload['email'],
                $payload['password'],
                $roleSlug,
                $verified
            );
        } catch (Exception $e) {
            if ($this->expectsJson()) {
                return $this->jsonErrorWithToast(__('roles.users.messages.create_error') . ' ' . $e->getMessage(), 500);
            }

            SessionManager::getInstance()->flashOldInput($enrollment->replayableInput($payload));
            $this->flash()->error(__('roles.users.messages.create_error') . ' ' . $e->getMessage());
            return $this->redirect('/users/enroll');
        }

        if ($this->expectsJson()) {
            return $this->jsonSuccessWithToast(
                ['user_id' => $userId],
                __('roles.users.messages.created')
            )->withRedirect('/users');
        }

        $this->toast('success', __('roles.users.messages.created'));
        return $this->redirect('/users');
    }

    /**
     * Resolves the selected role slug against the current role catalog.
     *
     * Responsibility: Resolves the selected role slug against the current role catalog.
     */
    private function normalizeRoleSlug(string $selectedSlug): string
    {
        $selectedSlug = trim($selectedSlug);

        foreach ($this->roles->allRoles() as $role) {
            if (($role['slug'] ?? '') === $selectedSlug) {
                return $selectedSlug;
            }
        }

        return 'user';
    }

    /**
     * Searches the privileged user directory using the grid state.
     *
     * Responsibility: Searches the privileged user directory using the grid state.
     * @param array<string, mixed> $state
     * @return array{rows: array<int, array<string, mixed>>, total: int}
     */
    private function searchUsers(array $state): array
    {
        $result = $this->userDirectory->searchAdminUsers($state);
        if (($result['rows'] ?? []) === [] && (int) ($result['total'] ?? 0) === 0) {
            // TODO: The repository intentionally hides storage details from the controller;
            // surface a generic message here only when the list is unexpectedly empty.
        }

        return $result;
    }

}
