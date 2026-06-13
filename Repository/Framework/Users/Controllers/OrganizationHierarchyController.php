<?php

declare(strict_types=1);

namespace Catalyst\Repository\Users\Controllers;

use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Catalyst\Framework\Organization\OrganizationRepository;
use InvalidArgumentException;
use Throwable;

/**
 * Manages administrator-configured organization hierarchy metadata.
 *
 * @package Catalyst\Repository\Users\Controllers
 * Responsibility: Provides UI endpoints to create organizations, units, hierarchy scopes and levels consumed by role classification.
 */
final class OrganizationHierarchyController extends Controller
{
    private OrganizationRepository $organizations;

    public function __construct()
    {
        parent::__construct();
        $this->organizations = new OrganizationRepository();
    }

    /**
     * Displays the organization hierarchy console.
     *
     * Responsibility: Renders existing hierarchy metadata and compact create forms.
     */
    public function index(Request $request): Response
    {
        $this->authorizeResource('manage', 'roles');

        return $this->view('users.organization-hierarchy', [
            'title' => (string)__('roles.organization_hierarchy.title'),
            'pageTitle' => (string)__('roles.organization_hierarchy.title'),
            'organizations' => $this->organizations->organizations(),
            'units' => $this->organizations->units(),
            'scopes' => $this->organizations->scopes(),
            'levels' => $this->organizations->levels(),
        ]);
    }

    /**
     * Creates or updates an organization.
     *
     * Responsibility: Persists organization metadata submitted by administrators.
     */
    public function storeOrganization(Request $request): Response
    {
        $this->authorizeResource('manage', 'roles');

        try {
            $this->organizations->saveOrganization(
                $this->requiredText($request, 'name', 160),
                $this->slug($request, 'slug', 120),
                $this->optionalText($request, 'description', 255),
                $this->bool($request, 'is_default')
            );
        } catch (Throwable $e) {
            return $this->postActionErrorRedirect('/users/organization-hierarchy', $this->message($e), 422);
        }

        return $this->postActionSuccessRedirect('/users/organization-hierarchy', (string)__('roles.organization_hierarchy.messages.organization_saved'));
    }

    /**
     * Creates or updates an organization unit.
     *
     * Responsibility: Persists horizontal unit metadata submitted by administrators.
     */
    public function storeUnit(Request $request): Response
    {
        $this->authorizeResource('manage', 'roles');

        try {
            $this->organizations->saveUnit(
                $this->positiveInt($request, 'organization_id'),
                $this->requiredText($request, 'name', 160),
                $this->slug($request, 'code', 80),
                $this->optionalText($request, 'unit_type', 80),
                $this->optionalText($request, 'description', 255),
                $this->optionalSlug($request, 'visual_token', 80),
                $this->color($request, 'color'),
                $this->int($request, 'sort_order'),
                $this->bool($request, 'is_active', true)
            );
        } catch (Throwable $e) {
            return $this->postActionErrorRedirect('/users/organization-hierarchy', $this->message($e), 422);
        }

        return $this->postActionSuccessRedirect('/users/organization-hierarchy', (string)__('roles.organization_hierarchy.messages.unit_saved'));
    }

    /**
     * Creates or updates a hierarchy scope.
     *
     * Responsibility: Persists hierarchy axis metadata submitted by administrators.
     */
    public function storeScope(Request $request): Response
    {
        $this->authorizeResource('manage', 'roles');

        try {
            $this->organizations->saveScope(
                $this->positiveInt($request, 'organization_id'),
                $this->slug($request, 'scope_key', 120),
                $this->requiredText($request, 'label', 160),
                $this->optionalText($request, 'description', 255),
                $this->optionalSlug($request, 'visual_token', 80),
                $this->color($request, 'color'),
                $this->int($request, 'sort_order'),
                $this->bool($request, 'is_active', true)
            );
        } catch (Throwable $e) {
            return $this->postActionErrorRedirect('/users/organization-hierarchy', $this->message($e), 422);
        }

        return $this->postActionSuccessRedirect('/users/organization-hierarchy', (string)__('roles.organization_hierarchy.messages.scope_saved'));
    }

    /**
     * Creates or updates a hierarchy level.
     *
     * Responsibility: Persists ordered level metadata submitted by administrators.
     */
    public function storeLevel(Request $request): Response
    {
        $this->authorizeResource('manage', 'roles');

        try {
            $this->organizations->saveLevel(
                $this->positiveInt($request, 'scope_id'),
                $this->slug($request, 'code', 80),
                $this->requiredText($request, 'label', 160),
                $this->int($request, 'level_order'),
                $this->optionalText($request, 'description', 255),
                $this->optionalSlug($request, 'visual_token', 80),
                $this->color($request, 'color'),
                $this->bool($request, 'is_active', true)
            );
        } catch (Throwable $e) {
            return $this->postActionErrorRedirect('/users/organization-hierarchy', $this->message($e), 422);
        }

        return $this->postActionSuccessRedirect('/users/organization-hierarchy', (string)__('roles.organization_hierarchy.messages.level_saved'));
    }

    private function requiredText(Request $request, string $key, int $max): string
    {
        $value = trim((string)$request->input($key, ''));
        if ($value === '' || mb_strlen($value) > $max) {
            throw new InvalidArgumentException((string)__('roles.organization_hierarchy.messages.invalid_payload'));
        }

        return $value;
    }

    private function optionalText(Request $request, string $key, int $max): ?string
    {
        $value = trim((string)$request->input($key, ''));
        if ($value === '') {
            return null;
        }
        if (mb_strlen($value) > $max) {
            throw new InvalidArgumentException((string)__('roles.organization_hierarchy.messages.invalid_payload'));
        }

        return $value;
    }

    private function slug(Request $request, string $key, int $max): string
    {
        $value = trim((string)$request->input($key, ''));
        if ($value === '' || mb_strlen($value) > $max || !preg_match('/^[a-z0-9][a-z0-9-]*$/', $value)) {
            throw new InvalidArgumentException((string)__('roles.organization_hierarchy.messages.invalid_key'));
        }

        return $value;
    }

    private function optionalSlug(Request $request, string $key, int $max): ?string
    {
        $value = trim((string)$request->input($key, ''));
        if ($value === '') {
            return null;
        }
        if (mb_strlen($value) > $max || !preg_match('/^[a-z0-9][a-z0-9-]*$/', $value)) {
            throw new InvalidArgumentException((string)__('roles.organization_hierarchy.messages.invalid_key'));
        }

        return $value;
    }

    private function positiveInt(Request $request, string $key): int
    {
        $value = (int)$request->input($key, 0);
        if ($value <= 0) {
            throw new InvalidArgumentException((string)__('roles.organization_hierarchy.messages.invalid_payload'));
        }

        return $value;
    }

    private function int(Request $request, string $key): int
    {
        return (int)$request->input($key, 0);
    }

    private function bool(Request $request, string $key, bool $default = false): bool
    {
        return in_array((string)$request->input($key, $default ? '1' : '0'), ['1', 'true', 'on', 'yes'], true);
    }

    private function color(Request $request, string $key): ?string
    {
        $value = trim((string)$request->input($key, ''));
        if ($value === '') {
            return null;
        }
        if (!preg_match('/^#[0-9a-fA-F]{6}$/', $value)) {
            throw new InvalidArgumentException((string)__('roles.organization_hierarchy.messages.invalid_color'));
        }

        return strtolower($value);
    }

    private function message(Throwable $e): string
    {
        return $e instanceof InvalidArgumentException ? $e->getMessage() : (string)__('roles.organization_hierarchy.messages.save_failed');
    }
}
