<?php

declare(strict_types=1);

/**
 * Catalyst PHP Framework
 *
 * A modern PHP 8.4 framework for building
 * robust and scalable web applications.
 *
 * PHP Version 8.4 (Required).
 *
 * @package    Catalyst
 *
 * @author     Walter Nuñez (arcanisgk/original founder)
 * @email      <wnunez@lh-2.net>
 * @email      <icarosnet@gmail.com>
 * @copyright  2024-2026 Walter Francisco Nuñez Cruz and Icaros Net
 * @license    Proprietary - https://catalyst.lh-2.net/license
 *
 * @version    GIT: See repository tags
 *
 * @category   Framework
 * @filesource
 *
 * @link       https://catalyst.lh-2.net Project homepage
 * @see        https://catalyst.lh-2.net/docs Documentation
 *
 */

namespace Catalyst\Repository\ApiPlatform\Requests;

use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Framework\Auth\UserProvider;
use Catalyst\Framework\Authorization\PermissionRegistry;
use Catalyst\Framework\Http\FormRequest;
use Catalyst\Helpers\Exceptions\ForbiddenException;
use Catalyst\Helpers\Exceptions\ValidationException;
use Catalyst\Helpers\Validation\Validator;

/**
 * Defines the Api Token Request class contract.
 *
 * @package Catalyst\Repository\ApiPlatform\Requests
 * Responsibility: Coordinates the api token request behavior within its module boundary.
 */
final class ApiTokenRequest extends FormRequest
{
    /**
     * @var array<string, mixed>|null
     */
    private ?array $resolvedData = null;

    /**
     * Handles the authorize workflow.
     */
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
