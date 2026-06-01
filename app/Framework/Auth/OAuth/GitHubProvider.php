<?php

declare(strict_types=1);

/**************************************************************************************
 *
 * Catalyst PHP Framework
 * PHP Version 8.4 (Required).
 *
 * @package   Catalyst
 * @subpackage Framework\Auth\OAuth
 * @see       https://github.com/arcanisgk/catalyst
 *
 * @author    Walter Nuñez (arcanisgk/original founder) <icarosnet@gmail.com>
 * @copyright 2023 - 2025
 * @license   http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 *
 * @category  Framework
 * @filesource
 *
 * @link      https://catalyst.dock Local development URL
 *
 * GitHubProvider — OAuth2 provider for GitHub login.
 *
 */

namespace Catalyst\Framework\Auth\OAuth;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;

/**************************************************************************************
 * GitHubProvider — GitHub OAuth2 integration via league/oauth2-client
 *
 * Extends AbstractProvider with GitHub-specific endpoints.
 * GitHub may return a null email if the user has it set to private;
 * the OAuthManager handles that case by fetching from /user/emails.
 *
 * Required env vars (in .env):
 *   GITHUB_CLIENT_ID
 *   GITHUB_CLIENT_SECRET
 *   GITHUB_REDIRECT_URI
 *
 * @package Catalyst\Framework\Auth\OAuth
 */
class GitHubProvider extends AbstractProvider
{
    /**
     * @inheritDoc
     */
    public function getBaseAuthorizationUrl(): string
    {
        return 'https://github.com/login/oauth/authorize';
    }

    /**
     * @inheritDoc
     */
    public function getBaseAccessTokenUrl(array $params): string
    {
        return 'https://github.com/login/oauth/access_token';
    }

    /**
     * @inheritDoc
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return 'https://api.github.com/user';
    }

    /**
     * @inheritDoc
     */
    protected function getDefaultScopes(): array
    {
        return ['user:email'];
    }

    /**
     * @inheritDoc
     */
    protected function getScopeSeparator(): string
    {
        return ' ';
    }

    /**
     * @inheritDoc
     */
    protected function getDefaultHeaders(): array
    {
        return ['Accept' => 'application/vnd.github+json'];
    }

    /**
     * @inheritDoc
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
     * @inheritDoc
     */
    protected function createResourceOwner(array $response, AccessToken $token): OAuthUser
    {
        return new OAuthUser($response, 'github');
    }
}
