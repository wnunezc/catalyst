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

namespace Catalyst\Repository\Workspaces\Catalogs\Requests;

use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Framework\Authorization\PermissionRegistry;
use Catalyst\Framework\Catalog\CatalogManager;
use Catalyst\Framework\Catalog\CatalogRepository;
use Catalyst\Framework\Http\FormRequest;
use Catalyst\Helpers\Exceptions\ForbiddenException;
use Catalyst\Helpers\Exceptions\ValidationException;
use Catalyst\Helpers\Validation\Validator;

/**
 * Validates catalog definition create and update payloads.
 *
 * @package Catalyst\Repository\Workspaces\Catalogs\Requests
 * Responsibility: Authorize catalog mutations and enforce accepted fields, validation rules and key uniqueness.
 */
final class CatalogDefinitionRequest extends FormRequest
{
    /**
     * @var array<string, mixed>|null
     */
    private ?array $resolvedData = null;

    /**
     * Authorizes creation or update according to the routed catalog identifier.
     *
     * Responsibility: Authorizes creation or update according to the routed catalog identifier.
     */
    public function authorize(): bool
    {
        return PermissionRegistry::getInstance()->userHasResourceAbility(
            AuthManager::getInstance()->user(),
            'workspaces-catalogs',
            (int) ($this->route('id') ?? 0) > 0 ? 'update' : 'create'
        );
    }

    /**
     * Returns the catalog definition fields accepted from input.
     *
     * Responsibility: Returns the catalog definition fields accepted from input.
     * @return string[]
     */
    public function only(): array
    {
        return [
            'catalog_key',
            'label',
            'description',
        ];
    }

    /**
     * Declares validation rules for catalog definition input.
     *
     * Responsibility: Declares validation rules for catalog definition input.
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'catalog_key' => 'required|max:120|regex:/^[a-z0-9_]+(?:-[a-z0-9_]+)*$/',
            'label' => 'required|max:150',
            'description' => 'max:4000',
        ];
    }

    /**
     * Returns translated labels for catalog definition validation errors.
     *
     * Responsibility: Returns translated labels for catalog definition validation errors.
     * @return array<string, string>
     */
    public function labels(): array
    {
        return [
            'catalog_key' => __('catalogs.form.catalog_key'),
            'label' => __('catalogs.form.catalog_label'),
            'description' => __('catalogs.common.description'),
        ];
    }

    /**
     * Identifies catalogs as the sensitivity policy resource.
     *
     * Responsibility: Identifies catalogs as the sensitivity policy resource.
     */
    protected function sensitiveResourceKey(): ?string
    {
        return CatalogManager::RESOURCE_KEY;
    }

    /**
     * Returns the validated catalog definition payload, resolving it lazily.
     *
     * Responsibility: Returns the validated catalog definition payload, resolving it lazily.
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
     * Authorizes and validates the complete catalog definition payload.
     *
     * Responsibility: Authorizes and validates the complete catalog definition payload.
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
     * Validates catalog key uniqueness beyond the base field rules.
     *
     * Responsibility: Validates catalog key uniqueness beyond the base field rules.
     * @param array<string, mixed> $data
     * @return array<string, string[]>
     */
    private function customErrors(array $data): array
    {
        $errors = [];
        $catalogKey = trim(strtolower((string) ($data['catalog_key'] ?? '')));

        if ($catalogKey === '') {
            return $errors;
        }

        if (CatalogRepository::getInstance()->existsCatalogKey($catalogKey, (int) ($this->route('id') ?? 0) ?: null)) {
            $errors['catalog_key'][] = __('catalogs.validation.catalog_key_unique');
        }

        return $errors;
    }
}
