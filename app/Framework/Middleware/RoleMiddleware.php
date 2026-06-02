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
use Catalyst\Framework\Authorization\PermissionRegistry;
use Catalyst\Framework\Authorization\RoleRepository;
use Catalyst\Framework\Http\RedirectResponse;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\RedirectTarget;
use Catalyst\Framework\Http\Response;
use Catalyst\Helpers\Exceptions\ForbiddenException;
use Closure;

/**
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
 * Responsibility: Requires authentication and enforces configured RBAC role and permission requirements.
 */
class RoleMiddleware extends CoreMiddleware
{
    private array $roles;
    private array $permissions;

    /**
     * Initializes the guard with required roles and permissions.
     *
     * Responsibility: Initializes the guard with required roles and permissions.
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

    /**
     * Verifies authentication, roles, and permissions before forwarding the request.
     *
     * Responsibility: Verifies authentication, roles, and permissions before forwarding the request.
     */
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
     * Returns the role slugs required by this guard.
     *
     * Responsibility: Returns the role slugs required by this guard.
     * @return string[]
     */
    public function getRequiredRoles(): array
    {
        return array_values($this->roles);
    }

    /**
     * Returns the permission slugs required by this guard.
     *
     * Responsibility: Returns the permission slugs required by this guard.
     * @return string[]
     */
    public function getRequiredPermissions(): array
    {
        return array_values($this->permissions);
    }
}
