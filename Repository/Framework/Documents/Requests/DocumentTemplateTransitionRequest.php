<?php

declare(strict_types=1);

namespace Catalyst\Repository\Documents\Requests;

use Catalyst\Framework\Http\FormRequest;

final class DocumentTemplateTransitionRequest extends FormRequest
{
    /**
     * @return string[]
     */
    public function only(): array
    {
        return ['transition', 'notes'];
    }

    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'transition' => 'required',
        ];
    }

    public function validationMessage(): string
    {
        return __('documents.messages.select_transition');
    }

    public function hasTransition(): bool
    {
        return $this->transition() !== '';
    }

    public function transition(): string
    {
        return trim((string) $this->input('transition', ''));
    }

    public function notes(): ?string
    {
        return trim((string) $this->input('notes', '')) ?: null;
    }
}
