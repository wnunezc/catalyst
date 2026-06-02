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

use Catalyst\Framework\Audit\AuditLogManager;
use Catalyst\Framework\Database\Model;

/**
 * HIPAA-compliant audit trail for Model subclasses.
 *
 * Automatically records which authenticated user created, modified,
 * or deleted each row. Satisfies HIPAA Technical Safeguard §164.312(b)
 * (Audit Controls) at the data layer.
 *
 * ## Usage
 *
 *   class MedicalRecord extends Model {
 *       use HasTimestampsTrait, HasAuditLogTrait;
 *   }
 *
 * ## Required DB columns
 *
 *   created_by INT UNSIGNED NULL,
 *   updated_by INT UNSIGNED NULL,
 *   deleted_by INT UNSIGNED NULL   -- only needed when combined with HasSoftDeletesTrait
 *
 * ## How it works
 *
 * - On INSERT  → sets created_by and updated_by to the current session user ID.
 * - On UPDATE  → refreshes updated_by.
 * - On DELETE  → sets deleted_by (cooperates with HasSoftDeletesTrait::delete() which
 *                includes dirty fields in its UPDATE before removing the scope).
 *
 * ## Combine with HasSoftDeletesTrait
 *
 * When both traits are used, soft-delete automatically persists deleted_by:
 *
 *   class User extends Model {
 *       use HasSoftDeletesTrait, HasAuditLogTrait;
 *   }
 *
 *   $user->delete();
 *   // → sets deleted_at AND deleted_by in a single UPDATE
 *
 * ## Session user resolution
 *
 * Reads $_SESSION['user_id'] set by AuthManager::createSession().
 * Returns null (and skips the column) when no session is active or no user
 * is authenticated — allowing unauthenticated operations (e.g. user registration)
 * to proceed without errors.
 *
 * Override resolveCurrentUserId() in your model to customise resolution logic.
 *
 * @package Catalyst\Framework\Traits
 * Responsibility: Stamps actor identifiers and records model lifecycle mutations.
 */
trait HasAuditLogTrait
{
    // -------------------------------------------------------------------------
    // Boot — registers lifecycle hooks
    // -------------------------------------------------------------------------

    /**
     * Called once per class by Model::bootIfNeeded().
     */
    protected static function bootHasAuditLogTrait(): void
    {
        static::registerHook('inserting', function (Model $model): void {
            $model->stampCreatedBy();
            $model->stampUpdatedBy();
        });

        static::registerHook('inserted', function (Model $model): void {
            AuditLogManager::getInstance()->recordCreated($model);
        });

        static::registerHook('updating', function (Model $model): void {
            $model->stampUpdatedBy();
            AuditLogManager::getInstance()->rememberModelState($model, 'updated');
        });

        static::registerHook('updated', function (Model $model): void {
            AuditLogManager::getInstance()->recordPendingMutation($model, 'updated');
        });

        // deleting fires both for hard deletes and before HasSoftDeletesTrait updates the row.
        // For hard deletes the field is set but the row is removed — harmless.
        // For soft deletes HasSoftDeletesTrait::delete() includes dirty fields in its UPDATE.
        static::registerHook('deleting', function (Model $model): void {
            $model->stampDeletedBy();
            AuditLogManager::getInstance()->rememberModelState($model, 'deleted');
        });

        static::registerHook('deleted', function (Model $model): void {
            AuditLogManager::getInstance()->recordPendingMutation($model, 'deleted');
        });

        static::registerHook('restoring', function (Model $model): void {
            AuditLogManager::getInstance()->rememberModelState($model, 'restored');
        });

        static::registerHook('restored', function (Model $model): void {
            AuditLogManager::getInstance()->recordPendingMutation($model, 'restored');
        });
    }

    // -------------------------------------------------------------------------
    // Stamp methods (called by hooks; may also be called manually)
    // -------------------------------------------------------------------------

    /**
     * Set created_by if not already set (preserves explicit overrides).
     *
     * Responsibility: Set created_by if not already set (preserves explicit overrides).
     */
    public function stampCreatedBy(): void
    {
        $userId = $this->resolveCurrentUserId();

        if ($userId !== null && !isset($this->attributes['created_by'])) {
            $this->attributes['created_by'] = $userId;
        }
    }

    /**
     * Refresh updated_by on every update.
     *
     * Responsibility: Refresh updated_by on every update.
     */
    public function stampUpdatedBy(): void
    {
        $userId = $this->resolveCurrentUserId();

        if ($userId !== null) {
            $this->attributes['updated_by'] = $userId;
        }
    }

    /**
     * Set deleted_by before the row is soft-deleted or hard-deleted. For hard deletes the column value is never persisted — this is a no-op.
     *
     * Responsibility: Set deleted_by before the row is soft-deleted or hard-deleted. For hard deletes the column value is never persisted — this is a no-op.
     */
    public function stampDeletedBy(): void
    {
        $userId = $this->resolveCurrentUserId();

        if ($userId !== null) {
            $this->attributes['deleted_by'] = $userId;
        }
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    /**
     * Returns the user identifier that created the model.
     *
     * Responsibility: Returns the user identifier that created the model.
     */
    public function createdBy(): ?int
    {
        $v = $this->attributes['created_by'] ?? null;
        return $v !== null ? (int) $v : null;
    }

    /**
     * Returns the user identifier that last updated the model.
     *
     * Responsibility: Returns the user identifier that last updated the model.
     */
    public function updatedBy(): ?int
    {
        $v = $this->attributes['updated_by'] ?? null;
        return $v !== null ? (int) $v : null;
    }

    /**
     * Returns the user identifier that deleted the model.
     *
     * Responsibility: Returns the user identifier that deleted the model.
     */
    public function deletedBy(): ?int
    {
        $v = $this->attributes['deleted_by'] ?? null;
        return $v !== null ? (int) $v : null;
    }

    // -------------------------------------------------------------------------
    // User ID resolution
    // -------------------------------------------------------------------------

    /**
     * Resolve the current authenticated user ID from the session. Returns null when: - PHP session is not active - No user is logged in (user_id absent in session) Override this method in your model to use a different resolution strategy (e.g. reading from a request context object, JWT claim, etc.).
     *
     * Responsibility: Resolve the current authenticated user ID from the session. Returns null when: - PHP session is not active - No user is logged in (user_id absent in session) Override this method in your model to use a different resolution strategy (e.g. reading from a request context object, JWT claim, etc.).
     */
    protected function resolveCurrentUserId(): ?int
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return null;
        }

        $id = $_SESSION['user_id'] ?? null;

        return $id !== null ? (int) $id : null;
    }
}
