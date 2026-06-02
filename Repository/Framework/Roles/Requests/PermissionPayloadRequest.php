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
 * Validates permission create and update payloads.
 *
 * @package Catalyst\Repository\Roles\Requests
 * Responsibility: Authorizes permission mutations and defines accepted fields, validation rules and labels.
 */
class PermissionPayloadRequest extends FormRequest
{
    /**
     * Determines whether the current user may create or update a permission.
     *
     * Responsibility: Determines whether the current user may create or update a permission.
     */
    public function authorize(): bool
    {
        return PermissionRegistry::getInstance()->userHasResourceAbility(
            AuthManager::getInstance()->user(),
            'permissions',
            (int) ($this->route('id') ?? 0) > 0 ? 'update' : 'create'
        );
    }

    /**
     * Returns the accepted permission payload fields.
     *
     * Responsibility: Returns the accepted permission payload fields.
     * @return string[]
     */
    public function only(): array
    {
        return ['name', 'slug', 'description'];
    }

    /**
     * Returns permission validation rules, excluding the edited record from uniqueness checks.
     *
     * Responsibility: Returns permission validation rules, excluding the edited record from uniqueness checks.
     * @return array<string, string|array<int, string>>
     */
    public function rules(): array
    {
        $permissionId = (int) ($this->route('id') ?? 0);
        $nameRule = 'required|max:100|unique:permissions,name';
        $slugRule = 'required|max:100|unique:permissions,slug';

        if ($permissionId > 0) {
            $nameRule .= ',' . $permissionId . ',id';
            $slugRule .= ',' . $permissionId . ',id';
        }

        return [
            'name' => $nameRule,
            'slug' => $slugRule,
            'description' => 'max:255',
        ];
    }

    /**
     * Returns translated labels for permission validation errors.
     *
     * Responsibility: Returns translated labels for permission validation errors.
     * @return array<string, string>
     */
    public function labels(): array
    {
        return [
            'name' => __('roles.common.name'),
            'slug' => __('roles.common.slug'),
            'description' => __('roles.common.description'),
        ];
    }
}
