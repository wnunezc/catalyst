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
 * DebugMiddleware component for the Catalyst Framework
 *
 */

namespace Catalyst\Framework\Middleware;

use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Closure;
use Exception;

/**************************************************************************************
 * Middleware that provides request and response debugging capabilities.
 *
 * This class logs request and response metadata around the next middleware.
 *
 * Audit note:
 * - implementation is real
 * - no active route consumers were confirmed in the current repo audit
 * - keep as an internal/legacy diagnostic middleware unless routing starts using it again
 *
 * @package Catalyst\Framework\Middleware
 * @since 1.0.0
 */
class DebugMiddleware extends CoreMiddleware
{
    /**
     * @throws Exception
     */
    public function process(Request $request, Closure $next): Response
    {
        // Log request information in development
        $this->log('Debug middleware processing request', [
            'uri' => $_SERVER['REQUEST_URI'] ?? '/',
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'GET',
            'time' => microtime(true)
        ]);

        // Process the request
        $response = $this->passToNext($request, $next);

        // Log response information
        $this->log('Debug middleware received response', [
            'status' => $response->getStatusCode(),
            'time' => microtime(true)
        ]);

        return $response;
    }
}
