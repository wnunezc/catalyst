<?php

declare(strict_types=1);

namespace App\Surface\Account\Requests;

use Catalyst\Framework\Http\Request;
use Catalyst\Helpers\Validation\Validator;

final class MfaRecoveryRequest
{
    /** @return array{data: array<string, string>, errors: array<string, string[]>} */
    public function validate(Request $request): array
    {
        $data = [
            'email' => strtolower(trim((string) $request->input('email', ''))),
        ];

        $validator = new Validator($data, [
            'email' => 'required|email|max:255',
        ]);

        return [
            'data' => $data,
            'errors' => $validator->fails() ? $validator->errors() : [],
        ];
    }
}
