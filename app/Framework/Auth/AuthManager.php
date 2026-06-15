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

namespace Catalyst\Framework\Auth;

use Catalyst\Framework\Authorization\RoleRepository;
use Catalyst\Framework\Session\SessionManager;
use Catalyst\Framework\Tenancy\TenancyManager;
use Catalyst\Framework\Traits\SingletonTrait;
use Catalyst\Helpers\Log\Logger;

/**
 * Manages authenticated sessions, remember-me restoration and MFA pending states.
 *
 * @package Catalyst\Framework\Auth
 * Responsibility: Orchestrate user authentication state through SessionManager, RememberMe and tenant-aware user context.
 */
class AuthManager
{
    use SingletonTrait;

    private const MFA_PENDING_TTL = 300;

    private UserProvider $users;
    private RememberMe   $remember;
    private SessionManager $session;
    private Logger $logger;
    /**
     * @var array<string, mixed>|null
     */
    private ?array $scopedUser = null;

    /**
     * Initializes authentication storage, session and logging collaborators.
     *
     * Responsibility: Initializes authentication storage, session and logging collaborators.
     */
    protected function __construct()
    {
        $this->users   = UserProvider::getInstance();
        $this->remember = RememberMe::getInstance();
        $this->session = SessionManager::getInstance();
        $this->logger  = Logger::getInstance();
    }

    // -------------------------------------------------------------------------
    // Public API
    // -------------------------------------------------------------------------

    /**
     * Attempt to log in a user with email and password.
     *
     * Responsibility: Attempt to log in a user with email and password.
     * @param string $email
     * @param string $password  Plain-text password
     * @param bool   $remember  Whether to set a remember-me cookie
     * @return bool
     */
    public function login(string $email, string $password, bool $remember = false): bool
    {
        $user = $this->users->findByEmail($email);

        if ($user === null) {
            $this->logger->info('Auth: login failed — user not found', ['email' => $email]);
            return false;
        }

        if (!$this->users->verifyPassword($password, $user['password'])) {
            $this->logger->info('Auth: login failed — wrong password', ['email' => $email]);
            return false;
        }

        $this->createSession($user);
        $this->users->updateLastLogin((int)$user['id']);

        if ($remember) {
            $this->remember->create((int)$user['id']);
        }

        $this->logger->info('Auth: login success', ['user_id' => $user['id']]);
        return true;
    }

    /**
     * Log in a user directly from their data array (used after OAuth / registration). Does NOT issue a remember-me token.
     *
     * Responsibility: Log in a user directly from their data array (used after OAuth / registration). Does NOT issue a remember-me token.
     * @param array<string, mixed> $user Full user row from the database.
     * @return void
     */
    public function loginUser(array $user): void
    {
        $this->createSession($user);
        $this->users->updateLastLogin((int)$user['id']);
        $this->logger->info('Auth: direct login', ['user_id' => $user['id']]);
    }

    /**
     * Create a full authenticated session from a pre-verified user row. Optionally issues a remember-me token. Used by LoginController after MFA-aware credential check and by MfaController after successful TOTP/backup verification (via completeMfaLogin()).
     *
     * Responsibility: Create a full authenticated session from a pre-verified user row. Optionally issues a remember-me token. Used by LoginController after MFA-aware credential check and by MfaController after successful TOTP/backup verification (via completeMfaLogin()).
     * @param array<string, mixed> $user Full user row.
     * @param bool  $remember Whether to set a remember-me cookie
     * @return void
     */
    public function loginFromUser(array $user, bool $remember = false): void
    {
        $this->createSession($user);
        $this->users->updateLastLogin((int)$user['id']);

        if ($remember) {
            $this->remember->create((int)$user['id']);
        }

        $this->logger->info('Auth: login (MFA-aware path)', ['user_id' => $user['id']]);
    }

    /**
     * Attempt to restore a session from a remember-me cookie.
     *
     * Responsibility: Attempt to restore a session from a remember-me cookie.
     * @return bool True if a valid token was found and the session was restored
     */
    public function loginFromRemember(): bool
    {
        if (!$this->remember->hasToken()) {
            return false;
        }

        $userId = $this->remember->resolve();

        if ($userId === null) {
            return false;
        }

        $user = $this->users->findById($userId);

        if ($user === null) {
            $this->remember->invalidate($userId);
            return false;
        }

        $this->createSession($user);
        $this->logger->info('Auth: login from remember-me token', ['user_id' => $userId]);
        return true;
    }

    /**
     * Destroy the current authenticated session and remember-me token.
     *
     * Responsibility: Destroy the current authenticated session and remember-me token.
     * @return void
     */
    public function logout(): void
    {
        $userId = $this->id();

        if ($userId !== null) {
            $this->remember->invalidate($userId);
        }

        $this->session
            ->remove('_auth_logged_in')
            ->remove('_auth_user_id')
            ->remove('_auth_user_email')
            ->remove('_auth_user_name')
            ->remove('_auth_user_role')
            ->remove('_auth_tenant_id')
            ->remove('_auth_tenant_key')
            ->remove('_auth_tenant_label')
            ->remove('_mfa_setup_secret');

        $this->clearPendingMfa();
        $this->clearMfaSetupPending();
        $this->session->regenerateId(true);

        $this->logger->info('Auth: logout', ['user_id' => $userId]);
    }

    /**
     * Check whether a user is currently authenticated.
     *
     * Responsibility: Check whether a user is currently authenticated.
     * @return bool
     */
    public function check(): bool
    {
        if ($this->scopedUser !== null) {
            return $this->tenantMatches($this->scopedUser);
        }

        if ($this->session->get('_auth_logged_in', false) !== true) {
            return false;
        }

        $tenantAwareUser = [
            'tenant_id' => $this->session->get('_auth_tenant_id'),
        ];

        if (!$this->tenantMatches($tenantAwareUser)) {
            $this->logout();

            return false;
        }

        return true;
    }

    /**
     * Get the authenticated user's data array.
     *
     * Responsibility: Exposes the authenticated user payload stored in the active session.
     * @return array|null
     */
    public function user(): ?array
    {
        if ($this->scopedUser !== null) {
            return $this->scopedUser;
        }

        if (!$this->check()) {
            return null;
        }

        return [
            'id'    => $this->session->get('_auth_user_id'),
            'email' => $this->session->get('_auth_user_email'),
            'name'  => $this->session->get('_auth_user_name'),
            'role'  => $this->session->get('_auth_user_role'),
            'tenant_id' => $this->session->get('_auth_tenant_id'),
            'tenant_key' => $this->session->get('_auth_tenant_key'),
            'tenant_label' => $this->session->get('_auth_tenant_label'),
        ];
    }

    /**
     * Get the authenticated user's ID.
     *
     * Responsibility: Exposes the authenticated user identifier from the active session payload.
     * @return int|null
     */
    public function id(): ?int
    {
        if ($this->scopedUser !== null) {
            $id = $this->scopedUser['id'] ?? null;

            return $id !== null ? (int) $id : null;
        }

        if (!$this->check()) {
            return null;
        }

        $id = $this->session->get('_auth_user_id');
        return $id !== null ? (int)$id : null;
    }

    /**
     * Scope an authenticated user to the current request without mutating the session. Used by non-session guards such as bearer API tokens so Gate, middleware and audit logging can keep consuming AuthManager as the single auth boundary.
     *
     * Responsibility: Scope an authenticated user to the current request without mutating the session. Used by non-session guards such as bearer API tokens so Gate, middleware and audit logging can keep consuming AuthManager as the single auth boundary.
     * @param array<string, mixed> $user
     */
    public function beginScopedUser(array $user): void
    {
        $this->scopedUser = TenancyManager::getInstance()->attachContextToUser($user);
    }

    /**
     * Clears the request-only authenticated user context.
     *
     * Responsibility: Clears the request-only authenticated user context.
     */
    public function clearScopedUser(): void
    {
        $this->scopedUser = null;
    }

    // -------------------------------------------------------------------------
    // MFA pending state (Etapa 12 — HIPAA §164.312(d))
    // -------------------------------------------------------------------------

    /**
     * Store a pending-MFA state after successful credential verification. The full session is not created until completeMfaLogin(), and the transition expires after five minutes.
     *
     * Responsibility: Stores the pending user, remember flag, safe redirect and issue time used to enforce the short MFA transition window.
     * @param int    $userId
     * @param bool   $remember  Whether to set remember-me after MFA passes
     * @param string $redirect  Safe redirect path after full login
     * @return void
     */
    public function setPendingMfa(int $userId, bool $remember, string $redirect): void
    {
        // Remove privileged transition state before rotating. Keep the old
        // anonymous session briefly available so concurrent requests cannot
        // recreate it and overwrite the newly issued cookie.
        $this->clearPendingMfa();
        $this->clearMfaSetupPending();
        $this->session->regenerateId(false);

        $this->session
            ->set('_mfa_pending_user_id',  $userId)
            ->set('_mfa_pending_remember', $remember)
            ->set('_mfa_pending_redirect', AuthInputGuard::localRedirect($redirect))
            ->set('_mfa_pending_issued_at', time());
    }

    /**
     * Check whether a non-expired pending MFA challenge is in progress.
     *
     * Responsibility: Rejects and clears missing or expired pending MFA challenge state.
     * @return bool
     */
    public function hasMfaPending(): bool
    {
        if ($this->pendingStateIsFresh('_mfa_pending_user_id', '_mfa_pending_issued_at')) {
            return true;
        }

        $this->clearPendingMfa();
        return false;
    }

    /**
     * Return the user ID stored in the pending MFA state, or null if absent.
     *
     * Responsibility: Return the user ID stored in the pending MFA state, or null if absent.
     * @return int|null
     */
    public function getMfaPendingUserId(): ?int
    {
        if (!$this->hasMfaPending()) {
            return null;
        }

        $id = $this->session->get('_mfa_pending_user_id');
        return $id !== null ? (int)$id : null;
    }

    /**
     * Return the remember flag stored in the pending MFA state.
     *
     * Responsibility: Return the remember flag stored in the pending MFA state.
     * @return bool
     */
    public function getMfaPendingRemember(): bool
    {
        return (bool)$this->session->get('_mfa_pending_remember', false);
    }

    /**
     * Return the redirect path stored in the pending MFA state.
     *
     * Responsibility: Return the redirect path stored in the pending MFA state.
     * @return string
     */
    public function getMfaPendingRedirect(): string
    {
        return AuthInputGuard::localRedirect((string)$this->session->get('_mfa_pending_redirect', '/'));
    }

    /**
     * Complete the MFA challenge: create full auth session and clear pending state.
     *
     * Responsibility: Complete the MFA challenge: create full auth session and clear pending state.
     * @return bool  False if no pending state or user no longer exists
     */
    public function completeMfaLogin(): bool
    {
        $userId = $this->getMfaPendingUserId();
        if ($userId === null) {
            return false;
        }

        $user = $this->users->findById($userId);
        if ($user === null) {
            $this->clearPendingMfa();
            return false;
        }

        $remember = $this->getMfaPendingRemember();
        $this->clearPendingMfa();

        $this->createSession($user);
        $this->users->updateLastLogin($userId);

        if ($remember) {
            $this->remember->create($userId);
        }

        $this->logger->info('Auth: MFA login complete', ['user_id' => $userId]);
        return true;
    }

    /**
     * Remove all pending MFA session keys.
     *
     * Responsibility: Remove all pending MFA session keys.
     * @return void
     */
    public function clearPendingMfa(): void
    {
        $this->session
            ->remove('_mfa_pending_user_id')
            ->remove('_mfa_pending_remember')
            ->remove('_mfa_pending_redirect')
            ->remove('_mfa_pending_issued_at');
    }

    // -------------------------------------------------------------------------
    // MFA forced-setup pending state
    // Used when MFA is globally required but the user has not yet configured it.
    // Credentials are verified but no session is created until setup completes.
    //
    // Session keys:
    //   _mfa_setup_pending_user_id   — int
    //   _mfa_setup_pending_remember  — bool
    //   _mfa_setup_pending_redirect  — string
    // -------------------------------------------------------------------------

    /**
     * Store a five-minute pending-MFA-setup state after successful credential verification.
     *
     * Responsibility: Stores the forced-setup identity, remember flag, safe redirect and issue time without creating a full session.
     * @param int    $userId
     * @param bool   $remember
     * @param string $redirect  Safe redirect after setup completes
     * @return void
     */
    public function setPendingMfaSetup(int $userId, bool $remember, string $redirect): void
    {
        // Remove privileged transition state before rotating. Keep the old
        // anonymous session briefly available so concurrent requests cannot
        // recreate it and overwrite the newly issued cookie.
        $this->clearPendingMfa();
        $this->clearMfaSetupPending();
        $this->session->regenerateId(false);

        $this->session
            ->set('_mfa_setup_pending_user_id',  $userId)
            ->set('_mfa_setup_pending_remember', $remember)
            ->set('_mfa_setup_pending_redirect', AuthInputGuard::localRedirect($redirect))
            ->set('_mfa_setup_pending_issued_at', time());
    }

    /**
     * Check whether a non-expired forced-MFA-setup flow is in progress.
     *
     * Responsibility: Rejects and clears missing or expired forced-MFA-setup state.
     * @return bool
     */
    public function hasMfaSetupPending(): bool
    {
        if ($this->pendingStateIsFresh('_mfa_setup_pending_user_id', '_mfa_setup_pending_issued_at')) {
            return true;
        }

        $this->clearMfaSetupPending();
        return false;
    }

    /**
     * Returns the user ID stored in the pending MFA setup state, or null if absent.
     *
     * Responsibility: Returns the user ID stored in the pending MFA setup state, or null if absent.
     * @return int|null
     */
    public function getMfaSetupPendingUserId(): ?int
    {
        if (!$this->hasMfaSetupPending()) {
            return null;
        }

        $id = $this->session->get('_mfa_setup_pending_user_id');
        return $id !== null ? (int)$id : null;
    }

    /**
     * Returns the remember flag stored in the pending MFA setup state.
     *
     * Responsibility: Returns the remember flag stored in the pending MFA setup state.
     * @return bool
     */
    public function getMfaSetupPendingRemember(): bool
    {
        return (bool)$this->session->get('_mfa_setup_pending_remember', false);
    }

    /**
     * Returns the safe redirect path stored in the pending MFA setup state.
     *
     * Responsibility: Returns the safe redirect path stored in the pending MFA setup state.
     * @return string
     */
    public function getMfaSetupPendingRedirect(): string
    {
        return AuthInputGuard::localRedirect((string)$this->session->get('_mfa_setup_pending_redirect', '/'));
    }

    /**
     * Complete a forced-setup login: create full session, issue remember-me if needed, and clear the pending-setup state.
     *
     * Responsibility: Complete a forced-setup login: create full session, issue remember-me if needed, and clear the pending-setup state.
     * @return bool  False if pending state is missing or user no longer exists
     */
    public function completeMfaSetupLogin(): bool
    {
        $userId = $this->getMfaSetupPendingUserId();
        if ($userId === null) {
            return false;
        }

        $user = $this->users->findById($userId);
        if ($user === null) {
            $this->clearMfaSetupPending();
            return false;
        }

        $remember = $this->getMfaSetupPendingRemember();
        $this->clearMfaSetupPending();

        $this->createSession($user);
        $this->users->updateLastLogin($userId);

        if ($remember) {
            $this->remember->create($userId);
        }

        $this->logger->info('Auth: MFA setup-flow login complete', ['user_id' => $userId]);
        return true;
    }

    /**
     * Remove all pending-MFA-setup session keys.
     *
     * Responsibility: Remove all pending-MFA-setup session keys.
     * @return void
     */
    public function clearMfaSetupPending(): void
    {
        $this->session
            ->remove('_mfa_setup_pending_user_id')
            ->remove('_mfa_setup_pending_remember')
            ->remove('_mfa_setup_pending_redirect')
            ->remove('_mfa_setup_pending_issued_at');
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Creates the tenant-aware session keys for a fully authenticated user.
     *
     * Responsibility: Creates the tenant-aware session keys for a fully authenticated user.
     * @param array<string, mixed> $user
     */
    private function createSession(array $user): void
    {
        // Authentication state is written only after rotation. The retained old
        // session remains anonymous and expires through the configured backend.
        $this->clearPendingMfa();
        $this->clearMfaSetupPending();
        $this->session->regenerateId(false);
        $user = TenancyManager::getInstance()->attachContextToUser($user);

        // Load primary role from user_roles pivot (lowest role ID = most privileged assigned first)
        $userId = (int)$user['id'];
        $roles  = RoleRepository::getInstance()->getUserRoles($userId);
        $primaryRole = $roles[0]['slug'] ?? 'user';

        $this->session
            ->set('_auth_logged_in', true)
            ->set('_auth_user_id',   $userId)
            ->set('_auth_user_email', $user['email'])
            ->set('_auth_user_name',  $user['name'])
            ->set('_auth_user_role',  $primaryRole)
            ->set('_auth_tenant_id', (int) ($user['tenant_id'] ?? 0))
            ->set('_auth_tenant_key', (string) ($user['tenant_key'] ?? 'default'))
            ->set('_auth_tenant_label', (string) ($user['tenant_label'] ?? 'Default tenant'));
    }

    /**
     * Checks whether a privileged pending-auth transition is still usable.
     *
     * Responsibility: Rejects missing, expired or future-dated MFA transition state before it can create an authenticated session.
     */
    private function pendingStateIsFresh(string $userKey, string $issuedAtKey): bool
    {
        if ($this->session->get($userKey) === null) {
            return false;
        }

        $issuedAt = (int) $this->session->get($issuedAtKey, 0);
        $now = time();

        return $issuedAt > 0
            && $issuedAt <= $now
            && ($now - $issuedAt) <= self::MFA_PENDING_TTL;
    }

    /**
     * Checks whether a user row belongs to the active tenant context.
     *
     * Responsibility: Checks whether a user row belongs to the active tenant context.
     * @param array<string, mixed> $user
     */
    private function tenantMatches(array $user): bool
    {
        $currentTenantId = TenancyManager::getInstance()->currentTenantId();
        $userTenantId = (int) ($user['tenant_id'] ?? 0);

        return $userTenantId <= 0 || $currentTenantId <= 0 || $userTenantId === $currentTenantId;
    }
}
