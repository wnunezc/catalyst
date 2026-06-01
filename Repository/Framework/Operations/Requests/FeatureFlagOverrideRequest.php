<?php

declare(strict_types=1);

namespace Catalyst\Repository\Operations\Requests;

use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Framework\Authorization\PermissionRegistry;
use Catalyst\Framework\Http\FormRequest;

final class FeatureFlagOverrideRequest extends FormRequest
{
    public function authorize(): bool
    {
        return PermissionRegistry::getInstance()->userHasResourceAbility(
            AuthManager::getInstance()->user(),
            'operations',
            'manage'
        );
    }

    /**
     * @return string[]
     */
    public function only(): array
    {
        return ['flag_key', 'subject_type', 'subject_key', 'enabled', 'note'];
    }

    /**
     * @return array<string, string|array<int, string>>
     */
    public function rules(): array
    {
        return [
            'flag_key' => 'required|max:180',
            'subject_type' => 'required|in:user,role',
            'subject_key' => 'required|max:180',
            'enabled' => 'in:0,1',
            'note' => 'max:255',
        ];
    }
}
