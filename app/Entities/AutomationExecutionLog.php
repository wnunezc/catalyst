<?php

declare(strict_types=1);

namespace Catalyst\Entities;

use Catalyst\Framework\Database\Model;
use Catalyst\Framework\Traits\BelongsToTenantTrait;
use Catalyst\Framework\Traits\HasTimestampsTrait;

final class AutomationExecutionLog extends Model
{
    use BelongsToTenantTrait;
    use HasTimestampsTrait;

    protected static string $table = 'automation_execution_logs';

    protected static array $fillable = [
        'tenant_id',
        'rule_id',
        'trigger_source',
        'event_name',
        'status',
        'message',
        'context_json',
        'result_json',
        'created_at',
        'updated_at',
    ];

    protected static array $casts = [
        'tenant_id' => 'int',
        'rule_id' => 'int',
        'context_json' => 'json',
        'result_json' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
