<?php

declare(strict_types=1);

namespace Catalyst\Framework\Presence;

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

final class RecordPresenceViewModel
{
    /**
     * @param array<string, mixed> $scope
     * @return array<string, mixed>
     */
    public static function build(array $scope): array
    {
    $presence = $scope['recordPresence'] ?? $scope['record_presence'] ?? null;
    if (!is_array($presence) || $presence === []) {
        return [
            'record_presence' => [
                'visible' => false,
            ],
        ];
    }

    $status = trim((string) ($presence['status'] ?? 'active'));
    $isOwner = (bool) ($presence['is_owner'] ?? false);
    $claimedBy = (int) ($presence['claimed_by'] ?? 0);
    $claimedByLabel = trim((string) ($presence['claimed_by_label'] ?? ''));
    $expiresAt = trim((string) ($presence['expires_at'] ?? ''));
    $claimedAt = trim((string) ($presence['claimed_at'] ?? ''));
    $releasedAt = trim((string) ($presence['released_at'] ?? ''));
    $resourceKey = trim((string) ($presence['resource_key'] ?? ''));
    $recordId = (int) ($presence['record_id'] ?? 0);
    $tenantId = (int) ($presence['tenant_id'] ?? 0);
    $heartbeatUrl = $resourceKey !== '' && $recordId > 0
        ? '/api/presence/' . rawurlencode($resourceKey) . '/' . $recordId . '/heartbeat'
        : '';

    $alertClass = match ($status) {
        'released' => 'alert-secondary',
        'expired' => 'alert-warning',
        default => $isOwner ? 'alert-info' : 'alert-danger',
    };

    $headline = match ($status) {
        'released' => __('ui.record_claim.released'),
        'expired' => __('ui.record_claim.expired'),
        default => $isOwner ? __('ui.record_claim.active_owner') : __('ui.record_claim.active_other'),
    };

    $detail = match ($status) {
        'released' => __('ui.record_claim.released_description'),
        'expired' => __('ui.record_claim.expired_description'),
        default => $isOwner
            ? __('ui.record_claim.owner_description')
            : __('ui.record_claim.other_description', [
                'actor' => $claimedByLabel !== '' ? $claimedByLabel : __('ui.record_claim.fallback_actor'),
            ]),
    };

    $hasPreviousTimestamps = $claimedAt !== '' || $expiresAt !== '';

    return [
        'record_presence' => [
            'visible' => true,
            'root_class' => 'record-presence',
            'alert_class' => $alertClass,
            'tenant_id' => (string) $tenantId,
            'resource_key' => $resourceKey,
            'record_id' => (string) $recordId,
            'is_owner_flag' => $isOwner ? '1' : '0',
            'owner_actor_id' => $isOwner ? (string) $claimedBy : '',
            'heartbeat_url' => $heartbeatUrl,
            'headline' => $headline,
            'detail' => $detail,
            'claimed_at' => $claimedAt,
            'expires_at' => $expiresAt,
            'released_at' => $releasedAt,
            'expires_class' => $claimedAt !== '' ? 'ms-3' : '',
            'released_class' => $hasPreviousTimestamps ? 'ms-3' : '',
            'claimed_hidden_class' => $claimedAt === '' ? 'd-none' : '',
            'expires_hidden_class' => $expiresAt === '' ? 'd-none' : '',
            'released_hidden_class' => $releasedAt === '' ? 'd-none' : '',
            'active_owner_label' => __('ui.record_claim.active_owner'),
            'active_other_label' => __('ui.record_claim.active_other'),
            'released_label' => __('ui.record_claim.released'),
            'expired_label' => __('ui.record_claim.expired'),
            'owner_description' => __('ui.record_claim.owner_description'),
            'other_description_template' => __('ui.record_claim.other_description', ['actor' => '__ACTOR__']),
            'released_description' => __('ui.record_claim.released_description'),
            'expired_description' => __('ui.record_claim.expired_description'),
            'fallback_actor' => __('ui.record_claim.fallback_actor'),
            'claimed_at_label' => __('ui.record_claim.claimed_at'),
            'expires_at_label' => __('ui.record_claim.expires_at'),
            'released_at_label' => __('ui.record_claim.released_at'),
        ],
    ];
    }
}
