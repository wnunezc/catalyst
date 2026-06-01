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
 * @note      This program is distributed in the hope that it will be useful
 *            WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 *            or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @category  Framework
 * @filesource
 *
 * @link      https://catalyst.dock Local development URL
 *
 * RoleMiddleware — route guard that enforces role and/or permission requirements.
 *
 */

namespace Catalyst\Framework\Middleware;

use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Framework\Authorization\PermissionRegistry;
use Catalyst\Framework\Authorization\RoleRepository;
use Catalyst\Framework\Http\RedirectResponse;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\RedirectTarget;
use Catalyst\Framework\Http\Response;
use Catalyst\Helpers\Exceptions\ForbiddenException;
use Closure;

/**************************************************************************************
 * RoleMiddleware — RBAC route guard
 *
 * Enforces role and/or permission checks on protected routes.
 * Must be applied AFTER AuthMiddleware (or used standalone — it checks auth first).
 *
 * ## Usage in routes.php
 *
 *   // Single role:
 *   $router->get('/admin', [AdminController::class, 'index'])
 *          ->middleware(new RoleMiddleware(roles: 'admin'));
 *
 *   // Multiple roles (OR — user needs at least one):
 *   $router->get('/reports', [ReportController::class, 'index'])
 *          ->middleware(new RoleMiddleware(roles: ['admin', 'manager']));
 *
 *   // Permission check:
 *   $router->post('/users', [UserController::class, 'store'])
 *          ->middleware(new RoleMiddleware(permissions: 'manage-users'));
 *
 *   // Roles AND permissions (both must pass):
 *   $router->delete('/users/{id}', [UserController::class, 'destroy'])
 *          ->middleware(new RoleMiddleware(roles: 'admin', permissions: 'manage-users'));
 *
 * ## Error responses
 *
 *   - Not authenticated: 401 JSON (API) or redirect to /login (web)
 *   - Authenticated but not authorized: throws ForbiddenException → 403
 *
 * @package Catalyst\Framework\Middleware
 */
class RoleMiddleware extends CoreMiddleware
{
    private array $roles;
    private array $permissions;

    /**
     * @param string|string[]|null $roles       Required role slug(s) — OR logic
     * @param string|string[]|null $permissions Required permission slug(s) — OR logic
     */
    public function __construct(
        string|array|null $roles       = null,
        string|array|null $permissions = null
    ) {
        $this->roles       = $roles === null ? [] : (array)$roles;
        $this->permissions = $permissions === null ? [] : (array)$permissions;
    }

    public function process(Request $request, Closure $next): Response
    {
        $auth = AuthManager::getInstance();

        // -- 1. Authentication check -------------------------------------------
        if (!$auth->check() && !$auth->loginFromRemember()) {
            if ($this->expectsJson($request)) {
                $response = new Response();
                $response->setStatusCode(401);
                $response->setHeader('Content-Type', 'application/json');
                $response->setContent(json_encode([
                    'success' => false,
                    'message' => 'Unauthenticated. Login required.',
                ]));
                return $response;
            }

            return new RedirectResponse(RedirectTarget::loginUrl($request->getUri()));
        }

        $userId = $auth->id();

        if ($userId === null) {
            throw ForbiddenException::forbidden('Unable to resolve authenticated user.');
        }

        $repo = RoleRepository::getInstance();
        $user = $auth->user();

        // -- 2. Role check (OR — user needs at least one of the required roles) -
        if (!empty($this->roles) && !$repo->userHasAnyRole($userId, $this->roles)) {
            $this->log('RBAC: role check failed', [
                'user_id'  => $userId,
                'required' => $this->roles,
            ]);
            throw ForbiddenException::role(implode('|', $this->roles));
        }

        // -- 3. Permission check (OR) ------------------------------------------
        if (
            !empty($this->permissions)
            && !PermissionRegistry::getInstance()->userHasAnyPermission($user, $this->permissions)
        ) {
            $this->log('RBAC: permission check failed', [
                'user_id'  => $userId,
                'required' => $this->permissions,
            ]);
            throw ForbiddenException::permission(implode('|', $this->permissions));
        }

        return $this->passToNext($request, $next);
    }

    /**
     * @return string[]
     */
    public function getRequiredRoles(): array
    {
        return array_values($this->roles);
    }

    /**
     * @return string[]
     */
    public function getRequiredPermissions(): array
    {
        return array_values($this->permissions);
    }
}
