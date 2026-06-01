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
 * MiddlewareInterface component for the Catalyst Framework
 *
 */

namespace Catalyst\Framework\Middleware;


use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Closure;

/**************************************************************************************
 * Interface for defining middleware components in the framework
 *
 * Middleware provides a mechanism for filtering HTTP requests entering
 * the application or modifying responses before they're returned to the client.
 *
 * @package Catalyst\Framework\Middleware
 */
interface MiddlewareInterface
{
    /**
     * Process an incoming server request
     *
     * Process an incoming server request and return a response, passing along the
     * request to the next middleware in the stack if needed.
     *
     * @param Request $request The request object
     * @param Closure $next The next middleware handler
     * @return Response The response object
     */
    public function process(Request $request, Closure $next): Response;
}
