<?php

declare(strict_types=1);

namespace Catalyst\Framework\Middleware;

use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Catalyst\Framework\Session\SessionManager;
use Catalyst\Framework\Tenancy\TenancyManager;
use Closure;

final class TenancyContextMiddleware extends CoreMiddleware
{
    public function process(Request $request, Closure $next): Response
    {
        $context = TenancyManager::getInstance()->applyRequestContext($request);

        if (SessionManager::getInstance()->isInitialized()) {
            SessionManager::getInstance()
                ->set('_tenant_context', $context)
                ->set('_tenant_id', (int) ($context['tenant_id'] ?? 0))
                ->set('_tenant_key', (string) ($context['tenant_key'] ?? 'default'))
                ->set('_tenant_label', (string) ($context['tenant_label'] ?? 'Default tenant'));
        }

        return $this->passToNext($request, $next);
    }
}
