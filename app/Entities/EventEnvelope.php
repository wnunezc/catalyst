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

namespace Catalyst\Entities;

use DateTimeImmutable;
use DateTimeZone;

/**
 * Immutable payload container for framework events.
 *
 * @package Catalyst\Entities
 * Responsibility: Carries event name, payload, metadata, identifier, and occurrence timestamp across dispatch boundaries.
 */
final class EventEnvelope
{
    public readonly string $id;
    public readonly DateTimeImmutable $occurredAt;

    /**
     * Initializes an event envelope with generated identity and UTC timestamp defaults.
     *
     * Responsibility: Initializes an event envelope with generated identity and UTC timestamp defaults.
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $meta
     */
    public function __construct(
        public readonly string $name,
        public readonly array $payload = [],
        public readonly array $meta = [],
        ?string $id = null,
        ?DateTimeImmutable $occurredAt = null
    ) {
        $this->id = $id ?? bin2hex(random_bytes(16));
        $this->occurredAt = $occurredAt ?? new DateTimeImmutable('now', new DateTimeZone('UTC'));
    }

    /**
     * Creates a named event envelope from payload and metadata arrays.
     *
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $meta
     */
    public static function named(string $name, array $payload = [], array $meta = []): self
    {
        return new self($name, $payload, $meta);
    }

    /**
     * Serializes the event envelope for persistence or queue transport. id:string, name:string, payload:array<string, mixed>, meta:array<string, mixed>, occurred_at:string.
     *
     * Responsibility: Serializes the event envelope for persistence or queue transport. id:string, name:string, payload:array<string, mixed>, meta:array<string, mixed>, occurred_at:string.
     * @return array{
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'payload' => $this->payload,
            'meta' => $this->meta,
            'occurred_at' => $this->occurredAt->format(DATE_ATOM),
        ];
    }

    /**
     * Rehydrates an event envelope from serialized event data.
     *
     * @param array{
     *   id?:string,
     *   name:string,
     *   payload?:array<string, mixed>,
     *   meta?:array<string, mixed>,
     *   occurred_at?:string
     * } $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: (string) ($data['name'] ?? ''),
            payload: isset($data['payload']) && is_array($data['payload']) ? $data['payload'] : [],
            meta: isset($data['meta']) && is_array($data['meta']) ? $data['meta'] : [],
            id: isset($data['id']) ? (string) $data['id'] : null,
            occurredAt: isset($data['occurred_at']) && is_string($data['occurred_at'])
                ? new DateTimeImmutable($data['occurred_at'])
                : null
        );
    }
}
