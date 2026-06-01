<?php

declare(strict_types=1);

namespace Catalyst\Framework\Navigation;

use Catalyst\Framework\Authorization\PermissionRegistry;
use Catalyst\Framework\Module\ModuleRegistry;
use Catalyst\Framework\Traits\SingletonTrait;
use Catalyst\Helpers\Config\ConfigManager;

final class NavigationRegistry
{
    use SingletonTrait;

    /**
     * @var array<string, array{label_key: string, description_key: string, icon: string, order: int}>
     */
    private const ADMIN_CONTEXTS = [
        'configuration' => [
            'label_key' => 'Configuration',
            'description_key' => 'Environment, health, appearance, feature flags, plugins and backups.',
            'icon' => 'ti ti-settings-cog',
            'order' => 10,
        ],
        'workspaces' => [
            'label_key' => 'Workspaces',
            'description_key' => 'Catalogs, module designer, media, documents and localization tools.',
            'icon' => 'ti ti-layout-grid',
            'order' => 20,
        ],
        'operations' => [
            'label_key' => 'Operations',
            'description_key' => 'Deployments and tenancy operations.',
            'icon' => 'ti ti-briefcase-2',
            'order' => 30,
        ],
        'users' => [
            'label_key' => 'Users',
            'description_key' => 'User management, enrollment, roles and permissions.',
            'icon' => 'ti ti-users',
            'order' => 40,
        ],
        'devtools' => [
            'label_key' => 'Devtools',
            'description_key' => 'Developer diagnostics and internal showcases.',
            'icon' => 'ti ti-flask',
            'order' => 90,
        ],
    ];

    /**
     * @return array<string, mixed>
     */
    public function adminShell(string $currentPath, ?array $user = null): array
    {
        $itemsByContext = [];

        foreach (ModuleRegistry::getInstance()->active() as $module) {
            foreach ((array)($module['navigation']['admin'] ?? []) as $item) {
                if (!is_array($item) || !$this->isVisible((array)($item['visibility'] ?? []), $user)) {
                    continue;
                }

                $context = (string)($item['context'] ?? 'workspaces');
                $matches = array_values(array_filter((array)($item['matches'] ?? [$item['href'] ?? '/']), 'is_string'));
                $item['matches'] = $matches;
                $item['active'] = $this->matchesAny($currentPath, $matches);
                $itemsByContext[$context][] = $item;
            }
        }

        foreach ($itemsByContext as &$items) {
            usort($items, static function (array $left, array $right): int {
                return [(int)($left['order'] ?? 999), (string)($left['label'] ?? '')]
                    <=> [(int)($right['order'] ?? 999), (string)($right['label'] ?? '')];
            });
        }
        unset($items);

        $activeContext = $this->resolveActiveContext($currentPath, $itemsByContext);
        $contexts = [];

        foreach (self::ADMIN_CONTEXTS as $key => $contextMeta) {
            if (empty($itemsByContext[$key])) {
                continue;
            }

            $firstItem = $itemsByContext[$key][0] ?? [];
            $contexts[] = [
                'key' => $key,
                'label' => $this->translateContextMeta($contextMeta['label_key']),
                'description' => $this->translateContextMeta($contextMeta['description_key']),
                'icon' => $contextMeta['icon'],
                'href' => (string)($firstItem['href'] ?? '/'),
                'active' => $key === $activeContext,
            ];
        }

        usort($contexts, static function (array $left, array $right): int {
            return (self::ADMIN_CONTEXTS[$left['key']]['order'] ?? 999)
                <=> (self::ADMIN_CONTEXTS[$right['key']]['order'] ?? 999);
        });

        $activeItems = $itemsByContext[$activeContext] ?? [];
        $activeMeta = self::ADMIN_CONTEXTS[$activeContext] ?? self::ADMIN_CONTEXTS['workspaces'];

        return [
            'activeKey' => $activeContext,
            'active' => [
                'label' => $this->translateContextMeta($activeMeta['label_key']),
                'description' => $this->translateContextMeta($activeMeta['description_key']),
                'icon' => $activeMeta['icon'],
                'items' => $activeItems,
            ],
            'contexts' => $contexts,
        ];
    }

    /**
     * @return array<string, string|null>
     */
    public function breadcrumbs(string $currentPath, ?array $user = null): array
    {
        $rules = [];

        foreach (ModuleRegistry::getInstance()->active() as $module) {
            foreach ((array)($module['navigation']['breadcrumbs'] ?? []) as $rule) {
                if (!is_array($rule) || !$this->isVisible((array)($rule['visibility'] ?? []), $user)) {
                    continue;
                }

                $rules[] = $rule;
            }
        }

        usort($rules, static function (array $left, array $right): int {
            return strlen((string)($right['pattern'] ?? '')) <=> strlen((string)($left['pattern'] ?? ''));
        });

        foreach ($rules as $rule) {
            if (!ModuleRegistry::pathMatches($currentPath, (string)($rule['pattern'] ?? ''))) {
                continue;
            }

            $trail = [];
            foreach ((array)($rule['trail'] ?? []) as $segment) {
                if (!is_array($segment) || ($segment['label'] ?? '') === '') {
                    continue;
                }

                $trail[(string)$segment['label']] = isset($segment['href'])
                    ? ($segment['href'] !== null ? (string)$segment['href'] : null)
                    : null;
            }

            return $trail;
        }

        return [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function publicMenu(string $currentPath = '/', ?array $user = null): array
    {
        $items = [];

        foreach (ModuleRegistry::getInstance()->active() as $module) {
            foreach ((array)($module['navigation']['public'] ?? []) as $item) {
                if (!is_array($item) || !$this->isVisible((array)($item['visibility'] ?? []), $user)) {
                    continue;
                }

                $matches = array_values(array_filter((array)($item['matches'] ?? [$item['href'] ?? '/']), 'is_string'));
                $item['matches'] = $matches;
                $item['active'] = $this->matchesAny($currentPath, $matches);
                $item['module_key'] = $module['key'];
                $items[] = $item;
            }
        }

        usort($items, static function (array $left, array $right): int {
            return [(int)($left['order'] ?? 999), (string)($left['label'] ?? '')]
                <=> [(int)($right['order'] ?? 999), (string)($right['label'] ?? '')];
        });

        return $items;
    }

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function definitionsForModule(string $moduleKey): array
    {
        $module = ModuleRegistry::getInstance()->findByKey($moduleKey);
        $navigation = is_array($module['navigation'] ?? null)
            ? (array) $module['navigation']
            : [];

        return [
            'admin' => array_values((array) ($navigation['admin'] ?? [])),
            'public' => array_values((array) ($navigation['public'] ?? [])),
            'breadcrumbs' => array_values((array) ($navigation['breadcrumbs'] ?? [])),
        ];
    }

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function allDefinitions(): array
    {
        $definitions = [
            'admin' => [],
            'public' => [],
            'breadcrumbs' => [],
        ];

        foreach (ModuleRegistry::getInstance()->all() as $module) {
            $moduleKey = (string) ($module['key'] ?? '');
            foreach ($this->definitionsForModule($moduleKey) as $bucket => $items) {
                foreach ($items as $item) {
                    if ($bucket !== 'breadcrumbs') {
                        $item['module_key'] = $moduleKey;
                    }

                    $definitions[$bucket][] = $item;
                }
            }
        }

        return $definitions;
    }

    /**
     * @param array<int, array<string, mixed>> $visibility
     */
    private function isVisible(array $visibility, ?array $user): bool
    {
        if ($visibility === []) {
            return true;
        }

        foreach ($visibility as $group) {
            if (!is_array($group)) {
                continue;
            }

            if ($this->matchesVisibilityGroup($group, $user)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<string, mixed> $group
     */
    private function matchesVisibilityGroup(array $group, ?array $user): bool
    {
        $config = ConfigManager::getInstance();
        $permissions = PermissionRegistry::getInstance();

        if (array_key_exists('configured', $group) && (bool)$group['configured'] !== $config->isConfigured()) {
            return false;
        }

        $environments = array_values(array_filter((array)($group['environments'] ?? []), 'is_string'));
        if ($environments !== [] && !in_array($config->getEnvironment(), $environments, true)) {
            return false;
        }

        if (($group['authenticated'] ?? false) === true && $user === null) {
            return false;
        }

        $rolesAny = array_values(array_filter((array)($group['roles_any'] ?? []), 'is_string'));
        if ($rolesAny !== [] && !$permissions->userHasAnyRole($user, $rolesAny)) {
            return false;
        }

        $permissionsAny = array_values(array_filter((array)($group['permissions_any'] ?? []), 'is_string'));
        if ($permissionsAny !== [] && !$permissions->userHasAnyPermission($user, $permissionsAny)) {
            return false;
        }

        return true;
    }

    /**
     * @param array<string, array<int, array<string, mixed>>> $itemsByContext
     */
    private function resolveActiveContext(string $currentPath, array $itemsByContext): string
    {
        foreach ($itemsByContext as $context => $items) {
            foreach ($items as $item) {
                if (($item['active'] ?? false) === true) {
                    return (string)$context;
                }
            }
        }

        return array_key_first($itemsByContext) ?? 'workspaces';
    }

    /**
     * @param string[] $patterns
     */
    private function matchesAny(string $path, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if (ModuleRegistry::pathMatches($path, $pattern)) {
                return true;
            }
        }

        return false;
    }

    private function translateContextMeta(string $key): string
    {
        $translated = __($key);

        return $translated !== $key ? $translated : $key;
    }
}
