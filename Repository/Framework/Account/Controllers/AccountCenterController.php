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

namespace Catalyst\Repository\Account\Controllers;

use Catalyst\Repository\Account\Requests\MfaRecoveryRequest;
use Catalyst\Repository\Account\Requests\SupportRecoveryRequest;
use Catalyst\Repository\Account\Services\AccountAvatarService;
use Catalyst\Repository\Account\Services\AccountRecoveryService;
use Catalyst\Repository\Account\Services\AccountSecurityService;
use Catalyst\Repository\Account\Support\AccountSurfaceViewModel;
use App\Repositories\UserProfileRepository;
use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\RedirectResponse;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;

/**
 * Serves authenticated account center pages and account recovery entry points.
 *
 * @package Catalyst\Repository\Account\Controllers
 * Responsibility: Renders account profile, MFA, recovery support and activity screens for signed-in users.
 */
final class AccountCenterController extends Controller
{
    /**
     * Renders the current user's profile summary.
     *
     * Responsibility: Renders the current user's profile summary.
     */
    public function profile(): Response
    {
        $user = AuthManager::getInstance()->user() ?? [];
        $profile = (new UserProfileRepository())->findByUserId((int) ($user['id'] ?? 0));
        $avatarPath = $profile !== null ? (string) ($profile->avatar_path ?? '') : '';
        $avatar = new AccountAvatarService();

        return $this->render('profile', __('account.profile.title'), [
            'account_user' => $user,
            'account_avatar_src' => $avatar->url($avatarPath),
            'auth_avatar_src' => $avatar->url($avatarPath),
        ]);
    }

    public function updateAvatar(Request $request): Response
    {
        $user = AuthManager::getInstance()->user() ?? [];
        $userId = (int) ($user['id'] ?? 0);

        if ($userId <= 0) {
            return $this->postActionErrorRedirect('/account/profile', __('messages.request_not_authorized'), 403);
        }

        $profile = (new UserProfileRepository())->findByUserId($userId);
        $oldPath = $profile !== null ? (string) ($profile->avatar_path ?? '') : '';

        try {
            (new AccountAvatarService())->update($userId, $request->file('avatar'), $oldPath);
        } catch (\RuntimeException $exception) {
            return $this->postActionErrorRedirect('/account/profile', $exception->getMessage(), 422);
        }

        return $this->postActionSuccessRedirect('/account/profile', __('account.messages.avatar_updated'));
    }

    /**
     * Redirects the legacy account security entry point to the MFA management screen.
     *
     * Responsibility: Redirects the legacy account security entry point to the MFA management screen.
     */
    public function security(): RedirectResponse
    {
        return $this->redirect('/account/security/mfa', 302);
    }

    /**
     * Renders the MFA status and management entry screen.
     *
     * Responsibility: Renders the MFA status and management entry screen.
     */
    public function mfa(): Response
    {
        return $this->render('mfa', __('account.mfa.title'), [
            'account_security' => (new AccountSecurityService())->overview(),
        ]);
    }

    /**
     * Renders the authenticated MFA recovery request form.
     *
     * Responsibility: Renders the authenticated MFA recovery request form.
     */
    public function mfaRecovery(): Response
    {
        return $this->render('mfa-recovery', __('account.mfa_recovery.title'), [
            'account_user' => AuthManager::getInstance()->user() ?? [],
        ]);
    }

    /**
     * Validates the authenticated MFA recovery request and sends a reset email when accepted.
     *
     * Responsibility: Validates the authenticated MFA recovery request and sends a reset email when accepted.
     */
    public function requestMfaRecovery(Request $request): Response
    {
        $validated = (new MfaRecoveryRequest())->validate($request);
        if ($validated['errors'] !== []) {
            $this->rememberValidationState($validated['data'], $validated['errors']);
            $this->flash()->error(__('account.messages.validation_failed'));

            return $this->redirect('/account/recovery/mfa');
        }

        (new AccountRecoveryService())->requestMfaResetByEmail($validated['data']['email']);
        $this->flash()->success(__('account.messages.mfa_recovery_sent'));

        return $this->redirect('/account/recovery/mfa');
    }

    /**
     * Redirects the account recovery landing route to the support flow.
     *
     * Responsibility: Redirects the account recovery landing route to the support flow.
     */
    public function recovery(): RedirectResponse
    {
        return $this->redirect('/account/recovery/support', 302);
    }

    /**
     * Renders the authenticated support recovery request form.
     *
     * Responsibility: Renders the authenticated support recovery request form.
     */
    public function support(): Response
    {
        return $this->render('support', __('account.support.title'), [
            'account_user' => AuthManager::getInstance()->user() ?? [],
            'forced_request_type' => '',
            'is_compromised_flow' => false,
            'support_form_action' => '/account/recovery/support',
        ]);
    }

    /**
     * Submits a general authenticated support recovery request.
     *
     * Responsibility: Submits a general authenticated support recovery request.
     */
    public function submitSupport(Request $request): Response
    {
        return $this->handleSupport($request, null, '/account/recovery/support');
    }

    /**
     * Renders the authenticated compromised-account recovery form.
     *
     * Responsibility: Renders the authenticated compromised-account recovery form.
     */
    public function compromised(): Response
    {
        return $this->render('support', __('account.support.compromised_title'), [
            'account_user' => AuthManager::getInstance()->user() ?? [],
            'forced_request_type' => 'compromised_account',
            'is_compromised_flow' => true,
            'support_form_action' => '/account/recovery/compromised',
        ]);
    }

    /**
     * Submits an authenticated compromised-account recovery request.
     *
     * Responsibility: Submits an authenticated compromised-account recovery request.
     */
    public function submitCompromised(Request $request): Response
    {
        return $this->handleSupport($request, 'compromised_account', '/account/recovery/compromised');
    }

    /**
     * Renders the account recovery and security activity timeline.
     *
     * Responsibility: Renders the account recovery and security activity timeline.
     */
    public function activity(): Response
    {
        return $this->render('activity', __('account.activity.title'), [
            'account_activity' => (new AccountSecurityService())->activity(),
        ]);
    }

    /**
     * Validates and stores an authenticated support recovery request.
     *
     * Responsibility: Validates and stores an authenticated support recovery request.
     */
    private function handleSupport(Request $request, ?string $forcedType, string $fallback): Response
    {
        $validated = (new SupportRecoveryRequest())->validate($request, $forcedType);
        if ($validated['errors'] !== []) {
            $this->rememberValidationState($validated['data'], $validated['errors']);
            $this->flash()->error(__('account.messages.validation_failed'));

            return $this->redirect($fallback);
        }

        (new AccountRecoveryService())->submitSupportRequest($validated['data']);
        $this->flash()->success(__('account.messages.support_request_created'));

        return $this->redirect($fallback);
    }

    /**
     * Renders an authenticated account view with the shared account shell scope.
     *
     * Responsibility: Renders an authenticated account view with the shared account shell scope.
     * @param array<string, mixed> $data
     */
    private function render(string $view, string $title, array $data = []): Response
    {
        $shell = new AccountSurfaceViewModel();
        $translationKey = str_replace('-', '_', $view);

        return $this->view('account.' . $view, $shell->authenticated(array_merge([
            'title' => $title,
            'pageTitle' => $title,
            'page_header' => [
                'eyebrow' => __("account.{$translationKey}.eyebrow"),
                'title' => $title,
                'description' => __("account.{$translationKey}.lead"),
            ],
            'breadcrumb_items' => [
                ['label' => __('account.nav.account'), 'href' => '/dashboard'],
                ['label' => $title, 'href' => '#', 'is_active' => true],
            ],
            'has_breadcrumbs' => true,
        ], $data)));
    }
}
