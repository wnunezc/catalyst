<?php

declare(strict_types=1);

namespace Catalyst\Repository\ApiPlatform\Requests;

use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Framework\Auth\UserProvider;
use Catalyst\Framework\Authorization\PermissionRegistry;
use Catalyst\Framework\Http\FormRequest;
use Catalyst\Helpers\Exceptions\ForbiddenException;
use Catalyst\Helpers\Exceptions\ValidationException;
use Catalyst\Helpers\Validation\Validator;

final class ApiTokenRequest extends FormRequest
{
    /**
     * @var array<string, mixed>|null
     */
    private ?array $resolvedData = null;

    public function authorize(): bool
    {
        return PermissionRegistry::getInstance()->userHasResourceAbility(
            AuthManager::getInstance()->user(),
            'api-platform',
            'create'
        );
    }

    /**
     * @return string[]
     */
    public function only(): array
    {
        return [
            'name',
            'user_id',
            'abilities_csv',
            'expires_at',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|max:150',
            'user_id' => 'required',
            'abilities_csv' => 'required|max:500',
            'expires_at' => 'max:30',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function labels(): array
    {
        return [
            'name' => __('apiplatform.form.labels.name'),
            'user_id' => __('apiplatform.form.labels.user'),
            'abilities_csv' => __('apiplatform.form.labels.abilities'),
            'expires_at' => __('apiplatform.form.labels.expires_at'),
        ];
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
            throw ForbiddenException::forbidden(__('messages.request_not_authorized'));
        }

        $this->prepareForValidation();
        $data = $this->validationData();
        $validator = new Validator($data, $this->rules(), $this->labels());
        $errors = $validator->fails() ? $validator->errors() : [];
        $errors = array_merge_recursive($errors, $this->customErrors($data));

        if ($errors !== []) {
            throw ValidationException::withErrors(
                $errors,
                $this->validationMessage(),
                $this->safeOldInput($data)
            );
        }

        $this->resolvedData = $data;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, string[]>
     */
    private function customErrors(array $data): array
    {
        $errors = [];
        $userId = (int) ($data['user_id'] ?? 0);

        if ($userId <= 0 || UserProvider::getInstance()->findById($userId) === null) {
            $errors['user_id'][] = __('apiplatform.validation.valid_active_user');
        }

        $abilities = array_values(array_filter(array_map('trim', explode(',', (string) ($data['abilities_csv'] ?? '')))));
        if ($abilities === []) {
            $errors['abilities_csv'][] = __('apiplatform.validation.abilities_required');
        }

        $expiresAt = trim((string) ($data['expires_at'] ?? ''));
        if ($expiresAt !== '' && strtotime($expiresAt) === false) {
            $errors['expires_at'][] = __('apiplatform.validation.expires_at_invalid');
        }

        return $errors;
    }
}
