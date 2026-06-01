<?php

declare(strict_types=1);

namespace Catalyst\Entities;

use Catalyst\Framework\Database\Model;
use Catalyst\Framework\Traits\BelongsToTenantTrait;
use Catalyst\Framework\Traits\HasAuditLogTrait;

final class TimelineEvent extends Model
{
    use BelongsToTenantTrait;
    use HasAuditLogTrait;

    protected static string $table = 'timeline_events';

    protected static array $fillable = [
        'tenant_id',
        'resource_key',
        'record_id',
        'event_key',
        'event_type',
        'label',
        'metadata_json',
        'occurred_at',
    ];

    protected static array $casts = [
        'tenant_id' => 'int',
        'record_id' => 'int',
        'metadata_json' => 'json',
        'occurred_at' => 'datetime',
    ];
}
