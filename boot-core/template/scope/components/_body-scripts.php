<?php

declare(strict_types=1);

return static function (): array {
    $assetVersion = static function (array $segments): int {
        $path = implode(DS, array_merge([PD, 'public'], $segments));
        return (int) (@filemtime($path) ?: time());
    };

    return [
        'bootstrap_bundle_asset_version' => $assetVersion(['assets', 'vendor', 'bootstrap', 'js', 'bootstrap.bundle.min.js']),
        'ui_actions_asset_version' => $assetVersion(['assets', 'js', 'catalyst', 'modules', 'ui-actions.js']),
        'theme_toggle_asset_version' => $assetVersion(['assets', 'js', 'catalyst', 'modules', 'theme-toggle.js']),
        'admin_form_dependencies_asset_version' => $assetVersion(['assets', 'js', 'catalyst', 'modules', 'admin-form-dependencies.js']),
        'admin_grid_asset_version' => $assetVersion(['assets', 'js', 'catalyst', 'modules', 'admin-grid.js']),
        'record_presence_asset_version' => $assetVersion(['assets', 'js', 'catalyst', 'modules', 'record-presence.js']),
        'flash_client_asset_version' => $assetVersion(['assets', 'js', 'catalyst', 'modules', 'flash-client.js']),
    ];
};
