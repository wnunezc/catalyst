<?php

declare(strict_types=1);

/**************************************************************************************
 *
 * Catalyst PHP Framework
 * PHP Version 8.4 (Required).
 *
 * @package   Catalyst
 * @subpackage Framework\Auth
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
 * OAuthManager — OAuth2 social login coordinator.
 *
 */

namespace Catalyst\Framework\Auth;

use Catalyst\Framework\Auth\OAuth\GitHubProvider;
use Catalyst\Framework\Auth\OAuth\GoogleProvider;
use Catalyst\Framework\Auth\OAuth\OAuthUser;
use Catalyst\Framework\Session\SessionManager;
use Catalyst\Framework\Traits\SingletonTrait;
use Catalyst\Helpers\Config\ConfigManager;
use Catalyst\Helpers\Log\Logger;
use Exception;
use League\OAuth2\Client\Provider\AbstractProvider;

/**************************************************************************************
 * OAuthManager — manages social login via league/oauth2-client
 *
 * Supported providers: 'google', 'github'
 * Add new providers by registering them in buildProvider().
 *
 * Flow:
 *   1. getAuthorizationUrl($provider) — redirect user to the provider
 *   2. handleCallback($provider, $code, $state) — exchange code for user data
 *
 * State (CSRF) is stored in session key '_oauth_state_{provider}'.
 *
 * @package Catalyst\Framework\Auth
 */
class OAuthManager
{
    use SingletonTrait;

    private SessionManager $session;
    private Logger $logger;

    /** @var array<string, AbstractProvider> */
    private array $providers = [];

    /**
     * Constructor
     */
    protected function __construct()
    {
        $this->session = SessionManager::getInstance();
        $this->logger  = Logger::getInstance();
    }

    // -------------------------------------------------------------------------
    // Public API
    // -------------------------------------------------------------------------

    /**
     * Get the authorization URL for the given provider and store CSRF state.
     *
     * @param string $provider 'google' | 'github'
     * @return string  Redirect URL
     * @throws Exception If provider is unknown or misconfigured
     */
    public function getAuthorizationUrl(string $provider): string
    {
        $p   = $this->getProvider($provider);
        $url = $p->getAuthorizationUrl();

        // Store state in session for CSRF validation
        $this->session->set('_oauth_state_' . $provider, $p->getState());

        return $url;
    }

    /**
     * Handle the OAuth callback: validate state, exchange code, return OAuthUser.
     *
     * @param string $provider
     * @param string $code   The authorization code from query string
     * @param string $state  The state from query string
     * @return OAuthUser|null  Null on CSRF mismatch or provider error
     */
    public function handleCallback(string $provider, string $code, string $state): ?OAuthUser
    {
        $sessionState = $this->session->get('_oauth_state_' . $provider);
        $this->session->remove('_oauth_state_' . $provider);

        if ($sessionState === null || !hash_equals((string)$sessionState, $state)) {
            $this->logger->warning('OAuthManager: CSRF state mismatch', ['provider' => $provider]);
            return null;
        }

        try {
            $p     = $this->getProvider($provider);
            $token = $p->getAccessToken('authorization_code', ['code' => $code]);

            /** @var OAuthUser $owner */
            $owner = $p->getResourceOwner($token);

            return $owner;
        } catch (Exception $e) {
            $this->logger->error('OAuthManager: callback failed', [
                'provider' => $provider,
                'error'    => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Check whether a provider is configured (has client ID/secret in env).
     *
     * @param string $provider
     * @return bool
     */
    public function isConfigured(string $provider): bool
    {
        $env = defined('GET_ENV_VAR') && is_array(GET_ENV_VAR) ? GET_ENV_VAR : [];

        return match ($provider) {
            'google' => !empty($env['GOOGLE_CLIENT_ID']) && !empty($env['GOOGLE_CLIENT_SECRET']),
            'github' => !empty($env['GITHUB_CLIENT_ID']) && !empty($env['GITHUB_CLIENT_SECRET']),
            default  => false,
        };
    }

    // -------------------------------------------------------------------------
    // Provider factory
    // -------------------------------------------------------------------------

    /**
     * Get (or build and cache) a provider instance.
     *
     * @param string $provider
     * @return AbstractProvider
     * @throws Exception
     */
    private function getProvider(string $provider): AbstractProvider
    {
        if (isset($this->providers[$provider])) {
            return $this->providers[$provider];
        }

        $this->providers[$provider] = $this->buildProvider($provider);
        return $this->providers[$provider];
    }

    /**
     * Build a provider from env configuration.
     *
     * @param string $provider
     * @return AbstractProvider
     * @throws Exception
     */
    private function buildProvider(string $provider): AbstractProvider
    {
        $env = defined('GET_ENV_VAR') && is_array(GET_ENV_VAR) ? GET_ENV_VAR : [];

        return match ($provider) {
            'google' => new GoogleProvider([
                'clientId'     => $env['GOOGLE_CLIENT_ID']     ?? '',
                'clientSecret' => $env['GOOGLE_CLIENT_SECRET'] ?? '',
                'redirectUri'  => $env['GOOGLE_REDIRECT_URI']  ?? $this->buildRedirectUri('google'),
            ]),
            'github' => new GitHubProvider([
                'clientId'     => $env['GITHUB_CLIENT_ID']     ?? '',
                'clientSecret' => $env['GITHUB_CLIENT_SECRET'] ?? '',
                'redirectUri'  => $env['GITHUB_REDIRECT_URI']  ?? $this->buildRedirectUri('github'),
            ]),
            default => throw new Exception("OAuthManager: unknown provider '{$provider}'"),
        };
    }

    /**
     * Build the default callback URI for a provider based on APP_URL env var.
     *
     * @param string $provider
     * @return string
     */
    private function buildRedirectUri(string $provider): string
    {
        try {
            $configManager = $GLOBALS['APP_CONFIGURATION'] ?? ConfigManager::getInstance();

            if ($configManager instanceof ConfigManager) {
                $app    = $configManager->entry('app', 'project');
                $appUrl = rtrim((string)($app['project_url'] ?? ''), '/');
                return $appUrl . '/auth/social/callback/' . $provider;
            }
        } catch (\Throwable) {
        }

        $env    = defined('GET_ENV_VAR') && is_array(GET_ENV_VAR) ? GET_ENV_VAR : [];
        $appUrl = rtrim($env['APP_URL'] ?? '', '/');
        return $appUrl . '/auth/social/callback/' . $provider;
    }
}
