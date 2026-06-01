<?php

declare(strict_types=1);

namespace Catalyst\Entities;

use Catalyst\Framework\Database\Model;
use Catalyst\Framework\Traits\BelongsToTenantTrait;
use Catalyst\Framework\Traits\HasAuditLogTrait;
use Catalyst\Framework\Traits\HasOptimisticLockingTrait;
use Catalyst\Framework\Traits\HasTimestampsTrait;

final class AutomationRule extends Model
{
    use BelongsToTenantTrait;
    use HasTimestampsTrait;
    use HasAuditLogTrait;
    use HasOptimisticLockingTrait;

    protected static string $table = 'automation_rules';

    protected static array $fillable = [
        'tenant_id',
        'name',
        'slug',
        'description',
        'trigger_type',
        'event_name',
        'cron_expression',
        'condition_json',
        'action_type',
        'action_payload_json',
        'is_enabled',
        'valid_from',
        'valid_to',
        'last_run_at',
        'lock_version',
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
    ];

    protected static array $casts = [
        'tenant_id' => 'int',
        'condition_json' => 'json',
        'action_payload_json' => 'json',
        'is_enabled' => 'bool',
        'valid_from' => 'datetime',
        'valid_to' => 'datetime',
        'lock_version' => 'int',
        'created_by' => 'int',
        'updated_by' => 'int',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'last_run_at' => 'datetime',
    ];
}
