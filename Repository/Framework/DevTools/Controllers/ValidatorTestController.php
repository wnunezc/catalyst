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

namespace Catalyst\Repository\DevTools\Controllers;

use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\JsonResponse;

/**
 * Exposes development endpoints for validator rule diagnostics.
 *
 * @package Catalyst\Repository\DevTools\Controllers
 * Responsibility: Returns deterministic validation and uniqueness-check responses.
 */
class ValidatorTestController extends Controller
{
    /**
     * Validates representative form fields or a forced invalid payload.
     *
     * Responsibility: Validates representative form fields or a forced invalid payload.
     */
    public function validatorTest(): JsonResponse
    {
        $mode = trim((string)$this->input('mode', ''));
        $data = $mode === 'invalid'
            ? [
                'name'                  => 'A',
                'email'                 => 'not-an-email',
                'age'                   => 5,
                'password'              => '123',
                'password_confirmation' => 'xyz',
            ]
            : $this->only(['name', 'email', 'age', 'password', 'password_confirmation']);

        $rules = [
            'name'     => 'required|min:3|max:50',
            'email'    => 'required|email',
            'age'      => 'required|integer|min_value:18|max_value:99',
            'password' => 'required|min:8|confirmed',
        ];

        $validator = $this->validate($data, $rules, [
            'name' => __('ui.labels.name'),
            'email' => __('devtools.validator_demo.labels.email'),
            'age' => __('devtools.validator_demo.labels.age'),
            'password' => __('devtools.validator_demo.labels.password'),
            'password_confirmation' => __('devtools.validator_demo.labels.password_confirmation'),
        ]);

        if ($validator->fails()) {
            return $this->jsonValidationError($validator->errors());
        }

        return $this->jsonSuccess(['fields' => array_keys($data)], __('devtools.validator_runtime.validation_passed'));
    }

    /**
     * Verifies uniqueness validation for a submitted demo email address.
     *
     * Responsibility: Verifies uniqueness validation for a submitted demo email address.
     */
    public function validatorUniqueTest(): JsonResponse
    {
        $data = $this->only(['email']);

        $rules = ['email' => 'required|email|unique:validator_demo_emails,email'];

        $validator = $this->validate($data, $rules, [
            'email' => __('devtools.validator_demo.labels.email_to_check'),
        ]);

        if ($validator->fails()) {
            return $this->jsonValidationError($validator->errors());
        }

        return $this->jsonSuccess(['email' => $data['email']], __('devtools.validator_runtime.unique_email'));
    }
}
