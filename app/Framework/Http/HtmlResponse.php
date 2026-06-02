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

/**
 * Represents an HTTP response for HTML content.
 *
 * @package Catalyst\Framework\Http
 * Responsibility: Ensures HTML responses carry the correct Content-Type header before delegating response transport to the base class.
 */
class HtmlResponse extends Response
{
    /**
     * Creates an HTML response with a charset-aware content type.
     *
     * Responsibility: Creates an HTML response with a charset-aware content type.
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
