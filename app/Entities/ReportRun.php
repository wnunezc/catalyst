<?php

declare(strict_types=1);

namespace Catalyst\Entities;

use Catalyst\Framework\Database\Model;
use Catalyst\Framework\Traits\BelongsToTenantTrait;
use Catalyst\Framework\Traits\HasAuditLogTrait;
use Catalyst\Framework\Traits\HasTimestampsTrait;

final class ReportRun extends Model
{
    use BelongsToTenantTrait;
    use HasTimestampsTrait;
    use HasAuditLogTrait;

    protected static string $table = 'report_runs';

    protected static array $fillable = [
        'tenant_id',
        'report_key',
        'format',
        'status',
        'criteria_json',
        'attach_resource_key',
        'attach_record_id',
        'queued_job_id',
        'output_media_item_id',
        'output_attachment_id',
        'error_message',
        'started_at',
        'completed_at',
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
    ];

    protected static array $casts = [
        'tenant_id' => 'int',
        'criteria_json' => 'json',
        'attach_record_id' => 'int',
        'queued_job_id' => 'int',
        'output_media_item_id' => 'int',
        'output_attachment_id' => 'int',
        'created_by' => 'int',
        'updated_by' => 'int',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
