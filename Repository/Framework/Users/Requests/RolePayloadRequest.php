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

namespace Catalyst\Repository\Users\Requests;

use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Framework\Authorization\PermissionRegistry;
use Catalyst\Framework\Http\FormRequest;
use Catalyst\Helpers\Exceptions\ForbiddenException;
use Catalyst\Helpers\Exceptions\ValidationException;
use Catalyst\Helpers\Validation\Validator;

/**
 * Validates role create and update payloads.
 *
 * @package Catalyst\Repository\Users\Requests
 * Responsibility: Authorizes role mutations and defines accepted fields, validation rules and labels.
 */
class RolePayloadRequest extends FormRequest
{
    /**
     * @var array<string, mixed>|null
     */
    private ?array $resolvedData = null;

    /**
     * Determines whether the current user may create or update a role.
     *
     * Responsibility: Determines whether the current user may create or update a role.
     */
    public function authorize(): bool
    {
        return PermissionRegistry::getInstance()->userHasResourceAbility(
            AuthManager::getInstance()->user(),
            'roles',
            (int) ($this->route('id') ?? 0) > 0 ? 'update' : 'create'
        );
    }

    /**
     * Returns the accepted role payload fields.
     *
     * Responsibility: Returns the accepted role payload fields.
     * @return string[]
     */
    public function only(): array
    {
        return ['name', 'slug', 'description', 'hierarchy_scope_id', 'hierarchy_level_id', 'organization_unit_ids'];
    }

    /**
     * Returns role validation rules, excluding the edited record from uniqueness checks.
     *
     * Responsibility: Returns role validation rules, excluding the edited record from uniqueness checks.
     * @return array<string, string|array<int, string>>
     */
    public function rules(): array
    {
        $roleId = (int) ($this->route('id') ?? 0);
        $nameRule = 'required|max:50|unique:roles,name';
        $slugRule = 'required|max:50|unique:roles,slug';

        if ($roleId > 0) {
            $nameRule .= ',' . $roleId . ',id';
            $slugRule .= ',' . $roleId . ',id';
        }

        return [
            'name' => $nameRule,
            'slug' => $slugRule,
            'description' => 'max:255',
            'hierarchy_scope_id' => 'max:20',
            'hierarchy_level_id' => 'max:20',
        ];
    }

    /**
     * Returns translated labels for role validation errors.
     *
     * Responsibility: Returns translated labels for role validation errors.
     * @return array<string, string>
     */
    public function labels(): array
    {
        return [
            'name' => __('roles.common.name'),
            'slug' => __('roles.common.slug'),
            'description' => __('roles.common.description'),
            'hierarchy_scope_id' => __('roles.organization.scope'),
            'hierarchy_level_id' => __('roles.organization.level'),
            'organization_unit_ids' => __('roles.organization.units'),
        ];
    }

    /**
     * Returns the validated role payload, including normalized organization unit ids.
     *
     * Responsibility: Returns the validated role payload, including normalized organization unit ids.
     * @return array<string, mixed>
     */
    public function validated(): array
    {
        if ($this->resolvedData === null) {
            $this->validateResolved();
        }

        return $this->resolvedData ?? [];
    }

    /**
     * Authorizes and validates role payload data plus custom array input.
     *
     * Responsibility: Keeps optional organization unit links validated before controller persistence.
     * @throws ValidationException
     * @throws ForbiddenException
     */
    public function validateResolved(): void
    {
        if (!$this->authorize()) {
            throw ForbiddenException::forbidden(__('messages.request_not_authorized'));
        }

        $this->prepareForValidation();
        $data = $this->validationData();
        $validator = new Validator($data, $this->rules(), $this->labels());
        $errors = $validator->fails() ? $validator->errors() : [];

        [$organizationUnitIds, $unitErrors] = $this->normalizeOrganizationUnitIds($data['organization_unit_ids'] ?? null);
        $errors = array_merge_recursive($errors, $unitErrors);

        if ($errors !== []) {
            throw ValidationException::withErrors(
                $errors,
                $this->validationMessage(),
                $this->safeOldInput($data)
            );
        }

        $data['organization_unit_ids'] = $organizationUnitIds;
        $this->resolvedData = $data;
    }

    /**
     * Normalizes optional role-to-organization-unit identifiers.
     *
     * Responsibility: Accepts the multi-select payload while rejecting non-positive or non-numeric identifiers.
     * @return array{0:array<int,int>,1:array<string,string[]>}
     */
    private function normalizeOrganizationUnitIds(mixed $value): array
    {
        if ($value === null || $value === '') {
            return [[], []];
        }

        if (!is_array($value)) {
            return [[], ['organization_unit_ids' => [(string) __('roles.organization.units_invalid')]]];
        }

        $ids = [];
        foreach ($value as $candidate) {
            if ($candidate === null || $candidate === '') {
                continue;
            }

            $candidate = trim((string) $candidate);
            if ($candidate === '' || !ctype_digit($candidate) || (int) $candidate <= 0) {
                return [[], ['organization_unit_ids' => [(string) __('roles.organization.units_invalid')]]];
            }

            $ids[] = (int) $candidate;
        }

        return [array_values(array_unique($ids)), []];
    }
}
