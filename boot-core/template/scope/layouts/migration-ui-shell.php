<?php

declare(strict_types=1);

$migrationProductShell = require __DIR__ . DS . '_migration-product-shell.php';

return static function (array $scope) use ($migrationProductShell): array {
    $title = (string) ($scope['title'] ?? 'Demo UI');
    $metaTags = [];
    $styleLinks = [];
    $scriptLinks = [];
    foreach ((array) ($scope['meta'] ?? []) as $name => $content) {
        $metaTags[] = [
            'name' => (string) $name,
            'content' => (string) $content,
        ];
    }

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

    $payload = [
        'document_title' => $title,
        'lang' => (string) ($scope['lang'] ?? 'en'),
        'meta_tags' => $metaTags,
        'style_links' => $styleLinks,
        'script_links' => $scriptLinks,
        'auth_name' => trim((string) ($scope['auth_name'] ?? 'Walter Nunez')),
        'auth_avatar_src' => trim((string) ($scope['auth_avatar_src'] ?? '/assets/img/inspinia/users/user-1.jpg')),
        'migration_nav_groups' => (array) ($scope['migration_nav_groups'] ?? []),
        'selected_doc_source_url' => (string) ($scope['selected_doc_source_url'] ?? '/demo-ui'),
    ];

    return $migrationProductShell(array_merge($scope, $payload));
};
