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

/**************************************************************************************
 * JsonResponse class for JSON API responses
 *
 * Specializes the base Response class for JSON content with appropriate
 * content-type headers and JSON encoding.
 *
 * @package Catalyst\Framework\Http
 */
/**
 * Defines the Json Response class contract.
 *
 * @package Catalyst\Framework\Http
 * Responsibility: Coordinates the json response behavior within its module boundary.
 */
class JsonResponse extends Response
{
    public const string HTML_POLICY_TRUSTED = 'trusted-html';

    /**
     * JSON encoding options
     *
     * @var int
     */
    protected int $encodingOptions;

    /**
     * Original data before JSON encoding
     *
     * @var mixed
     */
    protected mixed $data;

    /**
     * Notification bag for this response
     *
     * @var NotificationBag|null
     */
    protected ?NotificationBag $notifications = null;

    /**
     * Create a new JSON response
     *
     * @param mixed $data The data to encode as JSON
     * @param int $status The HTTP status code
     * @param array $headers Array of HTTP headers
     * @param int $options JSON encoding options
     * @param bool $json Whether the data is already JSON encoded
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
     * Get the original data
     *
     * @return mixed Original data
     */
    public function getData(): mixed
    {
        return $this->data;
    }

    /**
     * Set the data and encode it as JSON
     *
     * @param mixed $data The data to encode
     * @return self For method chaining
     */
    public function setData(mixed $data): self
    {
        $this->data = $data;
        $this->setContent($this->encodeData($data));

        return $this;
    }

    /**
     * Set JSON encoding options
     *
     * @param int $options JSON encoding options
     * @return self For method chaining
     */
    public function setEncodingOptions(int $options): self
    {
        $this->encodingOptions = $options;
        $this->setContent($this->encodeData($this->data));

        return $this;
    }

    /**
     * Add notifications to this response
     *
     * @param NotificationBag $bag Notification bag
     * @return self For method chaining
     */
    public function withNotification(NotificationBag $bag): self
    {
        $this->notifications = $bag;
        $this->updateDataWithNotifications();

        return $this;
    }

    /**
     * Get the notification bag
     *
     * @return NotificationBag|null
     */
    public function getNotifications(): ?NotificationBag
    {
        return $this->notifications;
    }

    /**
     * Update the data array to include notifications
     *
     * @return void
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
     * Get current JSON encoding options
     *
     * @return int JSON encoding options
     */
    public function getEncodingOptions(): int
    {
        return $this->encodingOptions;
    }

    /**
     * Encode the given data as JSON
     *
     * @param mixed $data Data to encode
     * @return string JSON encoded string
     * @throws InvalidArgumentException If encoding fails
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
     * Create a JSON response for an API result
     *
     * @param mixed $data The data payload
     * @param bool $success Whether the API call was successful
     * @param string|null $message Optional message
     * @param int $status HTTP status code
     * @param array $meta Additional metadata
     * @param array $headers HTTP headers
     * @param bool $noFlash Whether to suppress flash messages
     * @param NotificationBag|null $notifications Optional notifications to include
     * @return self New JsonResponse instance
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
     * Create a JSON error response
     *
     * @param string $message Error message
     * @param mixed $errors Detailed error information
     * @param int $status HTTP status code
     * @param array $headers HTTP headers
     * @return self New JsonResponse instance
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
     * Create a JSON validation error response
     *
     * @param array $errors Validation errors
     * @param string $message Error message
     * @param int $status HTTP status code
     * @param array $headers HTTP headers
     * @return self New JsonResponse instance
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
     * Add a client-side redirect instruction to the response.
     *
     * The JS FormHandler will navigate to this URL after processing notifications.
     * Use $delay to let toaster notifications appear before navigating.
     *
     * @param string $url   URL to redirect to
     * @param int    $delay Delay in milliseconds before redirect (default: 300)
     * @return self For method chaining
     */
    public function withRedirect(string $url, int $delay = 300): self
    {
        $data             = is_array($this->data) ? $this->data : ['data' => $this->data];
        $data['redirect'] = $url;

        if ($delay > 0) {
            $data['redirectDelay'] = $delay;
        }

        $this->data = $data;
        $this->setContent($this->encodeData($data));

        return $this;
    }

    /**
     * Add a page refresh instruction to the response.
     *
     * The JS FormHandler will reload the current page after processing notifications.
     * Use $delay to let toaster notifications appear before reloading.
     *
     * @param int $delay Delay in milliseconds before refresh (default: 300)
     * @return self For method chaining
     */
    public function withRefresh(int $delay = 300): self
    {
        $data           = is_array($this->data) ? $this->data : ['data' => $this->data];
        $data['refresh'] = true;

        if ($delay > 0) {
            $data['refreshDelay'] = $delay;
        }

        $this->data = $data;
        $this->setContent($this->encodeData($data));

        return $this;
    }

    /**
     * Add a partial DOM replacement instruction to the response.
     *
     * The shared JS client replaces the content of the target selector using
     * the provided HTML fragment when the response is processed.
     *
     * @param string $selector CSS selector to target
     * @param TrustedHtml $html Replacement HTML fragment
     * @return self For method chaining
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
