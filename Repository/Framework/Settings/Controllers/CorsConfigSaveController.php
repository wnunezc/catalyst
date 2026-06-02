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

namespace Catalyst\Repository\Settings\Controllers;

use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Catalyst\Helpers\Config\ConfigManager;

/**
 * CorsConfigSaveController — persists CORS feature configuration.
 *
 * Route:
 *   POST /setup/cors → saveCors()
 *
 * Accepts comma-separated strings for list fields (origins, methods, headers)
 * and writes them as arrays to cors.json via ConfigManager.
 *
 * @package Catalyst\Repository\Settings\Controllers
 */
class CorsConfigSaveController extends Controller
{
    /**
     * Persists the current state.
     */
    public function saveCors(Request $request): Response
    {
        $originsRaw = trim((string)$request->input('cors_allowed_origins', $request->input('allowed_origins', '*')));
        $methodsRaw = trim((string)$request->input('cors_allowed_methods', $request->input('allowed_methods', 'GET,POST,PUT,PATCH,DELETE,OPTIONS')));
        $headersRaw = trim((string)$request->input('cors_allowed_headers', $request->input('allowed_headers', 'Content-Type,Authorization,X-Requested-With,X-CSRF-TOKEN')));
        $exposedRaw = trim((string)$request->input('cors_exposed_headers', $request->input('exposed_headers', '')));

        $origins = array_values(array_filter(array_map('trim', explode(',', $originsRaw))));
        $methods = array_values(array_map(
            'strtoupper',
            array_filter(array_map('trim', explode(',', $methodsRaw)))
        ));
        $headers = array_values(array_filter(array_map('trim', explode(',', $headersRaw))));
        $exposed = $exposedRaw !== ''
            ? array_values(array_filter(array_map('trim', explode(',', $exposedRaw))))
            : [];

        $data = [
            'cors_max_age' => (string)$request->input('cors_max_age', $request->input('max_age', '86400')),
        ];

        $validator = $this->validate($data, [
            'cors_max_age' => 'required|integer|min_value:0',
        ]);

        if ($validator->fails()) {
            return $this->jsonValidationError($validator->errors());
        }

        ConfigManager::getInstance()->writeSection('cors', [
            'cors' => [
                'enabled'           => $this->booleanFlag($request, 'cors_enabled'),
                'allowed_origins'   => $origins ?: ['*'],
                'allowed_methods'   => $methods ?: ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
                'allowed_headers'   => $headers,
                'exposed_headers'   => $exposed,
                'allow_credentials' => $this->booleanFlag($request, 'cors_allow_credentials')
                    || $this->booleanFlag($request, 'allow_credentials'),
                'max_age'           => max(0, (int)$data['cors_max_age']),
            ],
        ]);

        return $this->jsonSuccessWithToast(null, __('settings.messages.saved'));
    }

    /**
     * Handles the boolean flag workflow.
     */
    private function booleanFlag(Request $request, string $key, bool $default = false): bool
    {
        return in_array((string) $request->input($key, $default ? '1' : '0'), ['1', 'true', 'on', 'yes'], true);
    }
}
