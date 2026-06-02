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
