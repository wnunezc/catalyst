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
 * SetupGuardMiddleware — dual-state access control for /setup routes.
 *
 **************************************************************************************/

namespace Catalyst\Framework\Middleware;

use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Framework\Authorization\RoleRepository;
use Catalyst\Framework\Http\ErrorResponseFactory;
use Catalyst\Framework\Http\RedirectResponse;
use Catalyst\Framework\Http\RedirectTarget;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Closure;

/**************************************************************************************
 * SetupGuardMiddleware — dual-state access control for /setup routes.
 *
 * SRP: this middleware is attached only to /setup routes (Settings module).
 * The complementary SetupMiddleware runs globally and decides where
 * unconfigured requests go; this one decides who may enter /setup itself.
 *
 * When the framework is NOT yet configured (first run):
 *   → Pass through without authentication. The setup wizard must be reachable
 *     before any database or session infrastructure exists.
 *
 * When the framework IS configured:
 *   → Require an authenticated admin session (same as AuthMiddleware + RoleMiddleware).
 *   → Unauthenticated HTML request → /login?redirect=/setup.
 *   → Unauthenticated AJAX / JSON → 401 JSON.
 *   → Authenticated but non-admin → 403 JSON / 403 HTML.
 *
 * @package Catalyst\Framework\Middleware
 **************************************************************************************/
class SetupGuardMiddleware extends CoreMiddleware
{
    use SetupAccessTrait;

    /**
     * @inheritDoc
     */
    public function process(Request $request, Closure $next): Response
    {
        // -- Not yet configured: open access ----------------------------------
        if (!$this->isFrameworkConfigured()) {
            return $this->passToNext($request, $next);
        }

        // -- Configured: require authenticated admin ---------------------------
        $auth = AuthManager::getInstance();

        if (!$auth->check() && !$auth->loginFromRemember()) {
            $this->log('SetupGuard: unauthenticated request blocked', ['uri' => $request->getUri()]);

            if ($this->expectsJson($request)) {
                return $this->setupJsonError('Login required.', 401);
            }

            return new RedirectResponse(RedirectTarget::loginUrl('/configuration/environment-setup'));
        }

        $userId = $auth->id();
        $repo   = RoleRepository::getInstance();

        if ($userId === null || !$repo->userHasAnyRole($userId, ['admin'])) {
            $this->log('SetupGuard: non-admin access blocked', ['uri' => $request->getUri()]);

            if ($this->expectsJson($request)) {
                return $this->setupJsonError('Admin access required.', 403);
            }

            return ErrorResponseFactory::forbidden(__('ui.errors.403_message'));
        }

        return $this->passToNext($request, $next);
    }
}
