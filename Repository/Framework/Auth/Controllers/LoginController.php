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
use Catalyst\Framework\Auth\UserProvider;
use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\RedirectTarget;
use Catalyst\Framework\Http\Response;
use Catalyst\Framework\Session\SessionManager;
use Catalyst\Helpers\Config\ConfigManager;

/**
 * Handles credential login and MFA-aware authentication branching.
 *
 * @package Catalyst\Repository\Auth\Controllers
 * Responsibility: Validates login input, protects account state checks, and creates either pending MFA state or a full session.
 */
class LoginController extends Controller
{
    /**
     * Renders the guest login form with sanitized redirect and preserved email input.
     *
     * Responsibility: Renders the guest login form with sanitized redirect and preserved email input.
     * @param Request $request
     * @return Response
     */
    public function showForm(Request $request): Response
    {
        return $this->view('auth.login', [
            'title'    => __('auth.login.title'),
            'redirect' => RedirectTarget::clean($request->input('redirect', '/')),
            'email'    => $request->input('email', ''),
        ], 200, 'auth');
    }

    /**
     * Validates credentials, enforces account status, and routes the user through MFA or session creation.
     *
     * Responsibility: Validates credentials, enforces account status, and routes the user through MFA or session creation.
     * @param Request $request
     * @return Response
     */
    public function login(Request $request): Response
    {
        $email    = trim((string)$request->input('email', ''));
        $password = (string)$request->input('password', '');
        $remember = (bool)$request->input('remember', false);
        $redirect = RedirectTarget::clean($request->input('redirect', '/'));

        // Step 1: field-level validation
        $validator = $this->validate(
            ['email' => $email, 'password' => $password],
            ['email' => 'required|email', 'password' => 'required']
        );

        if ($validator->fails()) {
            if ($this->expectsJson()) {
                return $this->jsonValidationError($validator->errors());
            }
            $this->rememberValidationState([
                'email' => $email,
                'redirect' => $redirect,
                'remember' => $remember ? '1' : '0',
            ], $validator->errors());
            $this->flash()->error(__('auth.validation.check_fields'));
            return $this->redirect('/login');
        }

        $safeRedirect = $redirect;

        // Step 2: find user by email regardless of active/verified status,
        //         then verify password — generic failure message prevents enumeration.
        $users = UserProvider::getInstance();
        $user  = $users->findByEmailAny($email);

        if ($user === null || !$users->verifyPassword($password, (string)($user['password'] ?? ''))) {
            if ($this->expectsJson()) {
                return $this->jsonErrorWithToast(__('auth.messages.login_failed'), 401);
            }
            SessionManager::getInstance()->flashOldInput([
                'email' => $email,
                'redirect' => $safeRedirect,
                'remember' => $remember ? '1' : '0',
            ]);
            $this->flash()->error(__('auth.messages.login_failed'));
            return $this->redirect('/login');
        }

        // Step 3: email must be verified before anything else
        if ((int)($user['email_verified'] ?? 0) !== 1) {
            if ($this->expectsJson()) {
                return $this->jsonErrorWithToast(__('auth.messages.email_not_verified'), 403);
            }
            SessionManager::getInstance()->flashOldInput([
                'email' => $email,
                'redirect' => $safeRedirect,
                'remember' => $remember ? '1' : '0',
            ]);
            $this->flash()->error(__('auth.messages.email_not_verified'));
            return $this->redirect('/login');
        }

        // Step 4: account must be active
        if ((int)($user['active'] ?? 0) !== 1) {
            if ($this->expectsJson()) {
                return $this->jsonErrorWithToast(__('auth.messages.account_inactive'), 403);
            }
            SessionManager::getInstance()->flashOldInput([
                'email' => $email,
                'redirect' => $safeRedirect,
                'remember' => $remember ? '1' : '0',
            ]);
            $this->flash()->error(__('auth.messages.account_inactive'));
            return $this->redirect('/login');
        }

        $auth      = AuthManager::getInstance();
        $mfaGlobal = (bool)ConfigManager::getInstance()->get('security.security.mfa_enabled', false);

        if ($mfaGlobal) {
            if ((int)($user['mfa_enabled'] ?? 0) === 0) {
                // Step 5a: MFA globally required but user has not configured it yet.
                // Store pending-setup state (no session created yet).
                $auth->setPendingMfaSetup((int)$user['id'], $remember, $safeRedirect);

                if ($this->expectsJson()) {
                    return $this->jsonSuccess(['mfa_setup_required' => true])
                        ->withRedirect('/mfa/setup', 0);
                }
                return $this->redirect('/mfa/setup');
            }

            // Step 5b: MFA configured -> pending challenge
            $auth->setPendingMfa((int)$user['id'], $remember, $safeRedirect);

            if ($this->expectsJson()) {
                return $this->jsonSuccess(['mfa_required' => true])
                    ->withRedirect('/mfa/challenge', 0);
            }
            return $this->redirect('/mfa/challenge');
        }

        // Step 6: MFA globally disabled -> create full session immediately
        $auth->loginFromUser($user, $remember);

        if ($this->expectsJson()) {
            return $this->jsonSuccessWithToast(null, __('auth.messages.login_success'))
                ->withRedirect($safeRedirect, 800);
        }

        return $this->redirect($safeRedirect);
    }
}
