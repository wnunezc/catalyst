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

namespace Catalyst\Repository\Auth\Controllers;

use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Framework\Auth\MfaManager;
use Catalyst\Framework\Auth\UserProvider;
use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Catalyst\Framework\Session\SessionManager;
use Catalyst\Helpers\Config\ConfigManager;
use Catalyst\Repository\Auth\Requests\MfaCodeRequest;

/**
 * Handles MFA setup, recovery-code use, and login challenge completion.
 *
 * @package Catalyst\Repository\Auth\Controllers
 * Responsibility: Enforces MFA access rules, provisions TOTP secrets, persists backup-code state, and completes pending logins.
 */
class MfaController extends Controller
{
    // -------------------------------------------------------------------------
    // Setup & management
    // -------------------------------------------------------------------------

    /**
     * Show the MFA setup page with a QR code to scan. Accessible in two modes: - Normal: user is already authenticated and wants to enable MFA. - Forced: MFA is globally required; user arrived here via login with no MFA configured yet (hasMfaSetupPending = true, no auth session).
     *
     * Responsibility: Show the MFA setup page with a QR code to scan. Accessible in two modes: - Normal: user is already authenticated and wants to enable MFA. - Forced: MFA is globally required; user arrived here via login with no MFA configured yet (hasMfaSetupPending = true, no auth session).
     * @param Request $request
     * @return Response
     */
    public function setup(Request $request): Response
    {
        if (!self::isMfaGloballyEnabled()) {
            $this->flash()->error(__('auth.mfa.globally_disabled'));
            return $this->redirect('/');
        }

        $auth         = AuthManager::getInstance();
        $setupPending = $auth->hasMfaSetupPending();

        // Must have either an active session OR be in forced-setup flow
        if (!$auth->check() && !$setupPending) {
            return $this->redirect('/login');
        }

        // Resolve user from whichever source is active
        $user = $this->resolveUser($auth, $setupPending);
        if ($user === null) {
            $auth->clearMfaSetupPending();
            return $this->redirect('/login');
        }

        $mfa    = MfaManager::getInstance();
        $email  = (string)($user['email'] ?? '');
        $issuer = $this->resolveIssuer();

        $secret = $mfa->generateSecret();
        SessionManager::getInstance()->set('_mfa_setup_secret', $secret);

        $qrUri     = $mfa->generateQrUri($secret, $email, $issuer);
        $mfaData   = UserProvider::getInstance()->getMfaData((int)($user['id'] ?? 0));
        $mfaActive = (int)($mfaData['mfa_enabled'] ?? 0) === 1;

        return $this->view('auth.mfa-setup', [
            'title'        => __('auth.mfa.setup_title'),
            'qrUri'        => $qrUri,
            'secret'       => $secret,
            'issuer'       => $issuer,
            'mfaActive'    => $mfaActive,
            'forcedSetup'  => $setupPending,
            'show_topbar' => false,
            'show_sidebar' => false,
            'show_status_bar' => false,
            'show_theme_customizer' => false,
            'show_auth_brand_panel' => true,
            'body_class' => 'catalyst-auth-body',
            'shell_class' => 'auth-layout-shell',
            'content_class' => 'auth-layout-shell__form',
            'surface_context' => 'auth',
        ]);
    }

    /**
     * Confirm the first TOTP code and permanently activate MFA. If the user arrived via the forced-setup flow (hasMfaSetupPending), the full login session is created here after successful activation.
     *
     * Responsibility: Confirm the first TOTP code and permanently activate MFA. If the user arrived via the forced-setup flow (hasMfaSetupPending), the full login session is created here after successful activation.
     * @param Request $request
     * @return Response
     */
    public function enable(Request $request): Response
    {
        if (!self::isMfaGloballyEnabled()) {
            return $this->expectsJson()
                ? $this->jsonErrorWithToast(__('auth.mfa.globally_disabled'), 403)
                : $this->redirect('/');
        }

        $auth         = AuthManager::getInstance();
        $setupPending = $auth->hasMfaSetupPending();

        if (!$auth->check() && !$setupPending) {
            return $this->postActionErrorRedirect('/login', __('auth.mfa.session_expired'), 401);
        }

        $user = $this->resolveUser($auth, $setupPending);
        if ($user === null) {
            $auth->clearMfaSetupPending();
            return $this->postActionErrorRedirect('/login', __('auth.mfa.session_expired'), 401);
        }

        $payload = (new MfaCodeRequest($request, false))->validated();
        $code = (string) ($payload['code'] ?? '');
        $secret = (string)SessionManager::getInstance()->get('_mfa_setup_secret', '');

        if ($secret === '') {
            return $this->postActionErrorRedirect('/mfa/setup', __('auth.mfa.setup_expired'));
        }

        if (!MfaManager::getInstance()->verifyCode($secret, $code)) {
            if ($this->expectsJson()) {
                return $this->jsonValidationError([
                    'code' => [__('auth.mfa.invalid_code')],
                ], __('auth.mfa.invalid_code'));
            }
            $this->rememberValidationState([], [
                'code' => [__('auth.mfa.invalid_code')],
            ]);
            $this->flash()->error(__('auth.mfa.invalid_code'));
            return $this->redirect('/mfa/setup');
        }

        $userId      = (int)($user['id'] ?? 0);
        $backupCodes = MfaManager::getInstance()->generateBackupCodes();

        UserProvider::getInstance()->enableMfa($userId, $secret, $backupCodes);
        $session = SessionManager::getInstance();
        $session->remove('_mfa_setup_secret');
        $session->set('_mfa_backup_codes_display', $backupCodes);

        // Forced-setup path: complete the login session now that MFA is configured
        if ($setupPending) {
            $safeRedirect = $auth->getMfaSetupPendingRedirect();
            $auth->completeMfaSetupLogin();

            if ($safeRedirect !== '' && $safeRedirect !== '/mfa/setup') {
                $session->set('_mfa_setup_continue_redirect', $safeRedirect);
            }

            return $this->postActionSuccessRedirect('/mfa/setup', __('auth.mfa.enabled_success'));
        }

        return $this->postActionSuccessRedirect('/mfa/setup', __('auth.mfa.enabled_success'));
    }

    /**
     * Disable MFA after verifying the user's current password.
     *
     * Responsibility: Disable MFA after verifying the user's current password.
     * @param Request $request
     * @return Response
     */
    public function disable(Request $request): Response
    {
        $auth = AuthManager::getInstance();

        if (!$auth->check()) {
            return $this->postActionErrorRedirect('/login', __('auth.mfa.session_expired'), 401);
        }

        $password = (string)$request->input('password', '');
        $users    = UserProvider::getInstance();
        $userId   = (int)($auth->user()['id'] ?? 0);

        $userRow = $users->findById($userId);
        if ($userRow === null || !$users->verifyPassword($password, (string)($userRow['password'] ?? ''))) {
            if ($this->expectsJson()) {
                return $this->jsonValidationError([
                    'password' => [__('auth.mfa.wrong_password')],
                ], __('auth.mfa.wrong_password'));
            }
            $this->rememberValidationState([], [
                'password' => [__('auth.mfa.wrong_password')],
            ]);
            $this->flash()->error(__('auth.mfa.wrong_password'));
            return $this->redirect('/mfa/setup');
        }

        $users->disableMfa($userId);

        return $this->postActionSuccessRedirect('/mfa/setup', __('auth.mfa.disabled_success'));
    }

    // -------------------------------------------------------------------------
    // Login challenge (requires pending MFA state)
    // -------------------------------------------------------------------------

    /**
     * Show the MFA challenge form during a pending login.
     *
     * Responsibility: Show the MFA challenge form during a pending login.
     * @param Request $request
     * @return Response
     */
    public function challenge(Request $request): Response
    {
        $auth = AuthManager::getInstance();

        if ($auth->check()) {
            return $this->redirect($auth->getMfaPendingRedirect() ?: '/');
        }

        if (!$auth->hasMfaPending()) {
            return $this->postActionErrorRedirect('/login', __('auth.mfa.session_expired'), 401);
        }

        return $this->view('auth.mfa-challenge', [
            'title' => __('auth.mfa.challenge_title'),
            'show_topbar' => false,
            'show_sidebar' => false,
            'show_status_bar' => false,
            'show_theme_customizer' => false,
            'show_auth_brand_panel' => true,
            'body_class' => 'catalyst-auth-body',
            'shell_class' => 'auth-layout-shell',
            'content_class' => 'auth-layout-shell__form',
            'surface_context' => 'auth',
        ]);
    }

    /**
     * Verify TOTP code (or backup code) and complete the pending login session.
     *
     * Responsibility: Verify TOTP code (or backup code) and complete the pending login session.
     * @param Request $request
     * @return Response
     */
    public function verify(Request $request): Response
    {
        $auth = AuthManager::getInstance();

        if ($auth->check()) {
            return $this->redirect('/');
        }

        if (!$auth->hasMfaPending()) {
            return $this->redirect('/login');
        }

        $payload = (new MfaCodeRequest($request, true))->validated();
        $code = (string) ($payload['code'] ?? '');
        $userId = $auth->getMfaPendingUserId();

        if ($userId === null) {
            return $this->postActionErrorRedirect('/login', __('auth.mfa.session_expired'), 401);
        }

        $mfaData = UserProvider::getInstance()->getMfaData($userId);

        if ($mfaData === null || empty($mfaData['mfa_secret'])) {
            $auth->clearPendingMfa();
            return $this->postActionErrorRedirect('/login', __('auth.messages.login_failed'), 401);
        }

        $secret   = (string)$mfaData['mfa_secret'];
        $verified = false;

        if (MfaManager::getInstance()->verifyCode($secret, $code)) {
            $verified = true;
        } else {
            $rawCodes    = $mfaData['mfa_backup_codes'];
            $backupCodes = $rawCodes !== null
                ? (array)(json_decode($rawCodes, true) ?? [])
                : [];

            if (MfaManager::getInstance()->verifyBackupCode($code, $backupCodes)) {
                UserProvider::getInstance()->updateMfaBackupCodes($userId, $backupCodes);
                $verified = true;
            }
        }

        if (!$verified) {
            if ($this->expectsJson()) {
                return $this->jsonValidationError([
                    'code' => [__('auth.mfa.invalid_code')],
                ], __('auth.mfa.invalid_code'));
            }
            $this->rememberValidationState([], [
                'code' => [__('auth.mfa.invalid_code')],
            ]);
            $this->flash()->error(__('auth.mfa.invalid_code'));
            return $this->redirect('/mfa/challenge');
        }

        $safeRedirect = $auth->getMfaPendingRedirect();

        if (!$auth->completeMfaLogin()) {
            return $this->postActionErrorRedirect('/login', __('auth.messages.login_failed'), 401);
        }

        return $this->postActionSuccessRedirect($safeRedirect, __('auth.messages.login_success'), null, 800);
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * True when the framework-level MFA toggle is on in security.json.
     *
     * @return bool
     */
    private static function isMfaGloballyEnabled(): bool
    {
        return (bool)ConfigManager::getInstance()->get('security.security.mfa_enabled', false);
    }

    /**
     * Resolve the user row from either the active session or the forced-setup pending state.
     *
     * Responsibility: Resolve the user row from either the active session or the forced-setup pending state.
     * @param AuthManager $auth
     * @param bool        $setupPending
     * @return array|null
     */
    private function resolveUser(AuthManager $auth, bool $setupPending): ?array
    {
        if ($setupPending) {
            $userId = $auth->getMfaSetupPendingUserId();
            return $userId !== null ? UserProvider::getInstance()->findById($userId) : null;
        }

        return $auth->user() !== null
            ? UserProvider::getInstance()->findById((int)($auth->user()['id'] ?? 0))
            : null;
    }

    /**
     * Resolve the application name for the otpauth:// URI issuer field.
     *
     * Responsibility: Resolve the application name for the otpauth:// URI issuer field.
     * @return string
     */
    private function resolveIssuer(): string
    {
        try {
            $name = ConfigManager::getInstance()->get('app.project.project_name');
            if ($name !== null && $name !== '') {
                return (string)$name;
            }
        } catch (\Throwable) {
            // ConfigManager not available
        }
        return 'Catalyst';
    }
}
