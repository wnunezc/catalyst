<?php

declare(strict_types=1);

namespace Catalyst\Framework\Middleware;

use Catalyst\Framework\Http\RedirectResponse;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Catalyst\Framework\Route\CanonicalPathRedirector;
use Closure;

final class CanonicalPathRedirectMiddleware extends CoreMiddleware
{
    public function process(Request $request, Closure $next): Response
    {
        $target = (new CanonicalPathRedirector())->redirectTarget($request->getUri());

        if ($target !== null) {
            $status = in_array(strtoupper($request->getMethod()), ['GET', 'HEAD'], true)
                ? 301
                : 308;

            return new RedirectResponse($target, $status);
        }

        return $this->passToNext($request, $next);
    }
}
