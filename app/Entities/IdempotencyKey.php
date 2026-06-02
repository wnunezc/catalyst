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

use Catalyst\Framework\Database\Model;
use Catalyst\Framework\Traits\BelongsToTenantTrait;
use Catalyst\Framework\Traits\HasTimestampsTrait;

/**
 * Defines the Idempotency Key class contract.
 *
 * @package Catalyst\Entities
 * Responsibility: Coordinates the idempotency key behavior within its module boundary.
 */
final class IdempotencyKey extends Model
{
    use BelongsToTenantTrait;
    use HasTimestampsTrait;

    protected static string $table = 'idempotency_keys';

    protected static array $fillable = [
        'tenant_id',
        'scope_key',
        'idempotency_key',
        'fingerprint_hash',
        'status',
        'outcome_json',
        'created_at',
        'updated_at',
        'completed_at',
    ];

    protected static array $casts = [
        'tenant_id' => 'int',
        'outcome_json' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'completed_at' => 'datetime',
    ];
}
