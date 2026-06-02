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

namespace Catalyst\Repository\Operations\Requests;

use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Framework\Authorization\PermissionRegistry;
use Catalyst\Framework\Http\FormRequest;

/**
 * Validates deployment execution input for platform operations.
 *
 * @package Catalyst\Repository\Operations\Requests
 * Responsibility: Authorizes and constrains deployment profile and dry-run fields.
 */
final class DeploymentRunRequest extends FormRequest
{
    /**
     * Returns whether the current user may execute deployment operations.
     *
     * Responsibility: Returns whether the current user may execute deployment operations.
     */
    public function authorize(): bool
    {
        return PermissionRegistry::getInstance()->userHasResourceAbility(
            AuthManager::getInstance()->user(),
            'operations',
            'manage'
        );
    }

    /**
     * Returns the deployment fields accepted from the request.
     *
     * Responsibility: Returns the deployment fields accepted from the request.
     * @return string[]
     */
    public function only(): array
    {
        return ['profile_key', 'dry_run'];
    }

    /**
     * Returns validation rules for deployment execution input.
     *
     * Responsibility: Returns validation rules for deployment execution input.
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
