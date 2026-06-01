<?php

declare(strict_types=1);

namespace Catalyst\Framework\Workflow;

use Catalyst\Framework\Traits\SingletonTrait;

final class WorkflowDefinitionRegistry
{
    use SingletonTrait;

    /** @var array<string, WorkflowDefinition> */
    private array $definitions = [];

    protected function __construct()
    {
        FrameworkWorkflowCatalog::registerDefaults($this);
    }

    public function register(WorkflowDefinition $definition): self
    {
        $this->definitions[$definition->key] = $definition;

        return $this;
    }

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
