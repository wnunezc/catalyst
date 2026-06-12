<?php

declare(strict_types=1);

namespace Catalyst\Repository\Configuration\Requests;

use Catalyst\Helpers\Exceptions\ValidationException;
use Catalyst\Helpers\Validation\Validator;

/**
 * Validates and normalizes FTP, FTPS and SFTP setup payloads.
 */
final class FtpConfigRequest extends AbstractSettingsRequest
{
    private ?array $resolvedData = null;

    public function rules(): array
    {
        return [
            'ftp_protocol' => 'required|in:ftp,ftps,sftp',
            'ftp_host' => 'required|max:255',
            'ftp_port' => 'required|integer|min_value:1|max_value:65535',
            'ftp_username' => 'required|max:255',
            'ftp_root' => 'required|max:255',
            'ftp_timeout' => 'required|integer|min_value:1|max_value:120',
        ];
    }

    public function validated(): array
    {
        if ($this->resolvedData === null) {
            $this->validateResolved();
        }

        return $this->resolvedData ?? [];
    }

    public function validateResolved(): void
    {
        $data = $this->validationData();
        $validator = new Validator($data, $this->rules(), $this->labels());
        $errors = $validator->fails() ? $validator->errors() : [];

        if (preg_match('/^[a-z0-9.-]+$/i', (string) $data['ftp_host']) !== 1) {
            $errors['ftp_host'][] = 'The transfer host must be a valid hostname or IP address.';
        }

        if ($errors !== []) {
            throw ValidationException::withErrors($errors, $this->validationMessage(), $this->safeOldInput($data));
        }

        $this->resolvedData = $data;
    }

    protected function validationData(): array
    {
        return [
            'ftp_protocol' => $this->lowerStringInput('ftp_protocol', 'ftp'),
            'ftp_host' => $this->stringInput('ftp_host'),
            'ftp_port' => $this->stringInput('ftp_port', '21'),
            'ftp_username' => $this->stringInput('ftp_username'),
            'ftp_root' => $this->stringInput('ftp_root', '/'),
            'ftp_timeout' => $this->stringInput('ftp_timeout', '10'),
            'ftp_password' => $this->stringInput('ftp_password'),
            'ftp_passive' => $this->booleanFlag('ftp_passive'),
        ];
    }

    /**
     * @param array<string, mixed> $existing
     * @return array<string, mixed>
     */
    public function resolved(array $existing = []): array
    {
        $data = $this->validated();
        $protocol = (string) $data['ftp_protocol'];
        $root = '/' . ltrim(str_replace('\\', '/', (string) $data['ftp_root']), '/');

        return [
            'ftp_protocol' => $protocol,
            'ftp_host' => (string) $data['ftp_host'],
            'ftp_port' => (int) $data['ftp_port'],
            'ftp_username' => (string) $data['ftp_username'],
            'ftp_password' => $data['ftp_password'] !== ''
                ? (string) $data['ftp_password']
                : (string) ($existing['ftp_password'] ?? ''),
            'ftp_root' => rtrim($root, '/') ?: '/',
            'ftp_timeout' => (int) $data['ftp_timeout'],
            'ftp_ssl' => $protocol === 'ftps',
            'ftp_passive' => (bool) $data['ftp_passive'],
        ];
    }
}
