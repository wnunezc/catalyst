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
use Catalyst\Framework\Http\Response;
use Catalyst\Repository\Settings\Requests\DevToolsConfigRequest;
use Catalyst\Repository\Settings\Support\DevToolsConfigWriter;

/**
 * Persists developer-tool compatibility settings.
 *
 * @package Catalyst\Repository\Settings\Controllers
 * Responsibility: Delegates validated debug and log-display writes and returns the setup AJAX response.
 */
final class DevToolsConfigSaveController extends Controller
{
    /**
     * Initializes the Dev Tools Config Save Controller instance.
     *
     * Responsibility: Initializes the Dev Tools Config Save Controller instance.
     */
    public function __construct(
        private readonly DevToolsConfigWriter $writer = new DevToolsConfigWriter()
    ) {
        parent::__construct();
    }

    /**
     * Saves validated developer-tool settings.
     *
     * Responsibility: Saves validated developer-tool settings.
     */
    public function saveDevTools(DevToolsConfigRequest $request): Response
    {
        $this->writer->save($request->validated());

        return $this->jsonSuccessWithToast(null, __('settings.messages.saved'));
    }
}
