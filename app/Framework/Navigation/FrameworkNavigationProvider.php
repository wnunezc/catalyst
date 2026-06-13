<?php

declare(strict_types=1);

namespace Catalyst\Framework\Navigation;

/**
 * Provides the canonical Framework navigation model.
 *
 * Responsibility: Combines the curated Framework catalog with authorized runtime module declarations.
 */
final class FrameworkNavigationProvider implements NavigationModelProvider
{
    public const ID = 'framework';

    /**
     * Returns the semantic model identifier.
     */
    public function id(): string
    {
        return self::ID;
    }

    /**
     * Builds authorized Framework navigation.
     *
     * @param array<string, mixed> $context
     * @return list<array<string, mixed>>
     */
    public function provide(array $context): array
    {
        $currentPath = (string) ($context['current_path'] ?? '/');
        $user = is_array($context['user'] ?? null) ? $context['user'] : null;
        $shell = NavigationRegistry::getInstance()->shell($currentPath, $user);

        return ShellNavigationPresenter::fromShell($shell, $currentPath);
    }
}
