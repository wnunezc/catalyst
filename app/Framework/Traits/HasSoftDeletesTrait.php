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
use Catalyst\Framework\Database\ModelQueryBuilder;

/**
 * Soft-delete support for Model subclasses.
 *
 * Instead of removing rows from the database, sets a `deleted_at` timestamp.
 * The ORM automatically excludes soft-deleted rows from all queries unless
 * withTrashed() or onlyTrashed() is called.
 *
 * ## Usage
 *
 *   class User extends Model {
 *       use HasSoftDeletesTrait;
 *   }
 *
 *   $user->delete();                         // sets deleted_at, row stays in DB
 *   $user->restore();                        // clears deleted_at
 *   $user->forceDelete();                    // hard-deletes permanently
 *   User::withTrashed()->get();              // includes soft-deleted rows
 *   User::onlyTrashed()->get();              // only soft-deleted rows
 *
 * ## Required DB columns
 *
 *   deleted_at DATETIME NULL DEFAULT NULL
 *
 * ## Column customization
 *
 *   const DELETED_AT = 'deleted_at';
 *
 * @package Catalyst\Framework\Traits
 */
trait HasSoftDeletesTrait
{
    /** Marker read by ModelQueryBuilder to auto-apply the soft-delete scope. */
    public const SOFT_DELETES = true;

    /** Column holding the soft-deletion timestamp. */
    public const DELETED_AT = 'deleted_at';

    // -------------------------------------------------------------------------
    // Boot — no hooks needed; scope is applied in ModelQueryBuilder constructor
    // -------------------------------------------------------------------------

    /**
     * Handles the boot has soft deletes trait workflow.
     */
    protected static function bootHasSoftDeletesTrait(): void
    {
        // ModelQueryBuilder reads SOFT_DELETES and DELETED_AT constants directly.
        // No additional hooks are required here.
    }

    // -------------------------------------------------------------------------
    // Override Model::delete() → soft delete
    // -------------------------------------------------------------------------

    /**
     * Soft-delete the model by setting the deleted_at column.
     *
     * Fires the deleting / deleted hooks so other traits (e.g. HasAuditLogTrait)
     * can inject fields (deleted_by) before the UPDATE is executed.
     */
    public function delete(): bool
    {
        if (!$this->exists()) {
            return false;
        }

        $this->fireHook('deleting');

        $col = static::DELETED_AT;
        $now = date('Y-m-d H:i:s');

        // Mark the attribute so getDirty() picks it up
        $this->attributes[$col] = $now;

        // Build the update payload from all dirty fields (includes deleted_by
        // if HasAuditLogTrait ran its hook and set it before this point)
        $dirty = $this->getDirty();
        unset($dirty[static::$primaryKey]);

        if (!empty($dirty)) {
            static::getConnectionInstance()
                ->table(static::getTable())
                ->whereEqual(static::getPrimaryKey(), $this->getKey())
                ->update($dirty);
        }

        $this->original = $this->attributes;

        $this->fireHook('deleted');

        return true;
    }

    // -------------------------------------------------------------------------
    // Hard delete (bypass soft-delete)
    // -------------------------------------------------------------------------

    /**
     * Permanently remove the row from the database.
     */
    public function forceDelete(): bool
    {
        if (!$this->exists()) {
            return false;
        }

        $this->fireHook('deleting');

        $affected = static::getConnectionInstance()
            ->table(static::getTable())
            ->whereEqual(static::getPrimaryKey(), $this->getKey())
            ->delete();

        if ($affected > 0) {
            // Mark as non-existent so further saves create a new row
            $this->attributes = [];
            $this->original   = [];
            // exists() reads $this->exists (protected in Model)
            $this->exists = false;

            $this->fireHook('deleted');
            return true;
        }

        return false;
    }

    // -------------------------------------------------------------------------
    // Restore
    // -------------------------------------------------------------------------

    /**
     * Restore a soft-deleted model by clearing deleted_at (and deleted_by).
     */
    public function restore(): bool
    {
        if (!$this->trashed()) {
            return false;
        }

        $this->fireHook('restoring');

        $col = static::DELETED_AT;
        $this->attributes[$col] = null;

        // Clear deleted_by if present (set by HasAuditLogTrait)
        if (array_key_exists('deleted_by', $this->attributes)) {
            $this->attributes['deleted_by'] = null;
        }

        $dirty = $this->getDirty();
        unset($dirty[static::$primaryKey]);

        if (!empty($dirty)) {
            static::getConnectionInstance()
                ->table(static::getTable())
                ->whereEqual(static::getPrimaryKey(), $this->getKey())
                ->update($dirty);
        }

        $this->original = $this->attributes;
        $this->fireHook('restored');

        return true;
    }

    // -------------------------------------------------------------------------
    // Status check
    // -------------------------------------------------------------------------

    /**
     * Check whether this model has been soft-deleted.
     */
    public function trashed(): bool
    {
        return !empty($this->attributes[static::DELETED_AT]);
    }

    // -------------------------------------------------------------------------
    // Static query shortcuts
    // -------------------------------------------------------------------------

    /**
     * Start a query that includes soft-deleted rows.
     */
    public static function withTrashed(): ModelQueryBuilder
    {
        return static::query()->withTrashed();
    }

    /**
     * Start a query that returns only soft-deleted rows.
     */
    public static function onlyTrashed(): ModelQueryBuilder
    {
        return static::query()->onlyTrashed();
    }
}
