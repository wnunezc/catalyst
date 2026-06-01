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
 * GoogleProvider — OAuth2 provider for Google Sign-In.
 *
 */

namespace Catalyst\Framework\Auth\OAuth;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;

/**************************************************************************************
 * GoogleProvider — Google OAuth2 integration via league/oauth2-client
 *
 * Extends AbstractProvider to add Google-specific endpoints and scope.
 * Returns OAuthUser as the resource owner.
 *
 * Required env vars (in .env):
 *   GOOGLE_CLIENT_ID
 *   GOOGLE_CLIENT_SECRET
 *   GOOGLE_REDIRECT_URI
 *
 * @package Catalyst\Framework\Auth\OAuth
 */
class GoogleProvider extends AbstractProvider
{
    /**
     * @inheritDoc
     */
    public function getBaseAuthorizationUrl(): string
    {
        return 'https://accounts.google.com/o/oauth2/v2/auth';
    }

    /**
     * @inheritDoc
     */
    public function getBaseAccessTokenUrl(array $params): string
    {
        return 'https://oauth2.googleapis.com/token';
    }

    /**
     * @inheritDoc
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return 'https://www.googleapis.com/oauth2/v3/userinfo';
    }

    /**
     * @inheritDoc
     */
    protected function getDefaultScopes(): array
    {
        return ['openid', 'email', 'profile'];
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
    protected function checkResponse(ResponseInterface $response, $data): void
    {
        if (isset($data['error'])) {
            $message = $data['error']['message'] ?? ($data['error'] ?? 'Unknown Google OAuth error');
            $code    = $data['error']['code']    ?? $response->getStatusCode();
            throw new IdentityProviderException((string)$message, (int)$code, $data);
        }
    }

    /**
     * @inheritDoc
     */
    protected function createResourceOwner(array $response, AccessToken $token): OAuthUser
    {
        return new OAuthUser($response, 'google');
    }
}
