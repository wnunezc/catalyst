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

namespace Catalyst\Repository\Configuration\Controllers;

use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\Response;
use Catalyst\Helpers\Config\ConfigManager;
use Catalyst\Repository\Configuration\Requests\CorsConfigRequest;

/**
 * CorsConfigSaveController — persists CORS feature configuration.
 *
 * Route:
 *   POST /configuration/environment-setup/cors → saveCors()
 *
 * Accepts comma-separated strings for list fields (origins, methods, headers)
 * and writes them as arrays to cors.json via ConfigManager.
 *
 * @package Catalyst\Repository\Configuration\Controllers
 * Responsibility: Validates and persists CORS policy values submitted by the setup surface.
 */
class CorsConfigSaveController extends Controller
{
    /**
     * Normalizes, validates and saves CORS settings.
     *
     * Responsibility: Normalizes, validates and saves CORS settings.
     */
    public function saveCors(CorsConfigRequest $request): Response
    {
        ConfigManager::getInstance()->writeSection('cors', [
            'cors' => $request->validated(),
        ]);

        return $this->jsonSuccessWithToast(null, __('settings.messages.saved'));
    }
}
