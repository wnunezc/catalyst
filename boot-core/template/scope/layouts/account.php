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
    $styleLinks = [];
    $scriptLinks = [];
    $metaTags = [];

    $appendStyle = static function (string $href, string $rel = 'stylesheet', string $media = '') use (&$styleLinks): void {
        if ($href === '') {
            return;
        }
        foreach ($styleLinks as $existing) {
            if (($existing['href'] ?? '') === $href) {
                return;
            }
        }
        $styleLinks[] = [
            'href' => $href,
            'rel' => $rel,
            'has_media' => $media !== '',
            'media' => $media,
        ];
    };

    $appendScript = static function (string $src, bool $defer = false, bool $async = false, string $type = '', string $nonce = '') use (&$scriptLinks): void {
        if ($src === '') {
            return;
        }
        foreach ($scriptLinks as $existing) {
            if (($existing['src'] ?? '') === $src) {
                return;
            }
        }
        $scriptLinks[] = [
            'src' => $src,
            'has_type' => $type !== '',
            'type' => $type,
            'defer' => $defer,
            'async' => $async,
            'has_nonce' => $nonce !== '',
            'nonce' => $nonce,
        ];
    };

    foreach ((array) ($scope['styles'] ?? []) as $style) {
        if (is_array($style)) {
            $appendStyle((string) ($style['href'] ?? ''), (string) ($style['rel'] ?? 'stylesheet'), trim((string) ($style['media'] ?? '')));
            continue;
        }
        $appendStyle((string) $style);
    }

    foreach ((array) ($scope['scripts'] ?? []) as $script) {
        if (is_array($script)) {
            $appendScript(
                (string) ($script['src'] ?? ''),
                !empty($script['defer']),
                !empty($script['async']),
                trim((string) ($script['type'] ?? '')),
                trim((string) ($script['nonce'] ?? ''))
            );
            continue;
        }
        $appendScript((string) $script);
    }

    foreach ((array) ($scope['meta'] ?? []) as $name => $content) {
        $metaTags[] = [
            'name' => (string) $name,
            'content' => (string) $content,
        ];
    }

    $moduleSlug = trim((string) ($scope['moduleSlug'] ?? ''));
    if ($moduleSlug !== '') {
        $moduleCssPath = implode(DS, [PD, 'public', 'assets', 'css', 'work', $moduleSlug, 'style.css']);
        $moduleJsPath = implode(DS, [PD, 'public', 'assets', 'js', 'work', $moduleSlug, 'script.js']);

        if (file_exists($moduleCssPath)) {
            $appendStyle('/assets/css/work/' . rawurlencode($moduleSlug) . '/style.css?v=' . rawurlencode((string) (@filemtime($moduleCssPath) ?: time())));
        }

        if (file_exists($moduleJsPath)) {
            $appendScript('/assets/js/work/' . rawurlencode($moduleSlug) . '/script.js?v=' . rawurlencode((string) (@filemtime($moduleJsPath) ?: time())), true, false, 'module');
        }
    }

    return array_merge($scope, [
        'meta_tags' => $metaTags,
        'style_links' => $styleLinks,
        'script_links' => $scriptLinks,
    ]);
};
