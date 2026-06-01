<?php

declare(strict_types=1);

namespace Catalyst\Repository\Roles\Requests;

use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Framework\Authorization\PermissionRegistry;
use Catalyst\Framework\Http\FormRequest;

class PermissionPayloadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return PermissionRegistry::getInstance()->userHasResourceAbility(
            AuthManager::getInstance()->user(),
            'permissions',
            (int) ($this->route('id') ?? 0) > 0 ? 'update' : 'create'
        );
    }

    /**
     * @return string[]
     */
    public function only(): array
    {
        return ['name', 'slug', 'description'];
    }

    /**
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
