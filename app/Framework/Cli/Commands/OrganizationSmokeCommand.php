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
use Catalyst\Framework\Argument\Option;
use Catalyst\Framework\Cli\AbstractCommand;
use Catalyst\Framework\Organization\OrganizationClassification;
use Catalyst\Framework\Organization\OrganizationClassificationPresenter;

/**
 * organization:smoke CLI command.
 *
 * Responsibility: Exercises organization hierarchy primitives without requiring a database connection.
 *
 * @package Catalyst\Framework\Cli\Commands
 */
final class OrganizationSmokeCommand extends AbstractCommand
{
    /**
     * Defines the accepted option schema for this command.
     *
     * Responsibility: Exposes CLI parser metadata only; command behavior stays inside execute().
     * @return Option[]
     */
    public function getOptions(): array
    {
        return [
            new Option(null, 'json', false, false, 'Render as JSON', false),
        ];
    }

    /**
     * Returns the command name registered in the CLI registry.
     *
     * Responsibility: Provides the stable command identifier consumed by CommandRegistry.
     */
    public function getName(): string
    {
        return 'organization:smoke';
    }

    /**
     * Returns the short help text shown for this command.
     *
     * Responsibility: Keeps command discovery text separate from execution logic.
     */
    public function getDescription(): string
    {
        return 'Exercise configurable organization hierarchy primitives';
    }

    /**
     * Runs the command workflow using parsed CLI arguments.
     *
     * Responsibility: Validates the non-DB contract for hierarchy scopes, levels, units and reusable badges.
     */
    public function execute(ArgumentBag $args): int
    {
        $json = (bool)($args->getOptionValue('json') ?? false);
        $checks = [
            'classification_value_object' => class_exists(OrganizationClassification::class),
            'classification_presenter' => class_exists(OrganizationClassificationPresenter::class),
            'organization_migration' => is_file(PD . DS . 'boot-core' . DS . 'database' . DS . 'migrations' . DS . '20260604010000_create_organization_hierarchy_tables.php'),
            'role_repository_integration' => $this->roleRepositoryHasOrganizationIntegration(),
            'role_admin_unit_form_integration' => $this->roleAdminHasOrganizationUnitIntegration(),
        ];

        $classification = null;
        $badge = null;
        if ($checks['classification_value_object'] && $checks['classification_presenter']) {
            $classification = OrganizationClassification::fromArray([
                'resource_key' => 'roles',
                'record_id' => 15,
                'organization_slug' => 'default',
                'scope_key' => 'authority',
                'scope_label' => 'Authority',
                'level_code' => 'L15',
                'level_label' => 'Institutional administrator',
                'level_order' => 15,
                'unit_code' => 'ops',
                'unit_label' => 'Operations',
                'visual_token' => 'authority-high',
                'color' => '#1f7a8c',
            ]);
            $badge = OrganizationClassificationPresenter::badge($classification);
            $checks['classification_payload'] = $classification->toArray()['scope_key'] === 'authority'
                && $classification->toArray()['level_order'] === 15;
            $checks['badge_payload'] = ($badge['label'] ?? '') === 'Institutional administrator'
                && ($badge['class'] ?? '') === 'org-badge org-badge--authority-high'
                && ($badge['style'] ?? '') === '--org-badge-color:#1f7a8c';
        } else {
            $checks['classification_payload'] = false;
            $checks['badge_payload'] = false;
        }

        $success = !in_array(false, $checks, true);
        $payload = [
            'success' => $success,
            'checks' => $checks,
            'classification' => $classification?->toArray(),
            'badge' => $badge,
        ];

        if ($json) {
            $this->line((string)json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return $success ? 0 : 1;
        }

        $this->line('');
        $this->info('Organization Hierarchy Smoke');
        $this->line(str_repeat('-', 74));
        foreach ($checks as $name => $passed) {
            $this->line(sprintf('  %-40s %s', ucwords(str_replace('_', ' ', $name)), $passed ? 'OK' : 'ISSUES'));
        }
        $this->line(str_repeat('-', 74));
        $success ? $this->success('Organization hierarchy contract is coherent.') : $this->error('Organization hierarchy contract has issues.');
        $this->line('');

        return $success ? 0 : 1;
    }

    /**
     * Confirms RoleRepository exposes optional classification without changing RBAC checks.
     *
     * Responsibility: Uses source inspection to protect the integration boundary from disappearing.
     */
    private function roleRepositoryHasOrganizationIntegration(): bool
    {
        $path = PD . DS . 'app' . DS . 'Framework' . DS . 'Authorization' . DS . 'RoleRepository.php';
        $contents = is_file($path) ? (string)file_get_contents($path) : '';

        return str_contains($contents, 'hierarchy_scope_id')
            && str_contains($contents, 'hierarchy_level_id')
            && str_contains($contents, 'syncRoleOrganizationUnits');
    }

    /**
     * Confirms the Roles admin UI exposes and submits horizontal unit links.
     *
     * Responsibility: Protects role organization unit editing from regressing to repository-only storage.
     */
    private function roleAdminHasOrganizationUnitIntegration(): bool
    {
        $controllerPath = PD . DS . 'Repository' . DS . 'Framework' . DS . 'Roles' . DS . 'Controllers' . DS . 'RolesController.php';
        $requestPath = PD . DS . 'Repository' . DS . 'Framework' . DS . 'Roles' . DS . 'Requests' . DS . 'RolePayloadRequest.php';
        $controller = is_file($controllerPath) ? (string)file_get_contents($controllerPath) : '';
        $request = is_file($requestPath) ? (string)file_get_contents($requestPath) : '';

        return str_contains($controller, 'organization_unit_ids')
            && str_contains($controller, 'organizationUnitOptions')
            && str_contains($controller, "positiveIntList(\$payload['organization_unit_ids'] ?? [])")
            && str_contains($request, 'normalizeOrganizationUnitIds');
    }
}
