<?php

declare(strict_types=1);

/**
 * Catalyst PHP Framework
 *
 * A modern PHP 8.4 framework for building
 * robust and scalable web applications.
 *
 * PHP Version 8.4 (Required).
 *
 * @package    Catalyst
 *
 * @author     Walter Nuñez (arcanisgk/original founder)
 * @email      <wnunez@lh-2.net>
 * @email      <icarosnet@gmail.com>
 * @copyright  2024-2026 Walter Francisco Nuñez Cruz and Icaros Net
 * @license    Proprietary - https://catalyst.lh-2.net/license
 *
 * @version    GIT: See repository tags
 *
 * @category   Framework
 * @filesource
 *
 * @link       https://catalyst.lh-2.net Project homepage
 * @see        https://catalyst.lh-2.net/docs Documentation
 *
 */

namespace App\Surface\Account\Requests;

use Catalyst\Framework\Http\Request;
use Catalyst\Helpers\Validation\Validator;

/**
 * Validates account recovery support form input.
 *
 * @package App\Surface\Account\Requests
 * Responsibility: Normalizes request type, known email, alternate email and user message for support review.
 */
final class SupportRecoveryRequest
{
    private const TYPES = [
        'lost_email_password',
        'lost_email_mfa',
        'compromised_account',
    ];

    /**
     * Validates support recovery input, optionally forcing the compromised-account request type.
     *
     * Responsibility: Validates support recovery input, optionally forcing the compromised-account request type.
     * @return array{data: array<string, string>, errors: array<string, string[]>}
     */
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
