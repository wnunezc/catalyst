<?php

declare(strict_types=1);

namespace Catalyst\Entities;

use Catalyst\Framework\Database\Model;
use Catalyst\Framework\Traits\BelongsToTenantTrait;

final class WorkflowTransition extends Model
{
    use BelongsToTenantTrait;

    protected static string $table = 'workflow_transitions';

    protected static array $fillable = [
        'tenant_id',
        'workflow_instance_id',
        'transition_key',
        'from_state',
        'to_state',
        'notes',
        'metadata',
        'actor_id',
        'occurred_at',
    ];

    protected static array $casts = [
        'tenant_id' => 'int',
        'workflow_instance_id' => 'int',
        'metadata' => 'json',
        'actor_id' => 'int',
        'occurred_at' => 'datetime',
    ];
}
