<?php

declare(strict_types=1);

use Catalyst\Framework\Session\FlashMessage;

return static function (array $scope): array {
    $flash = FlashMessage::getInstance();
    $yes = __('ui.common.yes');
    $no = __('ui.common.no');

    return [
        'constants_rows' => [
            ['name' => 'ENV', 'value' => (string) (ENV ?? 'undefined'), 'value_class' => ''],
            ['name' => 'IS_DEVELOPMENT', 'value' => defined('IS_DEVELOPMENT') && IS_DEVELOPMENT ? 'true' : 'false', 'value_class' => defined('IS_DEVELOPMENT') && IS_DEVELOPMENT ? 'text-success' : 'text-danger'],
            ['name' => 'IS_PRODUCTION', 'value' => defined('IS_PRODUCTION') && IS_PRODUCTION ? 'true' : 'false', 'value_class' => defined('IS_PRODUCTION') && IS_PRODUCTION ? 'text-success' : 'text-danger'],
            ['name' => 'IS_CONFIGURED', 'value' => defined('IS_CONFIGURED') && IS_CONFIGURED ? 'true' : 'false', 'value_class' => defined('IS_CONFIGURED') && IS_CONFIGURED ? 'text-success' : 'text-danger'],
            ['name' => 'PHP', 'value' => PHP_VERSION, 'value_class' => ''],
            ['name' => 'PD', 'value' => (string) (PD ?? 'undefined'), 'value_class' => ''],
        ],
        'flash_rows' => [
            ['label' => __('devtools.system_info.pending_messages'), 'value' => (string) $flash->count(), 'value_class' => ''],
            ['label' => __('devtools.system_info.has_success'), 'value' => $flash->has('success') ? $yes : $no, 'value_class' => $flash->has('success') ? 'text-success' : ''],
            ['label' => __('devtools.system_info.has_error'), 'value' => $flash->has('error') ? $yes : $no, 'value_class' => $flash->has('error') ? 'text-danger' : ''],
            ['label' => __('devtools.system_info.has_warning'), 'value' => $flash->has('warning') ? $yes : $no, 'value_class' => $flash->has('warning') ? 'text-warning' : ''],
            ['label' => __('devtools.system_info.has_info'), 'value' => $flash->has('info') ? $yes : $no, 'value_class' => $flash->has('info') ? 'text-info' : ''],
            ['label' => __('devtools.system_info.has_persistent'), 'value' => $flash->hasPersistent() ? $yes : $no, 'value_class' => $flash->hasPersistent() ? 'text-warning' : ''],
        ],
    ];
};
