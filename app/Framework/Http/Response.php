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
 * Represents a framework HTTP response.
 *
 * @package Catalyst\Framework\Http
 * Responsibility: Stores response content, status, headers and internal attributes, then sends headers and body to the client.
 */
class Response
{
    /**
     * @var array<int, string> HTTP status codes and their text labels.
     */
    protected static array $statusTexts = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',            // RFC2518
        103 => 'Early Hints',           // RFC8297
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',          // RFC4918
        208 => 'Already Reported',      // RFC5842
        226 => 'IM Used',               // RFC3229
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',    // RFC7238
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Content Too Large',
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',         // RFC2324
        421 => 'Misdirected Request',   // RFC7540
        422 => 'Unprocessable Content', // RFC4918
        423 => 'Locked',                // RFC4918
        424 => 'Failed Dependency',     // RFC4918
        425 => 'Too Early',             // RFC8470
        426 => 'Upgrade Required',      // RFC2817
        428 => 'Precondition Required', // RFC6585
        429 => 'Too Many Requests',     // RFC6585
        431 => 'Request Header Fields Too Large', // RFC6585
        451 => 'Unavailable For Legal Reasons',   // RFC7725
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',         // RFC2295
        507 => 'Insufficient Storage',            // RFC4918
        508 => 'Loop Detected',                   // RFC5842
        510 => 'Not Extended',                    // RFC2774
        511 => 'Network Authentication Required', // RFC6585
    ];

    /**
     * @var string Response body content.
     */
    protected string $content;

    /**
     * @var int HTTP status code.
     */
    protected int $statusCode;

    /**
     * @var array<string, string|array<string>> Response headers.
     */
    protected array $headers;

    /**
     * @var string Response charset.
     */
    protected string $charset;

    /**
     * @var bool Whether the response was already sent.
     */
    protected bool $sent = false;

    /**
     * Internal response attributes used by middleware and framework glue.
     *
     * These values are not sent as HTTP headers.
     *
     * @var array<string, mixed>
     */
    protected array $attributes = [];

    /**
     * Creates a response with body, status, headers and charset.
     *
     * Responsibility: Creates a response with body, status, headers and charset.
     */
    public function __construct(
        string $content = '',
        int    $statusCode = 200,
        array  $headers = [],
        string $charset = 'UTF-8'
    )
    {
        $this->content = $content;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
        $this->charset = $charset;
    }

    /**
     * Replaces the response body content.
     *
     * Responsibility: Replaces the response body content.
     */
    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Returns the response body content.
     *
     * Responsibility: Returns the response body content.
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Updates the HTTP status code.
     *
     * Responsibility: Updates the HTTP status code.
     * @throws InvalidArgumentException
     */
    public function setStatusCode(int $statusCode): self
    {
        if ($statusCode < 100 || $statusCode >= 600) {
            throw new InvalidArgumentException("Invalid HTTP status code: $statusCode");
        }

        $this->statusCode = $statusCode;
        return $this;
    }

    /**
     * Returns the HTTP status code.
     *
     * Responsibility: Returns the HTTP status code.
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Returns the status text for the current status code.
     *
     * Responsibility: Returns the status text for the current status code.
     */
    public function getStatusText(): string
    {
        return self::$statusTexts[$this->statusCode] ?? 'Unknown Status';
    }

    /**
     * Sets or appends a response header.
     *
     * Responsibility: Sets or appends a response header.
     */
    public function setHeader(string $name, string|array $value, bool $replace = true): self
    {
        $name = $this->normalizeHeaderName($name);

        if ($replace || !isset($this->headers[$name])) {
            $this->headers[$name] = $value;
        } else {
            // Append to existing header
            if (!is_array($this->headers[$name])) {
                $this->headers[$name] = [$this->headers[$name]];
            }
            if (is_array($value)) {
                $this->headers[$name] = array_merge($this->headers[$name], $value);
            } else {
                $this->headers[$name][] = $value;
            }
        }

        return $this;
    }

    /**
     * Returns all response headers.
     *
     * Responsibility: Returns all response headers.
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Stores an internal response attribute.
     *
     * Responsibility: Stores an internal response attribute.
     */
    public function setAttribute(string $name, mixed $value): self
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    /**
     * Returns an internal response attribute.
     *
     * Responsibility: Returns an internal response attribute.
     */
    public function getAttribute(string $name, mixed $default = null): mixed
    {
        return $this->attributes[$name] ?? $default;
    }

    /**
     * Checks whether an internal response attribute exists.
     *
     * Responsibility: Checks whether an internal response attribute exists.
     */
    public function hasAttribute(string $name): bool
    {
        return array_key_exists($name, $this->attributes);
    }

    /**
     * Updates the response charset.
     *
     * Responsibility: Updates the response charset.
     */
    public function setCharset(string $charset): self
    {
        $this->charset = $charset;
        return $this;
    }

    /**
     * Returns the response charset.
     *
     * Responsibility: Returns the response charset.
     */
    public function getCharset(): string
    {
        return $this->charset;
    }

    /**
     * Sends response headers and body to the client.
     *
     * Responsibility: Sends response headers and body to the client.
     */
    public function send(): self
    {
        if ($this->sent) {
            return $this;
        }

        $this->sendHeaders();
        $this->sendContent();

        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        } elseif (php_sapi_name() !== 'cli') {
            // Force close connection to flush output
            $this->flushBuffers();
        }

        $this->sent = true;
        return $this;
    }

    /**
     * Checks whether the response was already sent.
     *
     * Responsibility: Checks whether the response was already sent.
     */
    public function isSent(): bool
    {
        return $this->sent;
    }

    /**
     * Sends the HTTP status and headers when running outside CLI.
     *
     * Responsibility: Sends the HTTP status and headers when running outside CLI.
     */
    protected function sendHeaders(): self
    {
        // Only send headers if not CLI and headers not sent yet
        if (php_sapi_name() !== 'cli' && !headers_sent()) {
            // Send the status header
            http_response_code($this->statusCode);

            // Ensure Content-Type is set
            if (!isset($this->headers['Content-Type'])) {
                $this->setHeader('Content-Type', 'text/html; charset=' . $this->charset);
            }

            // Send all headers
            foreach ($this->headers as $name => $values) {
                $values = is_array($values) ? $values : [$values];
                foreach ($values as $value) {
                    header("$name: $value", false);
                }
            }
        }

        return $this;
    }

    /**
     * Writes the response body content.
     *
     * Responsibility: Writes the response body content.
     */
    protected function sendContent(): self
    {
        echo $this->content;
        return $this;
    }

    /**
     * Flushes output buffers after sending the response.
     *
     * Responsibility: Flushes output buffers after sending the response.
     */
    protected function flushBuffers(): self
    {
        if (function_exists('ob_get_level') && ob_get_level() > 0) {
            while (ob_get_level() > 0) {
                ob_end_flush();
            }
        }
        flush();
        return $this;
    }

    /**
     * Normalizes a header name to title-case segments.
     *
     * Responsibility: Normalizes a header name to title-case segments.
     */
    protected function normalizeHeaderName(string $name): string
    {
        return str_replace(' ', '-', ucwords(str_replace('-', ' ', strtolower($name))));
    }

    /**
     * Creates a 200 OK response.
     */
    public static function success(string $content = '', array $headers = []): static
    {
        return new static($content, 200, $headers);
    }

    /**
     * Creates a 201 Created response.
     */
    public static function created(string $content = '', array $headers = []): static
    {
        return new static($content, 201, $headers);
    }

    /**
     * Creates a 202 Accepted response.
     */
    public static function accepted(string $content = '', array $headers = []): static
    {
        return new static($content, 202, $headers);
    }

    /**
     * Creates a 204 No Content response.
     */
    public static function noContent(array $headers = []): static
    {
        return new static('', 204, $headers);
    }

    /**
     * Creates a 404 Not Found response.
     */
    public static function notFound(string $content = 'Not Found', array $headers = []): static
    {
        return new static($content, 404, $headers);
    }

    /**
     * Creates a 400 Bad Request response.
     */
    public static function badRequest(string $content = 'Bad Request', array $headers = []): static
    {
        return new static($content, 400, $headers);
    }

    /**
     * Creates a 401 Unauthorized response.
     */
    public static function unauthorized(string $content = 'Unauthorized', array $headers = []): static
    {
        return new static($content, 401, $headers);
    }

    /**
     * Creates a 403 Forbidden response.
     */
    public static function forbidden(string $content = 'Forbidden', array $headers = []): static
    {
        return new static($content, 403, $headers);
    }

    /**
     * Creates a 405 Method Not Allowed response with an Allow header.
     */
    public static function methodNotAllowed(
        array  $allowedMethods,
        string $content = 'Method Not Allowed',
        array  $headers = []
    ): static
    {
        $headers['Allow'] = implode(', ', $allowedMethods);
        return new static($content, 405, $headers);
    }

    /**
     * Creates a 500 Internal Server Error response.
     */
    public static function serverError(
        string $content = 'Internal Server Error',
        array  $headers = []
    ): static
    {
        return new static($content, 500, $headers);
    }

    /**
     * Creates a JSON response.
     */
    public static function json(
        mixed $data,
        int   $statusCode = 200,
        array $headers = [],
        int   $options = 0
    ): JsonResponse
    {
        return new JsonResponse($data, $statusCode, $headers, $options);
    }

    /**
     * Creates a redirect response.
     */
    public static function redirect(
        string $url,
        int    $statusCode = 302,
        array  $headers = []
    ): RedirectResponse
    {
        return new RedirectResponse($url, $statusCode, $headers);
    }
}
