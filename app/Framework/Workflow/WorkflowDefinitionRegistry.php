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

namespace Catalyst\Framework\Workflow;

use Catalyst\Framework\Traits\SingletonTrait;

/**
 * Defines the Workflow Definition Registry class contract.
 *
 * @package Catalyst\Framework\Workflow
 * Responsibility: Coordinates the workflow definition registry behavior within its module boundary.
 */
final class WorkflowDefinitionRegistry
{
    use SingletonTrait;

    /** @var array<string, WorkflowDefinition> */
    private array $definitions = [];

    /**
     * Initializes the Workflow Definition Registry instance.
     */
    protected function __construct()
    {
        FrameworkWorkflowCatalog::registerDefaults($this);
    }

    /**
     * Registers the requested definition.
     */
    public function register(WorkflowDefinition $definition): self
    {
        $this->definitions[$definition->key] = $definition;

        return $this;
    }

    /**
     * Returns the runtime value.
     */
    public function get(string $key): ?WorkflowDefinition
    {
        return $this->definitions[$key] ?? null;
    }

    /**
     * @return WorkflowDefinition[]
     */
    public function all(): array
    {
        return array_values($this->definitions);
    }

    /**
     * Handles the for resource workflow.
     */
    public function forResource(string $resourceKey): ?WorkflowDefinition
    {
        foreach ($this->definitions as $definition) {
            if ($definition->resourceKey === $resourceKey) {
                return $definition;
            }
        }

        return null;
    }
}
