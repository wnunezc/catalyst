<?php

declare(strict_types=1);

namespace Catalyst\Framework\Traits;

use Catalyst\Framework\Database\Model;

trait HasOptimisticLockingTrait
{
    public const OPTIMISTIC_LOCKING = true;
    public const LOCK_VERSION = 'lock_version';

    protected static function bootHasOptimisticLockingTrait(): void
    {
        static::registerHook('inserting', function (Model $model): void {
            $column = static::LOCK_VERSION;

            if ($model->getRawAttribute($column) === null || $model->getRawAttribute($column) === '') {
                $model->setAttribute($column, 1);
            }
        });
    }

    public function currentLockVersion(): ?int
    {
        $value = $this->getRawAttribute(static::LOCK_VERSION);

        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }
}
