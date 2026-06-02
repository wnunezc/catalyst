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

namespace Catalyst\Repository\Catalogs\Requests;

use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Framework\Authorization\PermissionRegistry;
use Catalyst\Framework\Catalog\CatalogManager;
use Catalyst\Framework\Catalog\CatalogRepository;
use Catalyst\Framework\Http\FormRequest;
use Catalyst\Framework\Temporal\EffectiveWindow;
use Catalyst\Helpers\Exceptions\ForbiddenException;
use Catalyst\Helpers\Exceptions\ValidationException;
use Catalyst\Helpers\Validation\Validator;

/**
 * Defines the Catalog Item Request class contract.
 *
 * @package Catalyst\Repository\Catalogs\Requests
 * Responsibility: Coordinates the catalog item request behavior within its module boundary.
 */
final class CatalogItemRequest extends FormRequest
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
            CatalogManager::RESOURCE_KEY,
            (int) ($this->route('itemId') ?? 0) > 0 ? 'update' : 'create'
        );
    }

    /**
     * @return string[]
     */
    public function only(): array
    {
        return [
            'item_key',
            'label',
            'description',
            'is_enabled',
            'valid_from',
            'valid_to',
            'sort_order',
            'metadata_json',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'item_key' => 'required|max:120|regex:/^[a-z0-9_]+(?:-[a-z0-9_]+)*$/',
            'label' => 'required|max:150',
            'description' => 'max:4000',
            'is_enabled' => 'boolean',
            'valid_from' => 'max:30',
            'valid_to' => 'max:30',
            'sort_order' => 'integer',
            'metadata_json' => 'max:20000',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function labels(): array
    {
        return [
            'item_key' => __('catalogs.form.item_key'),
            'label' => __('catalogs.form.item_label'),
            'description' => __('catalogs.common.description'),
            'is_enabled' => __('catalogs.form.enabled'),
            'valid_from' => __('catalogs.form.valid_from'),
            'valid_to' => __('catalogs.form.valid_to'),
            'sort_order' => __('catalogs.form.sort_order'),
            'metadata_json' => __('catalogs.form.metadata_json'),
        ];
    }

    /**
     * Handles the sensitive resource key workflow.
     */
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
        $catalogId = (int) ($this->route('id') ?? 0);
        $catalog = CatalogRepository::getInstance()->findDefinition($catalogId);

        if ($catalog === null) {
            $errors['item_key'][] = __('catalogs.validation.parent_catalog_missing');

            return $errors;
        }

        $itemKey = trim(strtolower((string) ($data['item_key'] ?? '')));
        if ($itemKey !== '' && CatalogRepository::getInstance()->existsItemKey($catalogId, $itemKey, (int) ($this->route('itemId') ?? 0) ?: null)) {
            $errors['item_key'][] = __('catalogs.validation.item_key_unique');
        }

        $validFrom = EffectiveWindow::getInstance()->normalize(isset($data['valid_from']) ? (string) $data['valid_from'] : null);
        $validTo = EffectiveWindow::getInstance()->normalize(isset($data['valid_to']) ? (string) $data['valid_to'] : null);

        if (($data['valid_from'] ?? '') !== '' && $validFrom === null) {
            $errors['valid_from'][] = __('catalogs.validation.valid_from_invalid');
        }

        if (($data['valid_to'] ?? '') !== '' && $validTo === null) {
            $errors['valid_to'][] = __('catalogs.validation.valid_to_invalid');
        }

        if ($validFrom !== null && $validTo !== null && strtotime($validTo) <= strtotime($validFrom)) {
            $errors['valid_to'][] = __('catalogs.validation.valid_to_before_start');
        }

        $metadataJson = trim((string) ($data['metadata_json'] ?? ''));
        if ($metadataJson !== '') {
            $decoded = json_decode($metadataJson, true);
            if (!is_array($decoded)) {
                $errors['metadata_json'][] = __('catalogs.validation.metadata_json_invalid');
            }
        }

        return $errors;
    }
}
