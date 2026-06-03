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

use Catalyst\Framework\Tenancy\TenancyManager;
use InvalidArgumentException;

/**
 * Issues scoped transactional sequence numbers.
 *
 * @package Catalyst\Framework\Sequence
 * Responsibility: Validates sequence requests and delegates atomic increments to the configured sequence store.
 */
final class SequenceManager
{
    /**
     * Initializes the sequence manager with a storage boundary.
     *
     * Responsibility: Keeps sequence validation separate from the concrete counter store.
     */
    public function __construct(private readonly SequenceStoreInterface $store = new DatabaseSequenceStore())
    {
    }

    /**
     * Returns the next number for a tenant-scoped sequence.
     *
     * Responsibility: Validates scope, sequence and tenant context before requesting an atomic counter increment.
     */
    public function next(
        string $scopeKey,
        string $sequenceKey = 'default',
        ?int $tenantId = null,
        int $startAt = 1,
        int $step = 1
    ): int {
        $scopeKey = $this->normalizeKey($scopeKey, 'scope');
        $sequenceKey = $this->normalizeKey($sequenceKey, 'sequence');

        if ($startAt < 0) {
            throw new InvalidArgumentException('Sequence start value must be zero or greater.');
        }

        if ($step < 1) {
            throw new InvalidArgumentException('Sequence step must be greater than zero.');
        }

        $tenantId ??= TenancyManager::getInstance()->requireCurrentTenantId();

        return $this->store->next($tenantId, $scopeKey, $sequenceKey, $startAt, $step);
    }

    /**
     * Normalizes a sequence key segment.
     *
     * Responsibility: Enforces the key grammar used for portable sequence scopes.
     */
    private function normalizeKey(string $key, string $label): string
    {
        $key = trim(strtolower($key));
        if ($key === '' || strlen($key) > 190 || preg_match('/^[a-z0-9][a-z0-9._:-]*[a-z0-9]$/', $key) !== 1) {
            throw new InvalidArgumentException("Invalid {$label} sequence key.");
        }

        return $key;
    }
}