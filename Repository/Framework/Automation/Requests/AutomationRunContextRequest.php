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

namespace Catalyst\Repository\Automation\Requests;

use Catalyst\Framework\Http\FormRequest;
use Catalyst\Helpers\Exceptions\ForbiddenException;
use Catalyst\Helpers\Exceptions\ValidationException;
use Catalyst\Helpers\Validation\Validator;

/**
 * Defines the Automation Run Context Request class contract.
 *
 * @package Catalyst\Repository\Automation\Requests
 * Responsibility: Coordinates the automation run context request behavior within its module boundary.
 */
final class AutomationRunContextRequest extends FormRequest
{
    /**
     * @var array<string, mixed>|null
     */
    private ?array $resolvedData = null;

    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * Handles the validation message workflow.
     */
    public function validationMessage(): string
    {
        return __('automation.messages.invalid_context_json');
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
     * @return array<string, mixed>
     */
    public function context(): array
    {
        return (array) ($this->validated()['context'] ?? []);
    }

    /**
     * Handles the context json workflow.
     */
    public function contextJson(): string
    {
        return (string) ($this->validated()['context_json'] ?? '{}');
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

        if ($errors === []) {
            $rawJson = $this->input('context_json');
            $context = $rawJson !== null
                ? json_decode((string) $rawJson, true)
                : $this->input('context', []);

            if ($rawJson !== null && !is_array($context)) {
                $errors['context_json'][] = __('automation.messages.invalid_context_json');
            } else {
                $this->resolvedData = [
                    'context' => is_array($context) ? $context : [],
                    'context_json' => $rawJson !== null
                        ? (string) $rawJson
                        : (json_encode(is_array($context) ? $context : [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}'),
                ];
            }
        }

        if ($errors !== []) {
            throw ValidationException::withErrors(
                $errors,
                $this->validationMessage(),
                $this->safeOldInput($data)
            );
        }
    }
}
