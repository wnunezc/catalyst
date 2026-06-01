<?php

declare(strict_types=1);

use Catalyst\Framework\Route\Router;
use Catalyst\Framework\View\View;
use Catalyst\Helpers\I18n\Translator;
use Catalyst\Repository\Operations\Controllers\DeploymentsController;
use Catalyst\Repository\Operations\Controllers\FeatureFlagsController;
use Catalyst\Repository\Operations\Controllers\ModuleDesignerController;
use Catalyst\Repository\Operations\Controllers\OperationsOverviewController;
use Catalyst\Repository\Operations\Controllers\AppearanceController;
use Catalyst\Repository\Operations\Controllers\LocalizationController;
use Catalyst\Repository\Operations\Controllers\PluginsController;
use Catalyst\Repository\Operations\Controllers\TenancyController;
use Catalyst\Framework\Middleware\AuthMiddleware;
use Catalyst\Framework\Middleware\RoleMiddleware;

$router = Router::getInstance();
View::getInstance()->addPath('operations', implode(DS, [PD, 'Repository', 'Framework', 'Operations', 'Views']));
Translator::getInstance()->addPath(implode(DS, [PD, 'Repository', 'Framework', 'Operations', 'lang']));
$moduleMiddleware = [AuthMiddleware::class, new RoleMiddleware(permissions: 'manage-platform-operations')];

$router->get('/operations', [OperationsOverviewController::class, 'index'])->middleware($moduleMiddleware);

$router->get('/workspaces/module-designer', [ModuleDesignerController::class, 'index'])->middleware($moduleMiddleware);
$router->get('/workspaces/module-designer/preview', [ModuleDesignerController::class, 'legacyPreviewEntry'])->middleware($moduleMiddleware);
$router->get('/workspaces/module-designer/generate', [ModuleDesignerController::class, 'legacyGenerateEntry'])->middleware($moduleMiddleware);
$router->post('/workspaces/module-designer/preview', [ModuleDesignerController::class, 'preview'])->middleware($moduleMiddleware)->throttle('admin_mutation');
$router->post('/workspaces/module-designer/generate', [ModuleDesignerController::class, 'generate'])->middleware($moduleMiddleware)->throttle('admin_mutation');

$router->get('/configuration/feature-flags', [FeatureFlagsController::class, 'featureFlags'])->middleware($moduleMiddleware);
$router->post('/configuration/feature-flags/defaults/{flagKey}', [FeatureFlagsController::class, 'setFeatureFlagDefault'])->middleware($moduleMiddleware);
$router->post('/configuration/feature-flags/overrides', [FeatureFlagsController::class, 'storeFeatureFlagOverride'])->middleware($moduleMiddleware);
$router->post('/configuration/feature-flags/overrides/{id}/delete', [FeatureFlagsController::class, 'deleteFeatureFlagOverride'])->middleware($moduleMiddleware);

$router->get('/workspaces/locale-tools', [LocalizationController::class, 'index'])->middleware($moduleMiddleware);
$router->post('/workspaces/locale-tools/settings', [LocalizationController::class, 'updateSettings'])->middleware($moduleMiddleware)->throttle('admin_mutation');
$router->post('/workspaces/locale-tools/create-locale', [LocalizationController::class, 'createLocale'])->middleware($moduleMiddleware)->throttle('admin_mutation');
$router->post('/workspaces/locale-tools/sync-locale', [LocalizationController::class, 'syncLocale'])->middleware($moduleMiddleware)->throttle('admin_mutation');

$router->get('/configuration/platform-appearance', [AppearanceController::class, 'index'])->middleware($moduleMiddleware);
$router->post('/configuration/platform-appearance', [AppearanceController::class, 'update'])->middleware($moduleMiddleware)->throttle('admin_mutation');

$router->get('/configuration/plugins', [PluginsController::class, 'plugins'])->middleware($moduleMiddleware);
$router->post('/configuration/plugins/{pluginKey}/toggle', [PluginsController::class, 'togglePlugin'])->middleware($moduleMiddleware);

$router->get('/operations/deployments', [DeploymentsController::class, 'deployments'])->middleware($moduleMiddleware);
$router->post('/operations/deployments/runs', [DeploymentsController::class, 'runDeployment'])->middleware($moduleMiddleware);
$router->get('/operations/tenancy', [TenancyController::class, 'tenancy'])->middleware($moduleMiddleware);
