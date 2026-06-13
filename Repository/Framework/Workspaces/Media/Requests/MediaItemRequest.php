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
use Catalyst\Framework\Media\MediaManager;
use Catalyst\Framework\Metadata\MetadataManager;
use Catalyst\Framework\Storage\StorageManager;
use Catalyst\Helpers\Exceptions\ForbiddenException;
use Catalyst\Helpers\Exceptions\ValidationException;
use Catalyst\Helpers\Validation\Validator;

/**
 * Validates media asset create and update payloads.
 *
 * @package Catalyst\Repository\Workspaces\Media\Requests
 * Responsibility: Authorize media mutations and enforce upload, storage and dynamic metadata constraints.
 */
final class MediaItemRequest extends FormRequest
{
    /**
     * @var array<string, mixed>|null
     */
    private ?array $resolvedData = null;

    /**
     * Authorizes creation or update according to the routed media identifier.
     *
     * Responsibility: Authorizes creation or update according to the routed media identifier.
     */
    public function authorize(): bool
    {
        return PermissionRegistry::getInstance()->userHasResourceAbility(
            AuthManager::getInstance()->user(),
            'workspaces-media-library',
            (int) ($this->route('id') ?? 0) > 0 ? 'update' : 'create'
        );
    }

    /**
     * Returns fixed and dynamic media asset fields accepted from input.
     *
     * Responsibility: Returns fixed and dynamic media asset fields accepted from input.
     * @return string[]
     */
    public function only(): array
    {
        $fields = ['name', 'disk', 'asset_file'];

        foreach (MetadataManager::getInstance()->definitionsFor(MediaManager::RESOURCE_KEY) as $definition) {
            $fieldKey = (string) ($definition['field_key'] ?? '');
            if ($fieldKey === '') {
                continue;
            }

            $fields[] = MetadataManager::inputKey($fieldKey);
        }

        return $fields;
    }

    /**
     * Declares upload, storage and dynamic metadata validation rules.
     *
     * Responsibility: Declares upload, storage and dynamic metadata validation rules.
     * @return array<string, string>
     */
    public function rules(): array
    {
        $rules = [
            'name' => 'required|max:150',
            'disk' => 'required|in:local,ftp',
            'asset_file' => (int) ($this->route('id') ?? 0) > 0
                ? 'file|mimes:csv,gif,jpeg,jpg,json,pdf,png,svg,txt,webp|max_size:10240'
                : 'required|file|mimes:csv,gif,jpeg,jpg,json,pdf,png,svg,txt,webp|max_size:10240',
        ];

        return array_merge($rules, MetadataManager::getInstance()->validationRules(MediaManager::RESOURCE_KEY));
    }

    /**
     * Returns translated labels for fixed and dynamic media validation errors.
     *
     * Responsibility: Returns translated labels for fixed and dynamic media validation errors.
     * @return array<string, string>
     */
    public function labels(): array
    {
        return array_merge([
            'name' => __('media.library.form.labels.name'),
            'disk' => __('media.library.form.labels.disk'),
            'asset_file' => __('media.library.form.labels.asset_file_create'),
        ], MetadataManager::getInstance()->validationLabels(MediaManager::RESOURCE_KEY));
    }

    /**
     * Returns the validated media asset payload, resolving it lazily.
     *
     * Responsibility: Returns the validated media asset payload, resolving it lazily.
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
     * Authorizes and validates the complete media asset payload.
     *
     * Responsibility: Authorizes and validates the complete media asset payload.
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
     * Validates dynamic metadata and remote storage readiness beyond the base rules.
     *
     * Responsibility: Validates dynamic metadata and remote storage readiness beyond the base rules.
     * @param array<string, mixed> $data
     * @return array<string, string[]>
     */
    private function customErrors(array $data): array
    {
        $errors = MetadataManager::getInstance()->validateFieldPayload(MediaManager::RESOURCE_KEY, $data);
        $disk = trim((string) ($data['disk'] ?? 'local'));
        $storageSummary = StorageManager::getInstance()->summary();

        if ($disk === 'ftp' && !(bool) ($storageSummary['remote_ready'] ?? false)) {
            $errors['disk'][] = __('media.library.validation.remote_storage_not_ready');
        }

        return $errors;
    }
}
