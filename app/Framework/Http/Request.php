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

/**
 * Captures and normalizes the current HTTP request.
 *
 * @package Catalyst\Framework\Http
 * Responsibility: Stores sanitized superglobal data, parses JSON input, resolves headers, files, attributes and request metadata for framework consumers.
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
     * Captures request method, content type and normalized input state.
     *
     * Responsibility: Captures request method, content type and normalized input state.
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
     * Stores superglobal values and trims GET and POST input recursively.
     *
     * Responsibility: Stores superglobal values and trims GET and POST input recursively.
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
     * Parses raw JSON request content into POST input.
     *
     * Responsibility: Parses raw JSON request content into POST input.
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
     * Returns the HTTP request method.
     *
     * Responsibility: Returns the HTTP request method.
     */
    public function getMethod(): string
    {
        return $this->requestMethod;
    }

    /**
     * Returns a value from GET parameters.
     *
     * Responsibility: Returns a value from GET parameters.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->get[$key] ?? $default;
    }

    /**
     * Returns a value from POST parameters.
     *
     * Responsibility: Returns a value from POST parameters.
     */
    public function post(string $key, mixed $default = null): mixed
    {
        return $this->post[$key] ?? $default;
    }

    /**
     * Returns all GET parameters.
     *
     * Responsibility: Returns all GET parameters.
     */
    public function getAllGet(): array
    {
        return $this->get;
    }

    /**
     * Returns all POST parameters.
     *
     * Responsibility: Returns all POST parameters.
     */
    public function getAllPost(): array
    {
        return $this->post;
    }

    /**
     * Returns the raw request body content.
     *
     * Responsibility: Returns the raw request body content.
     */
    public function getContent(): ?string
    {
        return $this->inputContent;
    }

    /**
     * Trims string input recursively without applying output escaping.
     *
     * Responsibility: Trims string input recursively without applying output escaping.
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
     * Returns the current request URI.
     *
     * Responsibility: Returns the current request URI.
     */
    public function getUri(): string
    {
        return $this->server['REQUEST_URI'] ?? '/';
    }

    /**
     * Returns a value from SERVER parameters.
     *
     * Responsibility: Returns a value from SERVER parameters.
     */
    public function server(string $key, mixed $default = null): mixed
    {
        return $this->server[$key] ?? $default;
    }

    /**
     * Returns all SERVER parameters.
     *
     * Responsibility: Returns all SERVER parameters.
     */
    public function getAllServer(): array
    {
        return $this->server;
    }

    /**
     * Stores an internal request attribute.
     *
     * Responsibility: Stores an internal request attribute.
     */
    public function setAttribute(string $key, mixed $value): self
    {
        $this->attributes[$key] = $value;

        return $this;
    }

    /**
     * Returns an internal request attribute.
     *
     * Responsibility: Returns an internal request attribute.
     */
    public function attribute(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }

    /**
     * Merges multiple internal request attributes.
     *
     * Responsibility: Merges multiple internal request attributes.
     * @param array<string, mixed> $attributes
     */
    public function mergeAttributes(array $attributes): self
    {
        $this->attributes = array_merge($this->attributes, $attributes);

        return $this;
    }

    /**
     * Returns all internal request attributes.
     *
     * Responsibility: Returns all internal request attributes.
     * @return array<string, mixed>
     */
    public function attributes(): array
    {
        return $this->attributes;
    }

    /**
     * Returns all HTTP headers or a single normalized header value.
     *
     * Responsibility: Returns all HTTP headers or a single normalized header value.
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
     * Returns the submitted idempotency key from headers or input.
     *
     * Responsibility: Returns the submitted idempotency key from headers or input.
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
     * Resolves the current request domain from host server values.
     *
     * Responsibility: Resolves the current request domain from host server values.
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
     * Resolves the client IP address, optionally trusting proxy headers.
     *
     * Responsibility: Resolves the client IP address, optionally trusting proxy headers.
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
     * Checks whether the request was sent through XMLHttpRequest.
     *
     * Responsibility: Checks whether the request was sent through XMLHttpRequest.
     */
    public function isAjax(): bool
    {
        return strtolower($this->server('HTTP_X_REQUESTED_WITH', '')) === 'xmlhttprequest';
    }

    /**
     * Checks whether the request expects a JSON response.
     *
     * Responsibility: Checks whether the request expects a JSON response.
     */
    public function expectsJson(): bool
    {
        $accept = $this->server('HTTP_ACCEPT', '');
        return str_contains($accept, 'application/json') || $this->isAjax();
    }

    /**
     * Returns an input value from POST or GET data.
     *
     * Responsibility: Returns an input value from POST or GET data.
     */
    public function input(string $key, mixed $default = null): mixed
    {
        return $this->post($key) ?? $this->get($key, $default);
    }

    /**
     * Returns merged GET and POST input.
     *
     * Responsibility: Returns merged GET and POST input.
     */
    public function all(): array
    {
        return array_merge($this->getAllGet(), $this->getAllPost());
    }

    /**
     * Returns only the requested input keys.
     *
     * Responsibility: Returns only the requested input keys.
     */
    public function only(array $keys): array
    {
        $all = $this->all();
        return array_intersect_key($all, array_flip($keys));
    }

    /**
     * Returns input except the requested keys.
     *
     * Responsibility: Returns input except the requested keys.
     */
    public function except(array $keys): array
    {
        $all = $this->all();
        return array_diff_key($all, array_flip($keys));
    }

    /**
     * Checks whether an input key exists.
     *
     * Responsibility: Checks whether an input key exists.
     */
    public function has(string $key): bool
    {
        return $this->input($key) !== null;
    }

    /**
     * Checks whether an input key exists and is not empty.
     *
     * Responsibility: Checks whether an input key exists and is not empty.
     */
    public function filled(string $key): bool
    {
        $value = $this->input($key);
        return $value !== null && $value !== '';
    }

    /**
     * Returns a normalized uploaded file by key.
     *
     * Responsibility: Returns a normalized uploaded file by key.
     */
    public function file(string $key): ?UploadedFile
    {
        $files = $this->files();

        return $files[$key] ?? null;
    }

    /**
     * Returns all normalized uploaded files.
     *
     * Responsibility: Returns all normalized uploaded files.
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
     * Normalizes one PHP file upload array into an UploadedFile instance.
     *
     * Responsibility: Normalizes one PHP file upload array into an UploadedFile instance.
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
