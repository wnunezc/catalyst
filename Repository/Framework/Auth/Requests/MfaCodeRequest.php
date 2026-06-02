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

use Catalyst\Framework\Auth\MfaManager;
use Catalyst\Framework\Http\FormRequest;
use Catalyst\Framework\Http\Request;
use Catalyst\Helpers\Exceptions\ForbiddenException;
use Catalyst\Helpers\Exceptions\ValidationException;
use Catalyst\Helpers\Validation\Validator;

/**
 * Validates MFA challenge and setup confirmation codes.
 *
 * @package Catalyst\Repository\Auth\Requests
 * Responsibility: Accepts TOTP codes and, when allowed, backup-code input before controllers verify the secret.
 */
final class MfaCodeRequest extends FormRequest
{
    private bool $allowBackupCode;

    /**
     * @var array<string, mixed>|null
     */
    private ?array $resolvedData = null;

    /**
     * Captures whether backup-code format is allowed for this MFA request.
     *
     * Responsibility: Captures whether backup-code format is allowed for this MFA request.
     */
    public function __construct(?Request $request = null, bool $allowBackupCode = true)
    {
        parent::__construct($request);
        $this->allowBackupCode = $allowBackupCode;
    }

    /**
     * Limits validation data to the MFA code field.
     *
     * Responsibility: Limits validation data to the MFA code field.
     * @return string[]
     */
    public function only(): array
    {
        return ['code'];
    }

    /**
     * Requires the MFA code field and constrains the accepted input length.
     *
     * Responsibility: Requires the MFA code field and constrains the accepted input length.
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'code' => 'required|min:6|max:9',
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
            'code' => __('auth.mfa.code_label'),
        ];
    }

    /**
     * Returns the generic message displayed when MFA code validation fails.
     *
     * Responsibility: Returns the generic message displayed when MFA code validation fails.
     */
    public function validationMessage(): string
    {
        return __('auth.mfa.invalid_code');
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
     * Authorizes and validates the request, then stores the normalized MFA code payload.
     *
     * Responsibility: Authorizes and validates the request, then stores the normalized MFA code payload.
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
        $errors = array_merge_recursive($errors, $this->codeErrors($data));

        if ($errors !== []) {
            throw ValidationException::withErrors(
                $errors,
                $this->validationMessage(),
                $this->safeOldInput($data)
            );
        }

        $this->resolvedData = [
            'code' => (string) ($data['code'] ?? ''),
        ];
    }

    /**
     * Builds validation data from the request using the trimmed MFA code value.
     *
     * Responsibility: Builds validation data from the request using the trimmed MFA code value.
     * @return array<string, mixed>
     */
    protected function validationData(): array
    {
        return [
            'code' => trim((string) $this->input('code', '')),
        ];
    }

    /**
     * Rejects codes that match neither TOTP format nor an allowed backup-code format.
     *
     * Responsibility: Rejects codes that match neither TOTP format nor an allowed backup-code format.
     * @param array<string, mixed> $data
     * @return array<string, string[]>
     */
    private function codeErrors(array $data): array
    {
        $code = (string) ($data['code'] ?? '');

        if ($code === '') {
            return [];
        }

        $mfa = MfaManager::getInstance();
        if ($mfa->normalizeTotpCode($code) !== null) {
            return [];
        }

        if ($this->allowBackupCode && strlen($mfa->normalizeBackupCode($code)) === 8) {
            return [];
        }

        return [
            'code' => [__('auth.mfa.invalid_code_format')],
        ];
    }
}
