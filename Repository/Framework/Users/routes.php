<?php

declare(strict_types=1);

use Catalyst\Framework\Middleware\AuthMiddleware;
use Catalyst\Framework\Middleware\RoleMiddleware;
use Catalyst\Framework\Route\Router;
use Catalyst\Framework\View\View;
use Catalyst\Helpers\I18n\Translator;
use Catalyst\Repository\Users\Controllers\PermissionsController;
use Catalyst\Repository\Users\Controllers\OrganizationHierarchyController;
use Catalyst\Repository\Users\Controllers\RolesController;
use Catalyst\Repository\Users\Controllers\UserManagementController;
use Catalyst\Repository\Users\Controllers\UserRolesController;
use Catalyst\Repository\Users\Controllers\AccountRecoveryReviewController;

$router = Router::getInstance();
View::getInstance()->addPath('users', implode(DS, [PD, 'Repository', 'Framework', 'Users', 'Views']));
Translator::getInstance()->addPath(implode(DS, [PD, 'Repository', 'Framework', 'Users', 'lang']));
$manageUsersMiddleware = [AuthMiddleware::class, new RoleMiddleware(permissions: 'manage-users')];
$manageRolesMiddleware = [AuthMiddleware::class, new RoleMiddleware(permissions: 'manage-roles')];
$accountRecoveryReviewMiddleware = [AuthMiddleware::class, new RoleMiddleware(permissions: 'manage-account-recovery')];

$router->get('/users', [UserManagementController::class, 'index'])->middleware($manageUsersMiddleware);
$router->get('/users/enroll', [UserManagementController::class, 'create'])->middleware($manageUsersMiddleware);
$router->post('/users/enroll', [UserManagementController::class, 'store'])->middleware($manageUsersMiddleware)->throttle('privileged_mutation');

$router->get('/users/roles/create', [RolesController::class, 'create'])->middleware($manageRolesMiddleware);
$router->post('/users/roles', [RolesController::class, 'store'])->middleware($manageRolesMiddleware)->throttle('privileged_mutation');
$router->get('/users/roles', [RolesController::class, 'index'])->middleware($manageRolesMiddleware);
$router->post('/users/roles/bulk-delete', [RolesController::class, 'bulkDestroy'])->middleware($manageRolesMiddleware)->throttle('privileged_mutation');
$router->get('/users/roles/{id}/edit', [RolesController::class, 'edit'])->middleware($manageRolesMiddleware);
$router->post('/users/roles/{id}/delete', [RolesController::class, 'destroy'])->middleware($manageRolesMiddleware)->throttle('privileged_mutation');
$router->post('/users/roles/{id}', [RolesController::class, 'update'])->middleware($manageRolesMiddleware)->throttle('privileged_mutation');
$router->get('/users/roles/{id}/permissions', [RolesController::class, 'permissions'])->middleware($manageRolesMiddleware);
$router->post('/users/roles/{id}/permissions', [RolesController::class, 'syncPermissions'])->middleware($manageRolesMiddleware)->throttle('privileged_mutation');

$router->get('/users/organization-hierarchy', [OrganizationHierarchyController::class, 'index'])->middleware($manageRolesMiddleware);
$router->post('/users/organization-hierarchy/organizations', [OrganizationHierarchyController::class, 'storeOrganization'])->middleware($manageRolesMiddleware)->throttle('privileged_mutation');
$router->post('/users/organization-hierarchy/organizations/{id}/delete', [OrganizationHierarchyController::class, 'destroyOrganization'])->middleware($manageRolesMiddleware)->throttle('privileged_mutation');
$router->post('/users/organization-hierarchy/units', [OrganizationHierarchyController::class, 'storeUnit'])->middleware($manageRolesMiddleware)->throttle('privileged_mutation');
$router->post('/users/organization-hierarchy/units/{id}/delete', [OrganizationHierarchyController::class, 'destroyUnit'])->middleware($manageRolesMiddleware)->throttle('privileged_mutation');
$router->post('/users/organization-hierarchy/scopes', [OrganizationHierarchyController::class, 'storeScope'])->middleware($manageRolesMiddleware)->throttle('privileged_mutation');
$router->post('/users/organization-hierarchy/scopes/{id}/delete', [OrganizationHierarchyController::class, 'destroyScope'])->middleware($manageRolesMiddleware)->throttle('privileged_mutation');
$router->post('/users/organization-hierarchy/levels', [OrganizationHierarchyController::class, 'storeLevel'])->middleware($manageRolesMiddleware)->throttle('privileged_mutation');
$router->post('/users/organization-hierarchy/levels/{id}/delete', [OrganizationHierarchyController::class, 'destroyLevel'])->middleware($manageRolesMiddleware)->throttle('privileged_mutation');

$router->get('/users/permissions/create', [PermissionsController::class, 'create'])->middleware($manageRolesMiddleware);
$router->post('/users/permissions', [PermissionsController::class, 'store'])->middleware($manageRolesMiddleware)->throttle('privileged_mutation');
$router->get('/users/permissions', [PermissionsController::class, 'index'])->middleware($manageRolesMiddleware);
$router->post('/users/permissions/bulk-delete', [PermissionsController::class, 'bulkDestroy'])->middleware($manageRolesMiddleware)->throttle('privileged_mutation');
$router->get('/users/permissions/{id}/edit', [PermissionsController::class, 'edit'])->middleware($manageRolesMiddleware);
$router->post('/users/permissions/{id}', [PermissionsController::class, 'update'])->middleware($manageRolesMiddleware)->throttle('privileged_mutation');
$router->post('/users/permissions/{id}/delete', [PermissionsController::class, 'destroy'])->middleware($manageRolesMiddleware)->throttle('privileged_mutation');

$router->get('/users/{userId}/roles', [UserRolesController::class, 'index'])->middleware($manageUsersMiddleware);
$router->post('/users/{userId}/roles/{roleId}/remove', [UserRolesController::class, 'remove'])->middleware($manageUsersMiddleware)->throttle('privileged_mutation');
$router->post('/users/{userId}/roles/{roleId}', [UserRolesController::class, 'assign'])->middleware($manageUsersMiddleware)->throttle('privileged_mutation');

$router->get('/users/account-recovery', [AccountRecoveryReviewController::class, 'index'])->middleware($accountRecoveryReviewMiddleware);
$router->get('/users/account-recovery/{id}', [AccountRecoveryReviewController::class, 'show'])->middleware($accountRecoveryReviewMiddleware);
$router->post('/users/account-recovery/{id}/approve', [AccountRecoveryReviewController::class, 'approve'])->middleware($accountRecoveryReviewMiddleware)->throttle('privileged_mutation');
$router->post('/users/account-recovery/{id}/reject', [AccountRecoveryReviewController::class, 'reject'])->middleware($accountRecoveryReviewMiddleware)->throttle('privileged_mutation');
