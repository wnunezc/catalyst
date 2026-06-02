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

use Catalyst\Framework\Appearance\PlatformAppearanceManager;
use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Framework\View\TrustedHtml;
use Catalyst\Helpers\Security\CsrfProtection;

$demoProductShell = require __DIR__ . DS . '_demo-product-shell.php';

return static function (array $scope) use ($demoProductShell): array {
    $branding = PlatformAppearanceManager::getInstance()->brandingViewModel();
    $brandName = (string) ($branding['brand_name'] ?? 'Catalyst');
    $title = (string) ($scope['title'] ?? $brandName);
    $pageTitle = (string) ($scope['pageTitle'] ?? $title);
    $authUser = AuthManager::getInstance()->user() ?? [];
    $currentUri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
    $currentPath = parse_url($currentUri, PHP_URL_PATH) ?: '/';
    $metaTags = [];
    $styleLinks = [];
    $scriptLinks = [];

    foreach ((array) ($scope['styles'] ?? []) as $style) {
        if (is_array($style)) {
            $media = trim((string) ($style['media'] ?? ''));
            $styleLinks[] = [
                'href' => (string) ($style['href'] ?? ''),
                'rel' => (string) ($style['rel'] ?? 'stylesheet'),
                'has_media' => $media !== '',
                'media' => $media,
            ];
            continue;
        }

        $styleLinks[] = [
            'href' => (string) $style,
            'rel' => 'stylesheet',
            'has_media' => false,
            'media' => '',
        ];
    }

    foreach ((array) ($scope['scripts'] ?? []) as $script) {
        if (is_array($script)) {
            $type = trim((string) ($script['type'] ?? ''));
            $nonce = trim((string) ($script['nonce'] ?? ''));
            $scriptLinks[] = [
                'src' => (string) ($script['src'] ?? ''),
                'has_type' => $type !== '',
                'type' => $type,
                'defer' => !empty($script['defer']),
                'async' => !empty($script['async']),
                'has_nonce' => $nonce !== '',
                'nonce' => $nonce,
            ];
            continue;
        }

        $scriptLinks[] = [
            'src' => (string) $script,
            'has_type' => false,
            'type' => '',
            'defer' => false,
            'async' => false,
            'has_nonce' => false,
            'nonce' => '',
        ];
    }

    foreach ((array) ($scope['meta'] ?? []) as $name => $content) {
        $metaTags[] = [
            'name' => (string) $name,
            'content' => (string) $content,
        ];
    }

    $activeAdminContext = 'administration';
    if (str_starts_with($currentPath, '/dashboard') || str_starts_with($currentPath, '/workspaces')) {
        $activeAdminContext = 'workspace';
    } elseif (str_starts_with($currentPath, '/test-features') || str_starts_with($currentPath, '/devtools')) {
        $activeAdminContext = 'devtools';
    }

    $payload = [
        'branding' => $branding,
        'document_title' => $title !== $brandName ? $title . ' - ' . $brandName : $title,
        'page_title' => $pageTitle,
        'lang' => (string) ($scope['lang'] ?? 'en'),
        'meta_tags' => $metaTags,
        'style_links' => $styleLinks,
        'script_links' => $scriptLinks,
        'active_admin_context' => $activeAdminContext,
        'auth_name' => trim((string) ($authUser['name'] ?? 'Guest')),
        'auth_role' => trim((string) ($authUser['role'] ?? 'guest')),
        'auth_avatar_src' => (string) ($scope['auth_avatar_src'] ?? '/assets/vendor/inspinia/images/users/user-1.jpg'),
        'logout_csrf_field' => TrustedHtml::fromString(CsrfProtection::getInstance()->getTokenField()),
    ];

    return $demoProductShell(array_merge($scope, $payload));
};
