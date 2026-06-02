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

/**
 * Orchestrates OAuth authorization-code login for configured providers.
 *
 * @package Catalyst\Framework\Auth
 * Responsibility: Create provider redirects, validate callback state and normalize provider users.
 */
class OAuthManager
{
    use SingletonTrait;

    private SessionManager $session;
    private Logger $logger;

    /** @var array<string, AbstractProvider> */
    private array $providers = [];

    /**
     * Initializes session and logging collaborators for OAuth flows.
     *
     * Responsibility: Initializes session and logging collaborators for OAuth flows.
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
     * Responsibility: Builds the provider authorization URL while storing OAuth CSRF state in the session.
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
     * Responsibility: Handle the OAuth callback: validate state, exchange code, return OAuthUser.
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
     * Responsibility: Check whether a provider is configured (has client ID/secret in env).
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
     * Responsibility: Get (or build and cache) a provider instance.
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
     * Responsibility: Build a provider from env configuration.
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
     * Responsibility: Build the default callback URI for a provider based on APP_URL env var.
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
