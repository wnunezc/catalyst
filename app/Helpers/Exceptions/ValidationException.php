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

namespace Catalyst\Helpers\Exceptions;

use RuntimeException;

/**
 * ValidationException — thrown when validation fails.
 *
 * Used by Controller::validateOrFail() to signal validation errors
 * to the ExceptionHandler, which converts them to a 422 JSON response.
 *
 * @package Catalyst\Helpers\Exceptions
 * Responsibility: Carries field errors, safe old input and response metadata for failed validation.
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
     * Responsibility: Private constructor — use factory methods.
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
     * Responsibility: Get field-level validation errors.
     * @return array<string, string[]>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get the HTTP status code.
     *
     * Responsibility: Exposes the response status associated with the validation failure.
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Returns sanitized input for HTML form repopulation.
     *
     * Responsibility: Returns sanitized input for HTML form repopulation.
     * @return array<string, mixed>
     */
    public function getOldInput(): array
    {
        return $this->oldInput;
    }

    /**
     * Returns the validation error bag name.
     *
     * Responsibility: Returns the validation error bag name.
     */
    public function getErrorBag(): string
    {
        return $this->errorBag;
    }
}
