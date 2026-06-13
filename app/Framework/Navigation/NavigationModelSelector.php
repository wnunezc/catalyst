<?php

declare(strict_types=1);

namespace Catalyst\Framework\Navigation;

use Catalyst\Framework\Traits\SingletonTrait;

/**
 * Selects one of the approved semantic navigation models.
 *
 * Responsibility: Prevents arbitrary sidebar replacement by dispatching only to fixed, normalized model providers.
 */
final class NavigationModelSelector
{
    use SingletonTrait;

    /**
     * @var array<string, NavigationModelProvider>
     */
    private array $providers;

    /**
     * Initializes the fixed navigation provider registry.
     *
     * Responsibility: Registers the three approved model identifiers without runtime aliases or profile semantics.
     */
    protected function __construct()
    {
        $providers = [
            new DemoUiNavigationProvider(),
            new FrameworkNavigationProvider(),
            new ApplicationNavigationProvider(),
        ];

        $this->providers = [];
        foreach ($providers as $provider) {
            $this->providers[$provider->id()] = $provider;
        }
    }

    /**
     * Selects and builds one approved navigation model.
     *
     * Responsibility: Returns an empty valid tree for unknown identifiers instead of falling back to another model.
     *
     * @param array<string, mixed> $context
     * @return list<array<string, mixed>>
     */
    public function select(string $model, array $context): array
    {
        $provider = $this->providers[$model] ?? null;

        return $provider?->provide($context) ?? [];
    }

    /**
     * Returns the approved semantic model identifiers.
     *
     * @return string[]
     */
    public function ids(): array
    {
        return array_keys($this->providers);
    }
}
