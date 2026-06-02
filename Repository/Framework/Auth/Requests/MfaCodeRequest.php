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
 * Defines the Mfa Code Request class contract.
 *
 * @package Catalyst\Repository\Auth\Requests
 * Responsibility: Coordinates the mfa code request behavior within its module boundary.
 */
final class MfaCodeRequest extends FormRequest
{
    private bool $allowBackupCode;

    /**
     * @var array<string, mixed>|null
     */
    private ?array $resolvedData = null;

    /**
     * Initializes the Mfa Code Request instance.
     */
    public function __construct(?Request $request = null, bool $allowBackupCode = true)
    {
        parent::__construct($request);
        $this->allowBackupCode = $allowBackupCode;
    }

    /**
     * @return string[]
     */
    public function only(): array
    {
        return ['code'];
    }

    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'code' => 'required|min:6|max:9',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function labels(): array
    {
        return [
            'code' => __('auth.mfa.code_label'),
        ];
    }

    /**
     * Handles the validation message workflow.
     */
    public function validationMessage(): string
    {
        return __('auth.mfa.invalid_code');
    }

    /**
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
     * @return array<string, mixed>
     */
    protected function validationData(): array
    {
        return [
            'code' => trim((string) $this->input('code', '')),
        ];
    }

    /**
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
