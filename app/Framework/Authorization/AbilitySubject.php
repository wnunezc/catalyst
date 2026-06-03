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

namespace Catalyst\Framework\Authorization;

/**
 * Carries the resource, record, and context used by resource authorization policies.
 *
 * @package Catalyst\Framework\Authorization
 * Responsibility: Represents the subject passed to resource-level authorization checks.
 */
final class AbilitySubject
{
    /**
     * Stores the target resource, optional record, and contextual authorization data.
     *
     * Responsibility: Binds required collaborators or immutable state without executing the main workflow.
     * @param array<string, mixed> $context
     */
    public function __construct(
        private readonly string $resource,
        private readonly mixed $record = null,
        private readonly array $context = []
    ) {
    }

    /**
     * Builds an authorization subject for a resource ability check.
     *
     * Responsibility: Creates the normalized value object used by permission checks without consulting registries.
     * @param array<string, mixed> $context
     */
    public static function make(string $resource, mixed $record = null, array $context = []): self
    {
        return new self($resource, $record, $context);
    }

    /**
     * Returns a copy of the subject with a different authorization record.
     *
     * Responsibility: Defines the focused behavior owned by this method and keeps side effects limited to its caller contract.
     */
    public function withRecord(mixed $record): self
    {
        return new self($this->resource, $record, $this->context);
    }

    /**
     * Returns a copy of the subject with merged contextual authorization data.
     *
     * Responsibility: Defines the focused behavior owned by this method and keeps side effects limited to its caller contract.
     * @param array<string, mixed> $context
     */
    public function withContext(array $context): self
    {
        return new self($this->resource, $this->record, array_replace($this->context, $context));
    }

    /**
     * Returns a copy of the subject with one contextual authorization value.
     *
     * Responsibility: Defines the focused behavior owned by this method and keeps side effects limited to its caller contract.
     */
    public function withContextValue(string $key, mixed $value): self
    {
        $key = trim($key);
        if ($key === '') {
            return $this;
        }

        return $this->withContext([$key => $value]);
    }

    /**
     * Returns the canonical resource name being authorized.
     *
     * Responsibility: Provides read-only access to normalized state without mutating framework runtime.
     */
    public function resource(): string
    {
        return $this->resource;
    }

    /**
     * Returns the optional record attached to the authorization subject.
     *
     * Responsibility: Provides read-only access to normalized state without mutating framework runtime.
     */
    public function record(): mixed
    {
        return $this->record;
    }

    /**
     * Returns additional data used by permission condition matching.
     *
     * Responsibility: Provides read-only access to normalized state without mutating framework runtime.
     * @return array<string, mixed>
     */
    public function context(): array
    {
        return $this->context;
    }

    /**
     * Returns one contextual authorization value or the provided default.
     *
     * Responsibility: Provides read-only access to normalized state without mutating framework runtime.
     */
    public function contextValue(string $key, mixed $default = null): mixed
    {
        $key = trim($key);
        if ($key === '') {
            return $default;
        }

        return $this->context[$key] ?? $default;
    }
}
