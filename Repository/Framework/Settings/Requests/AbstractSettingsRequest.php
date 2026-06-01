<?php

declare(strict_types=1);

namespace Catalyst\Repository\Settings\Requests;

use Catalyst\Framework\Http\FormRequest;

abstract class AbstractSettingsRequest extends FormRequest
{
    protected function stringInput(string $key, string $default = ''): string
    {
        return trim((string) $this->input($key, $default));
    }

    protected function lowerStringInput(string $key, string $default = ''): string
    {
        return strtolower($this->stringInput($key, $default));
    }

    protected function booleanFlag(string $key, bool $default = false): bool
    {
        $value = $this->input($key, $default ? '1' : '0');

        return in_array((string) $value, ['1', 'true', 'on', 'yes'], true);
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function safeOldInput(array $data): array
    {
        $data = parent::safeOldInput($data);

        foreach ([
            'app_key',
            'db_password',
            'mail_password',
            'ftp_password',
        ] as $sensitiveField) {
            unset($data[$sensitiveField]);
        }

        return $data;
    }
}
