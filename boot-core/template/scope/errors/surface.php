<?php

declare(strict_types=1);

return static function (array $scope): array {
    $ticket = trim((string) ($scope['error_ticket'] ?? ''));

    return [
        'error_status' => (string) ($scope['error_status'] ?? '500'),
        'error_title' => (string) ($scope['error_title'] ?? __('ui.errors.500_title')),
        'error_message' => (string) ($scope['error_message'] ?? __('ui.errors.500_message')),
        'error_ticket' => $ticket,
        'has_error_ticket' => $ticket !== '',
        'request_path' => (string) ($scope['request_path'] ?? '/'),
        'show_login_action' => !empty($scope['show_login_action']),
    ];
};
