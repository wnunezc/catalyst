<?php

declare(strict_types=1);

namespace Catalyst\Framework\Navigation;

/**
 * Provides navigation for authenticated application and account surfaces.
 *
 * Responsibility: Composes Framework and App declarations before applying the shared recursive contract.
 */
final class ApplicationNavigationProvider implements NavigationModelProvider
{
    public const ID = 'application';

    /**
     * Returns the semantic model identifier.
     */
    public function id(): string
    {
        return self::ID;
    }

    /**
     * Composes Framework and App navigation contributions.
     *
     * @param array<string, mixed> $context
     * @return list<array<string, mixed>>
     */
    public function provide(array $context): array
    {
        $currentPath = (string) ($context['current_path'] ?? '/');
        $appNodes = NavigationRegistry::getInstance()->application(
            $currentPath,
            is_array($context['user'] ?? null) ? $context['user'] : null
        );
        $nodes = $this->sortNodes(array_merge($this->frameworkNodes(), $appNodes));

        return NavigationTreeNormalizer::normalize(
            $nodes,
            $currentPath
        );
    }

    /**
     * Returns Framework-owned account capabilities for application surfaces.
     *
     * Responsibility: Declares common account navigation independently from physical App route ownership.
     * @return list<array<string, mixed>>
     */
    private function frameworkNodes(): array
    {
        return [
            [
                'kind' => 'title',
                'label' => __('account.nav.account'),
                'order' => 100,
            ],
            [
                'kind' => 'link',
                'label' => __('account.nav.profile'),
                'href' => '/account/profile',
                'icon' => 'ti ti-user-circle',
                'hint' => __('account.nav_hints.profile'),
                'order' => 110,
            ],
            [
                'kind' => 'container',
                'label' => __('account.nav.mfa'),
                'icon' => 'ti ti-2fa',
                'hint' => __('account.nav_hints.mfa'),
                'order' => 120,
                'children' => [
                    [
                        'kind' => 'link',
                        'label' => __('account.nav.mfa_manage'),
                        'href' => '/account/security/mfa',
                        'hint' => __('account.nav_hints.mfa_manage'),
                        'order' => 10,
                    ],
                    [
                        'kind' => 'link',
                        'label' => __('account.nav.mfa_recovery'),
                        'href' => '/account/recovery/mfa',
                        'hint' => __('account.nav_hints.mfa_recovery'),
                        'order' => 20,
                    ],
                ],
            ],
            [
                'kind' => 'container',
                'label' => __('account.nav.recovery'),
                'icon' => 'ti ti-lifebuoy',
                'hint' => __('account.nav_hints.recovery'),
                'order' => 130,
                'children' => [
                    [
                        'kind' => 'link',
                        'label' => __('account.nav.recovery_support'),
                        'href' => '/account/recovery/support',
                        'hint' => __('account.nav_hints.recovery_support'),
                        'order' => 10,
                    ],
                    [
                        'kind' => 'link',
                        'label' => __('account.nav.recovery_compromised'),
                        'href' => '/account/recovery/compromised',
                        'hint' => __('account.nav_hints.recovery_compromised'),
                        'order' => 20,
                    ],
                ],
            ],
            [
                'kind' => 'link',
                'label' => __('account.nav.activity'),
                'href' => '/account/activity',
                'icon' => 'ti ti-history',
                'hint' => __('account.nav_hints.activity'),
                'order' => 140,
            ],
        ];
    }

    /**
     * Orders Framework and App contributions as one application tree.
     *
     * Responsibility: Applies declaration order across owners while preserving recursively ordered descendants.
     *
     * @param list<array<string, mixed>> $nodes
     * @return list<array<string, mixed>>
     */
    private function sortNodes(array $nodes): array
    {
        usort($nodes, static function (array $left, array $right): int {
            return [(int) ($left['order'] ?? 999), (string) ($left['label'] ?? '')]
                <=> [(int) ($right['order'] ?? 999), (string) ($right['label'] ?? '')];
        });

        return $nodes;
    }
}
