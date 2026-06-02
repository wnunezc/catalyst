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

namespace Catalyst\Framework\Cli\Commands;

use Catalyst\Framework\Argument\ArgumentBag;
use Catalyst\Framework\Cli\AbstractCommand;

/**
 * roles:mvc-regression CLI command.
 *
 * Responsibility: Runs the roles:mvc-regression command to Verify Roles request and presentation boundaries.
 *
 * @package Catalyst\Framework\Cli\Commands
 */
final class RolesMvcRegressionCommand extends AbstractCommand
{
    /**
     * Returns the command name registered in the CLI registry.
     *
     * Responsibility: Returns the command name registered in the CLI registry.
     */
    public function getName(): string
    {
        return 'roles:mvc-regression';
    }

    /**
     * Returns the short help text shown for this command.
     *
     * Responsibility: Returns the short help text shown for this command.
     */
    public function getDescription(): string
    {
        return 'Verify Roles request and presentation boundaries';
    }

    /**
     * Runs the command workflow using parsed CLI arguments.
     *
     * Responsibility: Runs the command workflow using parsed CLI arguments.
     */
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

    /**
     * Describes the contents helper responsibility inside the CLI component.
     *
     * Responsibility: Supports the contents helper workflow used by this CLI component.
     */
    private function contents(string $relativePath): string
    {
        $path = PD . DS . str_replace('/', DS, $relativePath);

        return is_file($path) ? (string) file_get_contents($path) : '';
    }
}
