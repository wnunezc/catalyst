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

namespace Catalyst\Repository\Roles\Requests;

use Catalyst\Framework\Http\Request;
use Catalyst\Helpers\Validation\Validator;

/**
 * Defines the User Enrollment Request class contract.
 *
 * @package Catalyst\Repository\Roles\Requests
 * Responsibility: Coordinates the user enrollment request behavior within its module boundary.
 */
final class UserEnrollmentRequest
{
    /**
     * Initializes the User Enrollment Request instance.
     */
    public function __construct(
        private readonly Request $request
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function payload(): array
    {
        return [
            'name' => trim((string) $this->request->input('name', '')),
            'email' => trim((string) $this->request->input('email', '')),
            'password' => (string) $this->request->input('password', ''),
            'password_confirm' => (string) $this->request->input('password_confirm', ''),
            'role' => trim((string) $this->request->input('role', 'user')),
            'email_verified' => (string) $this->request->input('email_verified', '1'),
        ];
    }

    /**
     * @param array<string, string> $payload
     * @return array<string, string[]>
     */
    public function errors(array $payload): array
    {
        $validator = new Validator($payload, [
            'name' => 'required|min:2|max:255',
            'email' => 'required|email|max:255',
            'password' => 'required|min:8',
            'password_confirm' => 'required',
            'role' => 'required|max:50',
        ], [
            'name' => __('roles.users.form.labels.name'),
            'email' => __('roles.users.form.labels.email'),
            'password' => __('roles.users.form.labels.password'),
            'password_confirm' => __('roles.users.form.labels.password_confirm'),
            'role' => __('roles.users.form.labels.role'),
        ]);
        $errors = $validator->fails() ? $validator->errors() : [];

        if (($payload['password'] ?? '') !== ($payload['password_confirm'] ?? '')) {
            $errors['password_confirm'][] = __('auth.validation.password_mismatch');
        }

        return $errors;
    }

    /**
     * @param array<string, string> $payload
     * @return array<string, string>
     */
    public function replayableInput(array $payload): array
    {
        return [
            'name' => $payload['name'] ?? '',
            'email' => $payload['email'] ?? '',
            'role' => $payload['role'] ?? 'user',
            'email_verified' => $payload['email_verified'] ?? '1',
        ];
    }
}
