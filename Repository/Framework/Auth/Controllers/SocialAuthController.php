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

use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Framework\Auth\OAuthManager;
use Catalyst\Framework\Auth\UserProvider;
use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Exception;

/**************************************************************************************
 * SocialAuthController — OAuth2 social login via league/oauth2-client.
 *
 * Routes:
 *   GET /auth/social/{provider}          → redirectToProvider()
 *   GET /auth/social/callback/{provider} → callback()
 *
 * Supported providers: 'google', 'github'
 * Configure via env: GOOGLE_CLIENT_ID, GOOGLE_CLIENT_SECRET, etc.
 *
 * @package Catalyst\Repository\Auth\Controllers
 */
class SocialAuthController extends Controller
{
    /**
     * Redirect the user to the OAuth provider's authorization page.
     *
     * @param Request $request
     * @param string  $provider  'google' | 'github'
     * @return Response
     */
    public function redirectToProvider(Request $request, string $provider): Response
    {
        $oauth = OAuthManager::getInstance();

        if (!$oauth->isConfigured($provider)) {
            $this->flash()->error(__('auth.messages.social_not_configured'));
            return $this->redirect('/login');
        }

        try {
            $url = $oauth->getAuthorizationUrl($provider);
            return $this->redirect($url);
        } catch (Exception $e) {
            $this->logError('SocialAuthController::redirectToProvider failed', [
                'provider' => $provider,
                'error'    => $e->getMessage(),
            ]);
            $this->flash()->error(__('auth.messages.social_error'));
            return $this->redirect('/login');
        }
    }

    /**
     * Handle the OAuth provider callback, find or create the user, and log them in.
     *
     * @param Request $request
     * @param string  $provider
     * @return Response
     */
    public function callback(Request $request, string $provider): Response
    {
        $code  = (string)($request->input('code', ''));
        $state = (string)($request->input('state', ''));

        if ($code === '' || $state === '') {
            $this->flash()->error(__('auth.messages.social_cancelled'));
            return $this->redirect('/login');
        }

        try {
            $oauthUser = OAuthManager::getInstance()->handleCallback($provider, $code, $state);
        } catch (Exception $e) {
            $this->logError('SocialAuthController::callback failed', [
                'provider' => $provider,
                'error'    => $e->getMessage(),
            ]);
            $oauthUser = null;
        }

        if ($oauthUser === null) {
            $this->flash()->error(__('auth.messages.social_error'));
            return $this->redirect('/login');
        }

        $email = $oauthUser->getEmail();

        if ($email === null || $email === '') {
            $this->flash()->error(__('auth.messages.social_no_email'));
            return $this->redirect('/login');
        }

        $users      = UserProvider::getInstance();
        $providerId = $oauthUser->getId();

        // Check for existing social account link first
        $user = $users->findBySocialAccount($provider, $providerId);

        // Fallback: look up by email (social login verifies email ownership)
        if ($user === null) {
            $user = $users->findByEmailAny($email);

            if ($user !== null) {
                // Link social account to existing user
                $users->linkSocialAccount((int)$user['id'], $provider, $providerId);

                // Activate and verify email if the account was inactive/unverified
                if (!(bool)$user['active'] || !(bool)$user['email_verified']) {
                    $users->markEmailVerified((int)$user['id']);
                }

                // Re-fetch after potential updates
                $user = $users->findById((int)$user['id']);
            }
        }

        // Create a new user if none found
        if ($user === null) {
            $name   = $oauthUser->getName() ?? explode('@', $email)[0];
            $userId = $users->create($name, $email, '', 'user', true);
            $users->linkSocialAccount($userId, $provider, $providerId);
            $user = $users->findById($userId);
        }

        if ($user === null) {
            $this->flash()->error(__('auth.messages.social_error'));
            return $this->redirect('/login');
        }

        AuthManager::getInstance()->loginUser($user);
        return $this->redirect('/');
    }
}
