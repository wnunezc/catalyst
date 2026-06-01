<?php

declare(strict_types=1);

namespace Catalyst\Framework\Authorization;

final class AbilitySubject
{
    /**
     * @param array<string, mixed> $context
     */
    public function __construct(
        private readonly string $resource,
        private readonly mixed $record = null,
        private readonly array $context = []
    ) {
    }

    /**
     * @param array<string, mixed> $context
     */
    public static function make(string $resource, mixed $record = null, array $context = []): self
    {
        return new self($resource, $record, $context);
    }

    public function resource(): string
    {
        return $this->resource;
    }

    public function record(): mixed
    {
        return $this->record;
    }

    /**
     * @return array<string, mixed>
     */
    public function context(): array
    {
        return $this->context;
    }
}
