<?php

declare(strict_types=1);

namespace Catalyst\Repository\Workspaces\Localization\Requests;

use Catalyst\Framework\Http\FormRequest;
use Catalyst\Framework\Localization\LocalizationManager;
use Catalyst\Helpers\Exceptions\ValidationException;
use Catalyst\Helpers\Validation\Validator;

final class LocalizationSettingsRequest extends FormRequest
{
    /** @var array<string, mixed>|null */
    private ?array $resolved = null;

    public function only(): array
    {
        return ['default_locale', 'locale_labels_json'];
    }

    public function rules(): array
    {
        return [
            'default_locale' => 'required|max:12',
            'locale_labels_json' => 'required|max:12000',
        ];
    }

    public function validated(): array
    {
        if ($this->resolved === null) {
            $data = $this->validationData();
            $validator = new Validator($data, $this->rules());
            $errors = $validator->fails() ? $validator->errors() : [];
            $default = strtolower(trim((string) ($data['default_locale'] ?? '')));
            $labels = json_decode((string) ($data['locale_labels_json'] ?? ''), true);
            $available = LocalizationManager::getInstance()->availableLocales();

            if (!in_array($default, $available, true)) {
                $errors['default_locale'][] = __('workspaces.localization.validation.locale_missing');
            }
            if (!is_array($labels)) {
                $errors['locale_labels_json'][] = __('workspaces.localization.validation.labels_json');
            } else {
                foreach ($available as $locale) {
                    if (!isset($labels[$locale]) || !is_string($labels[$locale]) || trim($labels[$locale]) === '') {
                        $errors['locale_labels_json'][] = __('workspaces.localization.validation.labels_complete');
                        break;
                    }
                }
            }

            if ($errors !== []) {
                throw ValidationException::withErrors($errors, __('workspaces.localization.validation.failed'), $this->safeOldInput($data));
            }

            $this->resolved = ['default_locale' => $default, 'locale_labels' => $labels];
        }

        return $this->resolved;
    }
}
