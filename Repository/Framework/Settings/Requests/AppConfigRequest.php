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

namespace Catalyst\Repository\Settings\Requests;

use Catalyst\Framework\Localization\LocalizationManager;
use Catalyst\Helpers\Config\AppEntryCatalog;
use Catalyst\Helpers\Config\ConfigManager;
use Catalyst\Helpers\Exceptions\ForbiddenException;
use Catalyst\Helpers\Exceptions\ValidationException;
use Catalyst\Helpers\Validation\Validator;

/**
 * Validates and resolves application settings from the setup surface.
 *
 * @package Catalyst\Repository\Settings\Requests
 * Responsibility: Validates application metadata, entry points and locale choices before persistence.
 */
final class AppConfigRequest extends AbstractSettingsRequest
{
    /**
     * @var array<string, mixed>|null
     */
    private ?array $resolvedData = null;

    /**
     * Returns validation rules for application settings.
     *
     * Responsibility: Returns validation rules for application settings.
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'app_name' => 'required|max:100',
            'app_url' => 'required|max:255',
            'app_env' => 'required|in:development,staging,testing,production',
            'app_lang' => 'required|max:32',
            'app_timezone' => 'required|max:64',
            'app_entry' => 'required|max:64',
            'app_entry_secondary' => 'max:64',
            'app_key' => 'required|min:32',
        ];
    }

    /**
     * Returns the resolved application payload after validation.
     *
     * Responsibility: Returns the resolved application payload after validation.
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
     * Authorizes, validates and resolves the application payload.
     *
     * Responsibility: Authorizes, validates and resolves the application payload.
     * @throws ValidationException
     * @throws ForbiddenException
     */
    public function validateResolved(): void
    {
        if (!$this->authorize()) {
            throw ForbiddenException::forbidden('This request is not authorized.');
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

        $this->resolvedData = $this->resolvePayload($data);
    }

    /**
     * Builds normalized application input for validation.
     *
     * Responsibility: Builds normalized application input for validation.
     * @return array<string, mixed>
     */
    protected function validationData(): array
    {
        $config = ConfigManager::getInstance();

        return [
            'app_name' => $this->stringInput('app_name'),
            'app_url' => $this->stringInput('app_url'),
            'app_env' => $config->getEnvironment(),
            'app_lang' => $this->stringInput('app_lang', 'en'),
            'app_timezone' => $this->stringInput('app_timezone', 'UTC'),
            'app_entry' => $this->stringInput('app_entry', 'Home'),
            'app_entry_secondary' => $this->stringInput('app_entry_secondary', ''),
            'app_key' => $this->stringInput('app_key'),
            'app_debug' => $this->booleanFlag('app_debug'),
        ];
    }

    /**
     * Returns entry-point and locale errors not expressible by scalar rules.
     *
     * Responsibility: Returns entry-point and locale errors not expressible by scalar rules.
     * @param array<string, mixed> $data
     * @return array<string, string[]>
     */
    private function customErrors(array $data): array
    {
        $errors = [];
        $isDevelopment = (string) ($data['app_env'] ?? 'development') === 'development';
        $primaryEntries = AppEntryCatalog::primaryKeys($isDevelopment);
        $secondaryEntries = AppEntryCatalog::secondaryKeys($isDevelopment);
        $primary = (string) ($data['app_entry'] ?? '');
        $secondary = (string) ($data['app_entry_secondary'] ?? '');

        if (!in_array($primary, $primaryEntries, true)) {
            $errors['app_entry'][] = __('settings.validation.primary_entry_invalid');
        }

        if (AppEntryCatalog::requiresSecondary($primary)) {
            if ($secondary === '' || !in_array($secondary, $secondaryEntries, true)) {
                $errors['app_entry_secondary'][] = __('settings.validation.secondary_entry_required');
            }
        } elseif ($secondary !== '' && !in_array($secondary, $secondaryEntries, true)) {
            $errors['app_entry_secondary'][] = __('settings.validation.secondary_entry_invalid');
        }

        if (!in_array((string) ($data['app_lang'] ?? 'en'), LocalizationManager::getInstance()->availableLocales(), true)) {
            $errors['app_lang'][] = __('settings.validation.language_invalid');
        }

        return $errors;
    }

    /**
     * Clears an unused secondary entry point from the persisted payload.
     *
     * Responsibility: Clears an unused secondary entry point from the persisted payload.
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function resolvePayload(array $data): array
    {
        $primary = (string) ($data['app_entry'] ?? '');
        $secondary = '';

        if (AppEntryCatalog::requiresSecondary($primary)) {
            $secondary = (string) ($data['app_entry_secondary'] ?? '');
        }

        $data['app_entry_secondary'] = $secondary;

        return $data;
    }
}
