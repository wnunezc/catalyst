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
use Catalyst\Framework\Traits\HasAuditLogTrait;
use Catalyst\Framework\Traits\HasTimestampsTrait;

/**
 * Defines the Api Token class contract.
 *
 * @package Catalyst\Entities
 * Responsibility: Coordinates the api token behavior within its module boundary.
 */
final class ApiToken extends Model
{
    use BelongsToTenantTrait;
    use HasTimestampsTrait;
    use HasAuditLogTrait;

    protected static string $table = 'api_tokens';

    protected static array $fillable = [
        'tenant_id',
        'name',
        'token_prefix',
        'token_hash',
        'user_id',
        'abilities_json',
        'last_used_at',
        'expires_at',
        'revoked_at',
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
    ];

    protected static array $hidden = [
        'token_hash',
    ];

    protected static array $casts = [
        'tenant_id' => 'int',
        'user_id' => 'int',
        'abilities_json' => 'json',
        'created_by' => 'int',
        'updated_by' => 'int',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];
}
