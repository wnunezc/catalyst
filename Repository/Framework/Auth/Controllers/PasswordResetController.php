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
use Catalyst\Framework\Auth\RememberMe;
use Catalyst\Framework\Auth\TokenRepository;
use Catalyst\Framework\Auth\UserProvider;
use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Catalyst\Framework\Mail\MailManager;
use Catalyst\Helpers\Config\ConfigManager;
use Exception;

/**************************************************************************************
 * PasswordResetController — forgot-password flow via email link.
 *
 * Routes:
 *   GET  /forgot-password           → showRequestForm()
 *   POST /forgot-password           → sendResetLink()
 *   GET  /reset-password/{token}    → showResetForm()
 *   POST /reset-password/{token}    → reset()
 *
 * @package Catalyst\Repository\Auth\Controllers
 */
/**
 * Defines the Password Reset Controller class contract.
 *
 * @package Catalyst\Repository\Auth\Controllers
 * Responsibility: Coordinates the password reset controller behavior within its module boundary.
 */
class PasswordResetController extends Controller
{
    /**
     * Show the forgot-password form.
     *
     * @param Request $request
     * @return Response
     */
    public function showRequestForm(Request $request): Response
    {
        return $this->view('auth.forgot-password', [
            'title' => __('auth.forgot_password.title'),
        ], 200, 'auth');
    }

    /**
     * Send a password-reset link to the given email address.
     * Always returns a success message to prevent user enumeration.
     *
     * @param Request $request
     * @return Response
     */
    public function sendResetLink(Request $request): Response
    {
        $email = trim((string)$request->input('email', ''));

        $validator = $this->validate(
            ['email' => $email],
            ['email' => 'required|email']
        );

        if ($validator->fails()) {
            if ($this->expectsJson()) {
                $errors = $validator->errors();
                if (isset($errors['email'])) {
                    $errors['email'] = [__('auth.validation.email_invalid')];
                }

                return $this->jsonValidationError($errors);
            }
            $this->rememberValidationState([
                'email' => $email,
            ], $validator->errors());
            $this->flash()->error(__('auth.validation.email_invalid'));
            return $this->redirect('/forgot-password');
        }

        $user = UserProvider::getInstance()->findByEmail($email);

        // Always respond with the same message — no user enumeration
        if ($user !== null) {
            $token = TokenRepository::getInstance()->createPasswordResetToken((int)$user['id']);
            $this->sendResetEmail($email, $user['name'], $token);
        }

        $message = __('auth.messages.password_reset_sent');
        return $this->postActionSuccessRedirect('/login', $message, null, 1200);
    }

    /**
     * Show the password-reset form for a given token.
     *
     * @param Request $request
     * @param string  $token
     * @return Response
     */
    public function showResetForm(Request $request, string $token): Response
    {
        return $this->view('auth.reset-password', [
            'title' => __('auth.reset_password.title'),
            'token' => $token,
            'passwordPolicy' => AuthInputGuard::passwordPolicy(),
        ], 200, 'auth');
    }

    /**
     * Apply the new password if the token is valid.
     *
     * @param Request $request
     * @param string  $token
     * @return Response
     */
    public function reset(Request $request, string $token): Response
    {
        $password        = (string)$request->input('password', '');
        $passwordConfirm = (string)$request->input('password_confirm', '');

        $validator = $this->validate(
            ['password' => $password, 'password_confirm' => $passwordConfirm],
            ['password' => 'required|min:8', 'password_confirm' => 'required']
        );

        if ($validator->fails()) {
            if ($this->expectsJson()) {
                return $this->jsonValidationError($validator->errors());
            }
            $this->rememberValidationState([], $validator->errors());
            $this->flash()->error(__('auth.validation.check_fields'));
            return $this->redirect('/reset-password/' . $token);
        }

        $policyErrors = AuthInputGuard::passwordPolicyErrors($password);
        if ($policyErrors !== []) {
            if ($this->expectsJson()) {
                return $this->jsonValidationError($policyErrors);
            }
            $this->rememberValidationState([], $policyErrors);
            $this->flash()->error(__('auth.validation.check_fields'));
            return $this->redirect('/reset-password/' . $token);
        }

        if ($password !== $passwordConfirm) {
            if ($this->expectsJson()) {
                return $this->jsonValidationError([
                    'password_confirm' => [__('auth.validation.password_mismatch')],
                ]);
            }
            $this->rememberValidationState([], [
                'password_confirm' => [__('auth.validation.password_mismatch')],
            ]);
            $this->flash()->error(__('auth.validation.password_mismatch'));
            return $this->redirect('/reset-password/' . $token);
        }

        $userId = TokenRepository::getInstance()->consumePasswordResetToken($token);

        if ($userId === null) {
            return $this->postActionErrorRedirect('/forgot-password', __('auth.messages.reset_invalid'));
        }

        UserProvider::getInstance()->updatePassword($userId, $password);
        RememberMe::getInstance()->invalidate($userId);
        return $this->postActionSuccessRedirect('/login', __('auth.messages.password_reset_success'), null, 1200);
    }

    /**
     * Send the password-reset email.
     *
     * @param string $email
     * @param string $name
     * @param string $rawToken
     * @return void
     */
    private function sendResetEmail(string $email, string $name, string $rawToken): void
    {
        $appUrl = $this->resolveAppUrl();
        $link   = $appUrl . '/reset-password/' . $rawToken;

        $html = '<p>' . __('auth.email.reset_greeting', ['name' => e($name)]) . '</p>'
            . '<p><a href="' . e($link) . '">' . e($link) . '</a></p>'
            . '<p>' . __('auth.email.reset_expiry') . '</p>'
            . '<p>' . __('auth.email.reset_ignore') . '</p>';

        try {
            MailManager::getInstance()
                ->init()
                ->createMessage()
                ->to($email, $name)
                ->subject(__('auth.email.reset_subject'))
                ->html($html)
                ->send();
        } catch (Exception $e) {
            $this->logError('PasswordResetController: reset email failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Resolves the requested value.
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
