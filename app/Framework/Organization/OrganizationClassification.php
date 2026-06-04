<?php

declare(strict_types=1);

namespace Catalyst\Framework\Organization;

use InvalidArgumentException;

/**
 * Immutable organization classification value object.
 *
 * @package Catalyst\Framework\Organization
 * Responsibility: Normalizes hierarchy scope, level, unit, resource and visual metadata without performing authorization or persistence.
 */
final readonly class OrganizationClassification
{
    /**
     * Initializes an organization classification record.
     *
     * Responsibility: Stores already-normalized classification fields for transport to presenters and consumers.
     */
    public function __construct(
        public string $resourceKey,
        public string $recordId,
        public string $organizationSlug,
        public string $scopeKey,
        public string $scopeLabel,
        public string $levelCode,
        public string $levelLabel,
        public int $levelOrder,
        public ?string $unitCode = null,
        public ?string $unitLabel = null,
        public ?string $visualToken = null,
        public ?string $color = null
    ) {
        if ($this->resourceKey === '' || $this->recordId === '' || $this->scopeKey === '' || $this->levelCode === '') {
            throw new InvalidArgumentException('Organization classifications require resource, record, scope and level identifiers.');
        }
    }

    /**
     * Creates a classification from array data.
     *
     * Responsibility: Validates and normalizes untrusted service/repository payloads before presentation.
     * @param array<string, mixed> $payload
     */
    public static function fromArray(array $payload): self
    {
        return new self(
            self::normalizeKey((string)($payload['resource_key'] ?? '')),
            self::normalizeRecordId((string)($payload['record_id'] ?? '')),
            self::normalizeKey((string)($payload['organization_slug'] ?? 'default')),
            self::normalizeKey((string)($payload['scope_key'] ?? '')),
            trim((string)($payload['scope_label'] ?? $payload['scope_key'] ?? '')),
            self::normalizeKey((string)($payload['level_code'] ?? '')),
            trim((string)($payload['level_label'] ?? $payload['level_code'] ?? '')),
            (int)($payload['level_order'] ?? 0),
            self::nullableKey($payload['unit_code'] ?? null),
            self::nullableLabel($payload['unit_label'] ?? null),
            self::nullableToken($payload['visual_token'] ?? null),
            self::nullableColor($payload['color'] ?? null)
        );
    }

    /**
     * Exports the classification to a stable array payload.
     *
     * Responsibility: Provides a framework-neutral structure for profiles, roles, courses and future catalogs.
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'resource_key' => $this->resourceKey,
            'record_id' => $this->recordId,
            'organization_slug' => $this->organizationSlug,
            'scope_key' => $this->scopeKey,
            'scope_label' => $this->scopeLabel,
            'level_code' => $this->levelCode,
            'level_label' => $this->levelLabel,
            'level_order' => $this->levelOrder,
            'unit_code' => $this->unitCode,
            'unit_label' => $this->unitLabel,
            'visual_token' => $this->visualToken,
            'color' => $this->color,
        ];
    }

    /**
     * Normalizes framework resource and hierarchy keys.
     *
     * Responsibility: Keeps classification identifiers safe for storage, CSS-token derivation and lookups.
     */
    private static function normalizeKey(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9_.-]+/', '-', $value) ?? '';
        $value = trim($value, '.-');

        if ($value === '') {
            throw new InvalidArgumentException('Organization classification key cannot be empty.');
        }

        return $value;
    }

    /**
     * Normalizes record identifiers without constraining them to numeric IDs.
     *
     * Responsibility: Supports current integer IDs and future external catalog identifiers.
     */
    private static function normalizeRecordId(string $value): string
    {
        $value = trim($value);
        if ($value === '' || preg_match('/^[A-Za-z0-9_.:-]+$/', $value) !== 1) {
            throw new InvalidArgumentException('Organization classification record id is invalid.');
        }

        return $value;
    }

    /**
     * Normalizes an optional key value.
     *
     * Responsibility: Accepts absent unit metadata while preserving safe keys when supplied.
     */
    private static function nullableKey(mixed $value): ?string
    {
        $value = trim((string)($value ?? ''));

        return $value === '' ? null : self::normalizeKey($value);
    }

    /**
     * Normalizes optional display labels.
     *
     * Responsibility: Keeps empty optional labels represented as null.
     */
    private static function nullableLabel(mixed $value): ?string
    {
        $value = trim((string)($value ?? ''));

        return $value === '' ? null : $value;
    }

    /**
     * Normalizes optional visual tokens.
     *
     * Responsibility: Restricts token values to stable CSS modifier-safe names.
     */
    private static function nullableToken(mixed $value): ?string
    {
        $value = trim((string)($value ?? ''));

        if ($value === '') {
            return null;
        }

        $value = strtolower($value);
        $value = preg_replace('/[^a-z0-9-]+/', '-', $value) ?? '';
        $value = trim($value, '-');

        return $value === '' ? null : $value;
    }

    /**
     * Normalizes optional hex colors.
     *
     * Responsibility: Rejects non-hex color input so presenters can safely emit inline custom properties.
     */
    private static function nullableColor(mixed $value): ?string
    {
        $value = trim((string)($value ?? ''));

        if ($value === '') {
            return null;
        }

        if (preg_match('/^#[0-9A-Fa-f]{6}$/', $value) !== 1) {
            throw new InvalidArgumentException('Organization classification color must be a six-digit hex value.');
        }

        return strtolower($value);
    }
}
