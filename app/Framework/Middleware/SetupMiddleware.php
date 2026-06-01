<?php

declare(strict_types=1);

/**************************************************************************************
 *
 * Catalyst PHP Framework
 * PHP Version 8.4 (Required).
 *
 * @package   Catalyst
 * @subpackage Framework\Middleware
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
 * SetupMiddleware — redirects to /setup when the framework is not configured.
 *
 **************************************************************************************/

namespace Catalyst\Framework\Middleware;

use Catalyst\Framework\Http\RedirectResponse;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Closure;

/**************************************************************************************
 * SetupMiddleware — first-run configuration guard (global scope).
 *
 * Intercepts every request before routing. If ConfigManager::isConfigured()
 * returns false the user is redirected to /setup so they can complete the
 * initial framework setup.
 *
 * SRP: this middleware decides *where* unconfigured requests go. Access
 * control for /setup itself (open when unconfigured, admin-only once
 * configured) belongs to SetupGuardMiddleware.
 *
 * Bypass paths (always allowed regardless of configuration state):
 *   - /setup   and  /setup/*    — the setup panel itself
 *   - /login   and  /logout     — auth routes needed during setup
 *   - /assets/*                 — static public assets
 *
 * Registration in global-routes.php:
 *   $router->addMiddleware(SetupMiddleware::class);
 *
 * @package Catalyst\Framework\Middleware
 **************************************************************************************/
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
