<?php

declare(strict_types=1);

use Catalyst\Framework\View\TrustedHtml;

return static function (array $scope): array {
    $translate = static function (string $key, ?string $default = null): string {
        if (function_exists('__')) {
            return (string) __($key);
        }

        return $default ?? $key;
    };

    $nonceAttr = '';
    if (class_exists(\Catalyst\Helpers\Security\CspNonce::class)) {
        $nonce = \Catalyst\Helpers\Security\CspNonce::get();
        if ($nonce !== '') {
            $nonceAttr = ' nonce="' . htmlspecialchars($nonce, ENT_QUOTES, 'UTF-8') . '"';
        }
    }

    $errorArray = is_array($scope['errorArray'] ?? null) ? $scope['errorArray'] : [];

    return [
        'locale' => (string) ($_SESSION['app_locale'] ?? 'en'),
        'error_class' => (string) ($errorArray['class'] ?? ''),
        'control_title' => $translate('ui.errors.control_title', 'Error Control Software'),
        'description_label' => $translate('ui.errors.description', 'Description:'),
        'error_description' => (string) ($errorArray['description'] ?? ''),
        'file_label' => $translate('ui.errors.file', 'File:'),
        'error_file' => (string) ($errorArray['file'] ?? ''),
        'line_label' => $translate('ui.errors.line', 'Line:'),
        'error_line' => (int) ($errorArray['line'] ?? 0),
        'level_label' => $translate('ui.errors.level', 'Level:'),
        'error_level' => (string) ($errorArray['type'] ?? ''),
        'time_label' => $translate('ui.errors.time', 'Time:'),
        'error_time' => (string) ($errorArray['micro_time'] ?? ''),
        'trace_label' => $translate('ui.errors.trace', 'Backtrace Log:'),
        'trace_message' => (string) ($errorArray['trace_msg'] ?? ''),
        'related_code_label' => $translate('ui.errors.related_code', 'Related Code View'),
        'source_html' => TrustedHtml::fromString((string) ($scope['source'] ?? '')),
        'return_hint' => $translate('ui.errors.return_hint', 'Please try to Go Back'),
        'go_back_label' => $translate('ui.errors.go_back', 'Go Back'),
        'style_nonce_attr' => TrustedHtml::fromString($nonceAttr),
        'script_nonce_attr' => TrustedHtml::fromString($nonceAttr),
    ];
};
