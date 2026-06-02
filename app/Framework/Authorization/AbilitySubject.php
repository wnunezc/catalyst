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
     * Responsibility: Stores the target resource, optional record, and contextual authorization data.
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
     * @param array<string, mixed> $context
     */
    public static function make(string $resource, mixed $record = null, array $context = []): self
    {
        return new self($resource, $record, $context);
    }

    /**
     * Returns the canonical resource name being authorized.
     *
     * Responsibility: Returns the canonical resource name being authorized.
     */
    public function resource(): string
    {
        return $this->resource;
    }

    /**
     * Returns the optional record attached to the authorization subject.
     *
     * Responsibility: Returns the optional record attached to the authorization subject.
     */
    public function record(): mixed
    {
        return $this->record;
    }

    /**
     * Returns additional data used by permission condition matching.
     *
     * Responsibility: Returns additional data used by permission condition matching.
     * @return array<string, mixed>
     */
    public function context(): array
    {
        return $this->context;
    }
}
