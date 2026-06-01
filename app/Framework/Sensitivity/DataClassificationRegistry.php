<?php

declare(strict_types=1);

namespace Catalyst\Framework\Sensitivity;

use Catalyst\Framework\Traits\SingletonTrait;

final class DataClassificationRegistry
{
    use SingletonTrait;

    /**
     * @var array<string, array<string, array<string, string>>>
     */
    private const DEFINITIONS = [
        'automation-rules' => [
            'condition_json' => [
                SensitiveDataPolicy::CHANNEL_API => SensitiveDataPolicy::POLICY_MASKED,
                SensitiveDataPolicy::CHANNEL_AUDIT => SensitiveDataPolicy::POLICY_MASKED,
                SensitiveDataPolicy::CHANNEL_EXPORT => SensitiveDataPolicy::POLICY_MASKED,
                SensitiveDataPolicy::CHANNEL_LOG => SensitiveDataPolicy::POLICY_MASKED,
            ],
            'action_payload_json' => [
                SensitiveDataPolicy::CHANNEL_API => SensitiveDataPolicy::POLICY_MASKED,
                SensitiveDataPolicy::CHANNEL_AUDIT => SensitiveDataPolicy::POLICY_MASKED,
                SensitiveDataPolicy::CHANNEL_EXPORT => SensitiveDataPolicy::POLICY_MASKED,
                SensitiveDataPolicy::CHANNEL_LOG => SensitiveDataPolicy::POLICY_MASKED,
            ],
        ],
        'document-templates' => [
            'sample_payload_json' => [
                SensitiveDataPolicy::CHANNEL_API => SensitiveDataPolicy::POLICY_MASKED,
                SensitiveDataPolicy::CHANNEL_AUDIT => SensitiveDataPolicy::POLICY_MASKED,
                SensitiveDataPolicy::CHANNEL_EXPORT => SensitiveDataPolicy::POLICY_MASKED,
                SensitiveDataPolicy::CHANNEL_LOG => SensitiveDataPolicy::POLICY_MASKED,
            ],
        ],
        'document-artifacts' => [
            'payload_snapshot_json' => [
                SensitiveDataPolicy::CHANNEL_API => SensitiveDataPolicy::POLICY_MASKED,
                SensitiveDataPolicy::CHANNEL_AUDIT => SensitiveDataPolicy::POLICY_MASKED,
                SensitiveDataPolicy::CHANNEL_EXPORT => SensitiveDataPolicy::POLICY_MASKED,
                SensitiveDataPolicy::CHANNEL_LOG => SensitiveDataPolicy::POLICY_MASKED,
            ],
            'rendered_content' => [
                SensitiveDataPolicy::CHANNEL_API => SensitiveDataPolicy::POLICY_RESTRICTED,
                SensitiveDataPolicy::CHANNEL_AUDIT => SensitiveDataPolicy::POLICY_RESTRICTED,
                SensitiveDataPolicy::CHANNEL_EXPORT => SensitiveDataPolicy::POLICY_RESTRICTED,
                SensitiveDataPolicy::CHANNEL_LOG => SensitiveDataPolicy::POLICY_RESTRICTED,
            ],
        ],
        'automation-execution-logs' => [
            'context_json' => [
                SensitiveDataPolicy::CHANNEL_API => SensitiveDataPolicy::POLICY_MASKED,
                SensitiveDataPolicy::CHANNEL_AUDIT => SensitiveDataPolicy::POLICY_MASKED,
                SensitiveDataPolicy::CHANNEL_EXPORT => SensitiveDataPolicy::POLICY_MASKED,
                SensitiveDataPolicy::CHANNEL_LOG => SensitiveDataPolicy::POLICY_MASKED,
            ],
            'result_json' => [
                SensitiveDataPolicy::CHANNEL_API => SensitiveDataPolicy::POLICY_MASKED,
                SensitiveDataPolicy::CHANNEL_AUDIT => SensitiveDataPolicy::POLICY_MASKED,
                SensitiveDataPolicy::CHANNEL_EXPORT => SensitiveDataPolicy::POLICY_MASKED,
                SensitiveDataPolicy::CHANNEL_LOG => SensitiveDataPolicy::POLICY_MASKED,
            ],
        ],
    ];

    public function policyFor(?string $resourceKey, string $field, string $channel): ?string
    {
        $resourceKey = $this->normalizeResourceKey($resourceKey);
        $field = $this->normalizeField($field);

        if ($resourceKey === '' || $field === '') {
            return null;
        }

        $definition = self::DEFINITIONS[$resourceKey][$field] ?? null;
        if (!is_array($definition)) {
            return null;
        }

        return $definition[$channel] ?? null;
    }

    private function normalizeResourceKey(?string $value): string
    {
        return trim(str_replace('_', '-', strtolower((string) $value)));
    }

    private function normalizeField(?string $value): string
    {
        return trim(strtolower((string) $value));
    }
}
