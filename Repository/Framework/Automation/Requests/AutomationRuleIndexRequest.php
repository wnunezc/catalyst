<?php

declare(strict_types=1);

namespace Catalyst\Repository\Automation\Requests;

use Catalyst\Framework\Http\FormRequest;

final class AutomationRuleIndexRequest extends FormRequest
{
    /**
     * @return string[]
     */
    public function only(): array
    {
        return ['page', 'per_page', 'search', 'trigger_type', 'state', 'temporal_state'];
    }

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
    public function criteria(): array
    {
        $data = $this->validated();

        return [
            'page' => max(1, (int) ($data['page'] ?? 1)),
            'per_page' => min(100, max(1, (int) ($data['per_page'] ?? 20))),
            'search' => trim((string) ($data['search'] ?? '')),
            'trigger_type' => trim((string) ($data['trigger_type'] ?? '')),
            'state' => trim((string) ($data['state'] ?? '')),
            'temporal_state' => trim((string) ($data['temporal_state'] ?? '')),
        ];
    }
}
