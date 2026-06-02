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

use Catalyst\Framework\Middleware\AuthMiddleware;
use Catalyst\Framework\Middleware\RoleMiddleware;
use Catalyst\Framework\Route\Router;
use Catalyst\Framework\View\View;
use Catalyst\Helpers\I18n\Translator;
use Catalyst\Repository\Roles\Controllers\PermissionsController;
use Catalyst\Repository\Roles\Controllers\RolesController;
use Catalyst\Repository\Roles\Controllers\UserRolesController;
use Catalyst\Repository\Roles\Controllers\UserManagementController;

$router = Router::getInstance();
View::getInstance()->addPath('roles', implode(DS, [PD, 'Repository', 'Framework', 'Roles', 'Views']));
Translator::getInstance()->addPath(implode(DS, [PD, 'Repository', 'Framework', 'Roles', 'lang']));
$manageUsersMiddleware = [AuthMiddleware::class, new RoleMiddleware(permissions: 'manage-users')];
$manageRolesMiddleware = [AuthMiddleware::class, new RoleMiddleware(permissions: 'manage-roles')];

$router->get('/users', [UserManagementController::class, 'index'])->middleware($manageUsersMiddleware);
$router->get('/users/enroll', [UserManagementController::class, 'create'])->middleware($manageUsersMiddleware);
$router->post('/users/enroll', [UserManagementController::class, 'store'])->middleware($manageUsersMiddleware)->throttle('admin_mutation');

$router->get('/users/roles/create', [RolesController::class, 'create'])->middleware($manageRolesMiddleware);
$router->post('/users/roles', [RolesController::class, 'store'])->middleware($manageRolesMiddleware)->throttle('admin_mutation');
$router->get('/users/roles', [RolesController::class, 'index'])->middleware($manageRolesMiddleware);
$router->post('/users/roles/bulk-delete', [RolesController::class, 'bulkDestroy'])->middleware($manageRolesMiddleware)->throttle('admin_mutation');
$router->get('/users/roles/{id}/edit', [RolesController::class, 'edit'])->middleware($manageRolesMiddleware);
$router->post('/users/roles/{id}/delete', [RolesController::class, 'destroy'])->middleware($manageRolesMiddleware)->throttle('admin_mutation');
$router->post('/users/roles/{id}', [RolesController::class, 'update'])->middleware($manageRolesMiddleware)->throttle('admin_mutation');
$router->get('/users/roles/{id}/permissions', [RolesController::class, 'permissions'])->middleware($manageRolesMiddleware);
$router->post('/users/roles/{id}/permissions', [RolesController::class, 'syncPermissions'])->middleware($manageRolesMiddleware)->throttle('admin_mutation');

$router->get('/users/permissions/create', [PermissionsController::class, 'create'])->middleware($manageRolesMiddleware);
$router->post('/users/permissions', [PermissionsController::class, 'store'])->middleware($manageRolesMiddleware)->throttle('admin_mutation');
$router->get('/users/permissions', [PermissionsController::class, 'index'])->middleware($manageRolesMiddleware);
$router->post('/users/permissions/bulk-delete', [PermissionsController::class, 'bulkDestroy'])->middleware($manageRolesMiddleware)->throttle('admin_mutation');
$router->get('/users/permissions/{id}/edit', [PermissionsController::class, 'edit'])->middleware($manageRolesMiddleware);
$router->post('/users/permissions/{id}', [PermissionsController::class, 'update'])->middleware($manageRolesMiddleware)->throttle('admin_mutation');
$router->post('/users/permissions/{id}/delete', [PermissionsController::class, 'destroy'])->middleware($manageRolesMiddleware)->throttle('admin_mutation');

$router->get('/users/{userId}/roles', [UserRolesController::class, 'index'])->middleware($manageUsersMiddleware);
$router->post('/users/{userId}/roles/{roleId}/remove', [UserRolesController::class, 'remove'])->middleware($manageUsersMiddleware)->throttle('admin_mutation');
$router->post('/users/{userId}/roles/{roleId}', [UserRolesController::class, 'assign'])->middleware($manageUsersMiddleware)->throttle('admin_mutation');
