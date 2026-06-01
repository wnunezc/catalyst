<?php

declare(strict_types=1);

namespace Catalyst\Repository\Media\Requests;

use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Framework\Authorization\PermissionRegistry;
use Catalyst\Framework\Http\FormRequest;
use Catalyst\Framework\Media\MediaManager;
use Catalyst\Framework\Metadata\MetadataManager;
use Catalyst\Framework\Storage\StorageManager;
use Catalyst\Helpers\Exceptions\ForbiddenException;
use Catalyst\Helpers\Exceptions\ValidationException;
use Catalyst\Helpers\Validation\Validator;

final class MediaItemRequest extends FormRequest
{
    /**
     * @var array<string, mixed>|null
     */
    private ?array $resolvedData = null;

    public function authorize(): bool
    {
        return PermissionRegistry::getInstance()->userHasResourceAbility(
            AuthManager::getInstance()->user(),
            MediaManager::RESOURCE_KEY,
            (int) ($this->route('id') ?? 0) > 0 ? 'update' : 'create'
        );
    }

    /**
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
        $errors = MetadataManager::getInstance()->validateFieldPayload(MediaManager::RESOURCE_KEY, $data);
        $disk = trim((string) ($data['disk'] ?? 'local'));
        $storageSummary = StorageManager::getInstance()->summary();

        if ($disk === 'ftp' && !(bool) ($storageSummary['remote_ready'] ?? false)) {
            $errors['disk'][] = __('media.library.validation.remote_storage_not_ready');
        }

        return $errors;
    }
}
