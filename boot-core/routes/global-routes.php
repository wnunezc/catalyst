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

use Catalyst\Framework\Controllers\CanonicalRedirectController;
use Catalyst\Framework\Controllers\FlashController;
use Catalyst\Framework\Route\Router;

$router = Router::getInstance();

/**
 * Canonical redirects — normalize common entry-point aliases to root.
 * Using a controller method (not a Closure) so these routes are cacheable.
 */
$router->get('/index', [CanonicalRedirectController::class, 'root']);
$router->get('/index.php', [CanonicalRedirectController::class, 'root']);

// Framework-level flash actions.
$router->post('/flash/dismiss', [FlashController::class, 'dismiss']);
