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
 * Exception thrown when a route cannot be found
 *
 * This exception is thrown when a route matching the requested URI is not found
 * or when a named route doesn't exist.
 *
 * @package Catalyst\Framework\Exceptions;
 */
class RouteNotFoundException extends RuntimeException
{
    /**
     * Create a new route not found exception
     *
     * @param string $message Exception message
     * @param int $code Exception code
     * @param Throwable|null $previous Previous exception
     */
    public function __construct(
        string     $message = 'Route not found',
        int        $code = 404,
        ?Throwable $previous = null
    )
    {
        parent::__construct($message, $code, $previous);
    }
}
