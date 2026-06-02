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

namespace Catalyst\Framework\Auth\OAuth;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;

/**
 * Implements the Google OAuth2 provider contract.
 *
 * @package Catalyst\Framework\Auth\OAuth
 * Responsibility: Supply Google endpoints, scopes, error handling and normalized OAuth users.
 */
class GoogleProvider extends AbstractProvider
{
    /**
     * Returns the Google authorization endpoint.
     *
     * Responsibility: Returns the Google authorization endpoint.
     */
    public function getBaseAuthorizationUrl(): string
    {
        return 'https://accounts.google.com/o/oauth2/v2/auth';
    }

    /**
     * Returns the Google token exchange endpoint.
     *
     * Responsibility: Returns the Google token exchange endpoint.
     */
    public function getBaseAccessTokenUrl(array $params): string
    {
        return 'https://oauth2.googleapis.com/token';
    }

    /**
     * Returns the Google user-info endpoint.
     *
     * Responsibility: Returns the Google user-info endpoint.
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return 'https://www.googleapis.com/oauth2/v3/userinfo';
    }

    /**
     * Provides the default Google scopes required for identity and email.
     *
     * Responsibility: Provides the default Google scopes required for identity and email.
     */
    protected function getDefaultScopes(): array
    {
        return ['openid', 'email', 'profile'];
    }

    /**
     * Uses the Google-required space separator for OAuth scopes.
     *
     * Responsibility: Uses the Google-required space separator for OAuth scopes.
     */
    protected function getScopeSeparator(): string
    {
        return ' ';
    }

    /**
     * Converts Google error payloads into identity-provider exceptions.
     *
     * Responsibility: Converts Google error payloads into identity-provider exceptions.
     */
    protected function checkResponse(ResponseInterface $response, $data): void
    {
        if (isset($data['error'])) {
            $message = $data['error']['message'] ?? ($data['error'] ?? 'Unknown Google OAuth error');
            $code    = $data['error']['code']    ?? $response->getStatusCode();
            throw new IdentityProviderException((string)$message, (int)$code, $data);
        }
    }

    /**
     * Wraps the Google resource-owner response in the framework OAuth user type.
     *
     * Responsibility: Wraps the Google resource-owner response in the framework OAuth user type.
     */
    protected function createResourceOwner(array $response, AccessToken $token): OAuthUser
    {
        return new OAuthUser($response, 'google');
    }
}
