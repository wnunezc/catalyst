<?php

declare(strict_types=1);

/**************************************************************************************
 *
 * Catalyst PHP Framework
 * PHP Version 8.4 (Required).
 *
 * @package   Catalyst
 * @subpackage Framework
 * @see       https://github.com/arcanisgk/catalyst
 *
 * @author    Walter Nuñez (arcanisgk/original founder) <icarosnet@gmail.com>
 * @copyright 2023 - 2025
 * @license   http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 *
 * @note      This program is distributed in the hope that it will be useful
 *            WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 *            or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @category  Framework
 * @filesource
 *
 * @link      https://catalyst.dock Local development URL
 *
 * CallableMiddleware component for the Catalyst Framework
 *
 */

namespace Catalyst\Framework\Middleware;

use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Closure;

/**************************************************************************************
 * CallableMiddleware adapter class
 *
 * Wraps callable functions to make them compatible with the middleware interface.
 *
 * @package Catalyst\Framework\Middleware
 */
class CallableMiddleware implements MiddlewareInterface
{
    /**
     * The wrapped callable
     *
     * @var callable
     */
    private $callable;

    /**
     * Create a new callable middleware
     *
     * @param callable $callable The callable to wrap
     */
    public function __construct(callable $callable)
    {
        $this->callable = $callable;
    }

    /**
     * Process an incoming server request
     *
     * @param Request $request The request object
     * @param Closure $next The next middleware handler
     * @return Response The response object
     */
    public function process(Request $request, Closure $next): Response
    {
        // Execute the callable, passing the request and next handler
        return call_user_func($this->callable, $request, $next);
    }
}
