<?php

declare(strict_types=1);

namespace Catalyst\Entities;

use Catalyst\Framework\Database\Model;
use Catalyst\Framework\Traits\BelongsToTenantTrait;
use Catalyst\Framework\Traits\HasAuditLogTrait;
use Catalyst\Framework\Traits\HasOptimisticLockingTrait;
use Catalyst\Framework\Traits\HasTimestampsTrait;

final class MediaItem extends Model
{
    use BelongsToTenantTrait;
    use HasTimestampsTrait;
    use HasAuditLogTrait;
    use HasOptimisticLockingTrait;

    protected static string $table = 'media_library';

    protected static array $fillable = [
        'tenant_id',
        'name',
        'original_name',
        'disk',
        'path',
        'public_url',
        'mime_type',
        'extension',
        'size_bytes',
        'archived_at',
        'archived_by',
        'lock_version',
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
    ];

    protected static array $casts = [
        'tenant_id' => 'int',
        'size_bytes' => 'int',
        'archived_by' => 'int',
        'lock_version' => 'int',
        'created_by' => 'int',
        'updated_by' => 'int',
        'archived_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
