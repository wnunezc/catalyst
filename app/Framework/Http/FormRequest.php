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

namespace Catalyst\Framework\Http;

use Catalyst\Framework\Sensitivity\SensitiveDataPolicy;
use Catalyst\Helpers\Exceptions\ForbiddenException;
use Catalyst\Helpers\Exceptions\ValidationException;
use Catalyst\Helpers\Validation\Validator;

/**
 * Base request object for authorization and validation of form input.
 *
 * @package Catalyst\Framework\Http
 * Responsibility: Filters request payloads, runs validation rules, exposes validated data and prepares safe old input for failed submissions.
 */
abstract class FormRequest
{
    protected Request $request;

    /**
     * @var array<string, mixed>
     */
    private array $routeParameters = [];

    /**
     * @var array<string, mixed>|null
     */
    private ?array $validatedData = null;

    /**
     * Wraps the current HTTP request for validation.
     *
     * Responsibility: Wraps the current HTTP request for validation.
     */
    public function __construct(?Request $request = null)
    {
        $this->request = $request ?? Request::getInstance();
    }

    /**
     * Determines whether the request is authorized.
     *
     * Responsibility: Determines whether the request is authorized.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Returns the input keys allowed for validation.
     *
     * Responsibility: Returns the input keys allowed for validation.
     * @return string[]
     */
    public function only(): array
    {
        return [];
    }

    /**
     * Returns the input keys excluded from validation.
     *
     * Responsibility: Returns the input keys excluded from validation.
     * @return string[]
     */
    public function except(): array
    {
        return [];
    }

    /**
     * Returns validation rules for the request payload.
     *
     * Responsibility: Returns validation rules for the request payload.
     * @return array<string, string|array<int, string>>
     */
    abstract public function rules(): array;

    /**
     * Returns human-readable labels for validation fields.
     *
     * Responsibility: Returns human-readable labels for validation fields.
     * @return array<string, string>
     */
    public function labels(): array
    {
        return [];
    }

    /**
     * Returns the message used when validation fails.
     *
     * Responsibility: Returns the message used when validation fails.
     */
    public function validationMessage(): string
    {
        return 'Validation failed';
    }

    /**
     * Allows subclasses to normalize input before validation.
     *
     * Responsibility: Allows subclasses to normalize input before validation.
     */
    public function prepareForValidation(): void
    {
    }

    /**
     * Returns the sensitivity policy key used to sanitize old input.
     *
     * Responsibility: Returns the sensitivity policy key used to sanitize old input.
     */
    protected function sensitiveResourceKey(): ?string
    {
        return null;
    }

    /**
     * Stores route parameters for later request validation.
     *
     * Responsibility: Stores route parameters for later request validation.
     */
    public function setRouteParameters(array $routeParameters): static
    {
        $this->routeParameters = $routeParameters;

        return $this;
    }

    /**
     * Returns a route parameter value.
     *
     * Responsibility: Returns a route parameter value.
     */
    public function route(string $key, mixed $default = null): mixed
    {
        return $this->routeParameters[$key] ?? $default;
    }

    /**
     * Returns input and uploaded files merged into one payload.
     *
     * Responsibility: Returns input and uploaded files merged into one payload.
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return array_merge($this->request->all(), $this->request->files());
    }

    /**
     * Returns a scalar input value from the wrapped request.
     *
     * Responsibility: Returns a scalar input value from the wrapped request.
     */
    public function input(string $key, mixed $default = null): mixed
    {
        return $this->request->input($key, $default);
    }

    /**
     * Checks whether an input key exists.
     *
     * Responsibility: Checks whether an input key exists.
     */
    public function has(string $key): bool
    {
        return $this->request->has($key);
    }

    /**
     * Checks whether an input key exists and is not empty.
     *
     * Responsibility: Checks whether an input key exists and is not empty.
     */
    public function filled(string $key): bool
    {
        return $this->request->filled($key);
    }

    /**
     * Returns a normalized uploaded file by input key.
     *
     * Responsibility: Returns a normalized uploaded file by input key.
     */
    public function file(string $key): ?UploadedFile
    {
        return $this->request->file($key);
    }

    /**
     * Returns all normalized uploaded files.
     *
     * Responsibility: Returns all normalized uploaded files.
     * @return array<string, UploadedFile>
     */
    public function files(): array
    {
        return $this->request->files();
    }

    /**
     * Returns the wrapped HTTP request.
     *
     * Responsibility: Returns the wrapped HTTP request.
     */
    public function request(): Request
    {
        return $this->request;
    }

    /**
     * Returns validated data, running validation if needed.
     *
     * Responsibility: Returns validated data, running validation if needed.
     * @return array<string, mixed>
     * @throws ValidationException
     * @throws ForbiddenException
     */
    public function validated(): array
    {
        if ($this->validatedData === null) {
            $this->validateResolved();
        }

        return $this->validatedData ?? [];
    }

    /**
     * Authorizes and validates the request payload.
     *
     * Responsibility: Authorizes and validates the request payload.
     * @throws ValidationException
     * @throws ForbiddenException
     */
    public function validateResolved(): void
    {
        if (!$this->authorize()) {
            throw ForbiddenException::forbidden('This request is not authorized.');
        }

        $this->prepareForValidation();

        $data      = $this->validationData();
        $validator = new Validator($data, $this->rules(), $this->labels());

        if ($validator->fails()) {
            throw ValidationException::withErrors(
                $validator->errors(),
                $this->validationMessage(),
                $this->safeOldInput($data)
            );
        }

        $this->validatedData = $data;
    }

    /**
     * Builds the payload used by the validator.
     *
     * Responsibility: Builds the payload used by the validator.
     * @return array<string, mixed>
     */
    protected function validationData(): array
    {
        $data = $this->all();

        if ($this->only() !== []) {
            $data = array_intersect_key($data, array_flip($this->only()));
        }

        if ($this->except() !== []) {
            $data = array_diff_key($data, array_flip($this->except()));
        }

        return $data;
    }

    /**
     * Removes sensitive values from old input before flashing validation errors.
     *
     * Responsibility: Removes sensitive values from old input before flashing validation errors.
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function safeOldInput(array $data): array
    {
        foreach ([
            '_token',
            'csrf_token',
            'password',
            'password_confirm',
            'current_password',
            'new_password',
            'new_password_confirmation',
        ] as $sensitiveField) {
            unset($data[$sensitiveField]);
        }

        foreach ($data as $key => $value) {
            if ($value instanceof UploadedFile) {
                unset($data[$key]);
            }
        }

        $resourceKey = $this->sensitiveResourceKey();
        if ($resourceKey !== null) {
            $data = SensitiveDataPolicy::getInstance()->sanitize(
                $resourceKey,
                $data,
                SensitiveDataPolicy::CHANNEL_FORM
            );
        }

        return $data;
    }
}
