<?php

declare(strict_types=1);

namespace Catalyst\Repository\Configuration\Requests;

use Catalyst\Helpers\Exceptions\ValidationException;
use Catalyst\Helpers\Validation\Validator;

/**
 * Validates DKIM identifiers before they reach key storage.
 */
final class DkimGenerateRequest extends AbstractSettingsRequest
{
    private ?array $resolvedData = null;

    public function rules(): array
    {
        return [
            'dkim_domain' => 'required|max:255',
            'dkim_selector' => 'required|max:63',
            'dkim_connection' => 'required|max:64',
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

        if (filter_var($data['dkim_domain'], FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) === false) {
            $errors['dkim_domain'][] = 'The DKIM domain must be a valid hostname.';
        }

        if (preg_match('/^[a-z0-9](?:[a-z0-9_-]*[a-z0-9])?$/', $data['dkim_selector']) !== 1) {
            $errors['dkim_selector'][] = 'The DKIM selector contains invalid characters.';
        }

        if (preg_match('/^[a-z0-9](?:[a-z0-9_-]*[a-z0-9])?$/', $data['dkim_connection']) !== 1) {
            $errors['dkim_connection'][] = 'The mail connection identifier contains invalid characters.';
        }

        if ($errors !== []) {
            throw ValidationException::withErrors($errors, $this->validationMessage(), $this->safeOldInput($data));
        }

        $this->resolvedData = $data;
    }

    protected function validationData(): array
    {
        return [
            'dkim_domain' => $this->lowerStringInput('dkim_domain'),
            'dkim_selector' => $this->lowerStringInput('dkim_selector'),
            'dkim_connection' => $this->lowerStringInput('dkim_connection', 'mail1'),
        ];
    }
}
