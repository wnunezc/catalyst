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

use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Catalyst\Framework\Session\SessionManager;
use Catalyst\Framework\Tenancy\TenancyManager;
use Closure;

/**
 * Defines the Tenancy Context Middleware class contract.
 *
 * @package Catalyst\Framework\Middleware
 * Responsibility: Coordinates the tenancy context middleware behavior within its module boundary.
 */
final class TenancyContextMiddleware extends CoreMiddleware
{
    /**
     * Processes the current workflow.
     */
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
