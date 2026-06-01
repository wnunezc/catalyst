<?php

declare(strict_types=1);

use Catalyst\Framework\Middleware\ApiTokenMiddleware;
use Catalyst\Framework\Middleware\AuthMiddleware;
use Catalyst\Framework\Middleware\RoleMiddleware;
use Catalyst\Framework\Route\Router;
use Catalyst\Framework\View\View;
use Catalyst\Helpers\I18n\Translator;
use Catalyst\Repository\ApiPlatform\Controllers\ApiPlatformController;
use Catalyst\Repository\ApiPlatform\Controllers\VersionApiController;
use Catalyst\Repository\ApiPlatform\Controllers\WorkflowApiController;

$router = Router::getInstance();

View::getInstance()->addPath(
    'apiplatform',
    implode(DS, [PD, 'Repository', 'Framework', 'ApiPlatform', 'Views'])
);

Translator::getInstance()->addPath(
    implode(DS, [PD, 'Repository', 'Framework', 'ApiPlatform', 'lang'])
);

$adminMiddleware = [AuthMiddleware::class, new RoleMiddleware(permissions: 'manage-api-platform')];

$router->get('/api-platform', [ApiPlatformController::class, 'index'])
    ->middleware($adminMiddleware);

$router->post('/api-platform/tokens', [ApiPlatformController::class, 'storeToken'])
    ->middleware($adminMiddleware)
    ->throttle('admin_mutation');

$router->post('/api-platform/tokens/{id}/revoke', [ApiPlatformController::class, 'revokeToken'])
    ->middleware($adminMiddleware)
    ->throttle('admin_mutation');

$apiMiddleware = [ApiTokenMiddleware::class];

$router->get('/api/v1/catalog', [ApiPlatformController::class, 'apiCatalog'])
    ->middleware($apiMiddleware);

$router->get('/api/v1/workflows', [WorkflowApiController::class, 'index'])
    ->middleware($apiMiddleware);

$router->post('/api/v1/workflows/{id}/transition', [WorkflowApiController::class, 'transition'])
    ->middleware($apiMiddleware)
    ->throttle('api_mutation');

$router->get('/api/v1/versions/{resourceKey}/{recordId}', [VersionApiController::class, 'index'])
    ->middleware($apiMiddleware);

$router->post('/api/v1/versions/{id}/restore', [VersionApiController::class, 'restore'])
    ->middleware($apiMiddleware)
    ->throttle('api_mutation');
