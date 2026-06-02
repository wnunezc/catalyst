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
