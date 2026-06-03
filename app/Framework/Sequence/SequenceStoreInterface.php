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
 * Storage boundary for scoped sequence counters.
 *
 * @package Catalyst\Framework\Sequence
 * Responsibility: Defines the atomic operation required to advance a scoped sequence counter.
 */
interface SequenceStoreInterface
{
    /**
     * Atomically advances and returns the next sequence number.
     *
     * Responsibility: Owns concurrency-safe increment semantics for scoped sequence counters.
     */
    public function next(int $tenantId, string $scopeKey, string $sequenceKey, int $startAt = 1, int $step = 1): int;
}