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
use Catalyst\Repository\Documents\Controllers\DocumentTemplateApiController;
use Catalyst\Repository\Documents\Controllers\DocumentTemplateController;

$router = Router::getInstance();
View::getInstance()->addPath('documents', implode(DS, [PD, 'Repository', 'Framework', 'Documents', 'Views']));
Translator::getInstance()->addPath(implode(DS, [PD, 'Repository', 'Framework', 'Documents', 'lang']));
$documentMiddleware = [AuthMiddleware::class, new RoleMiddleware(permissions: 'manage-document-templates')];

$router->get('/workspaces/document-templates', [DocumentTemplateController::class, 'index'])->middleware($documentMiddleware);
$router->get('/workspaces/document-templates/create', [DocumentTemplateController::class, 'create'])->middleware($documentMiddleware);
$router->post('/workspaces/document-templates', [DocumentTemplateController::class, 'store'])->middleware($documentMiddleware)->throttle('admin_mutation');
$router->get('/workspaces/document-templates/{id}', [DocumentTemplateController::class, 'show'])->middleware($documentMiddleware);
$router->get('/workspaces/document-templates/{id}/edit', [DocumentTemplateController::class, 'edit'])->middleware($documentMiddleware);
$router->post('/workspaces/document-templates/{id}', [DocumentTemplateController::class, 'update'])->middleware($documentMiddleware)->throttle('admin_mutation');
$router->post('/workspaces/document-templates/{id}/delete', [DocumentTemplateController::class, 'destroy'])->middleware($documentMiddleware)->throttle('admin_mutation');
$router->post('/workspaces/document-templates/{id}/preview', [DocumentTemplateController::class, 'preview'])->middleware($documentMiddleware)->throttle('admin_mutation');
$router->post('/workspaces/document-templates/{id}/export', [DocumentTemplateController::class, 'export'])->middleware($documentMiddleware)->throttle('admin_mutation');
$router->post('/workspaces/document-templates/{id}/transition', [DocumentTemplateController::class, 'transition'])->middleware($documentMiddleware)->throttle('admin_mutation');
$router->post('/workspaces/document-templates/{id}/versions/{versionId}/restore', [DocumentTemplateController::class, 'restoreVersion'])->middleware($documentMiddleware)->throttle('admin_mutation');

$apiMiddleware = [\Catalyst\Framework\Middleware\ApiTokenMiddleware::class];
$router->get('/api/v1/document-templates', [DocumentTemplateApiController::class, 'apiIndex'])->middleware($apiMiddleware);
$router->get('/api/v1/document-templates/{id}', [DocumentTemplateApiController::class, 'apiShow'])->middleware($apiMiddleware);
$router->post('/api/v1/document-templates/{id}/preview', [DocumentTemplateApiController::class, 'apiPreview'])->middleware($apiMiddleware)->throttle('api_mutation');
$router->post('/api/v1/document-templates/{id}/export', [DocumentTemplateApiController::class, 'apiExport'])->middleware($apiMiddleware)->throttle('api_mutation');
