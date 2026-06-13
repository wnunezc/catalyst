<?php

declare(strict_types=1);

namespace Catalyst\Repository\Operations\Deployments\Requests;

use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Framework\Authorization\PermissionRegistry;
use Catalyst\Framework\Deployment\DeploymentManager;
use Catalyst\Framework\Http\FormRequest;

/**
 * Authorizes deployment execution and restricts profile selection to configured profiles.
 */
final class DeploymentRunRequest extends FormRequest
{
    public function authorize(): bool
    {
        return PermissionRegistry::getInstance()->userHasResourceAbility(
            AuthManager::getInstance()->user(),
            'operations-deployments',
            'manage'
        );
    }

    public function only(): array
    {
        return ['profile_key', 'dry_run'];
    }

    public function rules(): array
    {
        $profiles = array_keys(DeploymentManager::getInstance()->profiles());

        return [
            'profile_key' => 'required|max:120|in:' . implode(',', $profiles),
            'dry_run' => 'in:0,1',
        ];
    }

    public function labels(): array
    {
        return [
            'profile_key' => __('operations.deployments.form.fields.profile'),
            'dry_run' => __('operations.deployments.form.fields.dry_run'),
        ];
    }

    public function dryRun(): bool
    {
        return in_array($this->validated()['dry_run'] ?? null, [1, '1', true, 'on'], true);
    }
}
