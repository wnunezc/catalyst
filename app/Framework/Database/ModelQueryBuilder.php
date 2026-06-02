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

namespace Catalyst\Framework\Database;

use Catalyst\Framework\Database\Relations\Relation;
use Catalyst\Framework\Tenancy\TenancyManager;
use Catalyst\Helpers\Exceptions\ModelNotFoundException;

/**
 * Query builder that hydrates results into Model instances.
 *
 * Extends QueryBuilder by overriding get() and first() to return
 * typed Collection<Model> and ?Model instead of raw arrays.
 *
 * Also handles:
 * - Automatic soft-delete scope (when the model uses HasSoftDeletesTrait)
 * - Eager loading of relationships via with() — prevents N+1 queries
 * - Pagination via paginate()
 * - findOrFail() throwing ModelNotFoundException
 *
 * @package Catalyst\Framework\Database
 * Responsibility: Hydrates query results into models and applies ORM scopes, eager loading and pagination.
 */
class ModelQueryBuilder extends QueryBuilder
{
    /** @var bool Bypass soft-delete scope (include all rows). */
    protected bool $includeTrashed = false;

    /** @var bool Only return soft-deleted rows. */
    protected bool $onlyTrashedRows = false;

    /** @var bool Track whether the default soft-delete scope was applied. */
    protected bool $softScopeApplied = false;

    /** @var bool Track whether the default tenant scope was applied. */
    protected bool $tenantScopeApplied = false;

    /**
     * Relation names to eager-load after hydration.
     * Populated by with(). Consumed once per get() call.
     *
     * @var string[]
     */
    protected array $eagerLoad = [];

    /**
     * Initializes the Model Query Builder instance.
     *
     * Responsibility: Initializes the Model Query Builder instance.
     */
    public function __construct(
        Connection $connection,
        string $table,
        /** @var class-string<Model> */
        protected string $modelClass
    ) {
        parent::__construct($connection, $table);
        $this->applySoftDeleteScope();
        $this->applyTenantScope();
    }

    // -------------------------------------------------------------------------
    // Eager loading
    // -------------------------------------------------------------------------

    /**
     * Specify relations to eager-load with this query. Prevents N+1 — each named relation is resolved in at most 2 extra queries regardless of how many parent models the query returns. Example: User::query()->with('posts', 'profile')->get(); User::query()->with('roles')->paginate(20);.
     *
     * Responsibility: Specify relations to eager-load with this query. Prevents N+1 — each named relation is resolved in at most 2 extra queries regardless of how many parent models the query returns. Example: User::query()->with('posts', 'profile')->get(); User::query()->with('roles')->paginate(20);.
     * @param string ...$relations Relation method names defined on the model class.
     * @return static
     */
    public function with(string ...$relations): static
    {
        $this->eagerLoad = array_values(array_unique(
            array_merge($this->eagerLoad, $relations)
        ));

        return $this;
    }

    // -------------------------------------------------------------------------
    // Overrides — hydrate models
    // -------------------------------------------------------------------------

    /**
     * Execute and return a Collection of Model instances. If with() was called, eager-loads all specified relations.
     *
     * Responsibility: Execute and return a Collection of Model instances. If with() was called, eager-loads all specified relations.
     * @return Collection<Model>
     */
    public function get(array $columns = ['*']): Collection
    {
        if ($columns !== ['*']) {
            $this->columns = $columns;
        }

        [$query, $bindings] = $this->compileSelect();
        $rows = $this->connection->select($query, $bindings);

        $collection = $this->hydrateModels($rows);

        if (!empty($this->eagerLoad) && $collection->count() > 0) {
            $this->eagerLoadRelations($collection->all());
        }

        return $collection;
    }

    /**
     * Execute and return the first matching Model, or null.
     *
     * Responsibility: Execute and return the first matching Model, or null.
     */
    public function first(array $columns = ['*']): ?Model
    {
        if ($columns !== ['*']) {
            $this->columns = $columns;
        }

        return $this->limit(1)->get()->first();
    }

    /**
     * Like first() but throws ModelNotFoundException when no record is found.
     *
     * Responsibility: Like first() but throws ModelNotFoundException when no record is found.
     * @throws ModelNotFoundException
     */
    public function firstOrFail(array $columns = ['*']): Model
    {
        $model = $this->first($columns);

        if ($model === null) {
            throw ModelNotFoundException::forQuery($this->modelClass);
        }

        return $model;
    }

    /**
     * Find by primary key, returning null when not found.
     *
     * Responsibility: Find by primary key, returning null when not found.
     */
    public function find(int|string $id): ?Model
    {
        return $this->whereEqual(
            ($this->modelClass)::getPrimaryKey(),
            $id
        )->first();
    }

    /**
     * Find by primary key or throw ModelNotFoundException.
     *
     * Responsibility: Find by primary key or throw ModelNotFoundException.
     * @throws ModelNotFoundException
     */
    public function findOrFail(int|string $id): Model
    {
        $model = $this->find($id);

        if ($model === null) {
            throw ModelNotFoundException::forModel($this->modelClass, $id);
        }

        return $model;
    }

    // -------------------------------------------------------------------------
    // Pagination
    // -------------------------------------------------------------------------

    /**
     * Paginate results. Reads the current page from $_GET['page'] when $page is null.
     *
     * Responsibility: Paginate results. Reads the current page from $_GET['page'] when $page is null.
     * @param int      $perPage Records per page (default 15).
     * @param int|null $page    Override the current page number.
     */
    public function paginate(int $perPage = 15, ?int $page = null): Pagination
    {
        $currentPage = $page ?? max(1, (int) ($_GET['page'] ?? 1));

        // Count without LIMIT / OFFSET / ORDER
        $countBuilder          = clone $this;
        $countBuilder->limit   = null;
        $countBuilder->offset  = null;
        $countBuilder->orders  = [];
        $total = $countBuilder->count();

        $lastPage    = max(1, (int) ceil($total / $perPage));
        $currentPage = min($currentPage, $lastPage);

        $items = $this->forPage($currentPage, $perPage)->get();

        return new Pagination(
            items:       $items,
            total:       $total,
            perPage:     $perPage,
            currentPage: $currentPage,
            lastPage:    $lastPage,
            nextPage:    $currentPage < $lastPage ? $currentPage + 1 : null,
            prevPage:    $currentPage > 1         ? $currentPage - 1 : null,
        );
    }

    // -------------------------------------------------------------------------
    // Soft-delete scopes
    // -------------------------------------------------------------------------

    /**
     * Include soft-deleted rows in the query results.
     *
     * Responsibility: Include soft-deleted rows in the query results.
     */
    public function withTrashed(): static
    {
        if (!$this->hasSoftDeletes()) {
            return $this;
        }

        $this->includeTrashed = true;

        // Remove the IS NULL scope added in the constructor
        $col          = $this->getSoftDeleteColumn();
        $this->wheres = array_values(array_filter(
            $this->wheres,
            fn (array $w): bool => !(
                $w['type'] === 'null' &&
                $w['column'] === $col  &&
                $w['not'] === false
            )
        ));

        return $this;
    }

    /**
     * Return only soft-deleted rows.
     *
     * Responsibility: Return only soft-deleted rows.
     */
    public function onlyTrashed(): static
    {
        $this->withTrashed(); // removes IS NULL scope
        $this->onlyTrashedRows = true;
        $this->whereNotNull($this->getSoftDeleteColumn());

        return $this;
    }

    // -------------------------------------------------------------------------
    // Internal — eager loading
    // -------------------------------------------------------------------------

    /**
     * Resolve each eager relation and batch-load results into all parent models. A "template" instance of the model class is created with no attributes so we can call the relation factory method and obtain a configured Relation object (with the correct FK / local-key column names). The template's own attribute values are irrelevant — matchEager() only uses the column names stored on the Relation object and the $models array it receives.
     *
     * Responsibility: Resolve each eager relation and batch-load results into all parent models. A "template" instance of the model class is created with no attributes so we can call the relation factory method and obtain a configured Relation object (with the correct FK / local-key column names). The template's own attribute values are irrelevant — matchEager() only uses the column names stored on the Relation object and the $models array it receives.
     * @param Model[] $models Hydrated parent models to distribute results into.
     */
    protected function eagerLoadRelations(array $models): void
    {
        foreach ($this->eagerLoad as $relation) {
            // Create a template instance to resolve relation metadata
            $template = ($this->modelClass)::newInstance();

            if (!method_exists($template, $relation)) {
                continue;
            }

            $relObj = $template->$relation();

            if (!($relObj instanceof Relation)) {
                continue;
            }

            $relObj->matchEager($models, $relation);
        }
    }

    // -------------------------------------------------------------------------
    // Internal — soft deletes
    // -------------------------------------------------------------------------

    /**
     * Automatically add WHERE deleted_at IS NULL when model uses HasSoftDeletesTrait.
     *
     * Responsibility: Automatically add WHERE deleted_at IS NULL when model uses HasSoftDeletesTrait.
     */
    protected function applySoftDeleteScope(): void
    {
        if ($this->softScopeApplied || !$this->hasSoftDeletes()) {
            return;
        }

        $this->whereNull($this->getSoftDeleteColumn());
        $this->softScopeApplied = true;
    }

    /**
     * Applies tenant filtering to model queries when the model uses tenant scoping.
     *
     * Responsibility: Applies tenant filtering to model queries when the model uses tenant scoping.
     */
    protected function applyTenantScope(): void
    {
        if ($this->tenantScopeApplied || !$this->hasTenantScope()) {
            return;
        }

        $tenantId = TenancyManager::getInstance()->currentTenantId();
        if ($tenantId > 0) {
            $this->whereEqual($this->getTenantColumn(), $tenantId);
        }

        $this->tenantScopeApplied = true;
    }

    /**
     * Check whether the bound model class declares soft-delete support.
     *
     * Responsibility: Check whether the bound model class declares soft-delete support.
     */
    protected function hasSoftDeletes(): bool
    {
        return defined("{$this->modelClass}::SOFT_DELETES");
    }

    /**
     * Determines whether the model class participates in tenant-scoped queries.
     *
     * Responsibility: Determines whether the model class participates in tenant-scoped queries.
     */
    protected function hasTenantScope(): bool
    {
        return defined("{$this->modelClass}::TENANT_SCOPED") && ($this->modelClass)::TENANT_SCOPED === true;
    }

    /**
     * Resolve the soft-delete timestamp column name.
     *
     * Responsibility: Resolve the soft-delete timestamp column name.
     */
    protected function getSoftDeleteColumn(): string
    {
        return defined("{$this->modelClass}::DELETED_AT")
            ? ($this->modelClass)::DELETED_AT
            : 'deleted_at';
    }

    /**
     * Returns the tenant column used by the model query scope.
     *
     * Responsibility: Returns the tenant column used by the model query scope.
     */
    protected function getTenantColumn(): string
    {
        return defined("{$this->modelClass}::TENANT_COLUMN")
            ? ($this->modelClass)::TENANT_COLUMN
            : 'tenant_id';
    }

    /**
     * Override aggregate() to bypass model hydration. QueryBuilder::aggregate() calls $this->get() and then does $results[0], which fails when get() returns a Collection instead of a raw array. Here we query the connection directly so the result is always a plain array.
     *
     * Responsibility: Override aggregate() to bypass model hydration. QueryBuilder::aggregate() calls $this->get() and then does $results[0], which fails when get() returns a Collection instead of a raw array. Here we query the connection directly so the result is always a plain array.
     */
    protected function aggregate(string $function, string $column = '*'): ?array
    {
        $this->columns = ["$function($column) as aggregate"];
        [$query, $bindings] = $this->compileSelect();
        $rows = $this->connection->select($query, $bindings);
        return $rows[0] ?? null;
    }

    // -------------------------------------------------------------------------
    // Internal — hydration
    // -------------------------------------------------------------------------

    /**
     * Hydrate an array of raw DB rows into Model instances.
     *
     * Responsibility: Hydrate an array of raw DB rows into Model instances.
     * @return Collection<Model>
     */
    protected function hydrateModels(array $rows): Collection
    {
        $models = array_map(
            fn (array $row): Model => ($this->modelClass)::fromRow($row),
            $rows
        );

        return new Collection($models);
    }
}
