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
 * LoginThrottleMiddleware — IP-based brute force protection for auth routes.
 *
 */

namespace Catalyst\Framework\Middleware;

use Catalyst\Framework\Http\ErrorResponseFactory;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Catalyst\Framework\Http\JsonResponse;
use Closure;

/**************************************************************************************
 * Login Throttle Middleware
 *
 * Protects POST /login and POST /register from brute-force attacks.
 * Tracks request counts per IP address using a JSON file in storage.
 *
 * Limits:
 *   - 5 attempts per 10 minutes per IP
 *   - 10-minute lockout once limit is exceeded
 *
 * Storage: boot-core/storage/throttle/login_attempts.json
 * Key: SHA-256 of client IP (never stores raw IPs)
 *
 * @package Catalyst\Framework\Middleware
 */
class LoginThrottleMiddleware extends CoreMiddleware
{
    private const MAX_ATTEMPTS = 5;
    private const WINDOW_SECONDS = 600;   // 10 minutes
    private const LOCKOUT_SECONDS = 600;  // 10 minutes

    private string $storageFile;

    public function __construct()
    {
        $throttleDir = implode(DS, [PD, 'boot-core', 'storage', 'throttle']);

        if (!is_dir($throttleDir)) {
            mkdir($throttleDir, 0755, true);
        }

        $this->storageFile = $throttleDir . DS . 'login_attempts.json';
    }

    public function process(Request $request, Closure $next): Response
    {
        if (defined('IS_DEVELOPMENT') && IS_DEVELOPMENT) {
            return $this->passToNext($request, $next);
        }

        $ipKey = hash('sha256', $this->getClientIp());
        $data  = $this->loadData();
        $now   = time();

        // -- Check lockout -----------------------------------------------------
        if (isset($data[$ipKey])) {
            $entry = $data[$ipKey];

            // Still in lockout period?
            if (!empty($entry['locked_until']) && $entry['locked_until'] > $now) {
                $remaining = (int)ceil(($entry['locked_until'] - $now) / 60);
                return $this->tooManyAttemptsResponse($remaining);
            }

            // Window expired → reset entry
            if ($entry['window_start'] + self::WINDOW_SECONDS <= $now) {
                unset($data[$ipKey]);
                $this->saveData($data);
            }
        }

        // -- Count this attempt ------------------------------------------------
        if (!isset($data[$ipKey])) {
            $data[$ipKey] = [
                'count'        => 0,
                'window_start' => $now,
                'locked_until' => null,
            ];
        }

        $data[$ipKey]['count']++;

        if ($data[$ipKey]['count'] >= self::MAX_ATTEMPTS) {
            $data[$ipKey]['locked_until'] = $now + self::LOCKOUT_SECONDS;
            $this->saveData($data);
            return $this->tooManyAttemptsResponse((int)ceil(self::LOCKOUT_SECONDS / 60));
        }

        $this->saveData($data);

        return $this->passToNext($request, $next);
    }

    // -- Private helpers -------------------------------------------------------

    private function tooManyAttemptsResponse(int $minutesRemaining): Response
    {
        $message = sprintf(
            'Too many attempts. Please try again in %d minute%s.',
            $minutesRemaining,
            $minutesRemaining === 1 ? '' : 's'
        );

        if ($this->isAjaxRequest(Request::getInstance()) || $this->expectsJson(Request::getInstance())) {
            $response = JsonResponse::error($message, null, 429);
            if (!headers_sent()) {
                http_response_code(429);
                header('Retry-After: ' . self::LOCKOUT_SECONDS);
            }
            return $response;
        }

        return ErrorResponseFactory::tooManyRequests($message, self::LOCKOUT_SECONDS);
    }


    private function loadData(): array
    {
        if (!file_exists($this->storageFile)) {
            return [];
        }

        $json = file_get_contents($this->storageFile);
        if ($json === false || $json === '') {
            return [];
        }

        return json_decode($json, true) ?? [];
    }

    private function saveData(array $data): void
    {
        // Prune entries with expired windows to keep file small
        $now = time();
        foreach ($data as $key => $entry) {
            $lockExpired   = empty($entry['locked_until']) || $entry['locked_until'] <= $now;
            $windowExpired = $entry['window_start'] + self::WINDOW_SECONDS <= $now;
            if ($lockExpired && $windowExpired) {
                unset($data[$key]);
            }
        }

        file_put_contents(
            $this->storageFile,
            json_encode($data, JSON_PRETTY_PRINT),
            LOCK_EX
        );
    }
}
