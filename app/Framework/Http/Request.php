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

use Catalyst\Framework\Traits\SingletonTrait;
use Catalyst\Helpers\Log\Logger;
use Exception;

/**************************************************************************************
 * Request class for handling HTTP request data
 *
 * Provides methods for accessing and sanitizing request data
 * from various sources ($_GET, $_POST, $_REQUEST, etc.)
 *
 * @package Catalyst\Framework\Http
 */
/**
 * Defines the Request class contract.
 *
 * @package Catalyst\Framework\Http
 * Responsibility: Coordinates the request behavior within its module boundary.
 */
class Request
{
    use SingletonTrait;

    /**
     * @var array
     */
    private array $get = [];

    /**
     * @var array
     */
    private array $post = [];

    /**
     * @var array
     */
    private array $cookie = [];

    /**
     * @var array
     */
    private array $files = [];

    /**
     * @var array
     */
    private array $server = [];

    /**
     * @var array<string, mixed>
     */
    private array $attributes = [];

    /**
     * @var array<string, UploadedFile>|null
     */
    private ?array $normalizedFiles = null;

    /**
     * @var string|null
     */
    private ?string $inputContent = null;

    /**
     * @var string|mixed
     */
    private string $requestMethod;

    /**
     * @var string|mixed
     */
    private string $contentType;

    /**
     * Constructor
     * @throws Exception
     */
    protected function __construct()
    {
        $this->requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $this->contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $this->cleanSuperGlobals();
        $this->parseInputContent();
    }

    /**
     * Clean and store superglobal values
     *
     * @return self
     * @throws Exception
     */
    public function cleanSuperGlobals(): self
    {
        // Store original values in protected properties
        $this->get = $_GET ?? [];
        $this->post = $_POST ?? [];
        $this->cookie = $_COOKIE ?? [];
        $this->files = $_FILES ?? [];
        $this->server = $_SERVER ?? [];
        $this->normalizedFiles = null;

        // Sanitize all GET parameters
        foreach ($this->get as $key => $value) {
            $this->get[$key] = $this->sanitizeInput($value);
        }

        // Sanitize all POST parameters
        foreach ($this->post as $key => $value) {
            $this->post[$key] = $this->sanitizeInput($value);
        }

        // Only log in development for debugging purposes
        if (IS_DEVELOPMENT) {
            Logger::getInstance()->debug('Request parameters processed', [
                'method' => $this->requestMethod,
                'get_count' => count($this->get),
                'post_count' => count($this->post)
            ]);
        }

        return $this;
    }

    /**
     * Parse the raw input content based on content type
     *
     * @return self
     */
    public function parseInputContent(): self
    {
        $this->inputContent = file_get_contents('php://input');

        // If content type is JSON, parse it
        if (str_contains($this->contentType, 'application/json')) {
            $jsonData = json_decode($this->inputContent, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($jsonData)) {
                foreach ($jsonData as $key => $value) {
                    $this->post[$key] = $this->sanitizeInput($value);
                }
            }
        }

        return $this;
    }

    /**
     * Get request method
     *
     * @return string Request method (GET, POST, etc.)
     */
    public function getMethod(): string
    {
        return $this->requestMethod;
    }

    /**
     * Get value from GET parameters
     *
     * @param string $key Parameter name
     * @param mixed $default Default value if parameter doesn't exist
     * @return mixed Parameter value or default
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->get[$key] ?? $default;
    }

    /**
     * Get value from POST parameters
     *
     * @param string $key Parameter name
     * @param mixed $default Default value if parameter doesn't exist
     * @return mixed Parameter value or default
     */
    public function post(string $key, mixed $default = null): mixed
    {
        return $this->post[$key] ?? $default;
    }

    /**
     * Get all GET parameters
     *
     * @return array All GET parameters
     */
    public function getAllGet(): array
    {
        return $this->get;
    }

    /**
     * Get all POST parameters
     *
     * @return array All POST parameters
     */
    public function getAllPost(): array
    {
        return $this->post;
    }

    /**
     * Get raw input content
     *
     * @return string|null Raw input content
     */
    public function getContent(): ?string
    {
        return $this->inputContent;
    }

    /**
     * Sanitize input recursively.
     *
     * DESIGN DECISION — Input layer strategy:
     * Only trim() is applied here. htmlspecialchars() is intentionally NOT applied at input.
     *
     * Rationale:
     * - XSS prevention belongs at the OUTPUT layer (templates), not input.
     *   Use the e() helper in every template: <?= e($value) ?>
     * - SQL injection is prevented by prepared statements in the DB layer,
     *   NOT by htmlspecialchars() (which does not prevent SQL injection).
     * - Applying htmlspecialchars() at input corrupts data stored in the database
     *   (e.g. O'Brien becomes O&#039;Brien), breaks string comparisons, and forces
     *   the ORM/DB layer to reverse-decode values before writing.
     *
     * Output escaping reference: app/Helpers/GlobalFunction/dump-function.php — e()
     * DB safety reference: all queries must use prepared statements (PDO/MySQLi).
     *
     * @param mixed $input Input to sanitize
     * @return mixed Sanitized input
     */
    private function sanitizeInput(mixed $input): mixed
    {
        if (is_array($input)) {
            return array_map([$this, 'sanitizeInput'], $input);
        }

        if (is_string($input)) {
            return trim($input);
        }

        return $input;
    }

    /**
     * Get the current request URI
     *
     * @return string Request URI
     */
    public function getUri(): string
    {
        return $this->server['REQUEST_URI'] ?? '/';
    }

    /**
     * Get value from SERVER parameters
     *
     * @param string $key Parameter name
     * @param mixed $default Default value if parameter doesn't exist
     * @return mixed Parameter value or default
     */
    public function server(string $key, mixed $default = null): mixed
    {
        return $this->server[$key] ?? $default;
    }

    /**
     * Get all SERVER parameters
     *
     * @return array All SERVER parameters
     */
    public function getAllServer(): array
    {
        return $this->server;
    }

    /**
     * Updates the attribute value.
     */
    public function setAttribute(string $key, mixed $value): self
    {
        $this->attributes[$key] = $value;

        return $this;
    }

    /**
     * Handles the attribute workflow.
     */
    public function attribute(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function mergeAttributes(array $attributes): self
    {
        $this->attributes = array_merge($this->attributes, $attributes);

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function attributes(): array
    {
        return $this->attributes;
    }

    /**
     * Get HTTP headers from the request
     *
     * @param string|null $name Specific header name to retrieve (optional)
     * @return array|string|null All headers or specific header value if name provided
     */
    public function getHeaders(?string $name = null): array|string|null
    {
        // Use apache_request_headers() if available
        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
        } else {
            // Fallback to manual extraction from $_SERVER
            $headers = [];
            foreach ($_SERVER as $key => $value) {
                if (str_starts_with($key, 'HTTP_')) {
                    // Convert HTTP_ACCEPT_LANGUAGE to Accept-Language
                    $headerName = str_replace('_', '-', substr($key, 5));
                    $headerName = ucwords(strtolower($headerName), '-');
                    $headers[$headerName] = $value;
                } elseif (in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH', 'CONTENT_MD5'])) {
                    // Special case for these headers which don't have HTTP_ prefix
                    $headerName = str_replace('_', '-', $key);
                    $headerName = ucwords(strtolower($headerName), '-');
                    $headers[$headerName] = $value;
                }
            }
        }

        // Normalize header names to have consistent capitalization
        $normalizedHeaders = [];
        foreach ($headers as $key => $value) {
            $normalizedKey = str_replace(' ', '-', ucwords(str_replace('-', ' ', strtolower($key))));
            $normalizedHeaders[$normalizedKey] = $value;
        }

        // Return specific header if requested
        if ($name !== null) {
            $normalizedName = str_replace(' ', '-', ucwords(str_replace('-', ' ', strtolower($name))));
            return $normalizedHeaders[$normalizedName] ?? null;
        }

        return $normalizedHeaders;
    }

    /**
     * Handles the idempotency key workflow.
     */
    public function idempotencyKey(): string
    {
        $header = $this->getHeaders('Idempotency-Key');
        if (is_string($header) && trim($header) !== '') {
            return trim($header);
        }

        foreach (['_idempotency_key', 'idempotency_key'] as $field) {
            $value = $this->input($field);
            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        return '';
    }

    /**
     * Get the current domain of the application
     *
     * @param Request $request The current request
     * @return string The current domain
     */
    public function getCurrentDomain(Request $request): string
    {
        // Try to get domain from HTTP_HOST
        $host = $request->server('HTTP_HOST', '');

        // Remove port if present
        $domain = preg_replace('/:\d+$/', '', $host);

        // If empty, try SERVER_NAME
        if (empty($domain)) {
            $domain = $request->server('SERVER_NAME', '');
        }

        // If still empty, fallback to a default
        if (empty($domain)) {
            // You might want to define a default domain or throw an exception here
            $domain = 'localhost';
        }

        return $domain;
    }


    /**
     * Get the client IP address
     *
     * @param bool $trustProxy Whether to trust proxy headers (default: true)
     * @return string Client IP address
     */
    public function getClientIp(bool $trustProxy = false): string
    {
        // If we don't trust proxy headers, just return REMOTE_ADDR
        if (!$trustProxy) {
            return $this->server('REMOTE_ADDR', '0.0.0.0');
        }

        // Check for various proxy headers in order of reliability
        $headers = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];

        foreach ($headers as $header) {
            $ip = $this->server($header);

            if ($ip) {
                // HTTP_X_FORWARDED_FOR can contain multiple IPs separated by commas
                // In this case, the first IP is the original client
                if ($header === 'HTTP_X_FORWARDED_FOR') {
                    $ips = explode(',', $ip);
                    $ip = trim($ips[0]);
                }

                // Validate IP format
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        // Default fallback
        return '0.0.0.0';
    }

    /**
     * Check if the request is an AJAX request
     *
     * @return bool
     */
    public function isAjax(): bool
    {
        return strtolower($this->server('HTTP_X_REQUESTED_WITH', '')) === 'xmlhttprequest';
    }

    /**
     * Check if the request expects a JSON response
     *
     * @return bool
     */
    public function expectsJson(): bool
    {
        $accept = $this->server('HTTP_ACCEPT', '');
        return str_contains($accept, 'application/json') || $this->isAjax();
    }

    /**
     * Get an input value from GET or POST
     *
     * @param string $key Input key
     * @param mixed $default Default value if not found
     * @return mixed
     */
    public function input(string $key, mixed $default = null): mixed
    {
        return $this->post($key) ?? $this->get($key, $default);
    }

    /**
     * Get all input from GET and POST merged
     *
     * @return array
     */
    public function all(): array
    {
        return array_merge($this->getAllGet(), $this->getAllPost());
    }

    /**
     * Get only specific input keys
     *
     * @param array $keys Keys to retrieve
     * @return array
     */
    public function only(array $keys): array
    {
        $all = $this->all();
        return array_intersect_key($all, array_flip($keys));
    }

    /**
     * Get all input except specific keys
     *
     * @param array $keys Keys to exclude
     * @return array
     */
    public function except(array $keys): array
    {
        $all = $this->all();
        return array_diff_key($all, array_flip($keys));
    }

    /**
     * Check if an input key exists
     *
     * @param string $key Input key
     * @return bool
     */
    public function has(string $key): bool
    {
        return $this->input($key) !== null;
    }

    /**
     * Check if input is filled (exists and not empty)
     *
     * @param string $key Input key
     * @return bool
     */
    public function filled(string $key): bool
    {
        $value = $this->input($key);
        return $value !== null && $value !== '';
    }

    /**
     * Get a normalized uploaded file by key.
     *
     * @param string $key
     * @return UploadedFile|null
     */
    public function file(string $key): ?UploadedFile
    {
        $files = $this->files();

        return $files[$key] ?? null;
    }

    /**
     * Get all normalized uploaded files.
     *
     * @return array<string, UploadedFile>
     */
    public function files(): array
    {
        if ($this->normalizedFiles !== null) {
            return $this->normalizedFiles;
        }

        $normalized = [];

        foreach ($this->files as $key => $fileData) {
            $file = $this->normalizeFile($fileData);

            if ($file !== null) {
                $normalized[$key] = $file;
            }
        }

        $this->normalizedFiles = $normalized;

        return $this->normalizedFiles;
    }

    /**
     * @param mixed $fileData
     * @return UploadedFile|null
     */
    private function normalizeFile(mixed $fileData): ?UploadedFile
    {
        if (!is_array($fileData)) {
            return null;
        }

        $requiredKeys = ['tmp_name', 'name', 'type', 'size', 'error'];

        foreach ($requiredKeys as $requiredKey) {
            if (!array_key_exists($requiredKey, $fileData)) {
                return null;
            }
        }

        if (
            is_array($fileData['tmp_name'])
            || is_array($fileData['name'])
            || is_array($fileData['type'])
            || is_array($fileData['size'])
            || is_array($fileData['error'])
        ) {
            return null;
        }

        if ((int) $fileData['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        return new UploadedFile(
            (string) $fileData['tmp_name'],
            (string) $fileData['name'],
            (string) $fileData['type'],
            (int) $fileData['size'],
            (int) $fileData['error']
        );
    }
}
