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
 * Response component for the Catalyst Framework
 *
 */

namespace Catalyst\Framework\Http;

use InvalidArgumentException;

/**************************************************************************************
 * Response class representing an HTTP response
 *
 * Handles the response body, status code, and headers for HTTP responses
 * returned from the application.
 *
 * @package Catalyst\Framework\Http
 */
class Response
{
    /**
     * HTTP status codes and their text representations
     *
     * @var array<int, string>
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
     * Response content
     *
     * @var string
     */
    protected string $content;

    /**
     * HTTP status code
     *
     * @var int
     */
    protected int $statusCode;

    /**
     * Response headers
     *
     * @var array<string, string|array<string>>
     */
    protected array $headers;

    /**
     * Response charset
     *
     * @var string
     */
    protected string $charset;

    /**
     * Flag indicating if the response has been sent
     *
     * @var bool
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
     * Create a new response instance
     *
     * @param string $content Response content
     * @param int $statusCode HTTP status code
     * @param array $headers Response headers
     * @param string $charset Response charset
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
     * Set the response content
     *
     * @param string $content Response content
     * @return self For method chaining
     */
    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Get the response content
     *
     * @return string Response content
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Set HTTP status code
     *
     * @param int $statusCode HTTP status code
     * @return self For method chaining
     * @throws InvalidArgumentException If status code is invalid
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
     * Get the HTTP status code
     *
     * @return int HTTP status code
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Get the status text for the current status code
     *
     * @return string Status text
     */
    public function getStatusText(): string
    {
        return self::$statusTexts[$this->statusCode] ?? 'Unknown Status';
    }

    /**
     * Set a response header
     *
     * @param string $name Header name
     * @param string|array $value Header value
     * @param bool $replace Whether to replace existing headers with same name
     * @return self For method chaining
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
     * Get all response headers
     *
     * @return array Response headers
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Set an internal response attribute.
     *
     * @param string $name Attribute name
     * @param mixed $value Attribute value
     * @return self
     */
    public function setAttribute(string $name, mixed $value): self
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    /**
     * Get an internal response attribute.
     *
     * @param string $name Attribute name
     * @param mixed $default Default value when the attribute is missing
     * @return mixed
     */
    public function getAttribute(string $name, mixed $default = null): mixed
    {
        return $this->attributes[$name] ?? $default;
    }

    /**
     * Check if an internal response attribute exists.
     *
     * @param string $name Attribute name
     * @return bool
     */
    public function hasAttribute(string $name): bool
    {
        return array_key_exists($name, $this->attributes);
    }

    /**
     * Set the response charset
     *
     * @param string $charset Character set
     * @return self For method chaining
     */
    public function setCharset(string $charset): self
    {
        $this->charset = $charset;
        return $this;
    }

    /**
     * Get the response charset
     *
     * @return string Character set
     */
    public function getCharset(): string
    {
        return $this->charset;
    }

    /**
     * Send the response to the client
     *
     * @return self For method chaining
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
     * Check if the response has been sent
     *
     * @return bool True if the response has been sent
     */
    public function isSent(): bool
    {
        return $this->sent;
    }

    /**
     * Send the response headers
     *
     * @return self For method chaining
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
     * Send the response content
     *
     * @return self For method chaining
     */
    protected function sendContent(): self
    {
        echo $this->content;
        return $this;
    }

    /**
     * Flush all response buffers
     *
     * @return self For method chaining
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
     * Normalize header name (e.g., "content-type" to "Content-Type")
     *
     * @param string $name Header name
     * @return string Normalized header name
     */
    protected function normalizeHeaderName(string $name): string
    {
        return str_replace(' ', '-', ucwords(str_replace('-', ' ', strtolower($name))));
    }

    /**
     * Create a new success response
     *
     * @param string $content Response content
     * @param array $headers Response headers
     * @return static New response instance
     */
    public static function success(string $content = '', array $headers = []): static
    {
        return new static($content, 200, $headers);
    }

    /**
     * Create a new created response
     *
     * @param string $content Response content
     * @param array $headers Response headers
     * @return static New response instance
     */
    public static function created(string $content = '', array $headers = []): static
    {
        return new static($content, 201, $headers);
    }

    /**
     * Create a new accepted response
     *
     * @param string $content Response content
     * @param array $headers Response headers
     * @return static New response instance
     */
    public static function accepted(string $content = '', array $headers = []): static
    {
        return new static($content, 202, $headers);
    }

    /**
     * Create a new no content response
     *
     * @param array $headers Response headers
     * @return static New response instance
     */
    public static function noContent(array $headers = []): static
    {
        return new static('', 204, $headers);
    }

    /**
     * Create a not found response
     *
     * @param string $content Response content
     * @param array $headers Response headers
     * @return static New response instance
     */
    public static function notFound(string $content = 'Not Found', array $headers = []): static
    {
        return new static($content, 404, $headers);
    }

    /**
     * Create a bad request response
     *
     * @param string $content Response content
     * @param array $headers Response headers
     * @return static New response instance
     */
    public static function badRequest(string $content = 'Bad Request', array $headers = []): static
    {
        return new static($content, 400, $headers);
    }

    /**
     * Create an unauthorized response
     *
     * @param string $content Response content
     * @param array $headers Response headers
     * @return static New response instance
     */
    public static function unauthorized(string $content = 'Unauthorized', array $headers = []): static
    {
        return new static($content, 401, $headers);
    }

    /**
     * Create a forbidden response
     *
     * @param string $content Response content
     * @param array $headers Response headers
     * @return static New response instance
     */
    public static function forbidden(string $content = 'Forbidden', array $headers = []): static
    {
        return new static($content, 403, $headers);
    }

    /**
     * Create a method not allowed response
     *
     * @param array $allowedMethods Allowed HTTP methods
     * @param string $content Response content
     * @param array $headers Response headers
     * @return static New response instance
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
     * Create an internal server error response
     *
     * @param string $content Response content
     * @param array $headers Response headers
     * @return static New response instance
     */
    public static function serverError(
        string $content = 'Internal Server Error',
        array  $headers = []
    ): static
    {
        return new static($content, 500, $headers);
    }

    /**
     * Create a JSON response
     *
     * @param mixed $data Data to encode as JSON
     * @param int $statusCode HTTP status code
     * @param array $headers Response headers
     * @param int $options JSON encoding options
     * @return JsonResponse New JSON response instance
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
     * Create a redirect response
     *
     * @param string $url URL to redirect to
     * @param int $statusCode HTTP status code (301, 302, 303, 307, 308)
     * @param array $headers Additional headers
     * @return RedirectResponse New redirect response instance
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
