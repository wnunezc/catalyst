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
