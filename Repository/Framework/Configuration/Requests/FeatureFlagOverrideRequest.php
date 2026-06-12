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

namespace Catalyst\Repository\Configuration\Requests;

use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Framework\Authorization\PermissionRegistry;
use Catalyst\Framework\Http\FormRequest;

/**
 * Validates subject-specific feature-flag override input.
 *
 * @package Catalyst\Repository\Configuration\Requests
 * Responsibility: Authorizes and constrains feature-flag override mutations.
 */
final class FeatureFlagOverrideRequest extends FormRequest
{
    /**
     * Returns whether the current user may manage feature flags.
     *
     * Responsibility: Returns whether the current user may manage feature flags.
     */
    public function authorize(): bool
    {
        return PermissionRegistry::getInstance()->userHasResourceAbility(
            AuthManager::getInstance()->user(),
            'configuration',
            'manage'
        );
    }

    /**
     * Returns the feature-flag override fields accepted from the request.
     *
     * Responsibility: Returns the feature-flag override fields accepted from the request.
     * @return string[]
     */
    public function only(): array
    {
        return ['flag_key', 'subject_type', 'subject_key', 'enabled', 'note'];
    }

    /**
     * Returns validation rules for a feature-flag override.
     *
     * Responsibility: Returns validation rules for a feature-flag override.
     * @return array<string, string|array<int, string>>
     */
    public function rules(): array
    {
        return [
            'flag_key' => ['required', 'max:180', 'regex:/^[a-z0-9][a-z0-9._-]*$/'],
            'subject_type' => 'required|in:user,role',
            'subject_key' => ['required', 'max:180', 'regex:/^[A-Za-z0-9][A-Za-z0-9._:@-]*$/'],
            'enabled' => 'in:0,1',
            'note' => 'max:255',
        ];
    }
}
