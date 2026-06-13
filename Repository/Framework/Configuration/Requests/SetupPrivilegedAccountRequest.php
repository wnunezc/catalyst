<?php

declare(strict_types=1);

namespace Catalyst\Repository\Configuration\Requests;

use Catalyst\Helpers\Exceptions\ValidationException;
use Catalyst\Helpers\Validation\Validator;

/**
 * Validates the initial privileged account payload without replaying passwords.
 */
final class SetupPrivilegedAccountRequest extends AbstractSettingsRequest
{
    private ?array $resolvedData = null;

    public function rules(): array
    {
        return [
            'account_name' => 'required|min:2|max:255',
            'account_email' => 'required|email|max:255',
            'account_password' => 'required|min:8|max:128',
            'account_password_confirm' => 'required',
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

        if ($data['account_password'] !== $data['account_password_confirm']) {
            $errors['account_password_confirm'][] = __('settings.completion.errors.password_mismatch');
        }

        if ($errors !== []) {
            throw ValidationException::withErrors($errors, $this->validationMessage(), $this->safeOldInput($data));
        }

        $this->resolvedData = $data;
    }

    protected function validationData(): array
    {
        return [
            'account_name' => $this->stringInput('account_name'),
            'account_email' => $this->lowerStringInput('account_email'),
            'account_password' => (string) $this->input('account_password', ''),
            'account_password_confirm' => (string) $this->input('account_password_confirm', ''),
        ];
    }

    protected function safeOldInput(array $data): array
    {
        unset($data['account_password'], $data['account_password_confirm']);

        return parent::safeOldInput($data);
    }
}
