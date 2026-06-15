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
use Catalyst\Helpers\Config\ConfigManager;
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
    private const DEFAULT_ALLOWED_EXTENSIONS = ['csv', 'gif', 'jpeg', 'jpg', 'json', 'pdf', 'png', 'txt', 'webp'];
    private const DEFAULT_BLOCKED_EXTENSIONS = ['php', 'phtml', 'phar', 'cgi', 'pl', 'htm', 'html', 'svg'];
    private const DEFAULT_BLOCKED_MIME_TYPES = ['application/x-php', 'text/html', 'image/svg+xml'];

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
        $allowedExtensions = implode(',', $this->allowedUploadExtensions());
        $rules = [
            'name' => 'required|max:150',
            'disk' => 'required|in:local,ftp',
            'asset_file' => (int) ($this->route('id') ?? 0) > 0
                ? 'file|mimes:' . $allowedExtensions . '|max_size:10240'
                : 'required|file|mimes:' . $allowedExtensions . '|max_size:10240',
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

        $file = $data['asset_file'] ?? null;
        if ($file instanceof \Catalyst\Framework\Http\UploadedFile) {
            $extension = strtolower($file->getExtension());
            $mimeType = strtolower($file->getMimeType());

            if (in_array($extension, $this->blockedUploadExtensions(), true)) {
                $errors['asset_file'][] = __('media.library.validation.blocked_extension', ['extension' => $extension]);
            }

            if (in_array($mimeType, $this->blockedUploadMimeTypes(), true)) {
                $errors['asset_file'][] = __('media.library.validation.blocked_mime_type', ['mime' => $mimeType]);
            }
        }

        return $errors;
    }

    /**
     * Returns media upload extensions accepted by the form rule.
     *
     * SVG is disabled by default because browser-inline SVG can carry active
     * content. Existing projects can opt in with security.security.uploads.allow_svg=true.
     *
     * Responsibility: Builds the extension allowlist enforced by the media upload form rule.
     * @return string[]
     */
    private function allowedUploadExtensions(): array
    {
        $extensions = self::DEFAULT_ALLOWED_EXTENSIONS;

        if ($this->allowSvgUploads()) {
            $extensions[] = 'svg';
        }

        sort($extensions);
        return array_values(array_unique($extensions));
    }

    /**
     * Returns denied extensions for public media uploads.
     *
     * Responsibility: Resolves the configured extension denylist while honoring explicit SVG opt-in.
     * @return string[]
     */
    private function blockedUploadExtensions(): array
    {
        $configured = $this->uploadSecurityConfig()['blocked_extensions'] ?? self::DEFAULT_BLOCKED_EXTENSIONS;
        if (!is_array($configured)) {
            $configured = self::DEFAULT_BLOCKED_EXTENSIONS;
        }

        $extensions = array_values(array_unique(array_filter(array_map(
            static fn (mixed $value): string => strtolower(ltrim(trim((string) $value), '.')),
            $configured
        ))));

        if ($this->allowSvgUploads()) {
            $extensions = array_values(array_diff($extensions, ['svg']));
        }

        return $extensions;
    }

    /**
     * Returns denied MIME types for public media uploads.
     *
     * Responsibility: Resolves the configured MIME denylist while honoring explicit SVG opt-in.
     * @return string[]
     */
    private function blockedUploadMimeTypes(): array
    {
        $configured = $this->uploadSecurityConfig()['blocked_mime_types'] ?? self::DEFAULT_BLOCKED_MIME_TYPES;
        if (!is_array($configured)) {
            $configured = self::DEFAULT_BLOCKED_MIME_TYPES;
        }

        $mimeTypes = array_values(array_unique(array_filter(array_map(
            static fn (mixed $value): string => strtolower(trim((string) $value)),
            $configured
        ))));

        if ($this->allowSvgUploads()) {
            $mimeTypes = array_values(array_diff($mimeTypes, ['image/svg+xml']));
        }

        return $mimeTypes;
    }

    /**
     * Determines whether SVG uploads are explicitly allowed.
     *
     * Responsibility: Requires an explicit security setting before allowing active SVG content.
     */
    private function allowSvgUploads(): bool
    {
        return $this->boolean($this->uploadSecurityConfig()['allow_svg'] ?? false);
    }

    /**
     * Reads upload security settings with fail-closed defaults.
     *
     * Responsibility: Loads media upload security configuration without weakening defaults on config failures.
     * @return array<string, mixed>
     */
    private function uploadSecurityConfig(): array
    {
        try {
            $config = ConfigManager::getInstance()->get('security.security.uploads', []);
            return is_array($config) ? $config : [];
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Coerces JSON/env-style booleans without trusting arbitrary strings.
     *
     * Responsibility: Normalizes upload security toggles into strict booleans.
     */
    private function boolean(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_string($value)) {
            return in_array(strtolower(trim($value)), ['1', 'true', 'yes', 'on'], true);
        }

        return (bool) $value;
    }
}
