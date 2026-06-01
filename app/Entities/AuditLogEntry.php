<?php

declare(strict_types=1);

namespace Catalyst\Entities;

use Catalyst\Framework\Database\Model;
use Catalyst\Framework\Traits\BelongsToTenantTrait;

final class AuditLogEntry extends Model
{
    use BelongsToTenantTrait;

    protected static string $table = 'audit_logs';

    protected static array $fillable = [
        'channel',
        'event_name',
        'action',
        'resource',
        'resource_id',
        'resource_label',
        'actor_id',
        'actor_type',
        'tenant_id',
        'tenant_key',
        'request_method',
        'request_uri',
        'ip_address',
        'user_agent',
        'before_payload',
        'after_payload',
        'metadata',
        'occurred_at',
    ];

    protected static array $casts = [
        'actor_id' => 'int',
        'tenant_id' => 'int',
        'before_payload' => 'json',
        'after_payload' => 'json',
        'metadata' => 'json',
        'occurred_at' => 'datetime',
    ];
}
