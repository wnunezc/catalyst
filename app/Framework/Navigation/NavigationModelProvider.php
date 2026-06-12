<?php

declare(strict_types=1);

namespace Catalyst\Framework\Navigation;

/**
 * Provides one semantic navigation model for the shared sidebar.
 *
 * Responsibility: Defines the stable boundary used by the central selector to obtain normalized navigation trees.
 */
interface NavigationModelProvider
{
    /**
     * Returns the semantic model identifier.
     */
    public function id(): string;

    /**
     * Builds the normalized navigation tree for one document context.
     *
     * @param array<string, mixed> $context
     * @return list<array<string, mixed>>
     */
    public function provide(array $context): array;
}
