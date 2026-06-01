<?php

declare(strict_types=1);

namespace Catalyst\Repository\Catalogs\Requests;

use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Framework\Authorization\PermissionRegistry;
use Catalyst\Framework\Catalog\CatalogManager;
use Catalyst\Framework\Catalog\CatalogRepository;
use Catalyst\Framework\Http\FormRequest;
use Catalyst\Helpers\Exceptions\ForbiddenException;
use Catalyst\Helpers\Exceptions\ValidationException;
use Catalyst\Helpers\Validation\Validator;

final class CatalogDefinitionRequest extends FormRequest
{
    /**
     * @var array<string, mixed>|null
     */
    private ?array $resolvedData = null;

    public function authorize(): bool
    {
        return PermissionRegistry::getInstance()->userHasResourceAbility(
            AuthManager::getInstance()->user(),
            CatalogManager::RESOURCE_KEY,
            (int) ($this->route('id') ?? 0) > 0 ? 'update' : 'create'
        );
    }

    /**
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

    protected function sensitiveResourceKey(): ?string
    {
        return CatalogManager::RESOURCE_KEY;
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
