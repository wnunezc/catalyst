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

namespace Catalyst\Framework\Controllers;

use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Catalyst\Framework\Http\JsonResponse;
use Catalyst\Framework\Http\RedirectResponse;
use Catalyst\Framework\View\View;
use Catalyst\Framework\View\TrustedHtml;
use Catalyst\Framework\Route\Router;
use Catalyst\Framework\Session\FlashMessage;
use Catalyst\Framework\Session\SessionManager;
use Catalyst\Framework\Session\ToastQueue;
use Catalyst\Framework\Notification\NotificationBag;
use Catalyst\Framework\Notification\NotificationType;
use Catalyst\Framework\Sensitivity\SensitiveDataPolicy;
use Catalyst\Helpers\Log\Logger;
use Catalyst\Helpers\Validation\Validator;
use Catalyst\Helpers\Exceptions\ForbiddenException;
use Catalyst\Helpers\Exceptions\ValidationException;
use Catalyst\Framework\Authorization\Gate;
use Catalyst\Framework\Authorization\AbilitySubject;
use Catalyst\Framework\Traits\FrontResourceTrait;

/**
 * Base controller for HTTP request handlers.
 *
 * @package Catalyst\Framework\Controllers
 * Responsibility: Provides shared response, view, validation, authorization, notification, and redirect helpers.
 */
abstract class Controller
{
    use FrontResourceTrait;
    /**
     * The request instance
     *
     * @var Request
     */
    protected Request $request;

    /**
     * The logger instance
     *
     * @var Logger
     */
    protected Logger $logger;

    /**
     * The view engine instance
     *
     * @var View
     */
    protected View $viewEngine;

    /**
     * Initializes request, logger, and view engine collaborators.
     *
     * Responsibility: Initializes request, logger, and view engine collaborators.
     */
    public function __construct()
    {
        $this->request = Request::getInstance();
        $this->logger = Logger::getInstance();
        $this->viewEngine = View::getInstance();
    }

    /**
     * Render a view template, optionally wrapped in a layout.
     *
     * Responsibility: Render a view template, optionally wrapped in a layout.
     * @param string      $template Template name (dot notation: "pages.welcome")
     * @param array       $data     Data to pass to the template
     * @param int         $status   HTTP status code
     * @param string|null $layout   Layout name (e.g. 'base', 'admin'), or null for no layout
     * @return Response
     */
    protected function view(string $template, array $data = [], int $status = 200, ?string $layout = null): Response
    {
        $this->deployFrontAssets();

        return $this->viewEngine->render($template, $data, $status, $layout);
    }

    /**
     * Return a JSON response.
     *
     * Responsibility: Return a JSON response.
     * @param array $data Data to encode as JSON
     * @param int $status HTTP status code
     * @return JsonResponse
     */
    protected function json(array $data, int $status = 200): JsonResponse
    {
        return new JsonResponse($data, $status);
    }

    /**
     * Builds an HTML response explicitly marked as trusted fragment content.
     *
     * Responsibility: Builds an HTML response explicitly marked as trusted fragment content.
     */
    protected function trustedHtmlResponse(TrustedHtml|string $html, int $status = 200): Response
    {
        $content = $html instanceof TrustedHtml ? $html->toHtml() : $html;

        return new Response($content, $status, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'X-Catalyst-Fragment-Policy' => JsonResponse::HTML_POLICY_TRUSTED,
        ]);
    }

    /**
     * Return a successful JSON API response.
     *
     * Responsibility: Return a successful JSON API response.
     * @param mixed $data Response data
     * @param string $message Success message
     * @param int $status HTTP status code
     * @return JsonResponse
     */
    protected function jsonSuccess(mixed $data = null, string $message = 'Success', int $status = 200): JsonResponse
    {
        return JsonResponse::api($data, true, $message, $status);
    }

    /**
     * Return an error JSON API response.
     *
     * Responsibility: Return an error JSON API response.
     * @param string $message Error message
     * @param int $status HTTP status code
     * @param mixed $data Additional error data
     * @return JsonResponse
     */
    protected function jsonError(string $message, int $status = 400, mixed $data = null): JsonResponse
    {
        return JsonResponse::api($data, false, $message, $status);
    }

    /**
     * Return a redirect response.
     *
     * Responsibility: Return a redirect response.
     * @param string $url URL to redirect to
     * @param int $status HTTP status code (301 or 302)
     * @return RedirectResponse
     */
    protected function redirect(string $url, int $status = 302): RedirectResponse
    {
        return new RedirectResponse($url, $status);
    }

    /**
     * Check if the request is an AJAX request.
     *
     * Responsibility: Check if the request is an AJAX request.
     * @return bool
     */
    protected function isAjax(): bool
    {
        return $this->request->isAjax();
    }

    /**
     * Check if the request expects a JSON response.
     *
     * Responsibility: Check if the request expects a JSON response.
     * @return bool
     */
    protected function expectsJson(): bool
    {
        return $this->request->expectsJson();
    }

    /**
     * Log an info message.
     *
     * Responsibility: Log an info message.
     * @param string $message Log message
     * @param array $context Additional context
     * @return void
     */
    protected function logInfo(string $message, array $context = []): void
    {
        $this->logger->info($message, $context);
    }

    /**
     * Log an error message.
     *
     * Responsibility: Log an error message.
     * @param string $message Log message
     * @param array $context Additional context
     * @return void
     */
    protected function logError(string $message, array $context = []): void
    {
        $this->logger->error($message, $context);
    }

    /**
     * Log a debug message.
     *
     * Responsibility: Log a debug message.
     * @param string $message Log message
     * @param array $context Additional context
     * @return void
     */
    protected function logDebug(string $message, array $context = []): void
    {
        $this->logger->debug($message, $context);
    }

    /**
     * Log a warning message.
     *
     * Responsibility: Log a warning message.
     * @param string $message Log message
     * @param array $context Additional context
     * @return void
     */
    protected function logWarning(string $message, array $context = []): void
    {
        $this->logger->warning($message, $context);
    }

    /**
     * Return a JSON response for validation errors.
     *
     * Responsibility: Return a JSON response for validation errors.
     * @param array  $errors  Validation errors (field => messages)
     * @param string $message General error message
     * @param int    $status  HTTP status code
     * @return JsonResponse
     */
    protected function jsonValidationError(array $errors, string $message = 'Validation failed', int $status = 422): JsonResponse
    {
        return JsonResponse::validation($errors, $message, $status);
    }

    /**
     * Run validation and return the Validator instance. The caller is responsible for checking $v->fails() and handling errors.
     *
     * Responsibility: Run validation and return the Validator instance. The caller is responsible for checking $v->fails() and handling errors.
     * @param array<string, mixed>                    $data   Input data
     * @param array<string, string|array<int,string>> $rules  Validation rules
     * @param array<string, string>                   $labels Optional field labels
     * @return Validator
     */
    protected function validate(array $data, array $rules, array $labels = []): Validator
    {
        return new Validator($data, $rules, $labels);
    }

    /**
     * Run validation and throw ValidationException if it fails. The ExceptionHandler converts ValidationException to a 422 JSON response.
     *
     * Responsibility: Run validation and throw ValidationException if it fails. The ExceptionHandler converts ValidationException to a 422 JSON response.
     * @param array<string, mixed>                    $data    Input data
     * @param array<string, string|array<int,string>> $rules   Validation rules
     * @param array<string, string>                   $labels  Optional field labels
     * @param string                                  $message General error message
     * @return void
     * @throws ValidationException
     */
    protected function validateOrFail(
        array  $data,
        array  $rules,
        array  $labels = [],
        string $message = 'Validation failed'
    ): void {
        $validator = new Validator($data, $rules, $labels);

        if ($validator->fails()) {
            throw ValidationException::withErrors($validator->errors(), $message);
        }
    }

    /**
     * Assert the current user passes the given ability via Gate or Policy. Throws ForbiddenException (→ 403) if the check fails.
     *
     * Responsibility: Assert the current user passes the given ability via Gate or Policy. Throws ForbiddenException (→ 403) if the check fails.
     * @param string $ability  Gate name or Policy ability (e.g. 'edit', 'edit-post')
     * @param mixed  ...$args  Optional model or extra arguments for the gate/policy
     * @throws ForbiddenException
     */
    protected function authorize(string $ability, mixed ...$args): void
    {
        Gate::getInstance()->authorize($ability, ...$args);
    }

    /**
     * Check if the current user passes the given ability (non-throwing).
     *
     * Responsibility: Check if the current user passes the given ability (non-throwing).
     * @param string $ability  Gate name or Policy ability
     * @param mixed  ...$args  Optional model or extra arguments
     */
    protected function can(string $ability, mixed ...$args): bool
    {
        return Gate::getInstance()->allows($ability, ...$args);
    }

    /**
     * Authorizes an ability against a resource-specific subject wrapper.
     *
     * Responsibility: Authorizes an ability against a resource-specific subject wrapper.
     * @param array<string, mixed> $context
     */
    protected function authorizeResource(string $ability, string $resource, mixed $record = null, array $context = []): void
    {
        $this->authorize($ability, AbilitySubject::make($resource, $record, $context));
    }

    /**
     * Checks a resource-specific ability without throwing an authorization exception.
     *
     * Responsibility: Checks a resource-specific ability without throwing an authorization exception.
     * @param array<string, mixed> $context
     */
    protected function canResource(string $ability, string $resource, mixed $record = null, array $context = []): bool
    {
        return $this->can($ability, AbilitySubject::make($resource, $record, $context));
    }

    /**
     * Return a standardized API response.
     *
     * Responsibility: Return a standardized API response.
     * @param bool $success Whether the operation was successful
     * @param string $message Response message
     * @param mixed $data Response data
     * @param int $status HTTP status code
     * @param array $meta Additional metadata
     * @return JsonResponse
     */
    protected function apiResponse(bool $success, string $message, mixed $data = null, int $status = 200, array $meta = []): JsonResponse
    {
        return JsonResponse::api($data, $success, $message, $status, $meta);
    }

    /**
     * Returns a JSON success response with sensitive fields sanitized for a resource key.
     *
     * Responsibility: Returns a JSON success response with sensitive fields sanitized for a resource key.
     */
    protected function resourceJsonSuccess(
        string $resourceKey,
        mixed $data = null,
        string $message = 'Success',
        int $status = 200,
        array $meta = []
    ): JsonResponse {
        return JsonResponse::api(
            $this->sanitizeResourcePayload($resourceKey, $data),
            true,
            $message,
            $status,
            $meta
        );
    }

    /**
     * Sanitizes arrays or model payloads according to the resource sensitivity policy.
     *
     * Responsibility: Sanitizes arrays or model payloads according to the resource sensitivity policy.
     */
    protected function sanitizeResourcePayload(string $resourceKey, mixed $data): mixed
    {
        if (is_object($data) && method_exists($data, 'toArray')) {
            $data = $data->toArray();
        }

        if (!is_array($data)) {
            return $data;
        }

        if ($this->isSequentialArray($data)) {
            return array_map(
                fn (mixed $item): mixed => $this->sanitizeResourcePayload($resourceKey, $item),
                $data
            );
        }

        return SensitiveDataPolicy::getInstance()->sanitize(
            $resourceKey,
            $data,
            SensitiveDataPolicy::CHANNEL_API
        );
    }

    /**
     * Sanitizes version snapshots and diffs before exposing them through API responses.
     *
     * Responsibility: Sanitizes version snapshots and diffs before exposing them through API responses.
     * @param array<int, array<string, mixed>> $versions
     * @return array<int, array<string, mixed>>
     */
    protected function sanitizeVersionPayloads(string $resourceKey, array $versions): array
    {
        return array_map(function (array $version) use ($resourceKey): array {
            if (is_array($version['snapshot_json'] ?? null)) {
                $version['snapshot_json'] = $this->sanitizeResourcePayload($resourceKey, (array) $version['snapshot_json']);
            }

            if (is_array($version['diff_json'] ?? null)) {
                $policy = SensitiveDataPolicy::getInstance();
                $diff = [];

                foreach ((array) $version['diff_json'] as $field => $change) {
                    if (is_array($change)) {
                        $diff[(string) $field] = [
                            'before' => $policy->sanitizeField($resourceKey, (string) $field, $change['before'] ?? null, SensitiveDataPolicy::CHANNEL_API),
                            'after' => $policy->sanitizeField($resourceKey, (string) $field, $change['after'] ?? null, SensitiveDataPolicy::CHANNEL_API),
                        ];
                        continue;
                    }

                    $diff[(string) $field] = $policy->sanitizeField($resourceKey, (string) $field, $change, SensitiveDataPolicy::CHANNEL_API);
                }

                $version['diff_json'] = $diff;
            }

            return $version;
        }, $versions);
    }

    /**
     * Redirect to a named route.
     *
     * Responsibility: Redirect to a named route.
     * @param string $routeName Name of the route
     * @param array $parameters Route parameters
     * @param int $status HTTP status code (301, 302, 303, 307, 308)
     * @return RedirectResponse
     */
    protected function redirectToRoute(string $routeName, array $parameters = [], int $status = 302): RedirectResponse
    {
        $url = Router::getInstance()->url($routeName, $parameters);
        return new RedirectResponse($url, $status);
    }

    /**
     * Get the flash message instance.
     *
     * Responsibility: Provides the flash message bag used by controllers to enqueue one-shot feedback.
     * @return FlashMessage
     */
    protected function flash(): FlashMessage
    {
        return FlashMessage::getInstance();
    }

    /**
     * Queue an ephemeral toast notification for the next page load. Use for confirmations that don't require the user to re-render a form (logout, save, copy). For validation errors or blocking issues that must stay until re-render, use flash()->error(...) instead.
     *
     * Responsibility: Queue an ephemeral toast notification for the next page load. Use for confirmations that don't require the user to re-render a form (logout, save, copy). For validation errors or blocking issues that must stay until re-render, use flash()->error(...) instead.
     * @param string $type    success | error | warning | info
     * @param string $message Notification text
     * @return ToastQueue For chaining additional toasts on the same queue
     */
    protected function toast(string $type, string $message): ToastQueue
    {
        return ToastQueue::getInstance()->push($type, $message);
    }

    /**
     * Add a success flash message and optionally redirect.
     *
     * Responsibility: Add a success flash message and optionally redirect.
     * @param string $message The success message
     * @param string|null $redirectUrl Optional URL to redirect to
     * @return RedirectResponse|null
     */
    protected function withSuccess(string $message, ?string $redirectUrl = null): ?RedirectResponse
    {
        $this->flash()->success($message);

        if ($redirectUrl !== null) {
            return new RedirectResponse($redirectUrl);
        }

        return null;
    }

    /**
     * Add an error flash message and optionally redirect.
     *
     * Responsibility: Add an error flash message and optionally redirect.
     * @param string $message The error message
     * @param string|null $redirectUrl Optional URL to redirect to
     * @return RedirectResponse|null
     */
    protected function withError(string $message, ?string $redirectUrl = null): ?RedirectResponse
    {
        $this->flash()->error($message);

        if ($redirectUrl !== null) {
            return new RedirectResponse($redirectUrl);
        }

        return null;
    }

    /**
     * Stores non-sensitive old input and validation errors in the session.
     *
     * Responsibility: Stores non-sensitive old input and validation errors in the session.
     * @param array<string, mixed>           $input
     * @param array<string, string[]|string> $errors
     */
    protected function rememberValidationState(array $input, array $errors, string $bag = 'default'): void
    {
        foreach ([
            '_token',
            'csrf_token',
            'password',
            'password_confirm',
            'current_password',
            'new_password',
            'new_password_confirmation',
        ] as $sensitiveField) {
            unset($input[$sensitiveField]);
        }

        SessionManager::getInstance()
            ->flashOldInput($input)
            ->flashValidationErrors($errors, $bag);
    }

    /**
     * Get a request input value.
     *
     * Responsibility: Get a request input value.
     * @param string $key Input key
     * @param mixed $default Default value if not found
     * @return mixed
     */
    protected function input(string $key, mixed $default = null): mixed
    {
        return $this->request->input($key, $default);
    }

    /**
     * Get all request input.
     *
     * Responsibility: Get all request input.
     * @return array
     */
    protected function all(): array
    {
        return $this->request->all();
    }

    /**
     * Get only specific input keys.
     *
     * Responsibility: Get only specific input keys.
     * @param array $keys Keys to retrieve
     * @return array
     */
    protected function only(array $keys): array
    {
        return $this->request->only($keys);
    }

    /**
     * Get all input except specific keys.
     *
     * Responsibility: Get all input except specific keys.
     * @param array $keys Keys to exclude
     * @return array
     */
    protected function except(array $keys): array
    {
        return $this->request->except($keys);
    }

    /**
     * Create a new notification bag.
     *
     * Responsibility: Create a new notification bag.
     * @return NotificationBag
     */
    protected function notify(): NotificationBag
    {
        return new NotificationBag();
    }

    /**
     * Create a notification bag with a toaster.
     *
     * Responsibility: Create a notification bag with a toaster.
     * @param string $type Notification type (success, error, warning, info)
     * @param string $message Message content
     * @param array $options Additional options (title, duration, icon, etc.)
     * @return NotificationBag
     */
    protected function toaster(string $type, string $message, array $options = []): NotificationBag
    {
        return (new NotificationBag())->toaster($type, $message, $options);
    }

    /**
     * Create a notification bag with a modal.
     *
     * Responsibility: Create a notification bag with a modal.
     * @param string $url URL to load modal content from
     * @param array $options Modal options (title, size, backdrop, etc.)
     * @return NotificationBag
     */
    protected function modal(string $url, array $options = []): NotificationBag
    {
        return (new NotificationBag())->modal($url, $options);
    }

    /**
     * Return a JSON success response with a success toaster.
     *
     * Responsibility: Return a JSON success response with a success toaster.
     * @param mixed $data Response data
     * @param string $message Success message
     * @param int $status HTTP status code
     * @param array $toasterOptions Toaster options
     * @return JsonResponse
     */
    protected function jsonSuccessWithToast(
        mixed  $data = null,
        string $message = 'Success',
        int    $status = 200,
        array  $toasterOptions = []
    ): JsonResponse
    {
        return JsonResponse::api($data, true, $message, $status)
            ->withNotification($this->toaster('success', $message, $toasterOptions));
    }

    /**
     * Return a JSON error response with an error toaster.
     *
     * Responsibility: Return a JSON error response with an error toaster.
     * @param string $message Error message
     * @param int $status HTTP status code
     * @param mixed $data Additional error data
     * @param array $toasterOptions Toaster options
     * @return JsonResponse
     */
    protected function jsonErrorWithToast(
        string $message,
        int    $status = 400,
        mixed  $data = null,
        array  $toasterOptions = []
    ): JsonResponse
    {
        return JsonResponse::api($data, false, $message, $status)
            ->withNotification($this->toaster('error', $message, $toasterOptions));
    }

    /**
     * Builds a success response for post-action redirects across HTML and JSON flows.
     *
     * Responsibility: Builds a success response for post-action redirects across HTML and JSON flows.
     */
    protected function postActionSuccessRedirect(
        string $url,
        string $message,
        mixed $data = null,
        int $delay = 300,
        int $status = 200
    ): Response {
        return $this->postActionRedirect($url, $message, true, $status, $data, $delay);
    }

    /**
     * Builds an error response for post-action redirects across HTML and JSON flows.
     *
     * Responsibility: Builds an error response for post-action redirects across HTML and JSON flows.
     */
    protected function postActionErrorRedirect(
        string $url,
        string $message,
        int $status = 422,
        mixed $data = null,
        int $delay = 0
    ): Response {
        return $this->postActionRedirect($url, $message, false, $status, $data, $delay);
    }

    /**
     * Determines whether an array uses sequential numeric keys.
     *
     * Responsibility: Determines whether an array uses sequential numeric keys.
     * @param array<int|string, mixed> $value
     */
    private function isSequentialArray(array $value): bool
    {
        return array_keys($value) === range(0, count($value) - 1);
    }

    /**
     * Applies toast or flash state and returns the appropriate redirect response shape.
     *
     * Responsibility: Applies toast or flash state and returns the appropriate redirect response shape.
     */
    private function postActionRedirect(
        string $url,
        string $message,
        bool $success,
        int $status,
        mixed $data,
        int $delay
    ): Response {
        if ($this->expectsJson()) {
            if ($delay <= 0) {
                if ($success) {
                    $this->toast('success', $message);
                } else {
                    $this->flash()->error($message);
                }
            }

            $response = $success
                ? $this->jsonSuccessWithToast($data, $message, $status)
                : $this->jsonErrorWithToast($message, $status, $data);

            return $response->withRedirect($url, max(0, $delay));
        }

        if ($success) {
            $this->toast('success', $message);
        } else {
            $this->flash()->error($message);
        }

        return $this->redirect($url);
    }
}
