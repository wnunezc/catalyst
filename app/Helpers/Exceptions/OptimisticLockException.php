<?php

declare(strict_types=1);

namespace Catalyst\Helpers\Exceptions;

use RuntimeException;

final class OptimisticLockException extends RuntimeException
{
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

    public static function forModel(
        string $modelClass,
        int|string|null $recordId,
        string $column,
        int $expectedVersion,
        ?int $currentVersion = null
    ): self {
        return new self($modelClass, $recordId, $column, $expectedVersion, $currentVersion);
    }

    public function modelClass(): string
    {
        return $this->modelClass;
    }

    public function recordId(): int|string|null
    {
        return $this->recordId;
    }

    public function column(): string
    {
        return $this->column;
    }

    public function expectedVersion(): int
    {
        return $this->expectedVersion;
    }

    public function currentVersion(): ?int
    {
        return $this->currentVersion;
    }
}
