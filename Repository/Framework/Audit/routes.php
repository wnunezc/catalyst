<?php

declare(strict_types=1);

use Catalyst\Framework\Middleware\AuthMiddleware;
use Catalyst\Framework\Middleware\RoleMiddleware;
use Catalyst\Framework\Route\Router;
use Catalyst\Framework\View\View;
use Catalyst\Helpers\I18n\Translator;
use Catalyst\Repository\Audit\Controllers\AuditLogController;

$router = Router::getInstance();

View::getInstance()->addPath(
    'audit',
    implode(DS, [PD, 'Repository', 'Framework', 'Audit', 'Views'])
);

Translator::getInstance()->addPath(
    implode(DS, [PD, 'Repository', 'Framework', 'Audit', 'lang'])
);

$moduleMiddleware = [AuthMiddleware::class, new RoleMiddleware(permissions: 'manage-audit-log')];

$router->get('/audit-log', [AuditLogController::class, 'index'])
       ->middleware($moduleMiddleware);

$router->get('/audit-log/{id}', [AuditLogController::class, 'show'])
       ->middleware($moduleMiddleware);
