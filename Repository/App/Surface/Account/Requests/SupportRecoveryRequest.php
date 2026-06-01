<?php

declare(strict_types=1);

namespace App\Surface\Account\Requests;

use Catalyst\Framework\Http\Request;
use Catalyst\Helpers\Validation\Validator;

final class SupportRecoveryRequest
{
    private const TYPES = [
        'lost_email_password',
        'lost_email_mfa',
        'compromised_account',
    ];

    /** @return array{data: array<string, string>, errors: array<string, string[]>} */
    public function validate(Request $request, ?string $forcedType = null): array
    {
        $type = $forcedType ?? trim((string) $request->input('request_type', ''));
        if (!in_array($type, self::TYPES, true)) {
            $type = '';
        }

        $data = [
            'request_type' => $type,
            'known_email' => strtolower(trim((string) $request->input('known_email', ''))),
            'alternate_email' => strtolower(trim((string) $request->input('alternate_email', ''))),
            'message' => trim((string) $request->input('message', '')),
        ];

        $validator = new Validator($data, [
            'request_type' => 'required|in:lost_email_password,lost_email_mfa,compromised_account',
            'known_email' => 'required|email|max:255',
            'alternate_email' => 'required|email|max:255',
            'message' => 'required|min:20|max:1200',
        ]);

        return [
            'data' => $data,
            'errors' => $validator->fails() ? $validator->errors() : [],
        ];
    }
}
