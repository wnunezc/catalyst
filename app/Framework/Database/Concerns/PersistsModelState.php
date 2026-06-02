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

namespace Catalyst\Framework\Database\Concerns;

use Catalyst\Framework\Database\Connection;
use Catalyst\Framework\Database\DatabaseManager;
use Catalyst\Framework\Tenancy\TenancyManager;
use Catalyst\Helpers\Exceptions\OptimisticLockException;

/**
 * Defines the Persists Model State trait contract.
 *
 * @package Catalyst\Framework\Database\Concerns
 * Responsibility: Coordinates the persists model state behavior within its module boundary.
 */
trait PersistsModelState
{
    /**
     * Persists the current state.
     */
    public function save(): bool
    {
        if ($this->exists) {
            if (!$this->isDirty()) {
                return true;
            }

            return $this->performUpdate();
        }

        return $this->performInsert();
    }

    /**
     * Handles the update workflow.
     */
    public function update(array $attributes): bool
    {
        $this->fill($attributes);
        return $this->save();
    }

    /**
     * Handles the delete workflow.
     */
    public function delete(): bool
    {
        if (!$this->exists) {
            return false;
        }

        $this->fireHook('deleting');

        $query = static::getConnectionInstance()
            ->table(static::getTable())
            ->whereEqual(static::$primaryKey, $this->getKey());

        if ($this->usesTenantScoping()) {
            $query->whereEqual($this->tenantScopeColumn(), TenancyManager::getInstance()->requireCurrentTenantId());
        }

        $affected = $query->delete();

        if ($affected > 0) {
            $this->exists = false;
            $this->fireHook('deleted');
            return true;
        }

        return false;
    }

    /**
     * Handles the fresh workflow.
     */
    public function fresh(): ?static
    {
        if (!$this->exists) {
            return null;
        }

        return static::find($this->getKey());
    }

    /**
     * Handles the refresh workflow.
     */
    public function refresh(): static
    {
        $fresh = $this->fresh();

        if ($fresh !== null) {
            $this->attributes = $fresh->attributes;
            $this->original = $fresh->original;
        }

        return $this;
    }

    /**
     * Handles the exists workflow.
     */
    public function exists(): bool
    {
        return $this->exists;
    }

    /**
     * Resolves the requested value.
     */
    public static function resolveConnection(): Connection
    {
        return static::getConnectionInstance();
    }

    /**
     * Handles the perform insert workflow.
     */
    protected function performInsert(): bool
    {
        $this->fireHook('inserting');

        if ($this->usesOptimisticLocking()) {
            $lockColumn = $this->optimisticLockColumn();

            if (!isset($this->attributes[$lockColumn]) || $this->attributes[$lockColumn] === null || $this->attributes[$lockColumn] === '') {
                $this->attributes[$lockColumn] = 1;
            }
        }

        $data = $this->attributes;
        $pk = static::$primaryKey;

        if (!isset($data[$pk]) || $data[$pk] === null) {
            unset($data[$pk]);
        }

        $id = static::getConnectionInstance()->insert(static::getTable(), $data);

        if (!isset($this->attributes[$pk]) && $id > 0) {
            $this->attributes[$pk] = $id;
        }

        $this->original = $this->attributes;
        $this->exists = true;

        $this->fireHook('inserted');

        return true;
    }

    /**
     * Handles the perform update workflow.
     */
    protected function performUpdate(): bool
    {
        $this->fireHook('updating');
        $dirty = $this->getDirty();
        $lockColumn = null;
        $expectedVersion = null;
        $previousLockValue = null;

        if (empty($dirty)) {
            return true;
        }

        unset($dirty[static::$primaryKey]);

        if (!empty($dirty)) {
            $query = static::getConnectionInstance()
                ->table(static::getTable())
                ->whereEqual(static::$primaryKey, $this->getKey());

            if ($this->usesTenantScoping()) {
                $query->whereEqual($this->tenantScopeColumn(), TenancyManager::getInstance()->requireCurrentTenantId());
            }

            if ($this->usesOptimisticLocking()) {
                $lockColumn = $this->optimisticLockColumn();
                $expectedVersion = $this->expectedLockVersion($lockColumn);

                if ($expectedVersion === null) {
                    throw OptimisticLockException::forModel(
                        static::class,
                        $this->getKey(),
                        $lockColumn,
                        0,
                        $this->currentPersistedLockVersion($lockColumn)
                    );
                }

                $previousLockValue = $this->attributes[$lockColumn] ?? null;
                $nextVersion = $expectedVersion + 1;
                $dirty[$lockColumn] = $nextVersion;
                $this->attributes[$lockColumn] = $nextVersion;
                $query->whereEqual($lockColumn, $expectedVersion);
            }

            $affected = $query->update($dirty);

            if ($lockColumn !== null && $affected < 1) {
                if ($previousLockValue !== null) {
                    $this->attributes[$lockColumn] = $previousLockValue;
                }

                throw OptimisticLockException::forModel(
                    static::class,
                    $this->getKey(),
                    $lockColumn,
                    $expectedVersion ?? 0,
                    $this->currentPersistedLockVersion($lockColumn)
                );
            }
        }

        $this->original = $this->attributes;
        $this->fireHook('updated');

        return true;
    }

    /**
     * Returns the connection instance value.
     */
    protected static function getConnectionInstance(): Connection
    {
        return DatabaseManager::getInstance()->connection(static::$connection);
    }

    /**
     * Handles the uses optimistic locking workflow.
     */
    protected function usesOptimisticLocking(): bool
    {
        return defined(static::class . '::OPTIMISTIC_LOCKING') && static::OPTIMISTIC_LOCKING === true;
    }

    /**
     * Handles the optimistic lock column workflow.
     */
    protected function optimisticLockColumn(): string
    {
        return defined(static::class . '::LOCK_VERSION') ? static::LOCK_VERSION : 'lock_version';
    }

    /**
     * Handles the uses tenant scoping workflow.
     */
    protected function usesTenantScoping(): bool
    {
        return defined(static::class . '::TENANT_SCOPED') && static::TENANT_SCOPED === true;
    }

    /**
     * Handles the tenant scope column workflow.
     */
    protected function tenantScopeColumn(): string
    {
        return defined(static::class . '::TENANT_COLUMN') ? static::TENANT_COLUMN : 'tenant_id';
    }

    /**
     * Handles the expected lock version workflow.
     */
    protected function expectedLockVersion(string $column): ?int
    {
        $value = $this->attributes[$column] ?? $this->original[$column] ?? null;

        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    /**
     * Handles the current persisted lock version workflow.
     */
    protected function currentPersistedLockVersion(string $column): ?int
    {
        $query = static::getConnectionInstance()
            ->table(static::getTable())
            ->whereEqual(static::$primaryKey, $this->getKey());

        if ($this->usesTenantScoping()) {
            $query->whereEqual($this->tenantScopeColumn(), TenancyManager::getInstance()->requireCurrentTenantId());
        }

        $row = $query->first([$column]);

        if (!is_array($row) || !isset($row[$column]) || $row[$column] === null || $row[$column] === '') {
            return null;
        }

        return (int) $row[$column];
    }
}
