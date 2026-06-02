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
 * Validates MFA recovery form input.
 *
 * @package App\Surface\Account\Requests
 * Responsibility: Normalizes and validates the email address used to request an MFA reset.
 */
final class MfaRecoveryRequest
{
    /**
     * Extracts the submitted email and returns validation errors keyed by field.
     *
     * Responsibility: Extracts the submitted email and returns validation errors keyed by field.
     * @return array{data: array<string, string>, errors: array<string, string[]>}
     */
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
