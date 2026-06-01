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
 * RedirectResponse component for the Catalyst Framework
 *
 */

namespace Catalyst\Framework\Http;

use InvalidArgumentException;

/**************************************************************************************
 * RedirectResponse class for HTTP redirects
 *
 * Specializes the base Response class for redirecting to another URL
 * with appropriate status codes and headers.
 *
 * @package Catalyst\Framework\Http
 */
class RedirectResponse extends Response
{
    /**
     * The target URL
     *
     * @var string
     */
    protected string $targetUrl;

    /**
     * Valid redirect status codes
     *
     * @var array
     */
    protected static array $validStatusCodes = [301, 302, 303, 307, 308];

    /**
     * Create a new redirect response
     *
     * @param string $url The URL to redirect to
     * @param int $status The HTTP status code for the redirect
     * @param array $headers Additional headers to include
     * @throws InvalidArgumentException When an invalid status code is provided
     */
    public function __construct(string $url, int $status = 302, array $headers = [])
    {
        if (!in_array($status, self::$validStatusCodes)) {
            throw new InvalidArgumentException(
                "Invalid redirect status code: $status. Valid redirect status codes are: " .
                implode(', ', self::$validStatusCodes)
            );
        }

        $this->targetUrl = $url;

        // Set the Location header with the target URL
        $headers['Location'] = $url;

        // Initialize with empty content since redirects don't need content
        parent::__construct('', $status, $headers);
    }

    /**
     * Get the target URL
     *
     * @return string The target URL
     */
    public function getTargetUrl(): string
    {
        return $this->targetUrl;
    }

    /**
     * Set the target URL
     *
     * @param string $url The new target URL
     * @return self For method chaining
     */
    public function setTargetUrl(string $url): self
    {
        $this->targetUrl = $url;
        $this->setHeader('Location', $url);

        return $this;
    }

    /**
     * Prepare the redirect response content
     *
     * @return self For method chaining
     */
    protected function prepareContent(): self
    {
        // Set a minimal HTML body that redirects via meta tag as a fallback
        $this->setContent(
            '<!DOCTYPE html>' .
            '<html>' .
            '<head>' .
            '<meta charset="UTF-8" />' .
            '<meta http-equiv="refresh" content="0;url=' . htmlspecialchars($this->targetUrl, ENT_QUOTES) . '" />' .
            '<title>Redirecting</title>' .
            '</head>' .
            '<body>' .
            'Redirecting to <a href="' . htmlspecialchars($this->targetUrl, ENT_QUOTES) . '">' .
            htmlspecialchars($this->targetUrl, ENT_QUOTES) . '</a>.' .
            '</body>' .
            '</html>'
        );

        return $this;
    }

    /**
     * Send the redirect response
     *
     * @return self For method chaining
     */
    public function send(): self
    {
        // Prepare content before sending if we haven't already
        if (empty($this->getContent())) {
            $this->prepareContent();
        }

        return parent::send();
    }
}
