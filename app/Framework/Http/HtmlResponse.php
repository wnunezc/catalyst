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
 * HtmlResponse component for the Catalyst Framework
 *
 */

namespace Catalyst\Framework\Http;


/**************************************************************************************
 * HtmlResponse class for simple HTML content responses
 *
 * Specializes the base Response class for HTML content with appropriate
 * content-type headers.
 *
 * @package Catalyst\Framework\Http
 */
class HtmlResponse extends Response
{
    /**
     * Create a new HTML response
     *
     * @param string $content HTML content
     * @param int $status HTTP status code
     * @param array $headers HTTP headers
     * @param string $charset Character set
     */
    public function __construct(
        string $content = '',
        int    $status = 200,
        array  $headers = [],
        string $charset = 'UTF-8'
    )
    {
        // Set HTML-specific Content-Type header if not already set
        if (!isset($headers['Content-Type'])) {
            $headers['Content-Type'] = 'text/html; charset=' . $charset;
        }

        parent::__construct($content, $status, $headers, $charset);
    }
}
