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

namespace Catalyst\Framework\Route;

use Catalyst\Framework\Middleware\CanonicalPathRedirectMiddleware;
use Catalyst\Framework\Middleware\CorsMiddleware;
use Catalyst\Framework\Middleware\CsrfMiddleware;
use Catalyst\Framework\Middleware\RequestThrottlingMiddleware;
use Catalyst\Framework\Middleware\SetupMiddleware;
use Catalyst\Framework\Middleware\TenancyContextMiddleware;
use Catalyst\Framework\Middleware\WebSocketBootMiddleware;

/**
 * Defines the Global Middleware Registrar class contract.
 *
 * @package Catalyst\Framework\Route
 * Responsibility: Coordinates the global middleware registrar behavior within its module boundary.
 */
final class GlobalMiddlewareRegistrar
{
    /**
     * @return list<class-string>
     */
    public function middleware(): array
    {
        return [
            CorsMiddleware::class,
            CanonicalPathRedirectMiddleware::class,
            WebSocketBootMiddleware::class,
            TenancyContextMiddleware::class,
            SetupMiddleware::class,
            RequestThrottlingMiddleware::class,
            CsrfMiddleware::class,
        ];
    }

    /**
     * Registers the requested definition.
     */
    public function register(Router $router): void
    {
        foreach ($this->middleware() as $middleware) {
            $router->addMiddleware($middleware);
        }
    }
}
