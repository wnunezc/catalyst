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

namespace Catalyst\Framework\Http;

use InvalidArgumentException;

/**
 * Represents an HTTP redirect response.
 *
 * @package Catalyst\Framework\Http
 * Responsibility: Validates redirect status codes, sets Location headers and provides fallback HTML redirect content.
 */
class RedirectResponse extends Response
{
    /**
     * @var string Target URL sent in the Location header.
     */
    protected string $targetUrl;

    /**
     * @var array<int, int> HTTP status codes allowed for redirects.
     */
    protected static array $validStatusCodes = [301, 302, 303, 307, 308];

    /**
     * Creates a redirect response for the target URL.
     *
     * Responsibility: Creates a redirect response for the target URL.
     * @throws InvalidArgumentException
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
     * Returns the redirect target URL.
     *
     * Responsibility: Returns the redirect target URL.
     */
    public function getTargetUrl(): string
    {
        return $this->targetUrl;
    }

    /**
     * Updates the redirect target URL and Location header.
     *
     * Responsibility: Updates the redirect target URL and Location header.
     */
    public function setTargetUrl(string $url): self
    {
        $this->targetUrl = $url;
        $this->setHeader('Location', $url);

        return $this;
    }

    /**
     * Prepares fallback HTML content for clients that do not follow headers.
     *
     * Responsibility: Prepares fallback HTML content for clients that do not follow headers.
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
     * Sends the redirect response after ensuring fallback content exists.
     *
     * Responsibility: Sends the redirect response after ensuring fallback content exists.
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
