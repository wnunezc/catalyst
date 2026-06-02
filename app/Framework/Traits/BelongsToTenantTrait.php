<?php

declare(strict_types=1);

/**
 * Catalyst PHP Framework
 *
 * A modern PHP 8.4 framework for building
 * robust and scalable web applications.
 *
 * PHP Version 8.4 (Required).
 *
 * @package    Catalyst
 *
 * @author     Walter Nuñez (arcanisgk/original founder)
 * @email      <wnunez@lh-2.net>
 * @email      <icarosnet@gmail.com>
 * @copyright  2024-2026 Walter Francisco Nuñez Cruz and Icaros Net
 * @license    Proprietary - https://catalyst.lh-2.net/license
 *
 * @version    GIT: See repository tags
 *
 * @category   Framework
 * @filesource
 *
 * @link       https://catalyst.lh-2.net Project homepage
 * @see        https://catalyst.lh-2.net/docs Documentation
 *
 */

namespace Catalyst\Framework\Traits;

use Catalyst\Framework\Database\Model;
use Catalyst\Framework\Tenancy\TenancyManager;
use RuntimeException;

/**
 * Defines the Belongs To Tenant Trait trait contract.
 *
 * @package Catalyst\Framework\Traits
 * Responsibility: Coordinates the belongs to tenant trait behavior within its module boundary.
 */
trait BelongsToTenantTrait
{
    public const TENANT_SCOPED = true;
    public const TENANT_COLUMN = 'tenant_id';

    /**
     * Handles the boot belongs to tenant trait workflow.
     */
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
