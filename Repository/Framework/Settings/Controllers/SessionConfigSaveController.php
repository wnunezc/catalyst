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
use Catalyst\Repository\Settings\Requests\SessionConfigRequest;
use Catalyst\Repository\Settings\Support\SessionConfigWriter;

/**
 * Persists session settings submitted by the setup surface.
 *
 * @package Catalyst\Repository\Settings\Controllers
 * Responsibility: Delegates validated session configuration writes and returns the setup AJAX response.
 */
final class SessionConfigSaveController extends Controller
{
    /**
     * Initializes the Session Config Save Controller instance.
     *
     * Responsibility: Initializes the Session Config Save Controller instance.
     */
    public function __construct(
        private readonly SessionConfigWriter $writer = new SessionConfigWriter()
    ) {
        parent::__construct();
    }

    /**
     * Saves validated session settings.
     *
     * Responsibility: Saves validated session settings.
     */
    public function saveSession(SessionConfigRequest $request): Response
    {
        $this->writer->save($request->validated());

        return $this->jsonSuccessWithToast(null, __('settings.messages.saved'));
    }
}
