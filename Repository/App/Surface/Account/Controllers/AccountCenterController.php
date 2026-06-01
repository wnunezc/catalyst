<?php

declare(strict_types=1);

namespace App\Surface\Account\Controllers;

use App\Surface\Account\Requests\MfaRecoveryRequest;
use App\Surface\Account\Requests\SupportRecoveryRequest;
use App\Surface\Account\Services\AccountRecoveryService;
use App\Surface\Account\Services\AccountSecurityService;
use App\Surface\Account\Support\AccountShellViewModel;
use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\RedirectResponse;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;

final class AccountCenterController extends Controller
{
    public function profile(): Response
    {
        return $this->render('profile', __('account.profile.title'), [
            'account_user' => AuthManager::getInstance()->user() ?? [],
        ]);
    }

    public function security(): RedirectResponse
    {
        return $this->redirect('/account/security/mfa', 302);
    }

    public function mfa(): Response
    {
        return $this->render('mfa', __('account.mfa.title'), [
            'account_security' => (new AccountSecurityService())->overview(),
        ]);
    }

    public function mfaRecovery(): Response
    {
        return $this->render('mfa-recovery', __('account.mfa_recovery.title'), [
            'account_user' => AuthManager::getInstance()->user() ?? [],
        ]);
    }

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

    public function recovery(): RedirectResponse
    {
        return $this->redirect('/account/recovery/support', 302);
    }

    public function support(): Response
    {
        return $this->render('support', __('account.support.title'), [
            'account_user' => AuthManager::getInstance()->user() ?? [],
            'forced_request_type' => '',
            'is_compromised_flow' => false,
            'support_form_action' => '/account/recovery/support',
        ]);
    }

    public function submitSupport(Request $request): Response
    {
        return $this->handleSupport($request, null, '/account/recovery/support');
    }

    public function compromised(): Response
    {
        return $this->render('support', __('account.support.compromised_title'), [
            'account_user' => AuthManager::getInstance()->user() ?? [],
            'forced_request_type' => 'compromised_account',
            'is_compromised_flow' => true,
            'support_form_action' => '/account/recovery/compromised',
        ]);
    }

    public function submitCompromised(Request $request): Response
    {
        return $this->handleSupport($request, 'compromised_account', '/account/recovery/compromised');
    }

    public function activity(): Response
    {
        return $this->render('activity', __('account.activity.title'), [
            'account_activity' => (new AccountSecurityService())->activity(),
        ]);
    }

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

    /** @param array<string, mixed> $data */
    private function render(string $view, string $title, array $data = []): Response
    {
        $shell = new AccountShellViewModel();

        return $this->view('account.' . $view, $shell->authenticated(array_merge([
            'title' => $title,
            'pageTitle' => $title,
            'breadcrumb_items' => [
                ['label' => __('account.nav.account'), 'href' => '/dashboard'],
                ['label' => $title, 'href' => '#', 'is_active' => true],
            ],
            'has_breadcrumbs' => true,
        ], $data)), 200, 'account');
    }
}
