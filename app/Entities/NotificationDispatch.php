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

/**
 * Defines the Notification Dispatch class contract.
 *
 * @package Catalyst\Entities
 * Responsibility: Coordinates the notification dispatch behavior within its module boundary.
 */
final class NotificationDispatch
{
    /**
     * @param array<string, mixed> $meta
     */
    public function __construct(
        public readonly int $userId,
        public readonly string $type,
        public readonly string $title,
        public readonly ?string $body = null,
        public readonly array $meta = []
    ) {
    }

    /**
     * @return array{
     *   user_id:int,
     *   type:string,
     *   title:string,
     *   body:?string,
     *   meta:array<string, mixed>
     * }
     */
    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'type' => $this->type,
            'title' => $this->title,
            'body' => $this->body,
            'meta' => $this->meta,
        ];
    }

    /**
     * @param array{
     *   user_id?:int|string,
     *   type?:string,
     *   title?:string,
     *   body?:?string,
     *   meta?:array<string, mixed>
     * } $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            userId: (int) ($data['user_id'] ?? 0),
            type: trim((string) ($data['type'] ?? 'info')) ?: 'info',
            title: (string) ($data['title'] ?? ''),
            body: isset($data['body']) ? (string) $data['body'] : null,
            meta: isset($data['meta']) && is_array($data['meta']) ? $data['meta'] : []
        );
    }
}
