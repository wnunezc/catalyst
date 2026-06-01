<?php

declare(strict_types=1);

namespace Catalyst\Entities;

use Catalyst\Framework\Database\Model;
use Catalyst\Framework\Traits\HasAuditLogTrait;
use Catalyst\Framework\Traits\HasTimestampsTrait;

final class DeploymentRun extends Model
{
    use HasTimestampsTrait;
    use HasAuditLogTrait;

    protected static string $table = 'deployment_runs';

    protected static array $fillable = [
        'profile_key',
        'release_id',
        'environment',
        'status',
        'dry_run',
        'artifact_path',
        'remote_path',
        'summary_json',
        'error_message',
        'started_at',
        'finished_at',
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
    ];

    protected static array $casts = [
        'dry_run' => 'bool',
        'summary_json' => 'json',
        'created_by' => 'int',
        'updated_by' => 'int',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
