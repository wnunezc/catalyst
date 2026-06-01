<?php

declare(strict_types=1);

/**
 * Catalyst PHP Framework
 * PHP Version 8.4 (Required)
 *
 * Global Route Definitions
 *
 * Defines global middleware and canonical redirect routes.
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

use Catalyst\Framework\Authorization\Gate;
use Catalyst\Framework\Authorization\PermissionRegistry;
use Catalyst\Framework\Controllers\FlashController;
use Catalyst\Framework\Middleware\CanonicalPathRedirectMiddleware;
use Catalyst\Framework\Route\Router;
use Catalyst\Framework\Middleware\CorsMiddleware;
use Catalyst\Framework\Middleware\CsrfMiddleware;
use Catalyst\Framework\Middleware\RequestThrottlingMiddleware;
use Catalyst\Framework\Middleware\SetupMiddleware;
use Catalyst\Framework\Middleware\TenancyContextMiddleware;
use Catalyst\Framework\Middleware\WebSocketBootMiddleware;
use Catalyst\Repository\DevTools\Controllers\RouteTestController;

$router = Router::getInstance();

/**
 * Global middleware — applied to every route in every dispatch.
 * SecurityHeadersMiddleware is applied at Kernel level (wraps all responses including errors).
 * CorsMiddleware runs first: answers OPTIONS preflight before CSRF can block it.
 * CsrfMiddleware validates POST/PUT/DELETE/PATCH; self-skips GET and static resources.
 * WebSocketBootMiddleware ensures the Ratchet WS server is running (throttled to every 30 s).
 */
$router->addMiddleware(CorsMiddleware::class);
$router->addMiddleware(CanonicalPathRedirectMiddleware::class);
$router->addMiddleware(WebSocketBootMiddleware::class);
$router->addMiddleware(TenancyContextMiddleware::class);
$router->addMiddleware(SetupMiddleware::class);
$router->addMiddleware(RequestThrottlingMiddleware::class);
$router->addMiddleware(CsrfMiddleware::class);

/**
 * Gate definitions — registered once per request lifecycle.
 *
 * Use Gate::define() for cross-cutting authorization rules.
 * Model-specific logic belongs in Policy classes (registered via Gate::policy()).
 */
$gate = Gate::getInstance();
PermissionRegistry::getInstance()->registerGateDefinitions($gate);

/**
 * Canonical redirects — normalize common entry-point aliases to root.
 * Using a controller method (not a Closure) so these routes are cacheable.
 */
$router->get('/index', [RouteTestController::class, 'redirectToRoot']);
$router->get('/index.php', [RouteTestController::class, 'redirectToRoot']);

// Framework-level flash actions.
$router->post('/flash/dismiss', [FlashController::class, 'dismiss']);
