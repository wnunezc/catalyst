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
 * Responsibility: Represents authenticated application users for ORM reads and writes while hiding credential data.
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
