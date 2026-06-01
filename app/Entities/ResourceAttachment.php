<?php

declare(strict_types=1);

namespace Catalyst\Entities;

use Catalyst\Framework\Database\Model;
use Catalyst\Framework\Traits\BelongsToTenantTrait;
use Catalyst\Framework\Traits\HasAuditLogTrait;
use Catalyst\Framework\Traits\HasTimestampsTrait;

final class ResourceAttachment extends Model
{
    use BelongsToTenantTrait;
    use HasTimestampsTrait;
    use HasAuditLogTrait;

    protected static string $table = 'resource_attachments';

    protected static array $fillable = [
        'tenant_id',
        'resource_key',
        'record_id',
        'media_item_id',
        'document_artifact_id',
        'purpose',
        'attachment_type',
        'is_primary',
        'detached_at',
        'detached_by',
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
    ];

    protected static array $casts = [
        'tenant_id' => 'int',
        'record_id' => 'int',
        'media_item_id' => 'int',
        'document_artifact_id' => 'int',
        'is_primary' => 'bool',
        'detached_by' => 'int',
        'created_by' => 'int',
        'updated_by' => 'int',
        'detached_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
