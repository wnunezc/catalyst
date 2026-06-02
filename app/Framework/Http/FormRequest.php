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
 * Defines the Form Request class contract.
 *
 * @package Catalyst\Framework\Http
 * Responsibility: Coordinates the form request behavior within its module boundary.
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
     * Initializes the Form Request instance.
     */
    public function __construct(?Request $request = null)
    {
        $this->request = $request ?? Request::getInstance();
    }

    /**
     * Handles the authorize workflow.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return string[]
     */
    public function only(): array
    {
        return [];
    }

    /**
     * @return string[]
     */
    public function except(): array
    {
        return [];
    }

    /**
     * @return array<string, string|array<int, string>>
     */
    abstract public function rules(): array;

    /**
     * @return array<string, string>
     */
    public function labels(): array
    {
        return [];
    }

    /**
     * Handles the validation message workflow.
     */
    public function validationMessage(): string
    {
        return 'Validation failed';
    }

    /**
     * Handles the prepare for validation workflow.
     */
    public function prepareForValidation(): void
    {
    }

    /**
     * Handles the sensitive resource key workflow.
     */
    protected function sensitiveResourceKey(): ?string
    {
        return null;
    }

    /**
     * Updates the route parameters value.
     */
    public function setRouteParameters(array $routeParameters): static
    {
        $this->routeParameters = $routeParameters;

        return $this;
    }

    /**
     * Handles the route workflow.
     */
    public function route(string $key, mixed $default = null): mixed
    {
        return $this->routeParameters[$key] ?? $default;
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return array_merge($this->request->all(), $this->request->files());
    }

    /**
     * Handles the input workflow.
     */
    public function input(string $key, mixed $default = null): mixed
    {
        return $this->request->input($key, $default);
    }

    /**
     * Handles the has workflow.
     */
    public function has(string $key): bool
    {
        return $this->request->has($key);
    }

    /**
     * Handles the filled workflow.
     */
    public function filled(string $key): bool
    {
        return $this->request->filled($key);
    }

    /**
     * Handles the file workflow.
     */
    public function file(string $key): ?UploadedFile
    {
        return $this->request->file($key);
    }

    /**
     * @return array<string, UploadedFile>
     */
    public function files(): array
    {
        return $this->request->files();
    }

    /**
     * Handles the request workflow.
     */
    public function request(): Request
    {
        return $this->request;
    }

    /**
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
