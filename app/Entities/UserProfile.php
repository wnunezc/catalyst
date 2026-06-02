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
use Catalyst\Framework\Database\Relations\BelongsTo;
use Catalyst\Framework\Traits\BelongsToTenantTrait;
use Catalyst\Framework\Traits\HasAuditLogTrait;
use Catalyst\Framework\Traits\HasOptimisticLockingTrait;
use Catalyst\Framework\Traits\HasTimestampsTrait;
use Catalyst\Repository\Auth\Models\User;

/**
 * Defines the User Profile class contract.
 *
 * @package Catalyst\Entities
 * Responsibility: Coordinates the user profile behavior within its module boundary.
 */
final class UserProfile extends Model
{
    use BelongsToTenantTrait;
    use HasTimestampsTrait;
    use HasAuditLogTrait;
    use HasOptimisticLockingTrait;

    protected static string $table = 'user_profiles';

    protected static array $fillable = [
        'tenant_id',
        'user_id',
        'document_id',
        'phone',
        'organization',
        'position',
        'department',
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
        'lock_version',
    ];

    protected static array $casts = [
        'tenant_id' => 'int',
        'user_id' => 'int',
        'created_by' => 'int',
        'updated_by' => 'int',
        'lock_version' => 'int',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Handles the user workflow.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
