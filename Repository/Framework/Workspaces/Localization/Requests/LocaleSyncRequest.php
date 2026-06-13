<?php

declare(strict_types=1);

namespace Catalyst\Repository\Workspaces\Localization\Requests;

use Catalyst\Framework\Http\FormRequest;
use Catalyst\Framework\Localization\LocalizationManager;
use Catalyst\Helpers\Exceptions\ValidationException;
use Catalyst\Helpers\Validation\Validator;

final class LocaleSyncRequest extends FormRequest
{
    /** @var array<string, mixed>|null */
    private ?array $resolved = null;

    public function only(): array
    {
        return ['target_locale', 'dry_run_sync'];
    }

    public function rules(): array
    {
        return ['target_locale' => 'required|max:12'];
    }

    public function validated(): array
    {
        if ($this->resolved === null) {
            $data = $this->validationData();
            $validator = new Validator($data, $this->rules());
            $errors = $validator->fails() ? $validator->errors() : [];
            $locale = strtolower(trim((string) ($data['target_locale'] ?? '')));

            if ($locale === LocalizationManager::BASE_LOCALE) {
                $errors['target_locale'][] = __('workspaces.localization.validation.base_locale');
            } elseif (!in_array($locale, LocalizationManager::getInstance()->availableLocales(), true)) {
                $errors['target_locale'][] = __('workspaces.localization.validation.locale_missing');
            }

            if ($errors !== []) {
                throw ValidationException::withErrors($errors, __('workspaces.localization.validation.failed'), $this->safeOldInput($data));
            }

            $this->resolved = [
                'locale' => $locale,
                'dry_run' => in_array((string) ($data['dry_run_sync'] ?? ''), ['1', 'true', 'on', 'yes'], true),
            ];
        }

        return $this->resolved;
    }
}
