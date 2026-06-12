<?php

declare(strict_types=1);

namespace Catalyst\Repository\Configuration\Requests;

use Catalyst\Helpers\Exceptions\ValidationException;
use Catalyst\Helpers\Validation\Validator;

/**
 * Validates the initial administrator payload without replaying passwords.
 */
final class SetupAdminRequest extends AbstractSettingsRequest
{
    private ?array $resolvedData = null;

    public function rules(): array
    {
        return [
            'admin_name' => 'required|min:2|max:255',
            'admin_email' => 'required|email|max:255',
            'admin_password' => 'required|min:8|max:128',
            'admin_password_confirm' => 'required',
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

        if ($data['admin_password'] !== $data['admin_password_confirm']) {
            $errors['admin_password_confirm'][] = __('settings.completion.errors.password_mismatch');
        }

        if ($errors !== []) {
            throw ValidationException::withErrors($errors, $this->validationMessage(), $this->safeOldInput($data));
        }

        $this->resolvedData = $data;
    }

    protected function validationData(): array
    {
        return [
            'admin_name' => $this->stringInput('admin_name'),
            'admin_email' => $this->lowerStringInput('admin_email'),
            'admin_password' => (string) $this->input('admin_password', ''),
            'admin_password_confirm' => (string) $this->input('admin_password_confirm', ''),
        ];
    }

    protected function safeOldInput(array $data): array
    {
        unset($data['admin_password'], $data['admin_password_confirm']);

        return parent::safeOldInput($data);
    }
}
