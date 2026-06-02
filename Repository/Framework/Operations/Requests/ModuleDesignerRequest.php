<?php

declare(strict_types=1);

namespace Catalyst\Repository\Operations\Requests;

use Catalyst\Framework\Http\Request;

final class ModuleDesignerRequest
{
    public function __construct(private readonly Request $request)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function formState(): array
    {
        return [
            'space' => (string) $this->request->input('space', 'App'),
            'module' => (string) $this->request->input('module', ''),
            'description' => (string) $this->request->input('description', ''),
            'surface' => (string) $this->request->input('surface', 'public'),
            'permission_slug' => (string) $this->request->input('permission_slug', ''),
            'settings' => (string) $this->request->input('settings', ''),
            'feature_flags' => (string) $this->request->input('feature_flags', ''),
        ];
    }
}
