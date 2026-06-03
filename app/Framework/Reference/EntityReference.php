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

namespace Catalyst\Framework\Reference;

use InvalidArgumentException;

/**
 * Represents a generic typed reference to a domain record.
 *
 * @package Catalyst\Framework\Reference
 * Responsibility: Normalizes resource key, record id, label and metadata for cross-module entity references.
 */
final readonly class EntityReference
{
    /**
     * Stores a normalized entity reference.
     *
     * Responsibility: Captures resource and record identity for generic cross-feature references.
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        private string $resourceKey,
        private int|string $recordId,
        private string $label = '',
        private array $metadata = []
    ) {
        if (!self::isValidResourceKey($this->resourceKey)) {
            throw new InvalidArgumentException('Entity reference resource key is invalid.');
        }

        if (!self::isValidRecordId($this->recordId)) {
            throw new InvalidArgumentException('Entity reference record id is invalid.');
        }
    }

    /**
     * Builds a reference from a payload array.
     *
     * Responsibility: Validates incoming reference payloads before they enter framework contracts.
     * @param array<string, mixed> $payload
     */
    public static function fromArray(array $payload): self
    {
        return new self(
            (string)($payload['resource_key'] ?? $payload['type'] ?? ''),
            $payload['record_id'] ?? $payload['id'] ?? '',
            (string)($payload['label'] ?? ''),
            is_array($payload['metadata'] ?? null) ? $payload['metadata'] : []
        );
    }

    /**
     * Returns the canonical referenced resource key.
     *
     * Responsibility: Exposes the normalized resource namespace for persistence and authorization checks.
     */
    public function resourceKey(): string
    {
        return $this->resourceKey;
    }

    /**
     * Returns the referenced record identifier.
     *
     * Responsibility: Exposes the target record id without coupling to a concrete table.
     */
    public function recordId(): int|string
    {
        return $this->recordId;
    }

    /**
     * Exports the reference for persistence, APIs or audit metadata.
     *
     * Responsibility: Serializes generic references in a stable shape shared by framework services.
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'resource_key' => $this->resourceKey,
            'record_id' => $this->recordId,
            'label' => $this->label,
            'metadata' => $this->metadata,
        ];
    }

    /**
     * Validates a canonical resource key.
     *
     * Responsibility: Enforces the portable resource-key grammar used by registries and references.
     */
    public static function isValidResourceKey(string $resourceKey): bool
    {
        return preg_match('/^[a-z0-9][a-z0-9._-]{1,126}[a-z0-9]$/', $resourceKey) === 1;
    }

    /**
     * Validates a record identifier used by a generic reference.
     *
     * Responsibility: Rejects empty or oversized identifiers before they reach persistence boundaries.
     */
    public static function isValidRecordId(int|string $recordId): bool
    {
        if (is_int($recordId)) {
            return $recordId > 0;
        }

        $recordId = trim($recordId);
        return $recordId !== '' && strlen($recordId) <= 128 && preg_match('/^[A-Za-z0-9._:-]+$/', $recordId) === 1;
    }
}