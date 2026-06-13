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

use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Framework\Authorization\RoleRepository;
use Catalyst\Framework\Http\ErrorResponseFactory;
use Catalyst\Framework\Http\RedirectResponse;
use Catalyst\Framework\Http\RedirectTarget;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Closure;

/**
 * SetupGuardMiddleware — dual-state access control for environment setup routes.
 *
 * SRP: this middleware is attached only to environment setup routes (Configuration module).
 * The complementary SetupMiddleware runs globally and decides where
 * unconfigured requests go; this one decides who may enter setup itself.
 *
 * When the framework is NOT yet configured (first run):
 *   → Pass through without authentication. The setup wizard must be reachable
 *     before any database or session infrastructure exists.
 *
 * When the framework IS configured:
 *   → Require an authenticated privileged session (same as AuthMiddleware + RoleMiddleware).
 *   → Unauthenticated HTML request → /login?redirect=/configuration/environment-setup.
 *   → Unauthenticated AJAX / JSON → 401 JSON.
 *   → Authenticated without the required role → 403 JSON / 403 HTML.
 *
 * @package Catalyst\Framework\Middleware
 * Responsibility: Keeps first-run setup reachable while requiring an authenticated privileged role after configuration.
 */
class SetupGuardMiddleware extends CoreMiddleware
{
    use SetupAccessTrait;

    /**
     * Allows first-run setup or requires the configured privileged role after configuration.
     *
     * Responsibility: Allows first-run setup or requires the configured privileged role after configuration.
     */
    public function process(Request $request, Closure $next): Response
    {
        // -- Not yet configured: open access ----------------------------------
        if (!$this->isFrameworkConfigured()) {
            return $this->passToNext($request, $next);
        }

        // -- Configured: require the privileged role ---------------------------
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
            $this->log('SetupGuard: required-role access blocked', ['uri' => $request->getUri()]);

            if ($this->expectsJson($request)) {
                return $this->setupJsonError('Required role access denied.', 403);
            }

            return ErrorResponseFactory::forbidden(__('ui.errors.403_message'));
        }

        return $this->passToNext($request, $next);
    }
}
