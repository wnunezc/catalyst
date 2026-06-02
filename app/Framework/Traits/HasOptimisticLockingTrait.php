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
 * Adds lock-version checks to model updates.
 *
 * @package Catalyst\Framework\Traits
 * Responsibility: Rejects stale writes and increments optimistic lock versions.
 */
trait HasOptimisticLockingTrait
{
    public const OPTIMISTIC_LOCKING = true;
    public const LOCK_VERSION = 'lock_version';

    /**
     * Registers the update hook that validates and increments lock versions.
     */
    protected static function bootHasOptimisticLockingTrait(): void
    {
        static::registerHook('inserting', function (Model $model): void {
            $column = static::LOCK_VERSION;

            if ($model->getRawAttribute($column) === null || $model->getRawAttribute($column) === '') {
                $model->setAttribute($column, 1);
            }
        });
    }

    /**
     * Returns the model's current lock version.
     *
     * Responsibility: Returns the model's current lock version.
     */
    public function currentLockVersion(): ?int
    {
        $value = $this->getRawAttribute(static::LOCK_VERSION);

        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }
}
