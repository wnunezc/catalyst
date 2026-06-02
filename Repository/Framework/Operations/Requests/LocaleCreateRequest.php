<?php

declare(strict_types=1);

namespace Catalyst\Repository\Operations\Requests;

use Catalyst\Framework\Http\Request;
use Catalyst\Repository\Operations\Requests\Concerns\NormalizesCheckboxValues;

final class LocaleCreateRequest
{
    use NormalizesCheckboxValues;

    public function __construct(private readonly Request $request)
    {
    }

    public function locale(): string
    {
        return trim((string) $this->request->input('locale_code', ''));
    }

    public function label(): string
    {
        return trim((string) $this->request->input('locale_label', ''));
    }

    public function dryRun(): bool
    {
        return $this->checkboxValue($this->request->input('dry_run'));
    }
}
