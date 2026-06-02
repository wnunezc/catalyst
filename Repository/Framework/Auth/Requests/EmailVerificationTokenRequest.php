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

namespace Catalyst\Repository\Auth\Requests;

use Catalyst\Framework\Http\FormRequest;
use Catalyst\Helpers\Exceptions\ForbiddenException;
use Catalyst\Helpers\Exceptions\ValidationException;
use Catalyst\Helpers\Validation\Validator;

/**
 * Validates manually submitted email verification tokens.
 *
 * @package Catalyst\Repository\Auth\Requests
 * Responsibility: Normalizes token input and rejects values that cannot match a 64-character verification token.
 */
final class EmailVerificationTokenRequest extends FormRequest
{
    /**
     * @var array<string, mixed>|null
     */
    private ?array $resolvedData = null;

    /**
     * Limits validation data to the pasted verification token.
     *
     * Responsibility: Limits validation data to the pasted verification token.
     * @return string[]
     */
    public function only(): array
    {
        return ['token'];
    }

    /**
     * Requires the verification token field and constrains its accepted length.
     *
     * Responsibility: Requires the verification token field and constrains its accepted length.
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'token' => 'required|min:64|max:255',
        ];
    }

    /**
     * Provides the translated field label used in validation feedback.
     *
     * Responsibility: Provides the translated field label used in validation feedback.
     * @return array<string, string>
     */
    public function labels(): array
    {
        return [
            'token' => __('auth.verify.token_label'),
        ];
    }

    /**
     * Returns the generic message displayed when verification token validation fails.
     *
     * Responsibility: Returns the generic message displayed when verification token validation fails.
     */
    public function validationMessage(): string
    {
        return __('auth.verify.validation_failed');
    }

    /**
     * Returns normalized data, resolving validation once when needed.
     *
     * Responsibility: Returns normalized data, resolving validation once when needed.
     * @return array<string, mixed>
     */
    public function validated(): array
    {
        if ($this->resolvedData === null) {
            $this->validateResolved();
        }

        return $this->resolvedData ?? [];
    }

    /**
     * Authorizes and validates the request, then stores the normalized token payload.
     *
     * Responsibility: Authorizes and validates the request, then stores the normalized token payload.
     * @throws ValidationException
     * @throws ForbiddenException
     */
    public function validateResolved(): void
    {
        if (!$this->authorize()) {
            throw ForbiddenException::forbidden('This request is not authorized.');
        }

        $this->prepareForValidation();

        $data = $this->validationData();
        $validator = new Validator($data, $this->rules(), $this->labels());
        $errors = $validator->fails() ? $validator->errors() : [];
        $errors = array_merge_recursive($errors, $this->tokenErrors($data));

        if ($errors !== []) {
            throw ValidationException::withErrors(
                $errors,
                $this->validationMessage(),
                $this->safeOldInput($data)
            );
        }

        $this->resolvedData = [
            'token' => $this->normalizeToken((string) ($data['token'] ?? '')),
        ];
    }

    /**
     * Builds validation data from the request using the trimmed token value.
     *
     * Responsibility: Builds validation data from the request using the trimmed token value.
     * @return array<string, mixed>
     */
    protected function validationData(): array
    {
        return [
            'token' => $this->normalizeToken((string) $this->input('token', '')),
        ];
    }

    /**
     * Adds token format errors after the base validator confirms a value is present.
     *
     * Responsibility: Adds token format errors after the base validator confirms a value is present.
     * @param array<string, mixed> $data
     * @return array<string, string[]>
     */
    private function tokenErrors(array $data): array
    {
        $token = (string) ($data['token'] ?? '');

        if ($token === '') {
            return [];
        }

        if (!self::isWellFormedToken($token)) {
            return [
                'token' => [__('auth.verify.token_invalid_format')],
            ];
        }

        return [];
    }

    /**
     * Checks that the token is exactly 64 hexadecimal characters.
     */
    public static function isWellFormedToken(string $token): bool
    {
        return preg_match('/\A[a-fA-F0-9]{64}\z/', trim($token)) === 1;
    }

    /**
     * Trims surrounding whitespace from a submitted verification token.
     *
     * Responsibility: Trims surrounding whitespace from a submitted verification token.
     */
    private function normalizeToken(string $token): string
    {
        return trim($token);
    }
}
