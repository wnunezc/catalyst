<?php

declare(strict_types=1);

/**************************************************************************************
 *
 * Catalyst PHP Framework
 * PHP Version 8.4 (Required).
 *
 * @package   Catalyst\Repository\Auth\Controllers
 * @see       https://github.com/arcanisgk/catalyst
 *
 * @author    Walter Nuñez (arcanisgk/original founder) <icarosnet@gmail.com>
 * @copyright 2023 - 2025
 * @license   http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 *
 * @category  Repository
 * @filesource
 *
 * @link      https://catalyst.dock Local development URL
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
class EmailVerificationController extends Controller
{
    public function showManualForm(): Response
    {
        return $this->view('auth.verify-email', [
            'title' => __('auth.verify.title'),
        ], 200, 'auth');
    }

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
