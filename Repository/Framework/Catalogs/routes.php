<?php

declare(strict_types=1);

use Catalyst\Framework\Middleware\AuthMiddleware;
use Catalyst\Framework\Middleware\RoleMiddleware;
use Catalyst\Framework\Route\Router;
use Catalyst\Framework\View\View;
use Catalyst\Helpers\I18n\Translator;
use Catalyst\Repository\Catalogs\Controllers\CatalogController;

$router = Router::getInstance();
View::getInstance()->addPath('catalogs', implode(DS, [PD, 'Repository', 'Framework', 'Catalogs', 'Views']));
Translator::getInstance()->addPath(implode(DS, [PD, 'Repository', 'Framework', 'Catalogs', 'lang']));
$moduleMiddleware = [AuthMiddleware::class, new RoleMiddleware(permissions: 'manage-catalogs')];

$router->get('/workspaces/catalogs', [CatalogController::class, 'index'])->middleware($moduleMiddleware);
$router->get('/workspaces/catalogs/create', [CatalogController::class, 'create'])->middleware($moduleMiddleware);
$router->post('/workspaces/catalogs', [CatalogController::class, 'store'])->middleware($moduleMiddleware)->throttle('admin_mutation');
$router->get('/workspaces/catalogs/{id}', [CatalogController::class, 'show'])->middleware($moduleMiddleware);
$router->get('/workspaces/catalogs/{id}/edit', [CatalogController::class, 'edit'])->middleware($moduleMiddleware);
$router->post('/workspaces/catalogs/{id}', [CatalogController::class, 'update'])->middleware($moduleMiddleware)->throttle('admin_mutation');
$router->post('/workspaces/catalogs/{id}/delete', [CatalogController::class, 'destroy'])->middleware($moduleMiddleware)->throttle('admin_mutation');
$router->post('/workspaces/catalogs/{id}/transition', [CatalogController::class, 'transition'])->middleware($moduleMiddleware)->throttle('admin_mutation');
$router->post('/workspaces/catalogs/{id}/versions/{versionId}/restore', [CatalogController::class, 'restoreVersion'])->middleware($moduleMiddleware)->throttle('admin_mutation');
$router->get('/workspaces/catalogs/{id}/items/create', [CatalogController::class, 'createItem'])->middleware($moduleMiddleware);
$router->post('/workspaces/catalogs/{id}/items', [CatalogController::class, 'storeItem'])->middleware($moduleMiddleware)->throttle('admin_mutation');
$router->get('/workspaces/catalogs/{id}/items/{itemId}/edit', [CatalogController::class, 'editItem'])->middleware($moduleMiddleware);
$router->post('/workspaces/catalogs/{id}/items/{itemId}', [CatalogController::class, 'updateItem'])->middleware($moduleMiddleware)->throttle('admin_mutation');
$router->post('/workspaces/catalogs/{id}/items/{itemId}/delete', [CatalogController::class, 'destroyItem'])->middleware($moduleMiddleware)->throttle('admin_mutation');
