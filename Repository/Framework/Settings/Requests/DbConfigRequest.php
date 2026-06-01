<?php

declare(strict_types=1);

namespace Catalyst\Repository\Settings\Requests;

final class DbConfigRequest extends AbstractSettingsRequest
{
    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'db_host' => 'required|max:255',
            'db_port' => 'required|integer|min_value:1|max_value:65535',
            'db_database' => 'required|max:64',
            'db_username' => 'required|max:64',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function validationData(): array
    {
        return [
            'db_host' => $this->stringInput('db_host'),
            'db_port' => (string) $this->input('db_port', '3306'),
            'db_database' => $this->stringInput('db_database'),
            'db_username' => $this->stringInput('db_username'),
            'db_password' => $this->stringInput('db_password'),
        ];
    }
}
