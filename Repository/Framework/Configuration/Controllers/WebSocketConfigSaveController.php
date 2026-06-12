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
use Catalyst\Repository\Configuration\Requests\WebSocketConfigRequest;
use Catalyst\Repository\Configuration\Support\WebSocketConfigWriter;

/**
 * Persists WebSocket settings submitted by the setup surface.
 *
 * @package Catalyst\Repository\Configuration\Controllers
 * Responsibility: Delegates validated WebSocket configuration writes and returns the setup AJAX response.
 */
final class WebSocketConfigSaveController extends Controller
{
    /**
     * Initializes the Web Socket Config Save Controller instance.
     *
     * Responsibility: Initializes the Web Socket Config Save Controller instance.
     */
    public function __construct(
        private readonly WebSocketConfigWriter $writer = new WebSocketConfigWriter()
    ) {
        parent::__construct();
    }

    /**
     * Saves validated WebSocket settings.
     *
     * Responsibility: Saves validated WebSocket settings.
     */
    public function saveWebSocket(WebSocketConfigRequest $request): Response
    {
        $this->writer->save($request->validated());

        return $this->jsonSuccessWithToast(null, __('settings.messages.saved'));
    }
}
