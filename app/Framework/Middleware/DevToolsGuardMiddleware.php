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
use Catalyst\Framework\Http\ErrorResponseFactory;
use Catalyst\Framework\Http\JsonResponse;
use Catalyst\Framework\Http\RedirectResponse;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\RedirectTarget;
use Catalyst\Framework\Http\Response;
use Closure;

/**
 * DevToolsGuardMiddleware
 *
 * Hard gate for developer tooling routes.
 *
 * Rules:
 * - Outside development: always 403
 * - Development without login: standard auth behavior (401 JSON or redirect)
 * - Development with authenticated non-admin/non-authorized user: 403
 * - Development with admin or explicit permission: pass through
 *
 * @package Catalyst\Framework\Middleware
 * Responsibility: Restricts developer tooling routes to authorized users in development environments.
 */
class DevToolsGuardMiddleware extends CoreMiddleware
{
    private const EXPLICIT_PERMISSION = 'access-devtools';

    /**
     * @var string[]
     */
    private array $permissions;

    /**
     * Initializes the Dev Tools Guard Middleware instance.
     *
     * Responsibility: Initializes the Dev Tools Guard Middleware instance.
     */
    public function __construct(string|array|null $permissions = null)
    {
        $this->permissions = $permissions === null ? [] : array_values(array_filter(
            array_map(
                static fn (mixed $permission): string => trim((string) $permission),
                (array) $permissions
            ),
            static fn (string $permission): bool => $permission !== ''
        ));
    }

    /**
     * Allows authorized development requests and rejects every other DevTools access.
     *
     * Responsibility: Allows authorized development requests and rejects every other DevTools access.
     */
    public function process(Request $request, Closure $next): Response
    {
        if (!defined('IS_DEVELOPMENT') || !IS_DEVELOPMENT) {
            $this->log('DevToolsGuard: blocked outside development', ['uri' => $request->getUri()]);
            return $this->forbiddenResponse($request, 'DevTools disabled outside development.');
        }

        $auth = AuthManager::getInstance();

        if (!$auth->check() && !$auth->loginFromRemember()) {
            $this->log('DevToolsGuard: unauthenticated request blocked', ['uri' => $request->getUri()]);

            if ($this->expectsJson($request)) {
                return JsonResponse::error('Login required.', status: 401);
            }

            return new RedirectResponse(RedirectTarget::loginUrl($request->getUri()));
        }

        $userId = $auth->id();

        if ($userId === null) {
            $this->log('DevToolsGuard: authenticated session without user id', ['uri' => $request->getUri()]);
            return $this->forbiddenResponse($request, 'Unable to resolve authenticated user.');
        }

        $permissions = PermissionRegistry::getInstance();
        $user = $auth->user();
        $requiredPermissions = $this->getRequiredPermissions();

        $hasRequiredPermission = false;
        foreach ($requiredPermissions as $permission) {
            if ($permissions->userHasPermission($user, $permission)) {
                $hasRequiredPermission = true;
                break;
            }
        }

        if (
            !$permissions->userHasRole($user, 'admin')
            && !$hasRequiredPermission
        ) {
            $this->log('DevToolsGuard: access denied', [
                'uri' => $request->getUri(),
                'user_id' => $userId,
                'required_role' => 'admin',
                'required_permissions' => $requiredPermissions,
            ]);

            return $this->forbiddenResponse(
                $request,
                'DevTools access requires the admin role or a declared DevTools permission.'
            );
        }

        return $this->passToNext($request, $next);
    }

    /**
     * Returns permissions that authorize DevTools access in addition to the admin role.
     *
     * Responsibility: Returns permissions that authorize DevTools access in addition to the admin role.
     * @return string[]
     */
    public function getRequiredPermissions(): array
    {
        return array_values(array_unique(array_merge(
            [self::EXPLICIT_PERMISSION],
            $this->permissions
        )));
    }

    /**
     * Builds the appropriate forbidden response for HTML or JSON requests.
     *
     * Responsibility: Builds the appropriate forbidden response for HTML or JSON requests.
     */
    private function forbiddenResponse(Request $request, string $message): Response
    {
        if ($this->expectsJson($request)) {
            return JsonResponse::error($message, status: 403);
        }

        return ErrorResponseFactory::forbidden($message);
    }
}
