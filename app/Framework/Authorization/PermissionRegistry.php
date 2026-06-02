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

namespace Catalyst\Framework\Authorization;

use Catalyst\Framework\Traits\SingletonTrait;
use Catalyst\Framework\Module\ModuleRegistry;
/**
 * Loads permission definitions and evaluates role, permission, and resource abilities.
 *
 * @package Catalyst\Framework\Authorization
 * Responsibility: Bridges module permission metadata with Gate and RoleRepository checks.
 */
final class PermissionRegistry
{
    use SingletonTrait;

    /**
     * @var array<int, array<string, mixed>>|null
     */
    private ?array $definitions = null;

    /**
     * Returns all module-declared permission definitions cached for the request.
     *
     * Responsibility: Returns all module-declared permission definitions cached for the request.
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        if ($this->definitions !== null) {
            return $this->definitions;
        }

        return $this->definitions = ModuleRegistry::getInstance()->permissionDefinitions();
    }

    /**
     * Clears cached permission definitions so module metadata can be reloaded.
     *
     * Responsibility: Clears cached permission definitions so module metadata can be reloaded.
     */
    public function flushCache(): void
    {
        $this->definitions = null;
    }

    /**
     * Returns permission definitions declared by a module key.
     *
     * Responsibility: Returns permission definitions declared by a module key.
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
     * Finds a permission definition by slug.
     *
     * Responsibility: Finds a permission definition by slug.
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

    /**
     * Registers permission slugs and resource policies on the given gate instance.
     *
     * Responsibility: Registers permission slugs and resource policies on the given gate instance.
     */
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

    /**
     * Checks whether the resolved user has a specific role slug.
     *
     * Responsibility: Checks whether the resolved user has a specific role slug.
     */
    public function userHasRole(?array $user, string $roleSlug): bool
    {
        $userId = $this->resolveUserId($user);
        if ($userId === null) {
            return false;
        }

        return RoleRepository::getInstance()->userHasRole($userId, $roleSlug);
    }

    /**
     * Checks whether the resolved user has at least one role slug.
     *
     * Responsibility: Checks whether the resolved user has at least one role slug.
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

    /**
     * Checks whether the resolved user has a permission and satisfies its conditions.
     *
     * Responsibility: Checks whether the resolved user has a permission and satisfies its conditions.
     */
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
     * Checks whether the resolved user has at least one permission slug.
     *
     * Responsibility: Checks whether the resolved user has at least one permission slug.
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
     * Checks whether the resolved user has a permission matching a resource ability.
     *
     * Responsibility: Checks whether the resolved user has a permission matching a resource ability.
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
     * Returns permission definitions matching a resource and ability pair.
     *
     * Responsibility: Returns permission definitions matching a resource and ability pair.
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
     * Validates record ownership, state, and delegated policy constraints.
     *
     * Responsibility: Validates record ownership, state, and delegated policy constraints.
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
     * Checks whether a permission definition applies to the requested resource.
     *
     * Responsibility: Checks whether a permission definition applies to the requested resource.
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
     * Checks whether a permission definition applies to the requested ability.
     *
     * Responsibility: Checks whether a permission definition applies to the requested ability.
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
     * Returns action aliases accepted for a generic resource ability.
     *
     * Responsibility: Returns action aliases accepted for a generic resource ability.
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

    /**
     * Resolves the numeric user ID from an authorization user payload.
     *
     * Responsibility: Resolves the numeric user ID from an authorization user payload.
     */
    private function resolveUserId(?array $user): ?int
    {
        $userId = $user['id'] ?? null;
        if ($userId === null || $userId === '') {
            return null;
        }

        return (int)$userId;
    }

    /**
     * Extracts a field value from an array, object property, or getter method.
     *
     * Responsibility: Extracts a field value from an array, object property, or getter method.
     */
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
