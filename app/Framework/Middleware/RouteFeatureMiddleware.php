<?php

declare(strict_types=1);

namespace Catalyst\Framework\Middleware;

use Catalyst\Framework\FeatureFlag\FeatureFlagManager;
use Catalyst\Framework\Http\JsonResponse;
use Catalyst\Framework\Http\RedirectResponse;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Catalyst\Helpers\Log\Logger;
use Closure;

final class RouteFeatureMiddleware extends CoreMiddleware
{
    public function __construct(
        private readonly string $flagKey,
        private readonly ?string $redirectTo = null
    ) {
        parent::__construct();
    }

    public function flagKey(): string
    {
        return $this->flagKey;
    }

    public function redirectTo(): ?string
    {
        return $this->redirectTo;
    }

    public function __serialize(): array
    {
        return [
            'flagKey' => $this->flagKey,
            'redirectTo' => $this->redirectTo,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->flagKey = (string)($data['flagKey'] ?? '');
        $this->redirectTo = isset($data['redirectTo']) ? (string)$data['redirectTo'] : null;
        $this->logger = Logger::getInstance();
    }

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
