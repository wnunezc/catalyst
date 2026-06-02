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

namespace App\Services;

use App\Repositories\UserProfileRepository;
use Catalyst\Entities\UserProfile;

/**
 * Defines the User Profile Service class contract.
 *
 * @package App\Services
 * Responsibility: Coordinates the user profile service behavior within its module boundary.
 */
final class UserProfileService
{
    /**
     * Initializes the User Profile Service instance.
     */
    public function __construct(
        private readonly ?UserProfileRepository $profiles = null
    ) {
    }

    /**
     * @param array<string, mixed>|null $user
     * @return array<string, mixed>
     */
    public function dashboardSummary(?array $user): array
    {
        $profile = null;
        if (is_array($user) && isset($user['id'])) {
            $profile = $this->profiles()->findByUserId((int) $user['id']);
        }

        return [
            'user' => [
                'id' => (int) ($user['id'] ?? 0),
                'name' => (string) ($user['name'] ?? 'Usuario autenticado'),
                'email' => (string) ($user['email'] ?? ''),
                'role' => (string) ($user['role'] ?? ''),
                'tenant_key' => (string) ($user['tenant_key'] ?? ''),
            ],
            'profile' => $profile instanceof UserProfile ? $this->serializeProfile($profile) : null,
            'profile_count' => $this->profiles()->totalProfiles(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeProfile(UserProfile $profile): array
    {
        return [
            'id' => (int) $profile->getKey(),
            'user_id' => (int) ($profile->user_id ?? 0),
            'document_id' => (string) ($profile->document_id ?? ''),
            'phone' => (string) ($profile->phone ?? ''),
            'organization' => (string) ($profile->organization ?? ''),
            'position' => (string) ($profile->position ?? ''),
            'department' => (string) ($profile->department ?? ''),
            'updated_at' => $profile->updated_at instanceof \DateTimeInterface
                ? $profile->updated_at->format(DATE_ATOM)
                : (string) ($profile->updated_at ?? ''),
        ];
    }

    /**
     * Handles the profiles workflow.
     */
    private function profiles(): UserProfileRepository
    {
        return $this->profiles ?? new UserProfileRepository();
    }
}
