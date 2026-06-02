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

namespace Catalyst\Framework\Middleware;

use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Framework\Http\RedirectResponse;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\RedirectTarget;
use Catalyst\Framework\Http\Response;
use Closure;

/**************************************************************************************
 * AuthMiddleware — session-based route guard
 *
 * Order of checks:
 *   1. AuthManager::check() — active session exists
 *   2. AuthManager::loginFromRemember() — valid remember-me cookie
 *   3. Redirect to /login (HTML) or 401 JSON (API)
 *
 * Usage in routes.php:
 *   $router->get('/dashboard', [DashboardController::class, 'index'])
 *          ->middleware(AuthMiddleware::class);
 *
 * Usage with route groups:
 *   $router->group(['middleware' => AuthMiddleware::class], function($router) {
 *       $router->get('/dashboard', [...]);
 *   });
 *
 * @package Catalyst\Framework\Middleware
 */
/**
 * Defines the Auth Middleware class contract.
 *
 * @package Catalyst\Framework\Middleware
 * Responsibility: Coordinates the auth middleware behavior within its module boundary.
 */
class AuthMiddleware extends CoreMiddleware
{
    /**
     * @inheritDoc
     */
    public function process(Request $request, Closure $next): Response
    {
        $auth = AuthManager::getInstance();

        if ($auth->check()) {
            return $this->passToNext($request, $next);
        }

        if ($auth->loginFromRemember()) {
            $this->log('Auth: session restored from remember-me token');
            return $this->passToNext($request, $next);
        }

        $this->log('Auth: unauthenticated request blocked', ['uri' => $request->getUri()]);

        if ($this->expectsJson($request)) {
            $response = new Response();
            $response->setStatusCode(401);
            $response->setHeader('Content-Type', 'application/json');
            $response->setContent(json_encode(['error' => 'Unauthenticated', 'message' => 'Login required.']));
            return $response;
        }

        return new RedirectResponse(RedirectTarget::loginUrl($request->getUri()));
    }
}
