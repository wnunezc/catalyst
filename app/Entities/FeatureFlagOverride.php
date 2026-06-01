<?php

declare(strict_types=1);

namespace Catalyst\Entities;

use Catalyst\Framework\Database\Model;
use Catalyst\Framework\Traits\HasAuditLogTrait;
use Catalyst\Framework\Traits\HasTimestampsTrait;

final class FeatureFlagOverride extends Model
{
    use HasTimestampsTrait;
    use HasAuditLogTrait;

    protected static string $table = 'feature_flag_overrides';

    protected static array $fillable = [
        'flag_key',
        'subject_type',
        'subject_key',
        'enabled',
        'note',
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
    ];

    protected static array $casts = [
        'enabled' => 'bool',
        'created_by' => 'int',
        'updated_by' => 'int',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
