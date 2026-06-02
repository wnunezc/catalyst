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

namespace Catalyst\Framework\Middleware;

use Catalyst\Framework\Http\RedirectResponse;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Closure;

/**************************************************************************************
 * SetupMiddleware — first-run configuration guard (global scope).
 *
 * Intercepts every request before routing. If ConfigManager::isConfigured()
 * returns false the user is redirected to /configuration/environment-setup so they can complete the
 * initial framework setup.
 *
 * SRP: this middleware decides *where* unconfigured requests go. Access
 * control for /configuration/environment-setup itself (open when unconfigured, admin-only once
 * configured) belongs to SetupGuardMiddleware.
 *
 * Bypass paths (always allowed regardless of configuration state):
 *   - /configuration/environment-setup/* — the setup panel itself
 *   - /login   and  /logout     — auth routes needed during setup
 *   - /assets/*                 — static public assets
 *
 * Registration belongs to GlobalMiddlewareRegistrar so cached and cold
 * bootstraps receive the same pipeline.
 *
 * @package Catalyst\Framework\Middleware
 **************************************************************************************/
/**
 * Defines the Setup Middleware class contract.
 *
 * @package Catalyst\Framework\Middleware
 * Responsibility: Coordinates the setup middleware behavior within its module boundary.
 */
class SetupMiddleware extends CoreMiddleware
{
    use SetupAccessTrait;

    /**
     * @inheritDoc
     */
    public function process(Request $request, Closure $next): Response
    {
        if ($this->isFrameworkConfigured()) {
            return $this->passToNext($request, $next);
        }

        $uri = $this->normalizeSetupUri($request->getUri());
        if ($this->isSetupBypassUri($uri)) {
            return $this->passToNext($request, $next);
        }

        $this->log('Setup: app not configured, redirecting to /configuration/environment-setup', ['uri' => $uri]);

        return new RedirectResponse('/configuration/environment-setup');
    }
}
