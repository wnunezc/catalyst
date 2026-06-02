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

namespace Catalyst\Repository\Documents\Requests;

use Catalyst\Framework\Http\FormRequest;
use Catalyst\Helpers\Exceptions\ForbiddenException;
use Catalyst\Helpers\Exceptions\ValidationException;
use Catalyst\Helpers\Validation\Validator;

/**
 * Defines the Document Preview Payload Request class contract.
 *
 * @package Catalyst\Repository\Documents\Requests
 * Responsibility: Coordinates the document preview payload request behavior within its module boundary.
 */
class DocumentPreviewPayloadRequest extends FormRequest
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
        return __('documents.messages.invalid_payload_json');
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
    public function payload(): array
    {
        return (array) ($this->validated()['payload'] ?? []);
    }

    /**
     * Handles the payload json workflow.
     */
    public function payloadJson(): string
    {
        return (string) ($this->validated()['payload_json'] ?? '{}');
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
            $rawPayload = $this->input('payload_json');
            $payload = $rawPayload !== null
                ? json_decode((string) $rawPayload, true)
                : $this->input('payload', []);

            if ($rawPayload !== null && !is_array($payload)) {
                $errors['payload_json'][] = __('documents.messages.invalid_payload_json');
            } else {
                $this->resolvedData = [
                    'payload' => is_array($payload) ? $payload : [],
                    'payload_json' => $rawPayload !== null
                        ? (string) $rawPayload
                        : (json_encode(is_array($payload) ? $payload : [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}'),
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
