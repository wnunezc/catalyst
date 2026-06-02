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

namespace Catalyst\Helpers\Exceptions;

use RuntimeException;

/**
 * Represents an optimistic-lock conflict while persisting a model.
 *
 * @package Catalyst\Helpers\Exceptions
 * Responsibility: Carries model identity and expected versus stored lock versions.
 */
final class OptimisticLockException extends RuntimeException
{
    /**
     * Initializes the Optimistic Lock Exception instance.
     *
     * Responsibility: Initializes the Optimistic Lock Exception instance.
     */
    public function __construct(
        private readonly string $modelClass,
        private readonly int|string|null $recordId,
        private readonly string $column,
        private readonly int $expectedVersion,
        private readonly ?int $currentVersion = null
    ) {
        $identifier = $recordId === null ? '(unsaved)' : '#' . $recordId;
        $message = sprintf(
            'Concurrency conflict detected for %s%s. Expected %s=%d%s.',
            $modelClass,
            $identifier,
            $column,
            $expectedVersion,
            $currentVersion !== null ? sprintf(', but storage is already at %d', $currentVersion) : ''
        );

        parent::__construct($message);
    }

    /**
     * Creates a conflict exception for a model persistence attempt.
     */
    public static function forModel(
        string $modelClass,
        int|string|null $recordId,
        string $column,
        int $expectedVersion,
        ?int $currentVersion = null
    ): self {
        return new self($modelClass, $recordId, $column, $expectedVersion, $currentVersion);
    }

    /**
     * Returns the conflicted model class.
     *
     * Responsibility: Returns the conflicted model class.
     */
    public function modelClass(): string
    {
        return $this->modelClass;
    }

    /**
     * Returns the conflicted record identifier.
     *
     * Responsibility: Returns the conflicted record identifier.
     */
    public function recordId(): int|string|null
    {
        return $this->recordId;
    }

    /**
     * Returns the lock-version column name.
     *
     * Responsibility: Returns the lock-version column name.
     */
    public function column(): string
    {
        return $this->column;
    }

    /**
     * Returns the version expected by the writer.
     *
     * Responsibility: Returns the version expected by the writer.
     */
    public function expectedVersion(): int
    {
        return $this->expectedVersion;
    }

    /**
     * Returns the current stored version when known.
     *
     * Responsibility: Returns the current stored version when known.
     */
    public function currentVersion(): ?int
    {
        return $this->currentVersion;
    }
}
