<?php

declare(strict_types=1);

/**
 * Catalyst PHP Framework
 * PHP Version 8.4 (Required)
 *
 * Global Route Definitions
 *
 * Defines canonical redirect routes and framework-level actions.
 * Only framework-wide concerns belong here.
 * Module-specific routes live in each module's own routes.php file,
 * loaded automatically by Kernel::loadRoutes() via glob.
 *
 * @package   Catalyst
 * @author    Walter Nuñez (arcanisgk) <icarosnet@gmail.com>
 * @copyright 2023 - 2025
 * @license   http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 * @link      https://catalyst.dock Local development URL
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
