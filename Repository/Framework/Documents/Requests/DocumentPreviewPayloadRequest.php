<?php

declare(strict_types=1);

namespace Catalyst\Repository\Documents\Requests;

use Catalyst\Framework\Http\FormRequest;
use Catalyst\Helpers\Exceptions\ForbiddenException;
use Catalyst\Helpers\Exceptions\ValidationException;
use Catalyst\Helpers\Validation\Validator;

final class DocumentPreviewPayloadRequest extends FormRequest
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
