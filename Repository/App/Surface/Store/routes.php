<?php

declare(strict_types=1);

use App\Surface\Store\Controllers\StoreController;
use Catalyst\Framework\Route\Router;
use Catalyst\Framework\View\View;

$router = Router::getInstance();

View::getInstance()->addPath(
    'store',
    implode(DS, [PD, 'Repository', 'App', 'Surface', 'Store', 'Views'])
);

$router->get('/store', [StoreController::class, 'index']);
$router->get('/api/public/store', [StoreController::class, 'api']);
