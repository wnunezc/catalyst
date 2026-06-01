<?php

declare(strict_types=1);

namespace Catalyst\Repository\Settings\Requests;

final class SecurityConfigRequest extends AbstractSettingsRequest
{
    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'bcrypt_rounds' => 'required|integer|min_value:10|max_value:16',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function validationData(): array
    {
        return [
            'bcrypt_rounds' => (string) $this->input('bcrypt_rounds', '12'),
            'mfa_enabled' => $this->booleanFlag('mfa_enabled'),
        ];
    }
}
