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

namespace Catalyst\Framework\Temporal;

use Catalyst\Framework\Traits\SingletonTrait;
use DateTimeImmutable;
use DateTimeZone;

/**
 * Defines the Effective Window class contract.
 *
 * @package Catalyst\Framework\Temporal
 * Responsibility: Coordinates the effective window behavior within its module boundary.
 */
final class EffectiveWindow
{
    use SingletonTrait;

    public const STATE_ACTIVE = 'active';
    public const STATE_SCHEDULED = 'scheduled';
    public const STATE_EXPIRED = 'expired';

    /**
     * Normalizes the provided value.
     */
    public function normalize(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $timestamp = strtotime($value);

        return $timestamp === false ? null : gmdate('Y-m-d H:i:s', $timestamp);
    }

    /**
     * Handles the state workflow.
     */
    public function state(?string $validFrom, ?string $validTo, ?DateTimeImmutable $now = null): string
    {
        $now = $now ?? new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $from = $this->dateTime($validFrom);
        $to = $this->dateTime($validTo);

        if ($from !== null && $from > $now) {
            return self::STATE_SCHEDULED;
        }

        if ($to !== null && $to <= $now) {
            return self::STATE_EXPIRED;
        }

        return self::STATE_ACTIVE;
    }

    /**
     * Determines whether is Active.
     */
    public function isActive(?string $validFrom, ?string $validTo, ?DateTimeImmutable $now = null): bool
    {
        return $this->state($validFrom, $validTo, $now) === self::STATE_ACTIVE;
    }

    /**
     * Handles the sql for state workflow.
     */
    public function sqlForState(string $state, string $fromColumn = 'valid_from', string $toColumn = 'valid_to'): string
    {
        $from = $this->quote($fromColumn);
        $to = $this->quote($toColumn);

        return match ($state) {
            self::STATE_SCHEDULED => sprintf('(%s IS NOT NULL AND %s > UTC_TIMESTAMP())', $from, $from),
            self::STATE_EXPIRED => sprintf('(%s IS NOT NULL AND %s <= UTC_TIMESTAMP())', $to, $to),
            default => sprintf(
                '((%s IS NULL OR %s <= UTC_TIMESTAMP()) AND (%s IS NULL OR %s > UTC_TIMESTAMP()))',
                $from,
                $from,
                $to,
                $to
            ),
        };
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    public function decorate(array $row, string $fromKey = 'valid_from', string $toKey = 'valid_to'): array
    {
        $row['temporal_state'] = $this->state(
            isset($row[$fromKey]) ? (string) $row[$fromKey] : null,
            isset($row[$toKey]) ? (string) $row[$toKey] : null
        );

        return $row;
    }

    /**
     * Handles the date time workflow.
     */
    private function dateTime(?string $value): ?DateTimeImmutable
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        try {
            return new DateTimeImmutable($value, new DateTimeZone('UTC'));
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Handles the quote workflow.
     */
    private function quote(string $identifier): string
    {
        $parts = array_values(array_filter(array_map('trim', explode('.', trim($identifier))), static fn (string $part): bool => $part !== ''));

        if ($parts === []) {
            return '``';
        }

        return implode('.', array_map(
            static fn (string $part): string => '`' . str_replace('`', '``', $part) . '`',
            $parts
        ));
    }
}
