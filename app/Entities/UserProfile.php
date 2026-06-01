<?php

declare(strict_types=1);

namespace Catalyst\Entities;

use Catalyst\Framework\Database\Model;
use Catalyst\Framework\Database\Relations\BelongsTo;
use Catalyst\Framework\Traits\BelongsToTenantTrait;
use Catalyst\Framework\Traits\HasAuditLogTrait;
use Catalyst\Framework\Traits\HasOptimisticLockingTrait;
use Catalyst\Framework\Traits\HasTimestampsTrait;
use Catalyst\Repository\Auth\Models\User;

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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
