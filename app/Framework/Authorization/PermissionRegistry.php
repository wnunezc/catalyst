<?php

declare(strict_types=1);

namespace Catalyst\Framework\Authorization;

use Catalyst\Framework\Traits\SingletonTrait;
use Catalyst\Framework\Module\ModuleRegistry;
final class PermissionRegistry
{
    use SingletonTrait;

    /**
     * @var array<int, array<string, mixed>>|null
     */
    private ?array $definitions = null;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        if ($this->definitions !== null) {
            return $this->definitions;
        }

        return $this->definitions = ModuleRegistry::getInstance()->permissionDefinitions();
    }

    public function flushCache(): void
    {
        $this->definitions = null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function forModule(string $moduleKey): array
    {
        return array_values(array_filter(
            $this->all(),
            static fn (array $definition): bool => ($definition['module_key'] ?? '') === $moduleKey
        ));
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(string $slug): ?array
    {
        foreach ($this->all() as $definition) {
            if (($definition['slug'] ?? '') === $slug) {
                return $definition;
            }
        }

        return null;
    }

    public function registerGateDefinitions(Gate $gate): void
    {
        $gate->define('admin-area', fn (array $user): bool => $this->userHasRole($user, 'admin'));
        $gate->policy(AbilitySubject::class, ResourcePolicy::class);

        foreach ($this->all() as $definition) {
            $slug = (string)($definition['slug'] ?? '');
            if ($slug === '') {
                continue;
            }

            $gate->define($slug, function (array $user, mixed ...$args) use ($slug): bool {
                $record = $args[0] ?? null;
                return $this->userHasPermission($user, $slug, $record);
            });
        }
    }

    public function userHasRole(?array $user, string $roleSlug): bool
    {
        $userId = $this->resolveUserId($user);
        if ($userId === null) {
            return false;
        }

        return RoleRepository::getInstance()->userHasRole($userId, $roleSlug);
    }

    /**
     * @param string[] $roleSlugs
     */
    public function userHasAnyRole(?array $user, array $roleSlugs): bool
    {
        $userId = $this->resolveUserId($user);
        if ($userId === null || $roleSlugs === []) {
            return false;
        }

        return RoleRepository::getInstance()->userHasAnyRole($userId, $roleSlugs);
    }

    public function userHasPermission(?array $user, string $slug, mixed $record = null): bool
    {
        $userId = $this->resolveUserId($user);
        if ($userId === null) {
            return false;
        }

        $definition = $this->find($slug);
        $repo = RoleRepository::getInstance();

        $allowed = $repo->userHasPermission($userId, $slug);

        if (!$allowed && $definition !== null) {
            $fallbackRoles = array_values(array_filter((array)($definition['role_fallback_any'] ?? []), 'is_string'));
            if ($fallbackRoles !== []) {
                $allowed = $repo->userHasAnyRole($userId, $fallbackRoles);
            }
        }

        if (!$allowed) {
            return false;
        }

        return $definition === null
            ? true
            : $this->matchesConditions($user, $definition, $record);
    }

    /**
     * @param string[] $slugs
     */
    public function userHasAnyPermission(?array $user, array $slugs, mixed $record = null): bool
    {
        foreach ($slugs as $slug) {
            if ($this->userHasPermission($user, (string)$slug, $record)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function userHasResourceAbility(
        ?array $user,
        string $resource,
        string $ability,
        mixed $record = null,
        array $context = []
    ): bool {
        $resource = trim(strtolower($resource));
        $ability = trim(strtolower($ability));

        if ($resource === '' || $ability === '') {
            return false;
        }

        foreach ($this->resourceAbilityDefinitions($resource, $ability) as $definition) {
            $slug = (string) ($definition['slug'] ?? '');
            if ($slug === '') {
                continue;
            }

            $subjectRecord = $context['record'] ?? $record;
            if ($this->userHasPermission($user, $slug, $subjectRecord)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function resourceAbilityDefinitions(string $resource, string $ability): array
    {
        $matches = [];

        foreach ($this->all() as $definition) {
            if (!$this->definitionMatchesResource($definition, $resource)) {
                continue;
            }

            if (!$this->definitionMatchesAbility($definition, $ability)) {
                continue;
            }

            $matches[] = $definition;
        }

        return $matches;
    }

    /**
     * @param array<string, mixed> $user
     * @param array<string, mixed> $definition
     */
    private function matchesConditions(array $user, array $definition, mixed $record): bool
    {
        $recordRequired = (bool)($definition['record_required'] ?? false);
        if ($recordRequired && $record === null) {
            return false;
        }

        $ownerField = (string)($definition['owner_field'] ?? '');
        if ($ownerField !== '' && $record !== null) {
            $ownerId = $this->extractValue($record, $ownerField);
            if ((string)$ownerId !== (string)($user['id'] ?? '')) {
                return false;
            }
        }

        $stateField = (string)($definition['state_field'] ?? '');
        $states = array_values(array_filter((array)($definition['states_any'] ?? []), 'is_string'));
        if ($stateField !== '' && $states !== [] && $record !== null) {
            $state = (string)$this->extractValue($record, $stateField);
            if (!in_array($state, $states, true)) {
                return false;
            }
        }

        $policyAbility = (string)($definition['policy_ability'] ?? '');
        if ($policyAbility !== '' && $record !== null) {
            return Gate::getInstance()->forUser($user)->allows($policyAbility, $record);
        }

        return true;
    }

    /**
     * @param array<string, mixed> $definition
     */
    private function definitionMatchesResource(array $definition, string $resource): bool
    {
        $canonical = trim(strtolower((string) ($definition['resource'] ?? '')));
        $aliases = array_values(array_filter(array_map(
            static fn (mixed $value): string => trim(strtolower((string) $value)),
            (array) ($definition['resources_any'] ?? [])
        )));

        return $canonical === $resource || in_array($resource, $aliases, true);
    }

    /**
     * @param array<string, mixed> $definition
     */
    private function definitionMatchesAbility(array $definition, string $ability): bool
    {
        $explicit = array_values(array_filter(array_map(
            static fn (mixed $value): string => trim(strtolower((string) $value)),
            (array) ($definition['abilities_any'] ?? [])
        )));

        if ($explicit !== []) {
            return in_array($ability, $explicit, true);
        }

        $action = trim(strtolower((string) ($definition['action'] ?? '')));

        if ($action === '') {
            return false;
        }

        return in_array($action, $this->abilityActionAliases($ability), true);
    }

    /**
     * @return string[]
     */
    private function abilityActionAliases(string $ability): array
    {
        return match ($ability) {
            'view-any', 'view' => ['view', 'read', 'access', 'manage', 'audit'],
            'create' => ['create', 'manage'],
            'update' => ['update', 'edit', 'manage'],
            'delete' => ['delete', 'manage'],
            'restore' => ['restore', 'manage'],
            'export' => ['export', 'manage', 'audit'],
            'bulk-delete' => ['bulk-delete', 'delete', 'manage'],
            'bulk-restore' => ['bulk-restore', 'restore', 'manage'],
            'assign' => ['assign', 'update', 'manage'],
            'sync' => ['sync', 'update', 'manage'],
            default => [$ability, 'manage'],
        };
    }

    private function resolveUserId(?array $user): ?int
    {
        $userId = $user['id'] ?? null;
        if ($userId === null || $userId === '') {
            return null;
        }

        return (int)$userId;
    }

    private function extractValue(mixed $record, string $field): mixed
    {
        if (is_array($record)) {
            return $record[$field] ?? null;
        }

        if (is_object($record) && isset($record->{$field})) {
            return $record->{$field};
        }

        $getter = 'get' . ucfirst($field);

        if (is_object($record) && method_exists($record, $getter)) {
            return $record->{$getter}();
        }

        return null;
    }
}
