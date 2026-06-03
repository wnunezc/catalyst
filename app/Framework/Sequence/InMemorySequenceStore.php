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

namespace Catalyst\Framework\Sequence;

/**
 * In-memory sequence store for smoke tests and non-persistent previews.
 *
 * @package Catalyst\Framework\Sequence
 * Responsibility: Provides deterministic scoped sequence increments without database persistence.
 */
final class InMemorySequenceStore implements SequenceStoreInterface
{
    /**
     * @var array<string, int>
     */
    private array $values = [];

    /**
     * Atomically advances and returns the next sequence number.
     *
     * Responsibility: Owns concurrency-safe increment semantics for scoped sequence counters.
     */
    public function next(int $tenantId, string $scopeKey, string $sequenceKey, int $startAt = 1, int $step = 1): int
    {
        $key = $tenantId . ':' . $scopeKey . ':' . $sequenceKey;
        $current = $this->values[$key] ?? ($startAt - $step);
        $next = $current + $step;
        $this->values[$key] = $next;

        return $next;
    }
}