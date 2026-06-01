<?php

declare(strict_types=1);

namespace Catalyst\Repository\Settings\Requests;

final class SessionConfigRequest extends AbstractSettingsRequest
{
    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'session_driver' => 'required|in:file,database',
            'session_connection' => 'required|max:32',
            'session_table' => 'required|max:64',
            'session_name' => 'required|max:64',
            'session_lifetime' => 'required|integer|min_value:60',
            'session_same_site' => 'required|in:Strict,Lax,None',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function validationData(): array
    {
        return [
            'session_driver' => $this->lowerStringInput('session_driver', 'file'),
            'session_connection' => $this->stringInput('session_connection', 'db1'),
            'session_table' => $this->stringInput('session_table', 'sessions'),
            'session_name' => $this->stringInput('session_name', 'catalyst-session'),
            'session_lifetime' => (string) $this->input('session_lifetime', '2592000'),
            'session_same_site' => $this->stringInput('session_same_site', 'Strict'),
            'session_domain' => $this->stringInput('session_domain'),
            'session_secure' => $this->booleanFlag('session_secure', true),
            'session_http_only' => $this->booleanFlag('session_http_only', true),
        ];
    }
}
