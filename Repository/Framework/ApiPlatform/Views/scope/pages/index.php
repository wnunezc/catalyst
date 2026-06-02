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

use Catalyst\Framework\View\TrustedHtml;
use Catalyst\Helpers\Security\CsrfProtection;

return static function (array $scope): array {
    $catalogRoutes = [];
    foreach ((array) ($scope['catalogRoutes'] ?? []) as $route) {
        $route = is_array($route) ? $route : [];
        $catalogRoutes[] = [
            'method' => (string) ($route['method'] ?? 'GET'),
            'path' => (string) ($route['path'] ?? ''),
            'permission' => (string) ($route['permission'] ?? ''),
            'description' => (string) ($route['description'] ?? ''),
        ];
    }

    $tokensRows = [];
    foreach ((array) ($scope['tokens'] ?? []) as $token) {
        $token = is_array($token) ? $token : [];
        $tokensRows[] = [
            'name' => (string) ($token['name'] ?? ''),
            'token_prefix' => (string) ($token['token_prefix'] ?? ''),
            'user_id' => (int) ($token['user_id'] ?? 0),
            'abilities_label' => implode(', ', (array) ($token['abilities_json'] ?? [])),
            'expires_label' => (string) (($token['expires_at'] ?? null) ?: __('apiplatform.tokens.never')),
            'last_used_label' => (string) (($token['last_used_at'] ?? null) ?: __('apiplatform.tokens.never')),
            'revoke_url' => '/api-platform/tokens/' . (int) ($token['id'] ?? 0) . '/revoke',
        ];
    }

    return [
        'admin_header' => [
            'eyebrow' => __('apiplatform.index.eyebrow'),
            'title' => (string) ($scope['title'] ?? __('apiplatform.index.title')),
            'description' => __('apiplatform.index.description'),
            'metrics' => [
                ['label' => __('apiplatform.index.chips.bearer'), 'value' => 'Bearer'],
                ['label' => 'Version', 'value' => 'v1'],
                ['label' => __('apiplatform.index.chips.runtime'), 'value' => 'Enabled'],
            ],
        ],

        'form' => (array) ($scope['form'] ?? []),
        'catalog_routes' => $catalogRoutes,
        'tokens_rows' => $tokensRows,
        'created_token_plain_text' => (string) ($scope['createdTokenPlainText'] ?? ''),
        'has_created_token' => (string) ($scope['createdTokenPlainText'] ?? '') !== '',
        'csrfField' => TrustedHtml::fromString(CsrfProtection::getInstance()->getTokenField()),
    ];
};
