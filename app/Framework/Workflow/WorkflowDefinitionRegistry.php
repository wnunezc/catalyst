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
 * Stores workflow definitions indexed by workflow and resource key.
 *
 * @package Catalyst\Framework\Workflow
 * Responsibility: Registers built-in definitions and resolves definitions for workflow execution.
 */
final class WorkflowDefinitionRegistry
{
    use SingletonTrait;

    /** @var array<string, WorkflowDefinition> */
    private array $definitions = [];

    /**
     * Initializes the Workflow Definition Registry instance.
     *
     * Responsibility: Initializes the Workflow Definition Registry instance.
     */
    protected function __construct()
    {
        FrameworkWorkflowCatalog::registerDefaults($this);
    }

    /**
     * Registers a workflow definition under its workflow and resource keys.
     *
     * Responsibility: Registers a workflow definition under its workflow and resource keys.
     */
    public function register(WorkflowDefinition $definition): self
    {
        $this->definitions[$definition->key] = $definition;

        return $this;
    }

    /**
     * Returns a workflow definition by key.
     *
     * Responsibility: Returns a workflow definition by key.
     */
    public function get(string $key): ?WorkflowDefinition
    {
        return $this->definitions[$key] ?? null;
    }

    /**
     * Returns all registered workflow definitions.
     *
     * Responsibility: Returns all registered workflow definitions.
     * @return WorkflowDefinition[]
     */
    public function all(): array
    {
        return array_values($this->definitions);
    }

    /**
     * Returns the workflow definition assigned to a resource key.
     *
     * Responsibility: Returns the workflow definition assigned to a resource key.
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
