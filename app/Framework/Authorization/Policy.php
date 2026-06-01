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
 * Policy — abstract base class for model-based authorization.
 *
 */

namespace Catalyst\Framework\Authorization;

/**************************************************************************************
 * Policy (abstract)
 *
 * Base class for Catalyst authorization policies. Subclasses represent
 * authorization logic for a specific model or resource.
 *
 * ## Usage
 *
 *   class PostPolicy extends Policy
 *   {
 *       public function canEdit(array $user, array $post): bool
 *       {
 *           return $user['id'] === $post['user_id'];
 *       }
 *
 *       public function canDelete(array $user, array $post): bool
 *       {
 *           return $user['role'] === 'admin';
 *       }
 *   }
 *
 *   // Register the policy in routes.php:
 *   Gate::policy(Post::class, PostPolicy::class);
 *
 *   // Use in controller:
 *   $this->authorize('edit', $post);   // calls PostPolicy::canEdit
 *
 * ## Superadmin bypass
 *
 *   Override before() to grant access unconditionally for certain users:
 *
 *   public function before(array $user, string $ability): ?bool
 *   {
 *       return $user['role'] === 'admin' ? true : null;
 *   }
 *
 *   Returning true  → grant immediately (skip can* method)
 *   Returning false → deny immediately (skip can* method)
 *   Returning null  → evaluate can{Ability}() normally
 *
 * ## Method naming
 *
 *   Ability 'edit'   → method canEdit(array $user, mixed $model): bool
 *   Ability 'delete' → method canDelete(array $user, mixed $model): bool
 *   Ability 'view'   → method canView(array $user, mixed $model): bool
 *
 * @package Catalyst\Framework\Authorization
 */
abstract class Policy
{
    /**
     * Optional bypass hook — evaluated before any can* method.
     *
     * Return true  → grant access unconditionally.
     * Return false → deny access unconditionally.
     * Return null  → fall through to the specific can* method.
     *
     * @param array  $user    Current authenticated user
     * @param string $ability Ability being checked (e.g. 'edit', 'delete')
     * @return bool|null
     */
    public function before(array $user, string $ability): ?bool
    {
        return null;
    }
}
