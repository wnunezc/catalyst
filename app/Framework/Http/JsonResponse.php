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

use Catalyst\Framework\Notification\NotificationBag;
use Catalyst\Framework\View\TrustedHtml;
use InvalidArgumentException;
use JsonException;

/**
 * Represents an HTTP response encoded as JSON.
 *
 * @package Catalyst\Framework\Http
 * Responsibility: Encodes payloads, attaches notification metadata and exposes API response helpers for JSON clients.
 */
class JsonResponse extends Response
{
    public const string HTML_POLICY_TRUSTED = 'trusted-html';

    /**
     * @var int JSON encoding flags used for response payloads.
     */
    protected int $encodingOptions;

    /**
     * @var mixed Original response data before JSON encoding.
     */
    protected mixed $data;

    /**
     * @var NotificationBag|null Notification bag attached to the payload.
     */
    protected ?NotificationBag $notifications = null;

    /**
     * Creates a JSON response from raw data or a pre-encoded JSON string.
     *
     * Responsibility: Creates a JSON response from raw data or a pre-encoded JSON string.
     * @throws InvalidArgumentException
     */
    public function __construct(
        mixed $data = null,
        int   $status = 200,
        array $headers = [],
        int   $options = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
        bool  $json = false
    )
    {
        $this->encodingOptions = $options;

        // Set JSON-specific headers
        $headers['Content-Type'] = $headers['Content-Type'] ?? 'application/json';

        if ($json && is_string($data)) {
            // Data is already JSON encoded
            $this->data = json_decode($data, true);
            parent::__construct($data, $status, $headers);
        } else {
            // Data needs encoding
            $this->data = $data;
            parent::__construct($this->encodeData($data), $status, $headers);
        }
    }

    /**
     * Returns the original response data.
     *
     * Responsibility: Returns the original response data.
     */
    public function getData(): mixed
    {
        return $this->data;
    }

    /**
     * Replaces the response data and re-encodes the body.
     *
     * Responsibility: Replaces the response data and re-encodes the body.
     */
    public function setData(mixed $data): self
    {
        $this->data = $data;
        $this->setContent($this->encodeData($data));

        return $this;
    }

    /**
     * Updates JSON encoding options and re-encodes the body.
     *
     * Responsibility: Updates JSON encoding options and re-encodes the body.
     */
    public function setEncodingOptions(int $options): self
    {
        $this->encodingOptions = $options;
        $this->setContent($this->encodeData($this->data));

        return $this;
    }

    /**
     * Adds notification metadata to the JSON payload.
     *
     * Responsibility: Adds notification metadata to the JSON payload.
     */
    public function withNotification(NotificationBag $bag): self
    {
        $this->notifications = $bag;
        $this->updateDataWithNotifications();

        return $this;
    }

    /**
     * Returns the notification bag attached to the response.
     *
     * Responsibility: Returns the notification bag attached to the response.
     */
    public function getNotifications(): ?NotificationBag
    {
        return $this->notifications;
    }

    /**
     * Merges notification data into the encoded payload.
     *
     * Responsibility: Merges notification data into the encoded payload.
     */
    protected function updateDataWithNotifications(): void
    {
        if ($this->notifications === null || $this->notifications->isEmpty()) {
            return;
        }

        // Ensure data is an array
        $data = is_array($this->data) ? $this->data : ['data' => $this->data];

        // Add notifications
        $data['notifications'] = $this->notifications->toArray();

        // Update the response content
        $this->data = $data;
        $this->setContent($this->encodeData($data));
    }

    /**
     * Returns the active JSON encoding options.
     *
     * Responsibility: Returns the active JSON encoding options.
     */
    public function getEncodingOptions(): int
    {
        return $this->encodingOptions;
    }

    /**
     * Encodes a value into the response JSON string.
     *
     * Responsibility: Encodes a value into the response JSON string.
     * @throws InvalidArgumentException
     */
    protected function encodeData(mixed $data): string
    {
        if ($data === null) {
            return 'null';
        }

        try {
            $json = json_encode($data, $this->encodingOptions | JSON_THROW_ON_ERROR);

            if ($json === false) {
                throw new InvalidArgumentException('JSON encoding failed');
            }

            return $json;
        } catch (JsonException $e) {
            throw new InvalidArgumentException(
                'JSON encoding error: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Creates a structured API JSON response.
     */
    public static function api(
        mixed            $data = null,
        bool             $success = true,
        ?string          $message = null,
        int              $status = 200,
        array            $meta = [],
        array            $headers = [],
        bool             $noFlash = true,
        ?NotificationBag $notifications = null
    ): self
    {
        $result = [
            'success' => $success,
            'data' => $data,
            'noFlash' => $noFlash
        ];

        if ($message !== null) {
            $result['message'] = $message;
        }

        if (!empty($meta)) {
            $result['meta'] = $meta;
        }

        $response = new self($result, $status, $headers);

        if ($notifications !== null) {
            $response->withNotification($notifications);
        }

        return $response;
    }

    /**
     * Creates a structured API error response.
     */
    public static function error(
        string $message,
        mixed  $errors = null,
        int    $status = 400,
        array  $headers = []
    ): self
    {
        $data = [
            'success' => false,
            'message' => $message
        ];

        if ($errors !== null) {
            $data['errors'] = $errors;
        }

        return new self($data, $status, $headers);
    }

    /**
     * Creates a structured validation error response.
     */
    public static function validation(
        array  $errors,
        string $message = 'Validation failed',
        int    $status = 422,
        array  $headers = []
    ): self
    {
        return self::error($message, $errors, $status, $headers);
    }

    /**
     * Adds a client-side redirect instruction to the payload.
     *
     * Responsibility: Adds a client-side redirect instruction to the payload.
     */
    public function withRedirect(string $url): self
    {
        $data             = is_array($this->data) ? $this->data : ['data' => $this->data];
        $data['redirect'] = $url;

        $this->data = $data;
        $this->setContent($this->encodeData($data));

        return $this;
    }

    /**
     * Adds a client-side page refresh instruction to the payload.
     *
     * Responsibility: Adds a client-side page refresh instruction to the payload.
     */
    public function withRefresh(): self
    {
        $data           = is_array($this->data) ? $this->data : ['data' => $this->data];
        $data['refresh'] = true;

        $this->data = $data;
        $this->setContent($this->encodeData($data));

        return $this;
    }

    /**
     * Adds a trusted partial DOM replacement instruction to the payload.
     *
     * Responsibility: Adds a trusted partial DOM replacement instruction to the payload.
     */
    public function withHtml(string $selector, TrustedHtml $html): self
    {
        $selector = trim($selector);
        if ($selector === '') {
            throw new InvalidArgumentException('DOM injection selector cannot be empty.');
        }

        $data = is_array($this->data) ? $this->data : ['data' => $this->data];
        $data['in'] = $selector;
        $data['html'] = $html->toHtml();
        $data['html_policy'] = self::HTML_POLICY_TRUSTED;

        $this->data = $data;
        $this->setContent($this->encodeData($data));

        return $this;
    }
}
