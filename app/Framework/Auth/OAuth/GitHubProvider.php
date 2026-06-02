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
 * Implements the GitHub OAuth2 provider contract.
 *
 * @package Catalyst\Framework\Auth\OAuth
 * Responsibility: Supply GitHub endpoints, scopes, headers, error handling and normalized OAuth users.
 */
class GitHubProvider extends AbstractProvider
{
    /**
     * Returns the GitHub authorization endpoint.
     *
     * Responsibility: Returns the GitHub authorization endpoint.
     */
    public function getBaseAuthorizationUrl(): string
    {
        return 'https://github.com/login/oauth/authorize';
    }

    /**
     * Returns the GitHub token exchange endpoint.
     *
     * Responsibility: Returns the GitHub token exchange endpoint.
     */
    public function getBaseAccessTokenUrl(array $params): string
    {
        return 'https://github.com/login/oauth/access_token';
    }

    /**
     * Returns the GitHub user profile endpoint.
     *
     * Responsibility: Returns the GitHub user profile endpoint.
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return 'https://api.github.com/user';
    }

    /**
     * Provides the default GitHub scopes required for user email access.
     *
     * Responsibility: Provides the default GitHub scopes required for user email access.
     */
    protected function getDefaultScopes(): array
    {
        return ['user:email'];
    }

    /**
     * Uses the GitHub-supported space separator for OAuth scopes.
     *
     * Responsibility: Uses the GitHub-supported space separator for OAuth scopes.
     */
    protected function getScopeSeparator(): string
    {
        return ' ';
    }

    /**
     * Provides default GitHub API headers for resource-owner requests.
     *
     * Responsibility: Provides default GitHub API headers for resource-owner requests.
     */
    protected function getDefaultHeaders(): array
    {
        return ['Accept' => 'application/vnd.github+json'];
    }

    /**
     * Converts GitHub OAuth and API errors into identity-provider exceptions.
     *
     * Responsibility: Converts GitHub OAuth and API errors into identity-provider exceptions.
     */
    protected function checkResponse(ResponseInterface $response, $data): void
    {
        if (isset($data['error'])) {
            throw new IdentityProviderException(
                $data['error_description'] ?? $data['error'],
                $response->getStatusCode(),
                $data
            );
        }

        if ($response->getStatusCode() >= 400) {
            throw new IdentityProviderException(
                $data['message'] ?? 'GitHub API error',
                $response->getStatusCode(),
                $data
            );
        }
    }

    /**
     * Wraps the GitHub resource-owner response in the framework OAuth user type.
     *
     * Responsibility: Wraps the GitHub resource-owner response in the framework OAuth user type.
     */
    protected function createResourceOwner(array $response, AccessToken $token): OAuthUser
    {
        return new OAuthUser($response, 'github');
    }
}
