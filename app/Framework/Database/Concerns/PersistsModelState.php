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
 * Splits model insert, update, delete, refresh, and connection persistence behavior out of Model.
 *
 * @package Catalyst\Framework\Database\Concerns
 * Responsibility: Persist ORM model state with lifecycle hooks, tenant filters, and optimistic locking safeguards.
 */
trait PersistsModelState
{
    /**
     * Persists a new model or dirty existing model through insert or update operations.
     *
     * Responsibility: Persists a new model or dirty existing model through insert or update operations.
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
     * Mass-assigns attributes and persists the resulting model state.
     *
     * Responsibility: Mass-assigns attributes and persists the resulting model state.
     */
    public function update(array $attributes): bool
    {
        $this->fill($attributes);
        return $this->save();
    }

    /**
     * Deletes an existing model row using primary key and tenant scope constraints.
     *
     * Responsibility: Deletes an existing model row using primary key and tenant scope constraints.
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
     * Returns a newly queried copy of the current model or null when it is not persisted.
     *
     * Responsibility: Returns a newly queried copy of the current model or null when it is not persisted.
     */
    public function fresh(): ?static
    {
        if (!$this->exists) {
            return null;
        }

        return static::find($this->getKey());
    }

    /**
     * Replaces current attributes and original state with a freshly queried copy.
     *
     * Responsibility: Replaces current attributes and original state with a freshly queried copy.
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
     * Reports whether the model currently represents a persisted row.
     *
     * Responsibility: Reports whether the model currently represents a persisted row.
     */
    public function exists(): bool
    {
        return $this->exists;
    }

    /**
     * Resolves the database connection configured for the concrete model.
     */
    public static function resolveConnection(): Connection
    {
        return static::getConnectionInstance();
    }

    /**
     * Inserts the current attributes, initializes optimistic lock state, and marks the model as persisted.
     *
     * Responsibility: Inserts the current attributes, initializes optimistic lock state, and marks the model as persisted.
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
     * Updates dirty attributes with tenant scope and optimistic lock constraints when enabled.
     *
     * Responsibility: Updates dirty attributes with tenant scope and optimistic lock constraints when enabled.
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
     * Returns the DatabaseManager connection selected by the concrete model configuration.
     */
    protected static function getConnectionInstance(): Connection
    {
        return DatabaseManager::getInstance()->connection(static::$connection);
    }

    /**
     * Reports whether the concrete model opted into optimistic locking.
     *
     * Responsibility: Reports whether the concrete model opted into optimistic locking.
     */
    protected function usesOptimisticLocking(): bool
    {
        return defined(static::class . '::OPTIMISTIC_LOCKING') && static::OPTIMISTIC_LOCKING === true;
    }

    /**
     * Returns the optimistic lock column configured by the concrete model.
     *
     * Responsibility: Returns the optimistic lock column configured by the concrete model.
     */
    protected function optimisticLockColumn(): string
    {
        return defined(static::class . '::LOCK_VERSION') ? static::LOCK_VERSION : 'lock_version';
    }

    /**
     * Reports whether the concrete model requires tenant-scoped persistence queries.
     *
     * Responsibility: Reports whether the concrete model requires tenant-scoped persistence queries.
     */
    protected function usesTenantScoping(): bool
    {
        return defined(static::class . '::TENANT_SCOPED') && static::TENANT_SCOPED === true;
    }

    /**
     * Returns the tenant scope column configured by the concrete model.
     *
     * Responsibility: Returns the tenant scope column configured by the concrete model.
     */
    protected function tenantScopeColumn(): string
    {
        return defined(static::class . '::TENANT_COLUMN') ? static::TENANT_COLUMN : 'tenant_id';
    }

    /**
     * Reads the expected optimistic lock version from current or original attributes.
     *
     * Responsibility: Reads the expected optimistic lock version from current or original attributes.
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
     * Queries the currently persisted optimistic lock version for conflict reporting.
     *
     * Responsibility: Queries the currently persisted optimistic lock version for conflict reporting.
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
