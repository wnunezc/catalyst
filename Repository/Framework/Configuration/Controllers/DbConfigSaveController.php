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
use Catalyst\Framework\Http\JsonResponse;
use Catalyst\Framework\Http\Response;
use Catalyst\Framework\Notification\NotificationBag;
use Catalyst\Repository\Configuration\Requests\DbConfigRequest;
use Catalyst\Repository\Configuration\Support\DbConfigWriter;

/**
 * Persists database settings and reports connectivity readiness.
 *
 * @package Catalyst\Repository\Configuration\Controllers
 * Responsibility: Delegates validated database writes and surfaces connectivity warnings without discarding saved configuration.
 */
final class DbConfigSaveController extends Controller
{
    /**
     * Initializes the Db Config Save Controller instance.
     *
     * Responsibility: Initializes the Db Config Save Controller instance.
     */
    public function __construct(
        private readonly DbConfigWriter $writer = new DbConfigWriter()
    ) {
        parent::__construct();
    }

    /**
     * Saves validated database settings and reports the probe outcome.
     *
     * Responsibility: Saves validated database settings and reports the probe outcome.
     */
    public function saveDb(DbConfigRequest $request): Response
    {
        $probe = $this->writer->save($request->validated());

        if ($probe === 'ok' || $probe === 'db_missing') {
            return $this->jsonSuccessWithToast(null, __('settings.messages.saved'));
        }

        $bag = new NotificationBag();
        $bag->toaster('success', __('settings.messages.saved'))
            ->toaster('warning', __('settings.completion.errors.db_unreachable'));

        return JsonResponse::api(null, true, __('settings.messages.saved'), 200)
            ->withNotification($bag);
    }
}
