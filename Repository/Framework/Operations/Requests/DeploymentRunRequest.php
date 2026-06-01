<?php

declare(strict_types=1);

namespace Catalyst\Repository\Operations\Requests;

use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Framework\Authorization\PermissionRegistry;
use Catalyst\Framework\Http\FormRequest;

final class DeploymentRunRequest extends FormRequest
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
        return ['profile_key', 'dry_run'];
    }

    /**
     * @return array<string, string|array<int, string>>
     */
    public function rules(): array
    {
        return [
            'profile_key' => 'required|max:120',
            'dry_run' => 'in:0,1',
        ];
    }
}
