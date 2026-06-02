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

namespace Catalyst\Framework\Sensitivity;

use Catalyst\Framework\Traits\SingletonTrait;

/**
 * Defines the Data Classification Registry class contract.
 *
 * @package Catalyst\Framework\Sensitivity
 * Responsibility: Coordinates the data classification registry behavior within its module boundary.
 */
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

    /**
     * Handles the policy for workflow.
     */
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

    /**
     * Normalizes the provided value.
     */
    private function normalizeResourceKey(?string $value): string
    {
        return trim(str_replace('_', '-', strtolower((string) $value)));
    }

    /**
     * Normalizes the provided value.
     */
    private function normalizeField(?string $value): string
    {
        return trim(strtolower((string) $value));
    }
}
