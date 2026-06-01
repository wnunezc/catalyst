<?php

declare(strict_types=1);

/**
 * Catalyst PHP Framework
 * PHP Version 8.4 (Required)
 *
 * @package   Catalyst
 * @subpackage Helpers\Exceptions
 * @author    Walter Nuñez (arcanisgk) <icarosnet@gmail.com>
 * @copyright 2024 Walter Francisco Nuñez Cruz and Icaros Net
 * @license   Proprietary - https://catalyst.lh-2.net
 */

namespace Catalyst\Helpers\Exceptions;

use RuntimeException;

/**
 * ValidationException — thrown when validation fails.
 *
 * Used by Controller::validateOrFail() to signal validation errors
 * to the ExceptionHandler, which converts them to a 422 JSON response.
 *
 * @package Catalyst\Helpers\Exceptions
 */
class ValidationException extends RuntimeException
{
    /**
     * Field-level validation errors.
     * Structure: ['field' => ['message1', 'message2']]
     *
     * @var array<string, string[]>
     */
    private array $errors;

    /**
     * HTTP status code for the response.
     *
     * @var int
     */
    private int $statusCode;

    /**
     * @var array<string, mixed>
     */
    private array $oldInput;

    private string $errorBag;

    /**
     * Private constructor — use factory methods.
     *
     * @param array<string, string[]> $errors     Field-level errors
     * @param string                  $message    General error message
     * @param int                     $statusCode HTTP status code
     * @param array<string, mixed>    $oldInput   Safe old input for HTML re-render
     * @param string                  $errorBag   Error bag name
     */
    private function __construct(array $errors, string $message, int $statusCode, array $oldInput, string $errorBag)
    {
        parent::__construct($message, $statusCode);
        $this->errors     = $errors;
        $this->statusCode = $statusCode;
        $this->oldInput   = $oldInput;
        $this->errorBag   = $errorBag;
    }

    /**
     * Create a ValidationException from a field-errors array.
     *
     * @param array<string, string[]> $errors  Field-level errors
     * @param string                  $message General error message
     * @param array<string, mixed>    $oldInput Safe old input for HTML forms
     * @param string                  $errorBag Error bag name
     * @return self
     */
    public static function withErrors(
        array $errors,
        string $message = 'Validation failed',
        array $oldInput = [],
        string $errorBag = 'default'
    ): self
    {
        return new self($errors, $message, 422, $oldInput, $errorBag);
    }

    /**
     * Get field-level validation errors.
     *
     * @return array<string, string[]>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get the HTTP status code.
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @return array<string, mixed>
     */
    public function getOldInput(): array
    {
        return $this->oldInput;
    }

    public function getErrorBag(): string
    {
        return $this->errorBag;
    }
}
