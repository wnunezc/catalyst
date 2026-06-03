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

namespace Catalyst\Framework\Reference;

use Catalyst\Framework\Traits\SingletonTrait;

/**
 * Registry for resource keys that can be referenced generically.
 *
 * @package Catalyst\Framework\Reference
 * Responsibility: Register allowed generic reference resource types and validate references against them.
 */
final class EntityReferenceRegistry
{
    use SingletonTrait;

    /**
     * @var array<string, array<string, mixed>>
     */
    private array $definitions = [];

    /**
     * Registers or replaces one referenceable resource definition.
     *
     * Responsibility: Owns the catalog of resource types that may be used in generic references.
     * @param array<string, mixed> $definition
     */
    public function register(string $resourceKey, array $definition = []): void
    {
        $resourceKey = trim(strtolower($resourceKey));
        if (!EntityReference::isValidResourceKey($resourceKey)) {
            return;
        }

        $this->definitions[$resourceKey] = [
            'label' => trim((string)($definition['label'] ?? $resourceKey)),
            'owner_field' => trim((string)($definition['owner_field'] ?? '')),
            'visibility_field' => trim((string)($definition['visibility_field'] ?? '')),
            'route_pattern' => trim((string)($definition['route_pattern'] ?? '')),
            'metadata' => is_array($definition['metadata'] ?? null) ? $definition['metadata'] : [],
        ];
    }

    /**
     * Returns all registered reference definitions.
     *
     * Responsibility: Provides deterministic registry output for inspection and documentation.
     * @return array<string, array<string, mixed>>
     */
    public function all(): array
    {
        ksort($this->definitions);
        return $this->definitions;
    }

    /**
     * Finds a registered reference definition.
     *
     * Responsibility: Looks up reference metadata without creating implicit resource types.
     * @return array<string, mixed>|null
     */
    public function find(string $resourceKey): ?array
    {
        return $this->definitions[trim(strtolower($resourceKey))] ?? null;
    }

    /**
     * Reports whether a reference type is registered.
     *
     * Responsibility: Guards generic references against undeclared resource namespaces.
     */
    public function allows(string $resourceKey): bool
    {
        return $this->find($resourceKey) !== null;
    }

    /**
     * Validates a generic entity reference against registered resource types.
     *
     * Responsibility: Connects typed reference payloads to the registered resource catalog.
     */
    public function validate(EntityReference $reference): bool
    {
        return $this->allows($reference->resourceKey());
    }
}