<?php

declare(strict_types=1);

namespace Catalyst\Framework\Traits;

use Catalyst\Framework\Database\Model;
use Catalyst\Framework\Tenancy\TenancyManager;
use RuntimeException;

trait BelongsToTenantTrait
{
    public const TENANT_SCOPED = true;
    public const TENANT_COLUMN = 'tenant_id';

    protected static function bootBelongsToTenantTrait(): void
    {
        static::registerHook('inserting', static function (Model $model): void {
            $column = defined($model::class . '::TENANT_COLUMN')
                ? $model::TENANT_COLUMN
                : 'tenant_id';

            $tenantId = TenancyManager::getInstance()->requireCurrentTenantId();
            $currentValue = $model->getAttribute($column);

            if ($currentValue === null || $currentValue === '' || (int) $currentValue === 0) {
                $model->setAttribute($column, $tenantId);

                return;
            }

            if ((int) $currentValue !== $tenantId) {
                throw new RuntimeException('Tenant mismatch detected while inserting tenant-scoped model.');
            }
        });
    }
}
