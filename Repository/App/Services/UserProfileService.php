<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\UserProfileRepository;
use Catalyst\Entities\UserProfile;

final class UserProfileService
{
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

    private function profiles(): UserProfileRepository
    {
        return $this->profiles ?? new UserProfileRepository();
    }
}
