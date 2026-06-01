# Catalyst\Framework\Controllers

## Class: Controller (Abstract)
**File**: app/Framework/Controllers/Controller.php
**Namespace**: Catalyst\Framework\Controllers
**Type**: Abstract Class
**Purpose**: Base controller class that all controllers should extend

### Runtime Notes
- `RouteDispatcher` resolves controller constructors through the lightweight container, so typed service dependencies can now be injected progressively without replacing the framework bootstrap model.
- Typed `FormRequest` parameters are auto-resolved before the controller method runs; they validate themselves and throw `ValidationException` / `ForbiddenException` when needed.

### Properties
- `$request: Request` - **protected** - Request instance
- `$logger: Logger` - **protected** - Logger instance
- `$viewEngine: View` - **protected** - View instance (renamed from `$view` to avoid conflict with `view()` method)

### Protected Methods - Response Generation
- `view(string $template, array $data = [], int $status = 200, ?string $layout = null): Response` - Render a view template, optionally wrapped in a layout
- `json(array $data, int $status = 200): JsonResponse` - Return JSON response
- `jsonSuccess(mixed $data = null, string $message = 'Success', int $status = 200): JsonResponse` - Return success JSON
- `jsonError(string $message, int $status = 400, mixed $data = null): JsonResponse` - Return error JSON
- `jsonValidationError(array $errors, string $message = 'Validation failed', int $status = 422): JsonResponse` - Return validation errors
- `apiResponse(bool $success, string $message, mixed $data = null, int $status = 200, array $meta = []): JsonResponse` - Standardized API response
- `validate(array $data, array $rules, array $labels = []): Validator` - Run validation, return Validator instance for manual checking
- `validateOrFail(array $data, array $rules, array $labels = [], string $message = 'Validation failed'): void` - Run validation, throw `ValidationException` on failure (ExceptionHandler converts to 422 JSON)
- `redirect(string $url, int $status = 302): RedirectResponse` - Return redirect response
- `redirectToRoute(string $routeName, array $parameters = [], int $status = 302): RedirectResponse` - Redirect to named route

### Protected Methods - Flash Messages
- `flash(): FlashMessage` - Get FlashMessage instance
- `withSuccess(string $message, ?string $redirectUrl = null): ?RedirectResponse` - Flash success + optional redirect
- `withError(string $message, ?string $redirectUrl = null): ?RedirectResponse` - Flash error + optional redirect

### Protected Methods - Request Helpers
- `input(string $key, mixed $default = null): mixed` - Get request input value
- `all(): array` - Get all request input
- `only(array $keys): array` - Get specific input keys
- `except(array $keys): array` - Get all except specific keys
- `isAjax(): bool` - Check if request is AJAX
- `expectsJson(): bool` - Check if request expects JSON

### Protected Methods - Logging
- `logInfo(string $message, array $context = []): void` - Log info message
- `logError(string $message, array $context = []): void` - Log error message
- `logDebug(string $message, array $context = []): void` - Log debug message
- `logWarning(string $message, array $context = []): void` - Log warning message

### Protected Methods - Notifications (NEW)
- `notify(): NotificationBag` - Create a new notification bag for chaining
- `toaster(string $type, string $message, array $options = []): NotificationBag` - Create notification bag with toaster
- `modal(string $url, array $options = []): NotificationBag` - Create notification bag with modal
- `jsonSuccessWithToast(mixed $data, string $message, int $status, array $toasterOptions): JsonResponse` - JSON success with toaster
- `jsonErrorWithToast(string $message, int $status, mixed $data, array $toasterOptions): JsonResponse` - JSON error with toaster

### Usage Notes
- All application controllers MUST extend this class
- Provides common helper methods for response generation
- Auto-injects Request, Logger, and View instances
- Constructor injection is now supported for controller dependencies that the container can resolve (plain instantiable classes plus singleton-style services exposing `getInstance()`)

### Usage Examples
```php
class UserController extends Controller
{
    public function store(): Response
    {
        $data = $this->only(['name', 'email']);

        // ... save user ...

        // With flash message and redirect
        return $this->withSuccess('User created!', '/users');

        // Or JSON for API
        if ($this->expectsJson()) {
            return $this->jsonSuccess($user, 'User created');
        }
    }
}
```
