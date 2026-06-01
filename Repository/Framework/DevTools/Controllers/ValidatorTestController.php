<?php

declare(strict_types=1);

/**
 * Catalyst PHP Framework — DevTools
 *
 * ValidatorTestController — Etapa 3: Validation system tests.
 *
 * @package   Catalyst\Repository\DevTools\Controllers
 * @author    Walter Nuñez (arcanisgk) <icarosnet@gmail.com>
 * @copyright 2023 - 2025
 * @license   http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

namespace Catalyst\Repository\DevTools\Controllers;

use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\JsonResponse;

class ValidatorTestController extends Controller
{
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
