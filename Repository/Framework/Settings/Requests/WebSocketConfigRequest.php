<?php

declare(strict_types=1);

namespace Catalyst\Repository\Settings\Requests;

final class WebSocketConfigRequest extends AbstractSettingsRequest
{
    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'ws_port' => 'required|integer|min_value:1024|max_value:65535',
            'ws_host' => 'required|max:64',
            'ws_internal_port' => 'required|integer|min_value:1024|max_value:65535',
            'ws_publisher_url' => 'required|max:255',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function validationData(): array
    {
        return [
            'ws_port' => (string) $this->input('ws_port', '8080'),
            'ws_host' => $this->stringInput('ws_host', '0.0.0.0'),
            'ws_internal_port' => (string) $this->input('ws_internal_port', '8181'),
            'ws_publisher_url' => $this->stringInput('ws_publisher_url'),
            'ws_enabled' => $this->booleanFlag('ws_enabled', true),
        ];
    }
}
