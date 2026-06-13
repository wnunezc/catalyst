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

namespace Catalyst\Repository\Operations\ApiManagement\Requests;

use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Framework\Auth\UserProvider;
use Catalyst\Framework\Authorization\PermissionRegistry;
use Catalyst\Framework\Http\FormRequest;
use Catalyst\Helpers\Exceptions\ForbiddenException;
use Catalyst\Helpers\Exceptions\ValidationException;
use Catalyst\Helpers\Validation\Validator;

/**
 * Form request for creating API bearer tokens from the admin surface.
 *
 * @package Catalyst\Repository\Operations\ApiManagement\Requests
 * Responsibility: Authorizes token creation, limits accepted input, validates token fields,
 * and resolves sanitized payload data for the controller.
 */
final class ApiTokenRequest extends FormRequest
{
    /**
     * @var array<string, mixed>|null
     */
    private ?array $resolvedData = null;

    /**
     * Confirms the current user has permission to create API Management tokens.
     *
     * Responsibility: Confirms the current user has permission to create API Management tokens.
     */
    public function authorize(): bool
    {
        return PermissionRegistry::getInstance()->userHasResourceAbility(
            AuthManager::getInstance()->user(),
            'operations-api-management',
            'create'
        );
    }

    /**
     * Declares the request fields accepted for API token creation.
     *
     * Responsibility: Declares the request fields accepted for API token creation.
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
     * Defines base validation constraints for token name, owner, abilities, and expiration.
     *
     * Responsibility: Defines base validation constraints for token name, owner, abilities, and expiration.
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
     * Maps API token fields to translated validation labels.
     *
     * Responsibility: Maps API token fields to translated validation labels.
     * @return array<string, string>
     */
    public function labels(): array
    {
        return [
            'name' => __('apimanagement.form.labels.name'),
            'user_id' => __('apimanagement.form.labels.user'),
            'abilities_csv' => __('apimanagement.form.labels.abilities'),
            'expires_at' => __('apimanagement.form.labels.expires_at'),
        ];
    }

    /**
     * Returns the validated token payload, resolving validation on first access.
     *
     * Responsibility: Returns the validated token payload, resolving validation on first access.
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
     * Authorizes, prepares, validates, and stores the resolved API token payload.
     *
     * Responsibility: Authorizes, prepares, validates, and stores the resolved API token payload.
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
     * Adds API-token-specific validation for active users, abilities, and parseable expiration dates.
     *
     * Responsibility: Adds API-token-specific validation for active users, abilities, and parseable expiration dates.
     * @param array<string, mixed> $data
     * @return array<string, string[]>
     */
    private function customErrors(array $data): array
    {
        $errors = [];
        $userId = (int) ($data['user_id'] ?? 0);

        if ($userId <= 0 || UserProvider::getInstance()->findById($userId) === null) {
            $errors['user_id'][] = __('apimanagement.validation.valid_active_user');
        }

        $abilities = array_values(array_filter(array_map('trim', explode(',', (string) ($data['abilities_csv'] ?? '')))));
        if ($abilities === []) {
            $errors['abilities_csv'][] = __('apimanagement.validation.abilities_required');
        }

        $expiresAt = trim((string) ($data['expires_at'] ?? ''));
        if ($expiresAt !== '' && strtotime($expiresAt) === false) {
            $errors['expires_at'][] = __('apimanagement.validation.expires_at_invalid');
        }

        return $errors;
    }
}
