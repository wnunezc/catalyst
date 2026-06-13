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

namespace Catalyst\Repository\Workspaces\Documents\Requests;

use Catalyst\Framework\Http\FormRequest;
use Catalyst\Helpers\Exceptions\ForbiddenException;
use Catalyst\Helpers\Exceptions\ValidationException;
use Catalyst\Helpers\Validation\Validator;

/**
 * Validates render payloads submitted for document previews and exports.
 *
 * @package Catalyst\Repository\Workspaces\Documents\Requests
 * Responsibility: Decode document payload JSON and expose a normalized render context.
 */
class DocumentPreviewPayloadRequest extends FormRequest
{
    /**
     * @var array<string, mixed>|null
     */
    private ?array $resolvedData = null;

    /**
     * Declares validation rules for the render payload wrapper.
     *
     * Responsibility: Declares validation rules for the render payload wrapper.
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * Returns the message used for malformed document payload JSON.
     *
     * Responsibility: Returns the message used for malformed document payload JSON.
     */
    public function validationMessage(): string
    {
        return __('documents.messages.invalid_payload_json');
    }

    /**
     * Returns the normalized render payload wrapper, resolving it lazily.
     *
     * Responsibility: Returns the normalized render payload wrapper, resolving it lazily.
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
     * Returns the decoded document render payload.
     *
     * Responsibility: Returns the decoded document render payload.
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        return (array) ($this->validated()['payload'] ?? []);
    }

    /**
     * Returns the normalized JSON representation of the render payload.
     *
     * Responsibility: Returns the normalized JSON representation of the render payload.
     */
    public function payloadJson(): string
    {
        return (string) ($this->validated()['payload_json'] ?? '{}');
    }

    /**
     * Authorizes the request and resolves a valid array render payload.
     *
     * Responsibility: Authorizes the request and resolves a valid array render payload.
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
