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

/**
 * Automatic timestamp management for Model subclasses.
 *
 * Registers lifecycle hooks that populate `created_at` and `updated_at`
 * before every insert and update respectively.
 *
 * ## Usage
 *
 *   class Post extends Model {
 *       use HasTimestampsTrait;
 *   }
 *
 * ## Required DB columns
 *
 *   created_at DATETIME NULL,
 *   updated_at DATETIME NULL
 *
 * ## Customization
 *
 * Override the column name constants in your model:
 *
 *   const CREATED_AT = 'created_at';
 *   const UPDATED_AT = 'updated_at';
 *
 * @package Catalyst\Framework\Traits
 */
trait HasTimestampsTrait
{
    /** Column storing the creation timestamp. */
    public const CREATED_AT = 'created_at';

    /** Column storing the last-update timestamp. */
    public const UPDATED_AT = 'updated_at';

    // -------------------------------------------------------------------------
    // Boot — registers hooks into the Model lifecycle
    // -------------------------------------------------------------------------

    /**
     * Called once per class by Model::bootIfNeeded().
     * Registers inserting and updating hooks.
     */
    protected static function bootHasTimestampsTrait(): void
    {
        static::registerHook('inserting', function (Model $model): void {
            $model->setCreatedAt();
            $model->setUpdatedAt();
        });

        static::registerHook('updating', function (Model $model): void {
            $model->setUpdatedAt();
        });
    }

    // -------------------------------------------------------------------------
    // Timestamp setters
    // -------------------------------------------------------------------------

    /**
     * Set created_at only if not already present.
     * Allows explicit creation timestamps to be preserved.
     */
    public function setCreatedAt(): void
    {
        $col = static::CREATED_AT;

        if (!isset($this->attributes[$col])) {
            $this->attributes[$col] = $this->freshTimestamp();
        }
    }

    /**
     * Always refresh updated_at on every update.
     */
    public function setUpdatedAt(): void
    {
        $this->attributes[static::UPDATED_AT] = $this->freshTimestamp();
    }

    /**
     * Return the current timestamp string in the format expected by the DB.
     */
    public function freshTimestamp(): string
    {
        return date('Y-m-d H:i:s');
    }

    /**
     * Convenience: update updated_at and save.
     */
    public function touch(): bool
    {
        $this->setUpdatedAt();
        return $this->save();
    }
}
