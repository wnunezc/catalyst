<?php

declare(strict_types=1);

namespace Catalyst\Entities;

use Catalyst\Framework\Database\Model;
use Catalyst\Framework\Traits\BelongsToTenantTrait;
use Catalyst\Framework\Traits\HasAuditLogTrait;
use Catalyst\Framework\Traits\HasOptimisticLockingTrait;
use Catalyst\Framework\Traits\HasTimestampsTrait;

final class MetadataFieldDefinition extends Model
{
    use BelongsToTenantTrait;
    use HasTimestampsTrait;
    use HasAuditLogTrait;
    use HasOptimisticLockingTrait;

    protected static string $table = 'metadata_field_definitions';

    protected static array $fillable = [
        'tenant_id',
        'resource_key',
        'field_key',
        'label',
        'type',
        'section_key',
        'help_text',
        'placeholder',
        'default_value',
        'options_json',
        'catalog_key',
        'rules_extra',
        'is_required',
        'is_filterable',
        'is_listed',
        'sort_order',
        'max_length',
        'min_value',
        'max_value',
        'lock_version',
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
    ];

    protected static array $casts = [
        'tenant_id' => 'int',
        'options_json' => 'json',
        'is_required' => 'bool',
        'is_filterable' => 'bool',
        'is_listed' => 'bool',
        'sort_order' => 'int',
        'max_length' => 'int',
        'min_value' => 'float',
        'max_value' => 'float',
        'lock_version' => 'int',
        'created_by' => 'int',
        'updated_by' => 'int',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
