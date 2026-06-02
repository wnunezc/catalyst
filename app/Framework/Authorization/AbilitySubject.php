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
 * Defines the Ability Subject class contract.
 *
 * @package Catalyst\Framework\Authorization
 * Responsibility: Coordinates the ability subject behavior within its module boundary.
 */
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

    /**
     * Handles the resource workflow.
     */
    public function resource(): string
    {
        return $this->resource;
    }

    /**
     * Handles the record workflow.
     */
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
