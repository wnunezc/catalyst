<?php

declare(strict_types=1);

namespace Catalyst\Repository\Auth\Models;

use Catalyst\Framework\Database\Model;
use Catalyst\Framework\Traits\BelongsToTenantTrait;
use Catalyst\Framework\Traits\HasAuditLogTrait;

/**
 * User entity — maps to the `users` table.
 *
 * Uses $casts for boolean flags and datetime columns.
 * `password` is listed in $hidden so it never appears in toArray() / toJson().
 *
 * NOTE: `created_at` and `updated_at` are TIMESTAMP columns managed by MySQL
 * (DEFAULT CURRENT_TIMESTAMP / ON UPDATE CURRENT_TIMESTAMP), so HasTimestampsTrait
 * is intentionally omitted — the database handles them automatically.
 * PHP casts on read still provide DateTimeImmutable instances via $casts.
 *
 * @package Catalyst\Repository\Auth\Models
 */
class User extends Model
{
    use BelongsToTenantTrait;
    use HasAuditLogTrait;

    protected static string $table = 'users';

    protected static array $fillable = [
        'tenant_id',
        'name',
        'email',
        'password',
        'active',
        'email_verified',
        'last_login',
    ];

    protected static array $hidden = ['password'];

    protected static array $casts = [
        'tenant_id' => 'int',
        'active'         => 'bool',
        'email_verified' => 'bool',
        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',
        'last_login'     => 'datetime',
    ];
}
