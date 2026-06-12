<?php

declare(strict_types=1);

namespace Catalyst\Repository\Configuration\Requests;

use Catalyst\Helpers\Exceptions\ValidationException;
use Catalyst\Helpers\Validation\Validator;

/**
 * Validates and normalizes the CORS setup policy.
 */
final class CorsConfigRequest extends AbstractSettingsRequest
{
    private ?array $resolvedData = null;

    public function rules(): array
    {
        return [
            'cors_max_age' => 'required|integer|min_value:0|max_value:86400',
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

        foreach ($data['allowed_origins'] as $origin) {
            if ($origin !== '*' && !$this->validOrigin($origin)) {
                $errors['cors_allowed_origins'][] = 'Each CORS origin must be * or an absolute HTTP(S) origin.';
            }
        }

        $allowedMethods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS', 'HEAD'];
        foreach ($data['allowed_methods'] as $method) {
            if (!in_array($method, $allowedMethods, true)) {
                $errors['cors_allowed_methods'][] = 'CORS methods contain an unsupported HTTP method.';
            }
        }

        foreach (array_merge($data['allowed_headers'], $data['exposed_headers']) as $header) {
            if (preg_match("/^[!#$%&'*+.^_`|~0-9A-Za-z-]+$/", $header) !== 1) {
                $errors['cors_allowed_headers'][] = 'CORS headers must use valid HTTP field names.';
            }
        }

        if ($data['allow_credentials'] && in_array('*', $data['allowed_origins'], true)) {
            $errors['cors_allow_credentials'][] = 'Credentialed CORS cannot use a wildcard origin.';
        }

        if ($errors !== []) {
            throw ValidationException::withErrors($errors, $this->validationMessage(), $this->safeOldInput($data));
        }

        $this->resolvedData = $data;
    }

    protected function validationData(): array
    {
        return [
            'enabled' => $this->booleanFlag('cors_enabled'),
            'allowed_origins' => $this->csv('cors_allowed_origins', 'allowed_origins', ['*']),
            'allowed_methods' => array_map('strtoupper', $this->csv(
                'cors_allowed_methods',
                'allowed_methods',
                ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS']
            )),
            'allowed_headers' => $this->csv(
                'cors_allowed_headers',
                'allowed_headers',
                ['Content-Type', 'Authorization', 'X-Requested-With', 'X-CSRF-TOKEN']
            ),
            'exposed_headers' => $this->csv('cors_exposed_headers', 'exposed_headers', []),
            'allow_credentials' => $this->booleanFlag('cors_allow_credentials')
                || $this->booleanFlag('allow_credentials'),
            'cors_max_age' => $this->stringInput('cors_max_age', $this->stringInput('max_age', '86400')),
        ];
    }

    /**
     * @return string[]
     */
    private function csv(string $primary, string $fallback, array $default): array
    {
        $raw = $this->stringInput($primary, $this->stringInput($fallback));
        if ($raw === '') {
            return $default;
        }

        return array_values(array_unique(array_filter(array_map('trim', explode(',', $raw)))));
    }

    private function validOrigin(string $origin): bool
    {
        $parts = parse_url($origin);

        return is_array($parts)
            && in_array($parts['scheme'] ?? '', ['http', 'https'], true)
            && isset($parts['host'])
            && !isset($parts['user'], $parts['pass'], $parts['query'], $parts['fragment'])
            && (($parts['path'] ?? '') === '' || ($parts['path'] ?? '') === '/');
    }
}
