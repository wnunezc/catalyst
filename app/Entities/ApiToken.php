<?php

declare(strict_types=1);

namespace Catalyst\Entities;

use Catalyst\Framework\Database\Model;
use Catalyst\Framework\Traits\BelongsToTenantTrait;
use Catalyst\Framework\Traits\HasAuditLogTrait;
use Catalyst\Framework\Traits\HasTimestampsTrait;

final class ApiToken extends Model
{
    use BelongsToTenantTrait;
    use HasTimestampsTrait;
    use HasAuditLogTrait;

    protected static string $table = 'api_tokens';

    protected static array $fillable = [
        'tenant_id',
        'name',
        'token_prefix',
        'token_hash',
        'user_id',
        'abilities_json',
        'last_used_at',
        'expires_at',
        'revoked_at',
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
    ];

    protected static array $hidden = [
        'token_hash',
    ];

    protected static array $casts = [
        'tenant_id' => 'int',
        'user_id' => 'int',
        'abilities_json' => 'json',
        'created_by' => 'int',
        'updated_by' => 'int',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];
}
