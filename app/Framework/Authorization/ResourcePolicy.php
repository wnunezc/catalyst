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

/**
 * Maps generic resource abilities to permission registry checks.
 *
 * @package Catalyst\Framework\Authorization
 * Responsibility: Authorizes AbilitySubject instances through resource permission definitions.
 */
final class ResourcePolicy extends Policy
{
    /**
     * Checks whether the user can view a resource collection.
     *
     * Responsibility: Checks whether the user can view a resource collection.
     */
    public function canViewAny(array $user, AbilitySubject $subject): bool
    {
        return $this->allows($user, 'view-any', $subject);
    }

    /**
     * Checks whether the user can view a resource record.
     *
     * Responsibility: Checks whether the user can view a resource record.
     */
    public function canView(array $user, AbilitySubject $subject): bool
    {
        return $this->allows($user, 'view', $subject);
    }

    /**
     * Checks whether the user can create a resource record.
     *
     * Responsibility: Checks whether the user can create a resource record.
     */
    public function canCreate(array $user, AbilitySubject $subject): bool
    {
        return $this->allows($user, 'create', $subject);
    }

    /**
     * Checks whether the user can update a resource record.
     *
     * Responsibility: Checks whether the user can update a resource record.
     */
    public function canUpdate(array $user, AbilitySubject $subject): bool
    {
        return $this->allows($user, 'update', $subject);
    }

    /**
     * Checks whether the user can delete a resource record.
     *
     * Responsibility: Checks whether the user can delete a resource record.
     */
    public function canDelete(array $user, AbilitySubject $subject): bool
    {
        return $this->allows($user, 'delete', $subject);
    }

    /**
     * Checks whether the user can restore a resource record.
     *
     * Responsibility: Checks whether the user can restore a resource record.
     */
    public function canRestore(array $user, AbilitySubject $subject): bool
    {
        return $this->allows($user, 'restore', $subject);
    }

    /**
     * Checks whether the user can export resource data.
     *
     * Responsibility: Checks whether the user can export resource data.
     */
    public function canExport(array $user, AbilitySubject $subject): bool
    {
        return $this->allows($user, 'export', $subject);
    }

    /**
     * Checks whether the user can run a resource operation.
     *
     * Responsibility: Checks whether the user can run a resource operation.
     */
    public function canRun(array $user, AbilitySubject $subject): bool
    {
        return $this->allows($user, 'run', $subject);
    }

    /**
     * Checks whether the user can revoke a resource credential or grant.
     *
     * Responsibility: Checks whether the user can revoke a resource credential or grant.
     */
    public function canRevoke(array $user, AbilitySubject $subject): bool
    {
        return $this->allows($user, 'revoke', $subject);
    }

    /**
     * Checks whether the user can bulk delete resource records.
     *
     * Responsibility: Checks whether the user can bulk delete resource records.
     */
    public function canBulkDelete(array $user, AbilitySubject $subject): bool
    {
        return $this->allows($user, 'bulk-delete', $subject);
    }

    /**
     * Checks whether the user can bulk restore resource records.
     *
     * Responsibility: Checks whether the user can bulk restore resource records.
     */
    public function canBulkRestore(array $user, AbilitySubject $subject): bool
    {
        return $this->allows($user, 'bulk-restore', $subject);
    }

    /**
     * Checks whether the user can assign a resource relationship.
     *
     * Responsibility: Checks whether the user can assign a resource relationship.
     */
    public function canAssign(array $user, AbilitySubject $subject): bool
    {
        return $this->allows($user, 'assign', $subject);
    }

    /**
     * Checks whether the user can synchronize resource data.
     *
     * Responsibility: Checks whether the user can synchronize resource data.
     */
    public function canSync(array $user, AbilitySubject $subject): bool
    {
        return $this->allows($user, 'sync', $subject);
    }

    /**
     * Checks whether the user can manage a resource.
     *
     * Responsibility: Checks whether the user can manage a resource.
     */
    public function canManage(array $user, AbilitySubject $subject): bool
    {
        return $this->allows($user, 'manage', $subject);
    }

    /**
     * Delegates a resource ability decision to the permission registry.
     *
     * Responsibility: Delegates a resource ability decision to the permission registry.
     */
    private function allows(array $user, string $ability, AbilitySubject $subject): bool
    {
        return PermissionRegistry::getInstance()->userHasResourceAbility(
            $user,
            $subject->resource(),
            $ability,
            $subject->record(),
            $subject->context()
        );
    }
}
