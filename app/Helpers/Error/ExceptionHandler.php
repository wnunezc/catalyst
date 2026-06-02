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

namespace Catalyst\Helpers\Error;

use Catalyst\Framework\Http\ErrorResponseFactory;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Session\FlashMessage;
use Catalyst\Framework\Session\SessionManager;
use Exception;
use Catalyst\Framework\Traits\{SingletonTrait, OutputCleanerTrait};
use Catalyst\Helpers\Exceptions\ForbiddenException;
use Catalyst\Helpers\Exceptions\ValidationException;
use Throwable;

/**
 * Class that handles registered Exceptions.
 *
 * @package Catalyst\Helpers\Error;
 * Responsibility: Converts framework exceptions into HTTP, JSON or diagnostic error responses.
 */
class ExceptionHandler
{
    use SingletonTrait;

    use OutputCleanerTrait;

    /**
     * Exception handler. Captures and handles exceptions thrown in the application.
     *
     * Responsibility: Exception handler. Captures and handles exceptions thrown in the application.
     * @param Throwable $exception The captured exception.
     * @return void
     * @throws Exception
     */
    public function handle(Throwable $exception): void
    {
        // Clean any output already sent
        $this->cleanOutput();

        // -- ForbiddenException → 403 --------------------------------------
        if ($exception instanceof ForbiddenException) {
            $isAjax = (
                (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
                || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'))
            );

            if (!IS_CLI && !headers_sent()) {
                http_response_code(403);
            }

            if ($isAjax) {
                if (!IS_CLI && !headers_sent()) {
                    header('Content-Type: application/json; charset=UTF-8');
                }
                echo json_encode([
                    'success' => false,
                    'message' => 'Forbidden.',
                    'errors'  => [],
                ]);
            } else {
                ErrorResponseFactory::forbidden(__('ui.errors.403_message'))->send();
            }

            return;
        }

        // -- ValidationException → 422 JSON response -----------------------
        if ($exception instanceof ValidationException) {
            $request = Request::getInstance();
            $isAjax = $request->isAjax() || $request->expectsJson();

            if (!$isAjax) {
                SessionManager::getInstance()
                    ->flashOldInput($exception->getOldInput())
                    ->flashValidationErrors($exception->getErrors(), $exception->getErrorBag());

                $firstMessage = null;
                foreach ($exception->getErrors() as $messages) {
                    if (is_array($messages) && $messages !== []) {
                        $firstMessage = (string) $messages[0];
                        break;
                    }
                }

                FlashMessage::getInstance()->error($firstMessage ?? $exception->getMessage());

                if (!IS_CLI && !headers_sent()) {
                    header('Location: ' . $this->resolveBackRedirect());
                }

                return;
            }

            if (!IS_CLI && !headers_sent()) {
                http_response_code(422);
                header('Content-Type: application/json; charset=UTF-8');
            }

            echo json_encode([
                'success' => false,
                'message' => $exception->getMessage(),
                'errors'  => $exception->getErrors(),
            ]);

            return;
        }

        // Set HTTP 500 for uncaught exceptions in web context
        if (!IS_CLI && !headers_sent()) {
            http_response_code(500);
        }

        // Prepare error data
        $errorArray = [
            'class' => 'ExceptionHandler',
            'type' => ($exception->getCode() === 0 ? 'Uncaught Exception' : "Exception (Code: {$exception->getCode()})"),
            'description' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTrace(),
        ];

        $bug_output = ErrorOutput::getInstance();

        // Generate backtrace
        $errorArray['trace_msg'] = $bug_output->formatBacktrace($errorArray);

        // Display error
        $bug_output->display($errorArray);
    }

    /**
     * Resolves a safe redirect target after non-AJAX validation failures.
     *
     * Responsibility: Resolves a safe redirect target after non-AJAX validation failures.
     */
    private function resolveBackRedirect(): string
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '';

        if (is_string($referer) && $referer !== '') {
            $path = parse_url($referer, PHP_URL_PATH);
            $query = parse_url($referer, PHP_URL_QUERY);

            if (is_string($path) && str_starts_with($path, '/')) {
                return $query !== null && $query !== ''
                    ? $path . '?' . $query
                    : $path;
            }
        }

        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url((string) $uri, PHP_URL_PATH);

        return is_string($path) && str_starts_with($path, '/')
            ? $path
            : '/';
    }
}
