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

use Catalyst\Framework\Auth\TokenRepository;
use Catalyst\Framework\Auth\UserProvider;
use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Catalyst\Repository\Auth\Requests\EmailVerificationTokenRequest;

/**************************************************************************************
 * EmailVerificationController — activates an account via a one-time token link.
 *
 * Routes:
 *   GET  /verify-email          → showManualForm()
 *   POST /verify-email          → manualVerify()
 *   GET  /verify-email/{token}  → verify()
 *
 * @package Catalyst\Repository\Auth\Controllers
 */
/**
 * Defines the Email Verification Controller class contract.
 *
 * @package Catalyst\Repository\Auth\Controllers
 * Responsibility: Coordinates the email verification controller behavior within its module boundary.
 */
class EmailVerificationController extends Controller
{
    /**
     * Handles the detail display workflow.
     */
    public function showManualForm(): Response
    {
        return $this->view('auth.verify-email', [
            'title' => __('auth.verify.title'),
        ], 200, 'auth');
    }

    /**
     * Handles the manual verify workflow.
     */
    public function manualVerify(Request $request): Response
    {
        $payload = (new EmailVerificationTokenRequest($request))->validated();

        return $this->consumeToken((string) ($payload['token'] ?? ''));
    }

    /**
     * Consume the verification token and activate the account.
     *
     * @param Request $request
     * @param string  $token  Raw token from URL parameter
     * @return Response
     */
    public function verify(Request $request, string $token): Response
    {
        $token = trim($token);

        if (!EmailVerificationTokenRequest::isWellFormedToken($token)) {
            $this->flash()->error(__('auth.messages.verify_invalid'));
            return $this->redirect('/login');
        }

        return $this->consumeToken($token);
    }

    /**
     * Handles the consume token workflow.
     */
    private function consumeToken(string $token): Response
    {
        $userId = TokenRepository::getInstance()->consumeVerificationToken($token);

        if ($userId === null) {
            $this->flash()->error(__('auth.messages.verify_invalid'));
            return $this->redirect('/login');
        }

        UserProvider::getInstance()->markEmailVerified($userId);

        $this->toast('success', __('auth.messages.verify_success'));
        return $this->redirect('/login');
    }
}
