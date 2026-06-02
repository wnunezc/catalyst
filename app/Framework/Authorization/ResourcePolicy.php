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
 * Defines the Resource Policy class contract.
 *
 * @package Catalyst\Framework\Authorization
 * Responsibility: Coordinates the resource policy behavior within its module boundary.
 */
final class ResourcePolicy extends Policy
{
    /**
     * Determines whether can View Any.
     */
    public function canViewAny(array $user, AbilitySubject $subject): bool
    {
        return $this->allows($user, 'view-any', $subject);
    }

    /**
     * Determines whether can View.
     */
    public function canView(array $user, AbilitySubject $subject): bool
    {
        return $this->allows($user, 'view', $subject);
    }

    /**
     * Determines whether can Create.
     */
    public function canCreate(array $user, AbilitySubject $subject): bool
    {
        return $this->allows($user, 'create', $subject);
    }

    /**
     * Determines whether can Update.
     */
    public function canUpdate(array $user, AbilitySubject $subject): bool
    {
        return $this->allows($user, 'update', $subject);
    }

    /**
     * Determines whether can Delete.
     */
    public function canDelete(array $user, AbilitySubject $subject): bool
    {
        return $this->allows($user, 'delete', $subject);
    }

    /**
     * Determines whether can Restore.
     */
    public function canRestore(array $user, AbilitySubject $subject): bool
    {
        return $this->allows($user, 'restore', $subject);
    }

    /**
     * Determines whether can Export.
     */
    public function canExport(array $user, AbilitySubject $subject): bool
    {
        return $this->allows($user, 'export', $subject);
    }

    /**
     * Determines whether can Run.
     */
    public function canRun(array $user, AbilitySubject $subject): bool
    {
        return $this->allows($user, 'run', $subject);
    }

    /**
     * Determines whether can Revoke.
     */
    public function canRevoke(array $user, AbilitySubject $subject): bool
    {
        return $this->allows($user, 'revoke', $subject);
    }

    /**
     * Determines whether can Bulk Delete.
     */
    public function canBulkDelete(array $user, AbilitySubject $subject): bool
    {
        return $this->allows($user, 'bulk-delete', $subject);
    }

    /**
     * Determines whether can Bulk Restore.
     */
    public function canBulkRestore(array $user, AbilitySubject $subject): bool
    {
        return $this->allows($user, 'bulk-restore', $subject);
    }

    /**
     * Determines whether can Assign.
     */
    public function canAssign(array $user, AbilitySubject $subject): bool
    {
        return $this->allows($user, 'assign', $subject);
    }

    /**
     * Determines whether can Sync.
     */
    public function canSync(array $user, AbilitySubject $subject): bool
    {
        return $this->allows($user, 'sync', $subject);
    }

    /**
     * Determines whether can Manage.
     */
    public function canManage(array $user, AbilitySubject $subject): bool
    {
        return $this->allows($user, 'manage', $subject);
    }

    /**
     * Handles the allows workflow.
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
