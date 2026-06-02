<?php

declare(strict_types=1);

namespace Catalyst\Framework\Cli\Commands;

use Catalyst\Framework\Argument\ArgumentBag;
use Catalyst\Framework\Cli\AbstractCommand;

final class RolesMvcRegressionCommand extends AbstractCommand
{
    public function getName(): string
    {
        return 'roles:mvc-regression';
    }

    public function getDescription(): string
    {
        return 'Verify Roles request and presentation boundaries';
    }

    public function execute(ArgumentBag $args): int
    {
        $users = $this->contents('Repository/Framework/Roles/Controllers/UserManagementController.php');
        $roles = $this->contents('Repository/Framework/Roles/Controllers/RolesController.php');
        $permissions = $this->contents('Repository/Framework/Roles/Controllers/PermissionsController.php');
        $checks = [
            'enrollment_request_centralized' => class_exists(\Catalyst\Repository\Roles\Requests\UserEnrollmentRequest::class)
                && str_contains($users, 'new UserEnrollmentRequest($request)'),
            'enrollment_form_extracted' => class_exists(\Catalyst\Repository\Roles\Support\UserEnrollmentFormFactory::class)
                && !str_contains($users, 'FormBuilder::'),
            'role_bulk_request_centralized' => class_exists(\Catalyst\Repository\Roles\Requests\RoleBulkSelectionRequest::class)
                && str_contains($roles, 'new RoleBulkSelectionRequest($request)'),
            'permission_bulk_request_centralized' => class_exists(\Catalyst\Repository\Roles\Requests\PermissionBulkSelectionRequest::class)
                && str_contains($permissions, 'new PermissionBulkSelectionRequest($request)'),
            'permission_sync_request_centralized' => class_exists(\Catalyst\Repository\Roles\Requests\RolePermissionSyncRequest::class)
                && str_contains($roles, 'new RolePermissionSyncRequest($request)'),
        ];
        $ok = !in_array(false, $checks, true);

        $this->line('');
        $this->info('Roles MVC Regression');
        $this->line(str_repeat('-', 74));
        foreach ($checks as $name => $passed) {
            $this->line(sprintf('  %-40s %s', ucwords(str_replace('_', ' ', $name)), $passed ? 'OK' : 'ISSUES'));
        }
        $this->line(str_repeat('-', 74));
        $ok ? $this->success('Roles MVC contract is coherent.') : $this->error('Roles MVC contract has issues.');
        $this->line('');

        return $ok ? 0 : 1;
    }

    private function contents(string $relativePath): string
    {
        $path = PD . DS . str_replace('/', DS, $relativePath);

        return is_file($path) ? (string) file_get_contents($path) : '';
    }
}
