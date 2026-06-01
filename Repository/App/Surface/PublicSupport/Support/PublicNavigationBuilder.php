<?php

declare(strict_types=1);

namespace App\Surface\PublicSupport\Support;

use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Framework\Navigation\NavigationRegistry;

final class PublicNavigationBuilder
{
    /**
     * Build only application/public-surface navigation.
     *
     * Session actions live in the status bar account menu so public surfaces do
     * not duplicate account links or leak Admin Shell destinations into the
     * public navbar.
     *
     * @return array<int, array<string, mixed>>
     */
    public function build(string $currentPath): array
    {
        $auth = AuthManager::getInstance();
        $user = $auth->check() ? ($auth->user() ?? []) : null;

        return NavigationRegistry::getInstance()->publicMenu($currentPath, $user);
    }
}
