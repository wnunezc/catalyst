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

namespace Catalyst\Framework\Deletion;

/**
 * Represents one planned reverse cascade delete operation.
 *
 * @package Catalyst\Framework\Deletion
 * Responsibility: Carries one dependent delete, detach, archive or blocking operation in a safe delete plan.
 */
final readonly class ReverseCascadeDeleteStep
{
    /**
     * Stores one dependent delete operation with its action and metadata.
     *
     * Responsibility: Represents a single planned dependency operation in the reverse cascade contract.
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        private string $resourceKey,
        private string|int $recordId,
        private string $action,
        private string $label = '',
        private bool $blocking = false,
        private array $metadata = []
    ) {
    }

    /**
     * Builds a step from a dependency record array.
     *
     * Responsibility: Normalizes app-provided dependency records into typed delete steps.
     * @param array<string, mixed> $record
     */
    public static function fromRecord(string $resourceKey, string $action, array $record, bool $blocking = false): self
    {
        return new self(
            $resourceKey,
            $record['id'] ?? $record['record_id'] ?? '',
            $action,
            (string)($record['label'] ?? $record['name'] ?? ''),
            $blocking,
            array_diff_key($record, array_flip(['id', 'record_id', 'label', 'name']))
        );
    }

    /**
     * Returns the dependent resource key.
     *
     * Responsibility: Exposes the resource identifier used for routing the planned operation.
     */
    public function resourceKey(): string
    {
        return $this->resourceKey;
    }

    /**
     * Returns the dependent record identifier.
     *
     * Responsibility: Exposes the target record identifier for the planned dependency operation.
     */
    public function recordId(): string|int
    {
        return $this->recordId;
    }

    /**
     * Returns the planned action.
     *
     * Responsibility: Exposes the normalized action that the execution handler must apply.
     */
    public function action(): string
    {
        return $this->action;
    }

    /**
     * Reports whether this step blocks execution.
     *
     * Responsibility: Marks dependency records that must be resolved manually before deletion can continue.
     */
    public function isBlocking(): bool
    {
        return $this->blocking;
    }

    /**
     * Exports the step for CLI, logs or API responses.
     *
     * Responsibility: Serializes dependency operation details for review and audit trails.
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'resource_key' => $this->resourceKey,
            'record_id' => $this->recordId,
            'action' => $this->action,
            'label' => $this->label,
            'blocking' => $this->blocking,
            'metadata' => $this->metadata,
        ];
    }
}