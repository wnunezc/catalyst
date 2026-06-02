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

use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Framework\Traits\SingletonTrait;
use Catalyst\Helpers\Exceptions\ForbiddenException;
use Closure;
use LogicException;

/**
 * Resolves named gates and model policies for the current or scoped user.
 *
 * @package Catalyst\Framework\Authorization
 * Responsibility: Evaluates authorization abilities through registered closures and policy classes.
 */
class Gate
{
    use SingletonTrait;

    /** @var array<string, Closure> Registered gate closures */
    private array $gates = [];

    /** @var array<string, string> model FQCN → policy FQCN */
    private array $policies = [];

    /** Override user for forUser() calls */
    private ?array $overrideUser = null;

    // -- Registration ----------------------------------------------------------

    /**
     * Registers a closure callback for a named ability.
     *
     * Responsibility: Registers a closure callback for a named ability.
     * @param string  $ability  Ability slug resolved by authorization checks.
     * @param Closure $callback Callback receiving the resolved user and ability arguments.
     */
    public function define(string $ability, Closure $callback): void
    {
        $this->gates[$ability] = $callback;
    }

    /**
     * Registers the policy class responsible for a model class.
     *
     * Responsibility: Registers the policy class responsible for a model class.
     * @param string $modelClass  Model FQCN or subject class key.
     * @param string $policyClass Policy FQCN that must extend Policy.
     */
    public function policy(string $modelClass, string $policyClass): void
    {
        $this->policies[$modelClass] = $policyClass;
    }

    // -- Evaluation ------------------------------------------------------------

    /**
     * Checks whether the resolved user is allowed to perform an ability.
     *
     * Responsibility: Checks whether the resolved user is allowed to perform an ability.
     * @param mixed ...$args Optional model or extra arguments for the callback
     */
    public function allows(string $ability, mixed ...$args): bool
    {
        $user = $this->resolveUser();

        if ($user === null) {
            return false;
        }

        return $this->check($ability, $user, $args);
    }

    /**
     * Checks whether the resolved user is denied an ability.
     *
     * Responsibility: Checks whether the resolved user is denied an ability.
     */
    public function denies(string $ability, mixed ...$args): bool
    {
        return !$this->allows($ability, ...$args);
    }

    /**
     * Enforces an ability and raises a forbidden exception when it is denied.
     *
     * Responsibility: Enforces an ability and raises a forbidden exception when it is denied.
     * @throws ForbiddenException
     */
    public function authorize(string $ability, mixed ...$args): void
    {
        if (!$this->allows($ability, ...$args)) {
            throw ForbiddenException::action($ability);
        }
    }

    // -- User override ---------------------------------------------------------

    /**
     * Returns a cloned gate instance scoped to an explicit user payload.
     *
     * Responsibility: Returns a cloned gate instance scoped to an explicit user payload.
     */
    public function forUser(array $user): static
    {
        $clone               = clone $this;
        $clone->overrideUser = $user;
        return $clone;
    }

    // -- Private helpers -------------------------------------------------------

    /**
     * Resolves the explicit scoped user or the authenticated session user.
     *
     * Responsibility: Resolves the explicit scoped user or the authenticated session user.
     */
    private function resolveUser(): ?array
    {
        if ($this->overrideUser !== null) {
            return $this->overrideUser;
        }

        return AuthManager::getInstance()->user();
    }

    /**
     * Evaluates an ability through a registered gate closure or matching policy.
     *
     * Responsibility: Evaluates an ability through a registered gate closure or matching policy.
     */
    private function check(string $ability, array $user, array $args): bool
    {
        // 1. Registered gate closure
        if (isset($this->gates[$ability])) {
            return (bool)($this->gates[$ability])($user, ...$args);
        }

        // 2. Policy — look up by first argument type (model class)
        if (!empty($args)) {
            $policyClass = $this->findPolicyForArg($args[0]);

            if ($policyClass !== null) {
                return $this->callPolicy($ability, $policyClass, $user, $args);
            }
        }

        // 3. No gate or policy found — deny by default
        return false;
    }

    /**
     * Finds the registered policy class for an object, class string, parent, or interface.
     *
     * Responsibility: Finds the registered policy class for an object, class string, parent, or interface.
     */
    private function findPolicyForArg(mixed $model): ?string
    {
        if (is_object($model)) {
            $modelClass = $model::class;
        } elseif (is_string($model) && isset($this->policies[$model])) {
            $modelClass = $model;
        } else {
            return null;
        }

        if (isset($this->policies[$modelClass])) {
            return $this->policies[$modelClass];
        }

        // Check parent classes
        foreach ($this->policies as $registeredClass => $policyClass) {
            if (is_a($model, $registeredClass, true)) {
                return $policyClass;
            }
        }

        return null;
    }

    /**
     * Instantiates a policy and evaluates its before hook and ability method.
     *
     * Responsibility: Instantiates a policy and evaluates its before hook and ability method.
     */
    private function callPolicy(string $ability, string $policyClass, array $user, array $args): bool
    {
        if (!class_exists($policyClass)) {
            throw new LogicException("Policy class '{$policyClass}' does not exist.");
        }

        $policy = new $policyClass();

        if (!($policy instanceof Policy)) {
            throw new LogicException("Policy class '{$policyClass}' must extend " . Policy::class);
        }

        // before() hook
        $before = $policy->before($user, $ability);

        if ($before !== null) {
            return $before;
        }

        // Resolve method: ability 'edit' → canEdit, 'delete' → canDelete
        $method = 'can' . ucfirst(str_replace(['-', '_'], '', ucwords($ability, '-_')));

        if (!method_exists($policy, $method)) {
            return false;
        }

        return (bool)$policy->$method($user, ...$args);
    }
}
