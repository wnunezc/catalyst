<?php

declare(strict_types=1);

use Catalyst\Framework\Middleware\AuthMiddleware;
use Catalyst\Framework\Middleware\RoleMiddleware;
use Catalyst\Framework\Route\Router;
use Catalyst\Framework\View\View;
use Catalyst\Helpers\I18n\Translator;
use Catalyst\Repository\Media\Controllers\MediaLibraryController;
use Catalyst\Repository\Media\Controllers\MetadataFieldController;

$router = Router::getInstance();
View::getInstance()->addPath('media', implode(DS, [PD, 'Repository', 'Framework', 'Media', 'Views']));
Translator::getInstance()->addPath(implode(DS, [PD, 'Repository', 'Framework', 'Media', 'lang']));
$manageMediaMiddleware = [AuthMiddleware::class, new RoleMiddleware(permissions: 'manage-media-library')];
$manageMetadataMiddleware = [AuthMiddleware::class, new RoleMiddleware(permissions: 'manage-media-metadata')];

$router->get('/workspaces/media-library/upload', [MediaLibraryController::class, 'create'])->middleware($manageMediaMiddleware);
$router->post('/workspaces/media-library', [MediaLibraryController::class, 'store'])->middleware($manageMediaMiddleware)->throttle('admin_mutation');
$router->get('/workspaces/media-library', [MediaLibraryController::class, 'index'])->middleware($manageMediaMiddleware);
$router->post('/workspaces/media-library/bulk-delete', [MediaLibraryController::class, 'bulkDestroy'])->middleware($manageMediaMiddleware)->throttle('admin_mutation');
$router->get('/workspaces/media-library/{id}/edit', [MediaLibraryController::class, 'edit'])->middleware($manageMediaMiddleware);
$router->post('/workspaces/media-library/{id}', [MediaLibraryController::class, 'update'])->middleware($manageMediaMiddleware)->throttle('admin_mutation');
$router->post('/workspaces/media-library/{id}/delete', [MediaLibraryController::class, 'destroy'])->middleware($manageMediaMiddleware)->throttle('admin_mutation');

$router->get('/workspaces/media-fields/create', [MetadataFieldController::class, 'create'])->middleware($manageMetadataMiddleware);
$router->post('/workspaces/media-fields', [MetadataFieldController::class, 'store'])->middleware($manageMetadataMiddleware)->throttle('admin_mutation');
$router->get('/workspaces/media-fields', [MetadataFieldController::class, 'index'])->middleware($manageMetadataMiddleware);
$router->get('/workspaces/media-fields/{id}/edit', [MetadataFieldController::class, 'edit'])->middleware($manageMetadataMiddleware);
$router->post('/workspaces/media-fields/{id}', [MetadataFieldController::class, 'update'])->middleware($manageMetadataMiddleware)->throttle('admin_mutation');
$router->post('/workspaces/media-fields/{id}/delete', [MetadataFieldController::class, 'destroy'])->middleware($manageMetadataMiddleware)->throttle('admin_mutation');
