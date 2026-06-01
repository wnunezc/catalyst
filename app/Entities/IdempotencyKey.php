<?php

declare(strict_types=1);

namespace Catalyst\Entities;

use Catalyst\Framework\Database\Model;
use Catalyst\Framework\Traits\BelongsToTenantTrait;
use Catalyst\Framework\Traits\HasTimestampsTrait;

final class IdempotencyKey extends Model
{
    use BelongsToTenantTrait;
    use HasTimestampsTrait;

    protected static string $table = 'idempotency_keys';

    protected static array $fillable = [
        'tenant_id',
        'scope_key',
        'idempotency_key',
        'fingerprint_hash',
        'status',
        'outcome_json',
        'created_at',
        'updated_at',
        'completed_at',
    ];

    protected static array $casts = [
        'tenant_id' => 'int',
        'outcome_json' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'completed_at' => 'datetime',
    ];
}
