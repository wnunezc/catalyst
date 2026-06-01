<?php

declare(strict_types=1);

namespace Catalyst\Repository\Settings\Requests;

final class DevToolsConfigRequest extends AbstractSettingsRequest
{
    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * @return array<string, mixed>
     */
    protected function validationData(): array
    {
        return [
            'app_debug' => $this->booleanFlag('app_debug'),
            'display_logs' => $this->booleanFlag('display_logs'),
        ];
    }
}
