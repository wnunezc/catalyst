<?php

declare(strict_types=1);

namespace Catalyst\Entities;

use Catalyst\Framework\Database\Model;
use Catalyst\Framework\Traits\BelongsToTenantTrait;
use Catalyst\Framework\Traits\HasAuditLogTrait;
use Catalyst\Framework\Traits\HasOptimisticLockingTrait;
use Catalyst\Framework\Traits\HasTimestampsTrait;

final class CatalogItem extends Model
{
    use BelongsToTenantTrait;
    use HasTimestampsTrait;
    use HasAuditLogTrait;
    use HasOptimisticLockingTrait;

    protected static string $table = 'catalog_items';

    protected static array $fillable = [
        'tenant_id',
        'catalog_definition_id',
        'item_key',
        'label',
        'description',
        'is_enabled',
        'valid_from',
        'valid_to',
        'sort_order',
        'metadata_json',
        'lock_version',
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
    ];

    protected static array $casts = [
        'tenant_id' => 'int',
        'catalog_definition_id' => 'int',
        'is_enabled' => 'bool',
        'sort_order' => 'int',
        'metadata_json' => 'json',
        'lock_version' => 'int',
        'created_by' => 'int',
        'updated_by' => 'int',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'valid_from' => 'datetime',
        'valid_to' => 'datetime',
    ];
}
