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
 * Defines the Feature Flag Override Request class contract.
 *
 * @package Catalyst\Repository\Operations\Requests
 * Responsibility: Coordinates the feature flag override request behavior within its module boundary.
 */
final class FeatureFlagOverrideRequest extends FormRequest
{
    /**
     * Handles the authorize workflow.
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
