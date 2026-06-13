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

namespace Catalyst\Repository\Workspaces\Media\Requests;

use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Framework\Authorization\PermissionRegistry;
use Catalyst\Framework\Http\FormRequest;
use Catalyst\Framework\Metadata\MetadataManager;
use Catalyst\Helpers\Exceptions\ForbiddenException;
use Catalyst\Helpers\Exceptions\ValidationException;
use Catalyst\Helpers\Validation\Validator;

/**
 * Validates dynamic metadata field definition payloads.
 *
 * @package Catalyst\Repository\Workspaces\Media\Requests
 * Responsibility: Authorize metadata definition mutations and enforce resource, type and field configuration rules.
 */
final class MetadataFieldDefinitionRequest extends FormRequest
{
    /**
     * @var array<string, mixed>|null
     */
    private ?array $resolvedData = null;

    /**
     * Authorizes creation or update according to the routed metadata field identifier.
     *
     * Responsibility: Authorizes creation or update according to the routed metadata field identifier.
     */
    public function authorize(): bool
    {
        return PermissionRegistry::getInstance()->userHasResourceAbility(
            AuthManager::getInstance()->user(),
            'workspaces-media-fields',
            (int) ($this->route('id') ?? 0) > 0 ? 'update' : 'create'
        );
    }

    /**
     * Returns metadata definition fields accepted from input.
     *
     * Responsibility: Returns metadata definition fields accepted from input.
     * @return string[]
     */
    public function only(): array
    {
        return [
            'resource_key',
            'label',
            'field_key',
            'type',
            'section_key',
            'help_text',
            'placeholder',
            'default_value',
            'is_required',
            'is_filterable',
            'is_listed',
            'catalog_key',
            'sort_order',
            'max_length',
            'min_value',
            'max_value',
            'rules_extra',
            'select_options',
        ];
    }

    /**
     * Declares resource-aware validation rules for metadata definitions.
     *
     * Responsibility: Declares resource-aware validation rules for metadata definitions.
     * @return array<string, string>
     */
    public function rules(): array
    {
        $types = implode(',', array_keys(MetadataManager::getInstance()->supportedTypes()));
        $resources = implode(',', array_keys(\Catalyst\Framework\Metadata\MetadataResourceRegistry::getInstance()->all()));

        return [
            'resource_key' => 'required|in:' . $resources,
            'label' => 'required|max:120',
            'field_key' => 'required|max:100|regex:/^[a-z0-9_]+(?:-[a-z0-9_]+)*$/',
            'type' => 'required|in:' . $types,
            'section_key' => 'max:100|regex:/^[a-z0-9_\\-]*$/',
            'help_text' => 'max:255',
            'placeholder' => 'max:255',
            'default_value' => 'max:255',
            'is_required' => 'boolean',
            'is_filterable' => 'boolean',
            'is_listed' => 'boolean',
            'catalog_key' => 'max:120',
            'sort_order' => 'integer',
            'max_length' => 'integer',
            'min_value' => 'numeric',
            'max_value' => 'numeric',
            'rules_extra' => 'max:255',
            'select_options' => 'max:4000',
        ];
    }

    /**
     * Returns translated labels for metadata definition validation errors.
     *
     * Responsibility: Returns translated labels for metadata definition validation errors.
     * @return array<string, string>
     */
    public function labels(): array
    {
        return [
            'resource_key' => __('media.fields.form.labels.resource'),
            'label' => __('media.fields.form.labels.field_label'),
            'field_key' => __('media.fields.form.labels.field_key'),
            'type' => __('media.fields.form.labels.type'),
            'section_key' => __('media.fields.form.labels.section_key'),
            'help_text' => __('media.fields.form.labels.help_text'),
            'placeholder' => __('media.fields.form.labels.placeholder'),
            'default_value' => __('media.fields.form.labels.default_value'),
            'is_required' => __('media.fields.form.labels.required'),
            'is_filterable' => __('media.fields.form.labels.filterable'),
            'is_listed' => __('media.fields.form.labels.listed'),
            'catalog_key' => __('media.fields.form.labels.catalog'),
            'sort_order' => __('media.fields.form.labels.sort_order'),
            'max_length' => __('media.fields.form.labels.max_length'),
            'min_value' => __('media.fields.form.labels.min_value'),
            'max_value' => __('media.fields.form.labels.max_value'),
            'rules_extra' => __('media.fields.form.labels.extra_rules'),
            'select_options' => __('media.fields.form.labels.select_options'),
        ];
    }

    /**
     * Returns the validated metadata definition payload, resolving it lazily.
     *
     * Responsibility: Returns the validated metadata definition payload, resolving it lazily.
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
     * Authorizes and validates the complete metadata definition payload.
     *
     * Responsibility: Authorizes and validates the complete metadata definition payload.
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
        $errors = array_merge_recursive(
            $errors,
            MetadataManager::getInstance()->validateDefinitionPayload($data, (int) ($this->route('id') ?? 0) ?: null)
        );

        if ($errors !== []) {
            throw ValidationException::withErrors(
                $errors,
                $this->validationMessage(),
                $this->safeOldInput($data)
            );
        }

        $this->resolvedData = $data;
    }
}
