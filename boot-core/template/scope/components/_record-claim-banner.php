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

return static function (array $scope): array {
    $claimContext = $scope['claimContext'] ?? $scope['claim_context'] ?? null;
    if (!is_array($claimContext) || $claimContext === []) {
        return [
            'claim_banner' => [
                'visible' => false,
            ],
        ];
    }

    $status = trim((string) ($claimContext['status'] ?? 'active'));
    $isOwner = (bool) ($claimContext['is_owner'] ?? false);
    $claimedByLabel = trim((string) ($claimContext['claimed_by_label'] ?? ''));
    $expiresAt = trim((string) ($claimContext['expires_at'] ?? ''));
    $claimedAt = trim((string) ($claimContext['claimed_at'] ?? ''));
    $releasedAt = trim((string) ($claimContext['released_at'] ?? ''));
    $resourceKey = trim((string) ($claimContext['resource_key'] ?? ''));
    $recordId = (int) ($claimContext['record_id'] ?? 0);
    $tenantId = (int) ($claimContext['tenant_id'] ?? 0);
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
        'claim_banner' => [
            'visible' => true,
            'alert_class' => $alertClass,
            'tenant_id' => (string) $tenantId,
            'resource_key' => $resourceKey,
            'record_id' => (string) $recordId,
            'is_owner_flag' => $isOwner ? '1' : '0',
            'heartbeat_url' => $heartbeatUrl,
            'headline' => $headline,
            'detail' => $detail,
            'claimed_at' => $claimedAt,
            'expires_at' => $expiresAt,
            'released_at' => $releasedAt,
            'expires_class' => $claimedAt !== '' ? 'ms-3' : '',
            'released_class' => $hasPreviousTimestamps ? 'ms-3' : '',
        ],
    ];
};
