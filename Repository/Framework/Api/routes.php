<?php

declare(strict_types=1);

use Catalyst\Framework\Middleware\ApiTokenMiddleware;
use Catalyst\Framework\Route\Router;
use Catalyst\Helpers\I18n\Translator;
use Catalyst\Repository\Api\Controllers\CalendarApiController;
use Catalyst\Repository\Api\Controllers\CatalogApiController;
use Catalyst\Repository\Api\Controllers\VersionApiController;
use Catalyst\Repository\Api\Controllers\WorkflowApiController;

$router = Router::getInstance();
Translator::getInstance()->addPath(implode(DS, [PD, 'Repository', 'Framework', 'Api', 'lang']));
$apiMiddleware = [ApiTokenMiddleware::class];

$router->get('/api/v1/catalog', [CatalogApiController::class, 'index'])->middleware($apiMiddleware);
$router->get('/api/v1/calendar/events', [CalendarApiController::class, 'events'])->middleware($apiMiddleware);
$router->get('/api/v1/workflows', [WorkflowApiController::class, 'index'])->middleware($apiMiddleware);
$router->post('/api/v1/workflows/{id}/transition', [WorkflowApiController::class, 'transition'])->middleware($apiMiddleware)->throttle('api_mutation');
$router->get('/api/v1/versions/{resourceKey}/{recordId}', [VersionApiController::class, 'index'])->middleware($apiMiddleware);
$router->post('/api/v1/versions/{id}/restore', [VersionApiController::class, 'restore'])->middleware($apiMiddleware)->throttle('api_mutation');
