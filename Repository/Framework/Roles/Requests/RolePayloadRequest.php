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

namespace Catalyst\Repository\Roles\Requests;

use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Framework\Authorization\PermissionRegistry;
use Catalyst\Framework\Http\FormRequest;

/**
 * Validates role create and update payloads.
 *
 * @package Catalyst\Repository\Roles\Requests
 * Responsibility: Authorizes role mutations and defines accepted fields, validation rules and labels.
 */
class RolePayloadRequest extends FormRequest
{
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
        return ['name', 'slug', 'description', 'hierarchy_scope_id', 'hierarchy_level_id'];
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
        ];
    }
}
