<?php

declare(strict_types=1);

namespace Catalyst\Repository\Workspaces\Localization\Requests;

use Catalyst\Framework\Http\FormRequest;
use Catalyst\Framework\Localization\LocalizationManager;
use Catalyst\Helpers\Exceptions\ValidationException;
use Catalyst\Helpers\Validation\Validator;

final class LocaleCreateRequest extends FormRequest
{
    /** @var array<string, mixed>|null */
    private ?array $resolved = null;

    public function only(): array
    {
        return ['locale_code', 'locale_label', 'dry_run'];
    }

    public function rules(): array
    {
        return [
            'locale_code' => 'required|max:12',
            'locale_label' => 'required|max:80',
        ];
    }

    public function validated(): array
    {
        if ($this->resolved === null) {
            $data = $this->validationData();
            $validator = new Validator($data, $this->rules());
            $errors = $validator->fails() ? $validator->errors() : [];
            $locale = strtolower(trim((string) ($data['locale_code'] ?? '')));

            if ($locale !== '' && preg_match('/^[a-z]{2,3}(?:-[a-z0-9]{2,8})?$/', $locale) !== 1) {
                $errors['locale_code'][] = __('workspaces.localization.validation.locale_code');
            } elseif ($locale === LocalizationManager::BASE_LOCALE) {
                $errors['locale_code'][] = __('workspaces.localization.validation.base_locale');
            } elseif (in_array($locale, LocalizationManager::getInstance()->availableLocales(), true)) {
                $errors['locale_code'][] = __('workspaces.localization.validation.locale_exists');
            }

            if ($errors !== []) {
                throw ValidationException::withErrors($errors, __('workspaces.localization.validation.failed'), $this->safeOldInput($data));
            }

            $this->resolved = [
                'locale' => $locale,
                'label' => trim((string) ($data['locale_label'] ?? '')),
                'dry_run' => in_array((string) ($data['dry_run'] ?? ''), ['1', 'true', 'on', 'yes'], true),
            ];
        }

        return $this->resolved;
    }
}
