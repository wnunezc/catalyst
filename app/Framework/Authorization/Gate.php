<?php

declare(strict_types=1);

/**************************************************************************************
 *
 * Catalyst PHP Framework
 * PHP Version 8.4 (Required).
 *
 * @package   Catalyst
 * @subpackage Framework\Authorization
 * @see       https://github.com/arcanisgk/catalyst
 *
 * @author    Walter Nuñez (arcanisgk/original founder) <icarosnet@gmail.com>
 * @copyright 2023 - 2025
 * @license   http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 *
 * @note      This program is distributed in the hope that it will be useful
 *            WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 *            or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @category  Framework
 * @filesource
 *
 * @link      https://catalyst.dock Local development URL
 *
 * Gate — central authorization registry for closures and policies.
 *
 */

namespace Catalyst\Framework\Authorization;

use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Framework\Traits\SingletonTrait;
use Catalyst\Helpers\Exceptions\ForbiddenException;
use Closure;
use LogicException;

/**************************************************************************************
 * Gate — Authorization Engine
 *
 * Provides two complementary authorization mechanisms:
 *
 * 1. Gates (closures)
 *    Named Closure callbacks for arbitrary authorization logic.
 *    Registered with Gate::define(), checked with Gate::allows().
 *
 *    Gate::define('edit-post', function(array $user, array $post): bool {
 *        return $user['id'] === $post['user_id'];
 *    });
 *    Gate::allows('edit-post', $post);   // true / false
 *    Gate::authorize('edit-post', $post); // throws ForbiddenException if false
 *
 * 2. Policies (classes)
 *    Classes extending Policy, registered per model class.
 *    The Gate resolves the correct method by ability name (canEdit, canDelete…).
 *
 *    Gate::policy(Post::class, PostPolicy::class);
 *    Gate::allows('edit', $post);         // calls PostPolicy::canEdit($user, $post)
 *
 * The current authenticated user is resolved automatically from AuthManager.
 * To override: Gate::forUser($user)->allows('edit', $post)
 *
 * @package Catalyst\Framework\Authorization
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
     * Register a gate closure for a named ability.
     *
     * The closure receives (array $user, mixed ...$args).
     *
     * @param string  $ability  e.g. 'edit-post', 'admin-area'
     * @param Closure $callback function(array $user, mixed ...$args): bool
     */
    public function define(string $ability, Closure $callback): void
    {
        $this->gates[$ability] = $callback;
    }

    /**
     * Register a Policy class for a model class.
     *
     * @param string $modelClass  FQCN of the model (e.g. Post::class)
     * @param string $policyClass FQCN of the policy (e.g. PostPolicy::class)
     */
    public function policy(string $modelClass, string $policyClass): void
    {
        $this->policies[$modelClass] = $policyClass;
    }

    // -- Evaluation ------------------------------------------------------------

    /**
     * Check if the current user passes the given ability.
     *
     * Resolves to a Gate closure or a Policy method, in that order.
     *
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
     * Inverse of allows().
     */
    public function denies(string $ability, mixed ...$args): bool
    {
        return !$this->allows($ability, ...$args);
    }

    /**
     * Assert the ability is allowed; throw ForbiddenException otherwise.
     *
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
     * Return a new Gate scoped to the given user (does not mutate the singleton).
     *
     * Example:
     *   Gate::getInstance()->forUser($otherUser)->allows('edit-post', $post)
     */
    public function forUser(array $user): static
    {
        $clone               = clone $this;
        $clone->overrideUser = $user;
        return $clone;
    }

    // -- Private helpers -------------------------------------------------------

    private function resolveUser(): ?array
    {
        if ($this->overrideUser !== null) {
            return $this->overrideUser;
        }

        return AuthManager::getInstance()->user();
    }

    /**
     * Core evaluation: gate closure first, then policy.
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
     * Find a registered policy class for the given model instance.
     * Supports exact class match and parent class / interface matches.
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
     * Instantiate the policy and call before() + can{Ability}().
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
