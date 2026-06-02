<?php

declare(strict_types=1);

namespace Catalyst\Repository\Roles\Requests;

use Catalyst\Framework\Http\Request;
use Catalyst\Helpers\Validation\Validator;

final class UserEnrollmentRequest
{
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
