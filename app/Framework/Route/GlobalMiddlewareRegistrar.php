<?php

declare(strict_types=1);

namespace Catalyst\Framework\Route;

use Catalyst\Framework\Middleware\CanonicalPathRedirectMiddleware;
use Catalyst\Framework\Middleware\CorsMiddleware;
use Catalyst\Framework\Middleware\CsrfMiddleware;
use Catalyst\Framework\Middleware\RequestThrottlingMiddleware;
use Catalyst\Framework\Middleware\SetupMiddleware;
use Catalyst\Framework\Middleware\TenancyContextMiddleware;
use Catalyst\Framework\Middleware\WebSocketBootMiddleware;

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

    public function register(Router $router): void
    {
        foreach ($this->middleware() as $middleware) {
            $router->addMiddleware($middleware);
        }
    }
}
