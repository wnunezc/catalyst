<?php

declare(strict_types=1);

namespace Catalyst\Repository\Documents\Requests;

use Catalyst\Framework\Http\FormRequest;

final class DocumentTemplateIndexRequest extends FormRequest
{
    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * @return array{page:int,per_page:int,search:string,format:string,state:string}
     */
    public function criteria(): array
    {
        return [
            'page' => max(1, (int) $this->input('page', 1)),
            'per_page' => min(100, max(1, (int) $this->input('per_page', 20))),
            'search' => trim((string) $this->input('search', '')),
            'format' => trim((string) $this->input('format', '')),
            'state' => trim((string) $this->input('state', '')),
        ];
    }
}
