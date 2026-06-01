<?php

declare(strict_types=1);

use App\Surface\Landing\Controllers\LandingController;
use Catalyst\Framework\Route\Router;
use Catalyst\Framework\View\View;

$router = Router::getInstance();

View::getInstance()->addPath(
    'landing',
    implode(DS, [PD, 'Repository', 'App', 'Surface', 'Landing', 'Views'])
);

$router->get('/landing', [LandingController::class, 'index']);
$router->get('/api/public/landing', [LandingController::class, 'api']);
