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

namespace Catalyst\Repository\DevTools\Controllers;

use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Framework\Authorization\Gate;
use Catalyst\Framework\Authorization\RoleRepository;
use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\JsonResponse;

/**
 * Defines the Rbac Test Controller class contract.
 *
 * @package Catalyst\Repository\DevTools\Controllers
 * Responsibility: Coordinates the rbac test controller behavior within its module boundary.
 */
class RbacTestController extends Controller
{
    /**
     * Handles the rbac status workflow.
     */
    public function rbacStatus(): JsonResponse
    {
        $auth = AuthManager::getInstance();

        if (!$auth->check()) {
            return $this->jsonSuccess([
                'authenticated' => false,
                'roles'         => [],
                'permissions'   => [],
            ], __('devtools.rbac_runtime.not_authenticated'));
        }

        $user   = $auth->user();
        $userId = (int)($user['id'] ?? 0);
        $repo   = RoleRepository::getInstance();
        $gate   = Gate::getInstance();

        $gate->define('rbac-test-gate', fn(array $u): bool => ($u['role'] ?? '') === 'admin');

        return $this->jsonSuccess([
            'authenticated'    => true,
            'user_id'          => $userId,
            'legacy_role'      => $user['role'] ?? null,
            'roles'            => array_column($repo->getUserRoles($userId), 'slug'),
            'permissions'      => array_column($repo->getUserPermissions($userId), 'slug'),
            'gate_admin_check' => $gate->allows('rbac-test-gate'),
            'is_admin_role'    => $repo->userHasRole($userId, 'admin'),
        ], __('devtools.rbac_runtime.status_ok'));
    }

    /**
     * Creates the requested object.
     */
    public function makeAdmin(): JsonResponse
    {
        if (!defined('IS_DEVELOPMENT') || !IS_DEVELOPMENT) {
            return $this->jsonError(__('devtools.rbac_runtime.dev_only'), 403);
        }

        $auth = AuthManager::getInstance();

        if (!$auth->check()) {
            return $this->jsonError(__('devtools.rbac_runtime.login_required'), 401);
        }

        $userId = $auth->id();
        $assigned = RoleRepository::getInstance()->assignRoleSlugToUser($userId, 'admin');

        if (!$assigned) {
            return $this->jsonError(__('devtools.rbac_runtime.admin_role_missing'), 404);
        }

        return $this->jsonSuccess(
            ['user_id' => $userId, 'role' => 'admin'],
            __('devtools.rbac_runtime.promoted')
        )->withNotification(
            $this->toaster('success', __('devtools.rbac_runtime.promoted_toast'))
        );
    }
}
