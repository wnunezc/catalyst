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

use Catalyst\Framework\Session\ToastQueue;
use Catalyst\Framework\View\InlineJson;
use Catalyst\Framework\View\TrustedHtml;
use Catalyst\Helpers\Security\CspNonce;

return static function (array $scope): array {
    $assetVersion = static function (array $segments): int {
        $path = implode(DS, array_merge([PD, 'public'], $segments));

        if (is_dir($path)) {
            $maxMtime = (int) (@filemtime($path) ?: 0);
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)
            );

            foreach ($iterator as $entry) {
                if (!$entry->isFile()) {
                    continue;
                }

                $maxMtime = max($maxMtime, (int) $entry->getMTime());
            }

            return $maxMtime > 0 ? $maxMtime : time();
        }

        return (int) (@filemtime($path) ?: time());
    };

    $moduleSlug = $scope['moduleSlug'] ?? null;
    $toasterPosition = (string) ($scope['toasterPosition'] ?? 'top-right');
    $toasterDuration = (int) ($scope['toasterDuration'] ?? 5000);
    $maxToasts = (int) ($scope['maxToasts'] ?? 5);
    $modalSize = (string) ($scope['modalSize'] ?? 'medium');
    $modalBackdrop = (bool) ($scope['modalBackdrop'] ?? true);
    $fetchIntercept = (bool) ($scope['fetchIntercept'] ?? true);
    $formHandler = (bool) ($scope['formHandler'] ?? true);
    $pendingToasts = [];

    foreach (ToastQueue::getInstance()->all() as $toast) {
        $pendingToasts[] = [
            'type' => preg_replace('/[^a-z_]/i', '', (string) ($toast['type'] ?? 'info')) ?: 'info',
            'message' => (string) ($toast['message'] ?? ''),
        ];
    }

    $workAssetSlug = is_string($moduleSlug) && $moduleSlug !== ''
        ? rawurlencode($moduleSlug)
        : '';
    $workJs = $workAssetSlug !== ''
        ? implode(DS, [PD, 'public', 'assets', 'js', 'work', $moduleSlug, 'script.js'])
        : '';
    $workCss = $workAssetSlug !== ''
        ? implode(DS, [PD, 'public', 'assets', 'css', 'work', $moduleSlug, 'style.css'])
        : '';

    return [
        'csp_nonce' => CspNonce::get(),
        'catalyst_asset_version' => $assetVersion(['assets', 'js', 'catalyst']),
        'catalyst_runtime_config_json' => TrustedHtml::fromString(InlineJson::encode([
            'moduleVersion' => $assetVersion(['assets', 'js', 'catalyst']),
            'catalyst' => [
                'toaster' => [
                    'position' => $toasterPosition,
                    'duration' => $toasterDuration,
                    'maxToasts' => $maxToasts,
                    'pauseOnHover' => true,
                    'showProgress' => true,
                    'newestOnTop' => true,
                ],
                'modal' => [
                    'size' => $modalSize,
                    'backdrop' => $modalBackdrop,
                    'keyboard' => true,
                    'scrollable' => false,
                    'centered' => true,
                ],
                'fetchIntercept' => $fetchIntercept,
                'formHandler' => $formHandler,
            ],
            'pendingToasts' => $pendingToasts,
        ])),
        'has_work_js' => $workJs !== '' && file_exists($workJs),
        'has_work_css' => $workCss !== '' && file_exists($workCss),
        'work_asset_slug' => $workAssetSlug,
        'work_js_asset_version' => $workJs !== '' && file_exists($workJs) ? (int) (@filemtime($workJs) ?: time()) : 0,
        'work_css_asset_version' => $workCss !== '' && file_exists($workCss) ? (int) (@filemtime($workCss) ?: time()) : 0,
        'suppress_work_assets' => (bool) ($scope['suppress_work_assets'] ?? false),
    ];
};
