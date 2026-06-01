<?php

declare(strict_types=1);

namespace Catalyst\Entities;

use Catalyst\Framework\Database\Model;
use Catalyst\Framework\Traits\BelongsToTenantTrait;
use Catalyst\Framework\Traits\HasAuditLogTrait;
use Catalyst\Framework\Traits\HasOptimisticLockingTrait;
use Catalyst\Framework\Traits\HasTimestampsTrait;

final class DocumentTemplate extends Model
{
    use BelongsToTenantTrait;
    use HasTimestampsTrait;
    use HasAuditLogTrait;
    use HasOptimisticLockingTrait;

    protected static string $table = 'document_templates';

    protected static array $fillable = [
        'tenant_id',
        'name',
        'slug',
        'description',
        'format',
        'variables_schema_json',
        'sample_payload_json',
        'body_template',
        'lock_version',
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
    ];

    protected static array $casts = [
        'tenant_id' => 'int',
        'variables_schema_json' => 'json',
        'sample_payload_json' => 'json',
        'lock_version' => 'int',
        'created_by' => 'int',
        'updated_by' => 'int',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
