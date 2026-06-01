<?php

declare(strict_types=1);

namespace Catalyst\Repository\Roles\Controllers;

use Catalyst\Framework\Admin\Form\FormBuilder;
use Catalyst\Framework\Admin\Grid\DataGrid;
use Catalyst\Framework\Auth\UserDirectoryRepository;
use Catalyst\Framework\Auth\UserProvider;
use Catalyst\Framework\Authorization\RoleRepository;
use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\JsonResponse;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Catalyst\Framework\Session\SessionManager;
use Catalyst\Repository\Roles\Support\RbacLabelPresenter;
use Exception;

class UserManagementController extends Controller
{
    private RoleRepository $roles;
    private UserProvider $users;
    private UserDirectoryRepository $userDirectory;

    public function __construct(
        RoleRepository $roles,
        UserProvider $users,
        UserDirectoryRepository $userDirectory
    ) {
        parent::__construct();

        $this->roles = $roles;
        $this->users = $users;
        $this->userDirectory = $userDirectory;
    }

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

        return $this->view('roles.users-index', [
            'title' => $t('roles.users.title'),
            'pageTitle' => $t('roles.users.title'),
            'grid' => $gridBuilder->resolve($request),
        ], 200, 'admin');
    }

    public function create(Request $request): Response
    {
        $this->authorizeResource('create', 'users');

        return $this->view('roles.user-register', [
            'title' => __('roles.users.register_title'),
            'pageTitle' => __('roles.users.register_title'),
            'form' => $this->buildEnrollmentForm(),
        ], 200, 'admin');
    }

    public function store(Request $request): Response
    {
        $this->authorizeResource('create', 'users');

        $payload = [
            'name' => trim((string) $request->input('name', '')),
            'email' => trim((string) $request->input('email', '')),
            'password' => (string) $request->input('password', ''),
            'password_confirm' => (string) $request->input('password_confirm', ''),
            'role' => trim((string) $request->input('role', 'user')),
            'email_verified' => (string) $request->input('email_verified', '1'),
        ];

        if ($response = $this->validatePayload($payload)) {
            return $response;
        }

        if ($payload['password'] !== $payload['password_confirm']) {
            if ($this->expectsJson()) {
                return $this->jsonValidationError([
                    'password_confirm' => [__('auth.validation.password_mismatch')],
                ]);
            }

            $this->rememberValidationState($this->replayableInput($payload), [
                'password_confirm' => [__('auth.validation.password_mismatch')],
            ]);
            $this->flash()->error(__('auth.validation.password_mismatch'));
            return $this->redirect('/users/enroll');
        }

        if ($this->users->findByEmailAny($payload['email']) !== null) {
            if ($this->expectsJson()) {
                return $this->jsonValidationError([
                    'email' => [__('roles.users.messages.email_exists')],
                ], __('roles.users.messages.validation_failed'), 409);
            }

            $this->rememberValidationState($this->replayableInput($payload), [
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

            SessionManager::getInstance()->flashOldInput($this->replayableInput($payload));
            $this->flash()->error(__('roles.users.messages.create_error') . ' ' . $e->getMessage());
            return $this->redirect('/users/enroll');
        }

        if ($this->expectsJson()) {
            return $this->jsonSuccessWithToast(
                ['user_id' => $userId],
                __('roles.users.messages.created')
            )->withRedirect('/users', 1200);
        }

        $this->toast('success', __('roles.users.messages.created'));
        return $this->redirect('/users');
    }

    private function validatePayload(array $payload): JsonResponse|Response|null
    {
        $validator = $this->validate($payload, [
            'name' => 'required|min:2|max:255',
            'email' => 'required|email|max:255',
            'password' => 'required|min:8',
            'password_confirm' => 'required',
            'role' => 'required|max:50',
        ], [
            'name' => __('roles.users.form.labels.name'),
            'email' => __('roles.users.form.labels.email'),
            'password' => __('roles.users.form.labels.password'),
            'password_confirm' => __('roles.users.form.labels.password_confirm'),
            'role' => __('roles.users.form.labels.role'),
        ]);

        if (!$validator->fails()) {
            return null;
        }

        if ($this->expectsJson()) {
            return $this->jsonValidationError($validator->errors());
        }

        $this->rememberValidationState($this->replayableInput($payload), $validator->errors());
        $this->flash()->error(implode(' ', array_values($validator->firstErrors())));
        return $this->redirect('/users/enroll');
    }

    /**
     * @param array<string, string> $payload
     * @return array<string, string>
     */
    private function replayableInput(array $payload): array
    {
        return [
            'name' => $payload['name'] ?? '',
            'email' => $payload['email'] ?? '',
            'role' => $payload['role'] ?? 'user',
            'email_verified' => $payload['email_verified'] ?? '1',
        ];
    }

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

    /**
     * @return array<string, mixed>
     */
    private function buildEnrollmentForm(): array
    {
        $roleOptions = [];
        foreach ($this->roles->allRoles() as $role) {
            $slug = (string) ($role['slug'] ?? '');
            if ($slug === '') {
                continue;
            }

            $roleOptions[$slug] = RbacLabelPresenter::roleName((string) ($role['name'] ?? ''), $slug) . ' — ' . $slug;
        }

        if ($roleOptions === []) {
            $roleOptions['user'] = (string) __('roles.users.form.default_role_label');
        }

        return FormBuilder::make()
            ->action('/users/enroll')
            ->method('POST')
            ->wrapperClass('row g-3 admin-enrollment-form')
            ->sections([
                'identity' => [
                    'title' => (string) __('roles.users.enroll.sections.identity_title'),
                    'description' => (string) __('roles.users.enroll.sections.identity_description'),
                ],
                'security' => [
                    'title' => (string) __('roles.users.enroll.sections.security_title'),
                    'description' => (string) __('roles.users.enroll.sections.security_description'),
                ],
                'access' => [
                    'title' => (string) __('roles.users.enroll.sections.access_title'),
                    'description' => (string) __('roles.users.enroll.sections.access_description'),
                ],
            ])
            ->fields([
                'name' => [
                    'label' => (string) __('roles.users.form.labels.name'),
                    'required' => true,
                    'section' => 'identity',
                    'col_class' => 'col-12 col-xl-6',
                    'placeholder' => (string) __('roles.users.form.placeholders.name'),
                    'attributes' => ['maxlength' => 255, 'autocomplete' => 'name'],
                ],
                'email' => [
                    'label' => (string) __('roles.users.form.labels.email'),
                    'required' => true,
                    'section' => 'identity',
                    'col_class' => 'col-12 col-xl-6',
                    'type' => 'email',
                    'placeholder' => (string) __('roles.users.form.placeholders.email'),
                    'attributes' => ['maxlength' => 255, 'autocomplete' => 'email'],
                ],
                'password' => [
                    'label' => (string) __('roles.users.form.labels.password'),
                    'required' => true,
                    'section' => 'security',
                    'col_class' => 'col-12 col-xl-6',
                    'type' => 'password',
                    'placeholder' => (string) __('roles.users.form.placeholders.password'),
                    'help' => (string) __('roles.users.form.help.password'),
                    'attributes' => ['autocomplete' => 'new-password', 'minlength' => 8],
                    'value' => '',
                ],
                'password_confirm' => [
                    'label' => (string) __('roles.users.form.labels.password_confirm'),
                    'required' => true,
                    'section' => 'security',
                    'col_class' => 'col-12 col-xl-6',
                    'type' => 'password',
                    'placeholder' => (string) __('roles.users.form.placeholders.password_confirm'),
                    'attributes' => ['autocomplete' => 'new-password', 'minlength' => 8],
                    'value' => '',
                ],
                'role' => [
                    'label' => (string) __('roles.users.form.labels.role'),
                    'required' => true,
                    'section' => 'access',
                    'col_class' => 'col-12 col-xl-6',
                    'type' => 'select',
                    'options' => $roleOptions,
                    'help' => (string) __('roles.users.form.help.role'),
                ],
                'email_verified' => [
                    'label' => (string) __('roles.users.form.labels.email_verified'),
                    'section' => 'access',
                    'col_class' => 'col-12 col-xl-6',
                    'type' => 'select',
                    'options' => [
                        '1' => (string) __('roles.users.form.options.email_verified_yes'),
                        '0' => (string) __('roles.users.form.options.email_verified_no'),
                    ],
                    'help' => (string) __('roles.users.form.help.email_verified'),
                ],
            ])
            ->actions([
                [
                    'type' => 'submit',
                    'label' => (string) __('roles.users.form.actions.submit'),
                    'class' => 'btn btn-primary btn-sm',
                    'icon' => 'fa-solid fa-user-plus',
                ],
                [
                    'type' => 'link',
                    'label' => (string) __('roles.common.cancel'),
                    'href' => '/users',
                    'class' => 'btn btn-outline-secondary btn-sm',
                ],
            ])
            ->toArray();
    }

}
