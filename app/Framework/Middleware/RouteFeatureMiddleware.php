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

use Catalyst\Framework\FeatureFlag\FeatureFlagManager;
use Catalyst\Framework\Http\JsonResponse;
use Catalyst\Framework\Http\RedirectResponse;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Catalyst\Helpers\Log\Logger;
use Closure;

/**
 * Defines the Route Feature Middleware class contract.
 *
 * @package Catalyst\Framework\Middleware
 * Responsibility: Coordinates the route feature middleware behavior within its module boundary.
 */
final class RouteFeatureMiddleware extends CoreMiddleware
{
    /**
     * Initializes the Route Feature Middleware instance.
     */
    public function __construct(
        private readonly string $flagKey,
        private readonly ?string $redirectTo = null
    ) {
        parent::__construct();
    }

    /**
     * Handles the flag key workflow.
     */
    public function flagKey(): string
    {
        return $this->flagKey;
    }

    /**
     * Handles the redirect to workflow.
     */
    public function redirectTo(): ?string
    {
        return $this->redirectTo;
    }

    /**
     * Handles the serialize workflow.
     */
    public function __serialize(): array
    {
        return [
            'flagKey' => $this->flagKey,
            'redirectTo' => $this->redirectTo,
        ];
    }

    /**
     * Handles the unserialize workflow.
     */
    public function __unserialize(array $data): void
    {
        $this->flagKey = (string)($data['flagKey'] ?? '');
        $this->redirectTo = isset($data['redirectTo']) ? (string)$data['redirectTo'] : null;
        $this->logger = Logger::getInstance();
    }

    /**
     * Processes the current workflow.
     */
    public function process(Request $request, Closure $next): Response
    {
        if (FeatureFlagManager::getInstance()->isEnabledForCurrentUser($this->flagKey)) {
            return $this->passToNext($request, $next);
        }

        $this->log('Feature-flagged route blocked', [
            'flag' => $this->flagKey,
            'uri' => $request->getUri(),
        ]);

        if ($this->expectsJson($request)) {
            return new JsonResponse([
                'error' => 'feature-disabled',
                'message' => sprintf('Feature "%s" is disabled.', $this->flagKey),
            ], 404);
        }

        if ($this->redirectTo !== null && trim($this->redirectTo) !== '') {
            return new RedirectResponse($this->redirectTo);
        }

        return new Response('Feature unavailable.', 404);
    }
}
