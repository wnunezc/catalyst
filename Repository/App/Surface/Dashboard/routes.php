<?php

declare(strict_types=1);

use App\Surface\Dashboard\Controllers\DashboardController;
use Catalyst\Framework\Middleware\AuthMiddleware;
use Catalyst\Framework\Route\Router;
use Catalyst\Framework\View\View;
use Catalyst\Helpers\I18n\Translator;

$router = Router::getInstance();

View::getInstance()->addPath(
    'dashboard',
    implode(DS, [PD, 'Repository', 'App', 'Surface', 'Dashboard', 'Views'])
);

Translator::getInstance()->addPath(
    implode(DS, [PD, 'Repository', 'App', 'Surface', 'Dashboard', 'lang'])
);

$router->get('/dashboard', [DashboardController::class, 'index']);
$router->get('/api/public/dashboard', [DashboardController::class, 'api'])
       ->middleware(AuthMiddleware::class);
