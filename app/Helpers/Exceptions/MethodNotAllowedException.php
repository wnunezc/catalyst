<?php

declare(strict_types=1);

/**
 *
 * Catalyst PHP Framework
 * PHP Version 8.4 (Required).
 *
 * @package   Catalyst
 *
 * @see       https://catalyst.lh-2.net
 *
 * @author    Walter Nuñez (arcanisgk/original founder) <wnunez@lh-2.net>
 * @copyright 2024 Walter Francisco Nuñez Cruz and Icaros Net
 * @license   Proprietary - https://catalyst.lh-2.net
 *
 * @note      This program is provided "as is" without a warranty of any kind, too express
 *            or implied, including but not limited to the warranties of merchantability,
 *            fitness for a particular purpose, and non-infringement.
 *
 * @category  Framework
 * @filesource
 *
 * @link      https://catalyst.lh-2.net Project homepage
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
 * @package Catalyst\Framework\Exceptions;
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
     * Create a new method not allowed exception
     *
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
     * Get the HTTP methods that are allowed for the route
     *
     * @return array Array of allowed HTTP methods
     */
    public function getAllowedMethods(): array
    {
        return $this->allowedMethods;
    }
}