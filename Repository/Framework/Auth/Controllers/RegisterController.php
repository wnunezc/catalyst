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

use Catalyst\Framework\Auth\AuthInputGuard;
use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Framework\Auth\TokenRepository;
use Catalyst\Framework\Auth\UserProvider;
use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Catalyst\Framework\Mail\MailManager;
use Catalyst\Helpers\Config\ConfigManager;
use Exception;

/**
 * Handles self-service account registration and email verification delivery.
 *
 * @package Catalyst\Repository\Auth\Controllers
 * Responsibility: Validates registration input, creates unverified users, and sends one-time verification links.
 */
class RegisterController extends Controller
{
    /**
     * Show the registration form.
     *
     * Responsibility: Show the registration form.
     * @param Request $request
     * @return Response
     */
    public function showForm(Request $request): Response
    {
        return $this->view('auth.register', [
            'title'          => __('auth.register.title'),
            'passwordPolicy' => AuthInputGuard::passwordPolicy(),
        ], 200, 'auth');
    }

    /**
     * Process registration and send email verification link.
     *
     * Responsibility: Process registration and send email verification link.
     * @param Request $request
     * @return Response
     */
    public function register(Request $request): Response
    {
        $name            = trim((string)$request->input('name', ''));
        $email           = trim((string)$request->input('email', ''));
        $password        = (string)$request->input('password', '');
        $passwordConfirm = (string)$request->input('password_confirm', '');

        $validator = $this->validate(
            [
                'name'             => $name,
                'email'            => $email,
                'password'         => $password,
                'password_confirm' => $passwordConfirm,
            ],
            [
                'name'             => 'required|min:2|max:255',
                'email'            => 'required|email',
                'password'         => 'required|min:8',
                'password_confirm' => 'required',
            ]
        );

        if ($validator->fails()) {
            if ($this->expectsJson()) {
                return $this->jsonValidationError($validator->errors());
            }
            $this->rememberValidationState([
                'name' => $name,
                'email' => $email,
            ], $validator->errors());
            $this->flash()->error(__('auth.validation.check_fields'));
            return $this->redirect('/register');
        }

        $policyErrors = AuthInputGuard::passwordPolicyErrors($password);
        if ($policyErrors !== []) {
            if ($this->expectsJson()) {
                return $this->jsonValidationError($policyErrors);
            }
            $this->rememberValidationState([
                'name' => $name,
                'email' => $email,
            ], $policyErrors);
            $this->flash()->error(__('auth.validation.check_fields'));
            return $this->redirect('/register');
        }

        if ($password !== $passwordConfirm) {
            if ($this->expectsJson()) {
                return $this->jsonErrorWithToast(__('auth.validation.password_mismatch'), 422);
            }
            $this->rememberValidationState([
                'name' => $name,
                'email' => $email,
            ], [
                'password_confirm' => [__('auth.validation.password_mismatch')],
            ]);
            $this->flash()->error(__('auth.validation.password_mismatch'));
            return $this->redirect('/register');
        }

        $users = UserProvider::getInstance();

        if ($users->findByEmailAny($email) !== null) {
            if ($this->expectsJson()) {
                return $this->jsonErrorWithToast(__('auth.messages.email_exists'), 409);
            }
            $this->rememberValidationState([
                'name' => $name,
                'email' => $email,
            ], [
                'email' => [__('auth.messages.email_exists')],
            ]);
            $this->flash()->error(__('auth.messages.email_exists'));
            return $this->redirect('/register');
        }

        $userId = $users->create($name, $email, $password, 'user', false);
        $token  = TokenRepository::getInstance()->createVerificationToken($userId);

        $this->sendVerificationEmail($email, $name, $token);

        if ($this->expectsJson()) {
            return $this->jsonSuccessWithToast(null, __('auth.messages.register_success'))
                ->withRedirect('/login', 1500);
        }

        $this->toast('success', __('auth.messages.register_success'));
        return $this->redirect('/login');
    }

    /**
     * Send the email-verification message.
     *
     * Responsibility: Send the email-verification message.
     * @param string $email
     * @param string $name
     * @param string $rawToken
     * @return void
     */
    private function sendVerificationEmail(string $email, string $name, string $rawToken): void
    {
        $appUrl = $this->resolveAppUrl();
        $link   = $appUrl . '/verify-email/' . $rawToken;

        $html = '<p>' . __('auth.email.verify_greeting', ['name' => e($name)]) . '</p>'
            . '<p><a href="' . e($link) . '">' . e($link) . '</a></p>'
            . '<p>' . __('auth.email.verify_expiry') . '</p>';

        try {
            MailManager::getInstance()
                ->init()
                ->createMessage()
                ->to($email, $name)
                ->subject(__('auth.email.verify_subject'))
                ->html($html)
                ->send();
        } catch (Exception $e) {
            $this->logError('RegisterController: verification email failed', ['error' => $e->getMessage()]);
            if (defined('IS_DEVELOPMENT') && IS_DEVELOPMENT) {
                $this->flash()->error('[Dev] Verification email failed: ' . $e->getMessage());
            }
        }
    }

    /**
     * Resolves the public application URL used to build verification links.
     *
     * Responsibility: Resolves the public application URL used to build verification links.
     */
    private function resolveAppUrl(): string
    {
        try {
            $configManager = $GLOBALS['APP_CONFIGURATION'] ?? ConfigManager::getInstance();

            if ($configManager instanceof ConfigManager) {
                $app = $configManager->entry('app', 'project');
                return rtrim((string)($app['project_url'] ?? ''), '/');
            }
        } catch (\Throwable) {
        }

        $env = defined('GET_ENV_VAR') && is_array(GET_ENV_VAR) ? GET_ENV_VAR : [];
        return rtrim((string)($env['APP_URL'] ?? ''), '/');
    }
}
