<?php

declare(strict_types=1);

use App\Surface\Home\Controllers\HomeController;
use Catalyst\Framework\Route\Router;
use Catalyst\Framework\View\View;

$router = Router::getInstance();

View::getInstance()->addPath(
    'home',
    implode(DS, [PD, 'Repository', 'App', 'Surface', 'Home', 'Views'])
);

$router->get('/', [HomeController::class, 'root']);
$router->get('/home', [HomeController::class, 'index']);
$router->get('/api/public/home', [HomeController::class, 'api']);
