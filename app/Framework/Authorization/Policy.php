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
/**
 * Defines the Policy class contract.
 *
 * @package Catalyst\Framework\Authorization
 * Responsibility: Coordinates the policy behavior within its module boundary.
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
