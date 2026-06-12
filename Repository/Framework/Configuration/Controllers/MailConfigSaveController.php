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
use Catalyst\Repository\Configuration\Requests\MailConfigRequest;
use Catalyst\Repository\Configuration\Support\MailConfigWriter;

/**
 * Persists mail settings submitted by the setup surface.
 *
 * @package Catalyst\Repository\Configuration\Controllers
 * Responsibility: Delegates validated mail configuration writes and returns the setup AJAX response.
 */
final class MailConfigSaveController extends Controller
{
    /**
     * Initializes the Mail Config Save Controller instance.
     *
     * Responsibility: Initializes the Mail Config Save Controller instance.
     */
    public function __construct(
        private readonly MailConfigWriter $writer = new MailConfigWriter()
    ) {
        parent::__construct();
    }

    /**
     * Saves validated mail settings.
     *
     * Responsibility: Saves validated mail settings.
     */
    public function saveMail(MailConfigRequest $request): Response
    {
        $this->writer->save($request->validated());

        return $this->jsonSuccessWithToast(null, __('settings.messages.saved'));
    }
}
