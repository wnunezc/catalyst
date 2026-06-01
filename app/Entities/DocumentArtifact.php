<?php

declare(strict_types=1);

namespace Catalyst\Entities;

use Catalyst\Framework\Database\Model;
use Catalyst\Framework\Traits\BelongsToTenantTrait;
use Catalyst\Framework\Traits\HasAuditLogTrait;
use Catalyst\Framework\Traits\HasTimestampsTrait;

final class DocumentArtifact extends Model
{
    use BelongsToTenantTrait;
    use HasTimestampsTrait;
    use HasAuditLogTrait;

    protected static string $table = 'document_artifacts';

    protected static array $fillable = [
        'tenant_id',
        'document_template_id',
        'workflow_instance_id',
        'name',
        'format',
        'disk',
        'path',
        'public_url',
        'checksum_sha256',
        'payload_snapshot_json',
        'rendered_content',
        'archived_at',
        'archived_by',
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
    ];

    protected static array $casts = [
        'tenant_id' => 'int',
        'document_template_id' => 'int',
        'workflow_instance_id' => 'int',
        'payload_snapshot_json' => 'json',
        'archived_by' => 'int',
        'created_by' => 'int',
        'updated_by' => 'int',
        'archived_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
