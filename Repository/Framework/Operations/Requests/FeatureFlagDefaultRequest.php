<?php

declare(strict_types=1);

namespace Catalyst\Repository\Operations\Requests;

use Catalyst\Framework\Http\Request;
use Catalyst\Repository\Operations\Requests\Concerns\NormalizesCheckboxValues;

final class FeatureFlagDefaultRequest
{
    use NormalizesCheckboxValues;

    public function __construct(private readonly Request $request)
    {
    }

    public function enabled(): bool
    {
        return $this->checkboxValue($this->request->input('enabled'));
    }
}
