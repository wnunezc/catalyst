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
 * Defines the Has Optimistic Locking Trait trait contract.
 *
 * @package Catalyst\Framework\Traits
 * Responsibility: Coordinates the has optimistic locking trait behavior within its module boundary.
 */
trait HasOptimisticLockingTrait
{
    public const OPTIMISTIC_LOCKING = true;
    public const LOCK_VERSION = 'lock_version';

    /**
     * Handles the boot has optimistic locking trait workflow.
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
     * Handles the current lock version workflow.
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
