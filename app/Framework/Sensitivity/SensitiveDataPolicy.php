<?php

declare(strict_types=1);

namespace Catalyst\Framework\Sensitivity;

use Catalyst\Framework\Traits\SingletonTrait;
use Catalyst\Helpers\Security\SensitiveValueRedactor;

final class SensitiveDataPolicy
{
    use SingletonTrait;

    public const CHANNEL_API = 'api';
    public const CHANNEL_AUDIT = 'audit';
    public const CHANNEL_EXPORT = 'export';
    public const CHANNEL_FORM = 'form';
    public const CHANNEL_LOG = 'log';

    public const POLICY_PLAIN = 'plain';
    public const POLICY_MASKED = 'masked';
    public const POLICY_REDACTED = 'redacted';
    public const POLICY_RESTRICTED = 'restricted';

    private const MASKED = '[MASKED]';
    private const RESTRICTED = '[RESTRICTED]';

    private DataClassificationRegistry $registry;
    private SensitiveValueRedactor $redactor;

    protected function __construct()
    {
        $this->registry = DataClassificationRegistry::getInstance();
        $this->redactor = new SensitiveValueRedactor();
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function sanitize(?string $resourceKey, array $payload, string $channel): array
    {
        $sanitized = [];

        foreach ($payload as $key => $value) {
            $field = is_string($key) ? $key : (string) $key;
            $sanitized[$key] = $this->sanitizeField($resourceKey, $field, $value, $channel);
        }

        return $sanitized;
    }

    public function sanitizeField(?string $resourceKey, string $field, mixed $value, string $channel): mixed
    {
        $policy = $this->registry->policyFor($resourceKey, $field, $channel);

        if ($policy !== null) {
            return $this->applyPolicy($value, $policy);
        }

        if ($this->redactor->isSensitiveKey(strtolower($field))) {
            return SensitiveValueRedactor::REDACTED;
        }

        if (is_array($value)) {
            return $this->sanitize(null, $value, $channel);
        }

        return $value;
    }

    public function maskScalar(mixed $value): mixed
    {
        if ($value === null || is_bool($value) || is_int($value) || is_float($value)) {
            return $value;
        }

        if (!is_string($value)) {
            return self::MASKED;
        }

        $trimmed = trim($value);
        if ($trimmed === '') {
            return $value;
        }

        if (strlen($trimmed) <= 4) {
            return self::MASKED;
        }

        return substr($trimmed, 0, 2) . str_repeat('*', max(4, strlen($trimmed) - 4)) . substr($trimmed, -2);
    }

    private function applyPolicy(mixed $value, string $policy): mixed
    {
        return match ($policy) {
            self::POLICY_PLAIN => is_array($value) ? $this->sanitize(null, $value, self::CHANNEL_LOG) : $value,
            self::POLICY_MASKED => $this->maskValue($value),
            self::POLICY_RESTRICTED => self::RESTRICTED,
            default => SensitiveValueRedactor::REDACTED,
        };
    }

    private function maskValue(mixed $value): mixed
    {
        if (is_array($value)) {
            $masked = [];

            foreach ($value as $key => $nestedValue) {
                $field = is_string($key) ? $key : (string) $key;

                if ($this->redactor->isSensitiveKey(strtolower($field))) {
                    $masked[$key] = SensitiveValueRedactor::REDACTED;
                    continue;
                }

                $masked[$key] = is_array($nestedValue)
                    ? $this->maskValue($nestedValue)
                    : $this->maskScalar($nestedValue);
            }

            return $masked;
        }

        return $this->maskScalar($value);
    }
}
