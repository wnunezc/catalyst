<?php

declare(strict_types=1);

namespace Catalyst\Entities;

use Catalyst\Framework\Database\Model;
use Catalyst\Framework\Traits\BelongsToTenantTrait;
use Catalyst\Framework\Traits\HasAuditLogTrait;
use Catalyst\Framework\Traits\HasTimestampsTrait;

final class MetadataFieldValue extends Model
{
    use BelongsToTenantTrait;
    use HasTimestampsTrait;
    use HasAuditLogTrait;

    protected static string $table = 'metadata_field_values';

    protected static array $fillable = [
        'tenant_id',
        'resource_key',
        'record_id',
        'field_definition_id',
        'value_text',
        'value_number',
        'value_boolean',
        'value_date',
        'value_datetime',
        'media_item_id',
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
    ];

    protected static array $casts = [
        'tenant_id' => 'int',
        'record_id' => 'int',
        'field_definition_id' => 'int',
        'value_number' => 'float',
        'value_boolean' => 'bool',
        'media_item_id' => 'int',
        'created_by' => 'int',
        'updated_by' => 'int',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
