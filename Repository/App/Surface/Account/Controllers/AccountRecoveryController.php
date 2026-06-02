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

namespace App\Surface\Account\Controllers;

use App\Surface\Account\Requests\MfaRecoveryRequest;
use App\Surface\Account\Requests\SupportRecoveryRequest;
use App\Surface\Account\Services\AccountRecoveryService;
use App\Surface\Account\Support\AccountShellViewModel;
use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;

/**
 * Defines the Account Recovery Controller class contract.
 *
 * @package App\Surface\Account\Controllers
 * Responsibility: Coordinates the account recovery controller behavior within its module boundary.
 */
final class AccountRecoveryController extends Controller
{
    /**
     * Handles the start workflow.
     */
    public function start(): Response
    {
        return $this->guest('recovery-start', __('account.recovery_public.start_title'));
    }

    /**
     * Handles the detail display workflow.
     */
    public function showMfaRequest(): Response
    {
        return $this->guest('mfa-request', __('account.recovery_public.mfa_title'));
    }

    /**
     * Handles the request mfa reset workflow.
     */
    public function requestMfaReset(Request $request): Response
    {
        $validated = (new MfaRecoveryRequest())->validate($request);
        if ($validated['errors'] !== []) {
            $this->rememberValidationState($validated['data'], $validated['errors']);
            $this->flash()->error(__('account.messages.validation_failed'));
            return $this->redirect('/account-recovery/mfa');
        }

        (new AccountRecoveryService())->requestMfaResetByEmail($validated['data']['email']);
        $this->flash()->success(__('account.messages.mfa_recovery_sent'));

        return $this->redirect('/login');
    }

    /**
     * Handles the consume mfa reset workflow.
     */
    public function consumeMfaReset(string $token): Response
    {
        $ok = (new AccountRecoveryService())->consumeMfaResetToken($token);
        $ok
            ? $this->flash()->success(__('account.messages.mfa_recovery_success'))
            : $this->flash()->error(__('account.messages.recovery_token_invalid'));

        return $this->redirect('/login');
    }

    /**
     * Handles the detail display workflow.
     */
    public function showSupportRequest(): Response
    {
        return $this->guest('support-request', __('account.recovery_public.support_title'), [
            'forced_request_type' => '',
            'is_compromised_flow' => false,
            'support_form_action' => '/account-recovery/support',
        ]);
    }

    /**
     * Handles the submit support request workflow.
     */
    public function submitSupportRequest(Request $request): Response
    {
        return $this->handleSupport($request, null, '/account-recovery/support');
    }

    /**
     * Handles the detail display workflow.
     */
    public function showCompromisedRequest(): Response
    {
        return $this->guest('support-request', __('account.recovery_public.compromised_title'), [
            'forced_request_type' => 'compromised_account',
            'is_compromised_flow' => true,
            'support_form_action' => '/account-recovery/compromised',
        ]);
    }

    /**
     * Handles the submit compromised request workflow.
     */
    public function submitCompromisedRequest(Request $request): Response
    {
        return $this->handleSupport($request, 'compromised_account', '/account-recovery/compromised');
    }

    /**
     * Handles the request workflow.
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

        return $this->redirect('/login');
    }

    /** @param array<string, mixed> $data */
    private function guest(string $view, string $title, array $data = []): Response
    {
        $shell = new AccountShellViewModel();

        return $this->view('account.' . $view, $shell->guest(array_merge([
            'title' => $title,
            'pageTitle' => $title,
        ], $data)), 200, 'account');
    }
}
