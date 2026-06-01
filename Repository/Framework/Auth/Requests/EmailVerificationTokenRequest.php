<?php

declare(strict_types=1);

namespace Catalyst\Repository\Auth\Requests;

use Catalyst\Framework\Http\FormRequest;
use Catalyst\Helpers\Exceptions\ForbiddenException;
use Catalyst\Helpers\Exceptions\ValidationException;
use Catalyst\Helpers\Validation\Validator;

final class EmailVerificationTokenRequest extends FormRequest
{
    /**
     * @var array<string, mixed>|null
     */
    private ?array $resolvedData = null;

    /**
     * @return string[]
     */
    public function only(): array
    {
        return ['token'];
    }

    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'token' => 'required|min:64|max:255',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function labels(): array
    {
        return [
            'token' => __('auth.verify.token_label'),
        ];
    }

    public function validationMessage(): string
    {
        return __('auth.verify.validation_failed');
    }

    /**
     * @return array<string, mixed>
     */
    public function validated(): array
    {
        if ($this->resolvedData === null) {
            $this->validateResolved();
        }

        return $this->resolvedData ?? [];
    }

    /**
     * @throws ValidationException
     * @throws ForbiddenException
     */
    public function validateResolved(): void
    {
        if (!$this->authorize()) {
            throw ForbiddenException::forbidden('This request is not authorized.');
        }

        $this->prepareForValidation();

        $data = $this->validationData();
        $validator = new Validator($data, $this->rules(), $this->labels());
        $errors = $validator->fails() ? $validator->errors() : [];
        $errors = array_merge_recursive($errors, $this->tokenErrors($data));

        if ($errors !== []) {
            throw ValidationException::withErrors(
                $errors,
                $this->validationMessage(),
                $this->safeOldInput($data)
            );
        }

        $this->resolvedData = [
            'token' => $this->normalizeToken((string) ($data['token'] ?? '')),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function validationData(): array
    {
        return [
            'token' => $this->normalizeToken((string) $this->input('token', '')),
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, string[]>
     */
    private function tokenErrors(array $data): array
    {
        $token = (string) ($data['token'] ?? '');

        if ($token === '') {
            return [];
        }

        if (!self::isWellFormedToken($token)) {
            return [
                'token' => [__('auth.verify.token_invalid_format')],
            ];
        }

        return [];
    }

    public static function isWellFormedToken(string $token): bool
    {
        return preg_match('/\A[a-fA-F0-9]{64}\z/', trim($token)) === 1;
    }

    private function normalizeToken(string $token): string
    {
        return trim($token);
    }
}
