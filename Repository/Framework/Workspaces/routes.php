<?php

declare(strict_types=1);

/**
 * Catalyst PHP Framework
 *
 * Workspaces routes are registered only as each complete vertical surface
 * migration removes the same routes from its previous owner.
 *
 * @package Catalyst
 */

use Catalyst\Framework\Route\Router;
use Catalyst\Framework\View\View;
use Catalyst\Framework\Middleware\ApiTokenMiddleware;
use Catalyst\Helpers\I18n\Translator;
use Catalyst\Repository\Workspaces\Catalogs\Controllers\CatalogController;
use Catalyst\Repository\Workspaces\Documents\Controllers\DocumentTemplateApiController;
use Catalyst\Repository\Workspaces\Documents\Controllers\DocumentTemplateController;
use Catalyst\Repository\Workspaces\Media\Controllers\MediaLibraryController;
use Catalyst\Repository\Workspaces\Media\Controllers\MetadataFieldController;
use Catalyst\Repository\Workspaces\Localization\Controllers\LocalizationController;
use Catalyst\Repository\Workspaces\MailTemplates\Controllers\MailTemplateController;
use Catalyst\Repository\Workspaces\ModuleDesigner\Controllers\ModuleDesignerController;
use Catalyst\Repository\Workspaces\Support\WorkspacesAccessContract;

$router = Router::getInstance();
View::getInstance()->addPath(
    'workspaces',
    implode(DS, [PD, 'Repository', 'Framework', 'Workspaces', 'Views'])
);
$moduleDesignerMiddleware = WorkspacesAccessContract::middleware(WorkspacesAccessContract::MODULE_DESIGNER);

$router->get('/workspaces/module-designer', [ModuleDesignerController::class, 'index'])->middleware($moduleDesignerMiddleware);
$router->post('/workspaces/module-designer/preview', [ModuleDesignerController::class, 'preview'])->middleware($moduleDesignerMiddleware)->throttle('privileged_mutation');
$router->post('/workspaces/module-designer/generate', [ModuleDesignerController::class, 'generate'])->middleware($moduleDesignerMiddleware)->throttle('privileged_mutation');
$router->post('/workspaces/module-designer/modules/{key}/delete', [ModuleDesignerController::class, 'destroy'])->middleware($moduleDesignerMiddleware)->throttle('privileged_mutation');

$localizationMiddleware = WorkspacesAccessContract::middleware(WorkspacesAccessContract::LOCALIZATION);
$router->get('/workspaces/locale-tools', [LocalizationController::class, 'index'])->middleware($localizationMiddleware);
$router->post('/workspaces/locale-tools/settings', [LocalizationController::class, 'updateSettings'])->middleware($localizationMiddleware)->throttle('privileged_mutation');
$router->post('/workspaces/locale-tools/create-locale', [LocalizationController::class, 'createLocale'])->middleware($localizationMiddleware)->throttle('privileged_mutation');
$router->post('/workspaces/locale-tools/sync-locale', [LocalizationController::class, 'syncLocale'])->middleware($localizationMiddleware)->throttle('privileged_mutation');

$mailTemplatesMiddleware = WorkspacesAccessContract::middleware(WorkspacesAccessContract::MAIL_TEMPLATES);
$router->get('/workspaces/mail-templates', [MailTemplateController::class, 'index'])->middleware($mailTemplatesMiddleware);
$router->get('/workspaces/mail-templates/create', [MailTemplateController::class, 'create'])->middleware($mailTemplatesMiddleware);
$router->post('/workspaces/mail-templates', [MailTemplateController::class, 'store'])->middleware($mailTemplatesMiddleware)->throttle('privileged_mutation');
$router->post('/workspaces/mail-templates/assets', [MailTemplateController::class, 'storeAsset'])->middleware($mailTemplatesMiddleware)->throttle('privileged_mutation');
$router->post('/workspaces/mail-templates/assets/{name}/delete', [MailTemplateController::class, 'destroyAsset'])->middleware($mailTemplatesMiddleware)->throttle('privileged_mutation');
$router->get('/workspaces/mail-templates/{key}', [MailTemplateController::class, 'show'])->middleware($mailTemplatesMiddleware);
$router->post('/workspaces/mail-templates/{key}', [MailTemplateController::class, 'update'])->middleware($mailTemplatesMiddleware)->throttle('privileged_mutation');
$router->post('/workspaces/mail-templates/{key}/preview', [MailTemplateController::class, 'preview'])->middleware($mailTemplatesMiddleware)->throttle('privileged_mutation');
$router->post('/workspaces/mail-templates/{key}/test', [MailTemplateController::class, 'sendTest'])->middleware($mailTemplatesMiddleware)->throttle('privileged_mutation');
$router->post('/workspaces/mail-templates/{key}/restore', [MailTemplateController::class, 'restore'])->middleware($mailTemplatesMiddleware)->throttle('privileged_mutation');
$router->post('/workspaces/mail-templates/{key}/delete', [MailTemplateController::class, 'destroy'])->middleware($mailTemplatesMiddleware)->throttle('privileged_mutation');

View::getInstance()->addPath(
    'catalogs',
    implode(DS, [PD, 'Repository', 'Framework', 'Workspaces', 'Catalogs', 'Views'])
);
Translator::getInstance()->addPath(
    implode(DS, [PD, 'Repository', 'Framework', 'Workspaces', 'Catalogs', 'lang'])
);
$catalogsMiddleware = WorkspacesAccessContract::middleware(WorkspacesAccessContract::CATALOGS);

$router->get('/workspaces/catalogs', [CatalogController::class, 'index'])->middleware($catalogsMiddleware);
$router->get('/workspaces/catalogs/create', [CatalogController::class, 'create'])->middleware($catalogsMiddleware);
$router->post('/workspaces/catalogs', [CatalogController::class, 'store'])->middleware($catalogsMiddleware)->throttle('privileged_mutation');
$router->get('/workspaces/catalogs/{id}', [CatalogController::class, 'show'])->middleware($catalogsMiddleware);
$router->get('/workspaces/catalogs/{id}/edit', [CatalogController::class, 'edit'])->middleware($catalogsMiddleware);
$router->post('/workspaces/catalogs/{id}', [CatalogController::class, 'update'])->middleware($catalogsMiddleware)->throttle('privileged_mutation');
$router->post('/workspaces/catalogs/{id}/delete', [CatalogController::class, 'destroy'])->middleware($catalogsMiddleware)->throttle('privileged_mutation');
$router->post('/workspaces/catalogs/{id}/transition', [CatalogController::class, 'transition'])->middleware($catalogsMiddleware)->throttle('privileged_mutation');
$router->post('/workspaces/catalogs/{id}/versions/{versionId}/restore', [CatalogController::class, 'restoreVersion'])->middleware($catalogsMiddleware)->throttle('privileged_mutation');
$router->get('/workspaces/catalogs/{id}/items/create', [CatalogController::class, 'createItem'])->middleware($catalogsMiddleware);
$router->post('/workspaces/catalogs/{id}/items', [CatalogController::class, 'storeItem'])->middleware($catalogsMiddleware)->throttle('privileged_mutation');
$router->get('/workspaces/catalogs/{id}/items/{itemId}/edit', [CatalogController::class, 'editItem'])->middleware($catalogsMiddleware);
$router->post('/workspaces/catalogs/{id}/items/{itemId}', [CatalogController::class, 'updateItem'])->middleware($catalogsMiddleware)->throttle('privileged_mutation');
$router->post('/workspaces/catalogs/{id}/items/{itemId}/delete', [CatalogController::class, 'destroyItem'])->middleware($catalogsMiddleware)->throttle('privileged_mutation');

View::getInstance()->addPath(
    'media',
    implode(DS, [PD, 'Repository', 'Framework', 'Workspaces', 'Media', 'Views'])
);
Translator::getInstance()->addPath(
    implode(DS, [PD, 'Repository', 'Framework', 'Workspaces', 'Media', 'lang'])
);
$mediaFieldsMiddleware = WorkspacesAccessContract::middleware(WorkspacesAccessContract::MEDIA_FIELDS);

$router->get('/workspaces/media-fields/create', [MetadataFieldController::class, 'create'])->middleware($mediaFieldsMiddleware);
$router->post('/workspaces/media-fields', [MetadataFieldController::class, 'store'])->middleware($mediaFieldsMiddleware)->throttle('privileged_mutation');
$router->get('/workspaces/media-fields', [MetadataFieldController::class, 'index'])->middleware($mediaFieldsMiddleware);
$router->get('/workspaces/media-fields/{id}/edit', [MetadataFieldController::class, 'edit'])->middleware($mediaFieldsMiddleware);
$router->post('/workspaces/media-fields/{id}', [MetadataFieldController::class, 'update'])->middleware($mediaFieldsMiddleware)->throttle('privileged_mutation');
$router->post('/workspaces/media-fields/{id}/delete', [MetadataFieldController::class, 'destroy'])->middleware($mediaFieldsMiddleware)->throttle('privileged_mutation');

$mediaLibraryMiddleware = WorkspacesAccessContract::middleware(WorkspacesAccessContract::MEDIA_LIBRARY);

$router->get('/workspaces/media-library/upload', [MediaLibraryController::class, 'create'])->middleware($mediaLibraryMiddleware);
$router->post('/workspaces/media-library', [MediaLibraryController::class, 'store'])->middleware($mediaLibraryMiddleware)->throttle('privileged_mutation');
$router->get('/workspaces/media-library', [MediaLibraryController::class, 'index'])->middleware($mediaLibraryMiddleware);
$router->post('/workspaces/media-library/bulk-delete', [MediaLibraryController::class, 'bulkDestroy'])->middleware($mediaLibraryMiddleware)->throttle('privileged_mutation');
$router->get('/workspaces/media-library/{id}/edit', [MediaLibraryController::class, 'edit'])->middleware($mediaLibraryMiddleware);
$router->post('/workspaces/media-library/{id}', [MediaLibraryController::class, 'update'])->middleware($mediaLibraryMiddleware)->throttle('privileged_mutation');
$router->post('/workspaces/media-library/{id}/delete', [MediaLibraryController::class, 'destroy'])->middleware($mediaLibraryMiddleware)->throttle('privileged_mutation');

View::getInstance()->addPath(
    'documents',
    implode(DS, [PD, 'Repository', 'Framework', 'Workspaces', 'Documents', 'Views'])
);
Translator::getInstance()->addPath(
    implode(DS, [PD, 'Repository', 'Framework', 'Workspaces', 'Documents', 'lang'])
);
$documentMiddleware = WorkspacesAccessContract::middleware(WorkspacesAccessContract::DOCUMENT_TEMPLATES);

$router->get('/workspaces/document-templates', [DocumentTemplateController::class, 'index'])->middleware($documentMiddleware);
$router->get('/workspaces/document-templates/create', [DocumentTemplateController::class, 'create'])->middleware($documentMiddleware);
$router->post('/workspaces/document-templates', [DocumentTemplateController::class, 'store'])->middleware($documentMiddleware)->throttle('privileged_mutation');
$router->get('/workspaces/document-templates/{id}', [DocumentTemplateController::class, 'show'])->middleware($documentMiddleware);
$router->get('/workspaces/document-templates/{id}/edit', [DocumentTemplateController::class, 'edit'])->middleware($documentMiddleware);
$router->post('/workspaces/document-templates/{id}', [DocumentTemplateController::class, 'update'])->middleware($documentMiddleware)->throttle('privileged_mutation');
$router->post('/workspaces/document-templates/{id}/delete', [DocumentTemplateController::class, 'destroy'])->middleware($documentMiddleware)->throttle('privileged_mutation');
$router->post('/workspaces/document-templates/{id}/preview', [DocumentTemplateController::class, 'preview'])->middleware($documentMiddleware)->throttle('privileged_mutation');
$router->post('/workspaces/document-templates/{id}/export', [DocumentTemplateController::class, 'export'])->middleware($documentMiddleware)->throttle('privileged_mutation');
$router->post('/workspaces/document-templates/{id}/transition', [DocumentTemplateController::class, 'transition'])->middleware($documentMiddleware)->throttle('privileged_mutation');
$router->post('/workspaces/document-templates/{id}/versions/{versionId}/restore', [DocumentTemplateController::class, 'restoreVersion'])->middleware($documentMiddleware)->throttle('privileged_mutation');

$documentApiMiddleware = [ApiTokenMiddleware::class];
$router->get('/api/v1/document-templates', [DocumentTemplateApiController::class, 'apiIndex'])->middleware($documentApiMiddleware);
$router->get('/api/v1/document-templates/{id}', [DocumentTemplateApiController::class, 'apiShow'])->middleware($documentApiMiddleware);
$router->post('/api/v1/document-templates/{id}/preview', [DocumentTemplateApiController::class, 'apiPreview'])->middleware($documentApiMiddleware)->throttle('api_mutation');
$router->post('/api/v1/document-templates/{id}/export', [DocumentTemplateApiController::class, 'apiExport'])->middleware($documentApiMiddleware)->throttle('api_mutation');
