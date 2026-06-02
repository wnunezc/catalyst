<?php

declare(strict_types=1);

namespace Catalyst\Repository\Operations\Requests;

use Catalyst\Framework\Http\Request;

final class LocalizationSettingsRequest
{
    public function __construct(private readonly Request $request)
    {
    }

    public function defaultLocale(): string
    {
        return strtolower(trim((string) $this->request->input('default_locale', '')));
    }

    public function labelsJson(): string
    {
        return trim((string) $this->request->input('locale_labels_json', '{}'));
    }
}
