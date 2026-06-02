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
use Throwable;

/**
 * Exception thrown when an HTTP method is not allowed for a route
 *
 * This exception is thrown when a route exists for the requested URI, but the
 * HTTP method used is not allowed for that route (405 Method Not Allowed).
 *
 * @package Catalyst\Helpers\Exceptions
 * Responsibility: Carries the HTTP methods accepted by a route after a 405 match failure.
 */
class MethodNotAllowedException extends RuntimeException
{
    /**
     * HTTP methods that are allowed for the route
     *
     * @var array
     */
    private array $allowedMethods;

    /**
     * Create a new method not allowed exception.
     *
     * Responsibility: Create a new method not allowed exception.
     * @param string $message Exception message
     * @param array $allowedMethods HTTP methods allowed for this route
     * @param int $code Exception code
     * @param Throwable|null $previous Previous exception
     */
    public function __construct(
        string     $message = 'Method not allowed',
        array      $allowedMethods = [],
        int        $code = 405,
        ?Throwable $previous = null
    )
    {
        $this->allowedMethods = $allowedMethods;
        parent::__construct($message, $code, $previous);
    }

    /**
     * Get the HTTP methods that are allowed for the route.
     *
     * Responsibility: Exposes the route methods accepted by the failed request target.
     * @return array Array of allowed HTTP methods
     */
    public function getAllowedMethods(): array
    {
        return $this->allowedMethods;
    }
}
