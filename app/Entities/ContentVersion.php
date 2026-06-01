<?php

declare(strict_types=1);

namespace Catalyst\Entities;

use Catalyst\Framework\Database\Model;
use Catalyst\Framework\Traits\BelongsToTenantTrait;

final class ContentVersion extends Model
{
    use BelongsToTenantTrait;

    protected static string $table = 'content_versions';

    protected static array $fillable = [
        'tenant_id',
        'resource_key',
        'record_id',
        'version_number',
        'summary',
        'snapshot_json',
        'diff_json',
        'actor_id',
        'created_at',
    ];

    protected static array $casts = [
        'tenant_id' => 'int',
        'record_id' => 'int',
        'version_number' => 'int',
        'snapshot_json' => 'json',
        'diff_json' => 'json',
        'actor_id' => 'int',
        'created_at' => 'datetime',
    ];
}
