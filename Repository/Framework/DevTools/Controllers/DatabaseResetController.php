<?php

declare(strict_types=1);

/**
 * Catalyst PHP Framework
 * PHP Version 8.4 (Required)
 *
 * @package   Catalyst\Repository\DevTools\Controllers
 * @author    Walter Nuñez (arcanisgk) <icarosnet@gmail.com>
 * @copyright 2023 - 2025
 * @license   http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 * @link      https://catalyst.dock Local development URL
 */

namespace Catalyst\Repository\DevTools\Controllers;

use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\Response;
use Catalyst\Repository\DevTools\Services\DatabaseResetService;
use Throwable;

/**
 * DatabaseResetController — development-only database reset endpoint.
 *
 * The controller keeps HTTP orchestration thin. Destructive reset details live
 * in DatabaseResetService so SQL/file replay behavior is not embedded directly
 * in the web layer.
 */
class DatabaseResetController extends Controller
{
    /**
     * POST /test-features/db-reset
     *
     * Drops all known tables then re-creates and seeds them via the canonical
     * database SQL and pending migrations. Accessible only in development mode.
     */
    public function reset(): Response
    {
        if (!defined('IS_DEVELOPMENT') || !IS_DEVELOPMENT) {
            return $this->postActionErrorRedirect('/test-features', __('devtools.database_runtime.reset_dev_only'), 403);
        }

        try {
            (new DatabaseResetService())->reset();

            return $this->postActionSuccessRedirect('/test-features', __('devtools.database_runtime.reset_success'));
        } catch (Throwable $e) {
            return $this->postActionErrorRedirect('/test-features', __('devtools.database_runtime.reset_failed_prefix') . $e->getMessage(), 500);
        }
    }
}
