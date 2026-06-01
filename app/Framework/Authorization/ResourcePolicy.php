<?php

declare(strict_types=1);

namespace Catalyst\Framework\Authorization;

final class ResourcePolicy extends Policy
{
    public function canViewAny(array $user, AbilitySubject $subject): bool
    {
        return $this->allows($user, 'view-any', $subject);
    }

    public function canView(array $user, AbilitySubject $subject): bool
    {
        return $this->allows($user, 'view', $subject);
    }

    public function canCreate(array $user, AbilitySubject $subject): bool
    {
        return $this->allows($user, 'create', $subject);
    }

    public function canUpdate(array $user, AbilitySubject $subject): bool
    {
        return $this->allows($user, 'update', $subject);
    }

    public function canDelete(array $user, AbilitySubject $subject): bool
    {
        return $this->allows($user, 'delete', $subject);
    }

    public function canRestore(array $user, AbilitySubject $subject): bool
    {
        return $this->allows($user, 'restore', $subject);
    }

    public function canExport(array $user, AbilitySubject $subject): bool
    {
        return $this->allows($user, 'export', $subject);
    }

    public function canRun(array $user, AbilitySubject $subject): bool
    {
        return $this->allows($user, 'run', $subject);
    }

    public function canRevoke(array $user, AbilitySubject $subject): bool
    {
        return $this->allows($user, 'revoke', $subject);
    }

    public function canBulkDelete(array $user, AbilitySubject $subject): bool
    {
        return $this->allows($user, 'bulk-delete', $subject);
    }

    public function canBulkRestore(array $user, AbilitySubject $subject): bool
    {
        return $this->allows($user, 'bulk-restore', $subject);
    }

    public function canAssign(array $user, AbilitySubject $subject): bool
    {
        return $this->allows($user, 'assign', $subject);
    }

    public function canSync(array $user, AbilitySubject $subject): bool
    {
        return $this->allows($user, 'sync', $subject);
    }

    public function canManage(array $user, AbilitySubject $subject): bool
    {
        return $this->allows($user, 'manage', $subject);
    }

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
