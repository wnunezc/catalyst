<?php

declare(strict_types=1);

namespace Catalyst\Helpers\Log;

use Catalyst\Framework\Sensitivity\SensitiveDataPolicy;
use Catalyst\Helpers\Security\SensitiveValueRedactor;

final class LoggerContextSanitizer
{
    private SensitiveDataPolicy $policy;

    public function __construct(
        private readonly SensitiveValueRedactor $redactor = new SensitiveValueRedactor()
    ) {
        $this->policy = SensitiveDataPolicy::getInstance();
    }

    public function sanitize(array $context): array
    {
        $resourceKey = null;

        foreach (['resource_key', 'resource'] as $candidate) {
            if (isset($context[$candidate]) && is_string($context[$candidate]) && trim($context[$candidate]) !== '') {
                $resourceKey = trim((string) $context[$candidate]);
                break;
            }
        }

        foreach (['payload', 'context', 'result', 'before', 'after'] as $field) {
            if (isset($context[$field]) && is_array($context[$field])) {
                $context[$field] = $this->policy->sanitize(
                    $resourceKey,
                    $context[$field],
                    SensitiveDataPolicy::CHANNEL_LOG
                );
            }
        }

        return $this->policy->sanitize($resourceKey, $context, SensitiveDataPolicy::CHANNEL_LOG);
    }
}
