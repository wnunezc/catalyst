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
use Catalyst\Repository\Settings\Services\SetupAdminProvisioner;
use Catalyst\Repository\Settings\Services\SetupDatabaseException;
use Catalyst\Repository\Settings\Services\SetupDatabaseService;
use Throwable;

/**************************************************************************************
 * SetupCompletionController — setup admin provisioning + finalization.
 *
 * This is the ONLY controller that flips `app.project.project_config` to true.
 * Partial setup save controllers must never touch that flag, so the
 * framework does not lock itself out of login while configuration is still
 * incomplete (e.g. DB not reachable yet).
 *
 * Responsibilities:
 *   1. createAdmin() provisions the initial active administrator account.
 *   2. complete() flips project_config=true only after an active admin exists.
 *
 * Validation battery run by complete():
 *   1. Reject if already configured.
 *   2. app.json and db.json exist on disk.
 *   3. Database is reachable (auto-create DB if missing).
 *   4. Core auth tables exist (`users`, `roles`, `user_roles`).
 *   5. At least one active admin user exists.
 *   6. Write project_config=true to app.json.
 *
 * Route:
 *   POST /configuration/environment-setup/admin    → createAdmin()
 *   POST /configuration/environment-setup/complete → complete()
 *
 * @package Catalyst\Repository\Settings\Controllers
 **************************************************************************************/
/**
 * Defines the Setup Completion Controller class contract.
 *
 * @package Catalyst\Repository\Settings\Controllers
 * Responsibility: Coordinates the setup completion controller behavior within its module boundary.
 */
class SetupCompletionController extends Controller
{

    private SetupDatabaseService $setupDatabase;
    private SetupAdminProvisioner $adminProvisioner;

    /**
     * Initializes the Setup Completion Controller instance.
     */
    public function __construct()
    {
        parent::__construct();

        $this->setupDatabase = SetupDatabaseService::make();
        $this->adminProvisioner = SetupAdminProvisioner::make();
    }

    /**
     * Create the initial active administrator account without finalizing setup.
     *
     * @param Request $request
     * @return Response  JSON only (AJAX endpoint)
     */
    public function createAdmin(Request $request): Response
    {
        $cfg = ConfigManager::getInstance();

        if ($cfg->isConfigured()) {
            return $this->jsonErrorWithToast(
                __('settings.completion.errors.already_configured'),
                409
            );
        }

        try {
            $pdo = $this->setupDatabase->open();
        } catch (SetupDatabaseException $e) {
            $this->logError('SetupCompletion: setup database unavailable', [
                'error' => $e->detail() ?: $e->translationKey(),
            ]);

            return $this->jsonErrorWithToast(
                $e->translatedMessage(),
                $e->httpStatus()
            );
        }

        if ($this->adminProvisioner->adminExists($pdo)) {
            return $this->jsonSuccessWithToast(
                ['admin_exists' => true],
                __('settings.completion.admin_exists_success')
            )->withRefresh(800);
        }

        $adminName     = trim((string)$request->input('admin_name', ''));
        $adminEmail    = trim((string)$request->input('admin_email', ''));
        $adminPass     = (string)$request->input('admin_password', '');
        $adminPassConf = (string)$request->input('admin_password_confirm', '');

        $validator = $this->validate(
            [
                'admin_name'             => $adminName,
                'admin_email'            => $adminEmail,
                'admin_password'         => $adminPass,
                'admin_password_confirm' => $adminPassConf,
            ],
            [
                'admin_name'             => 'required|min:2|max:255',
                'admin_email'            => 'required|email|max:255',
                'admin_password'         => 'required|min:8|max:128',
                'admin_password_confirm' => 'required',
            ]
        );

        if ($validator->fails()) {
            return $this->jsonValidationError($validator->errors());
        }

        if ($adminPass !== $adminPassConf) {
            return $this->jsonValidationError([
                'admin_password_confirm' => __('settings.completion.errors.password_mismatch'),
            ]);
        }

        if ($this->adminProvisioner->userExistsByEmail($pdo, $adminEmail)) {
            return $this->jsonValidationError([
                'admin_email' => __('settings.completion.errors.admin_email_exists'),
            ]);
        }

        try {
            $this->adminProvisioner->ensureAdminRole($pdo);
        } catch (Throwable $e) {
            return $this->jsonErrorWithToast(
                __('settings.completion.errors.admin_create_failed') . ' — ' . $e->getMessage(),
                500
            );
        }

        try {
            $this->adminProvisioner->createAdmin($adminName, $adminEmail, $adminPass);
        } catch (Throwable $e) {
            return $this->jsonErrorWithToast(
                __('settings.completion.errors.admin_create_failed') . ' — ' . $e->getMessage(),
                500
            );
        }

        if (!$this->adminProvisioner->adminExists($pdo)) {
            return $this->jsonErrorWithToast(
                __('settings.completion.errors.admin_create_failed'),
                500
            );
        }

        return $this->jsonSuccessWithToast(
            ['admin_created' => true],
            __('settings.completion.admin_create_success')
        )->withRefresh(800);
    }

    /**
     * Finalize the setup wizard.
     *
     * @param Request $request
     * @return Response  JSON only (AJAX endpoint)
     */
    public function complete(Request $request): Response
    {
        $cfg = ConfigManager::getInstance();

        // -- 1. Reject if already configured ---------------------------------
        if ($cfg->isConfigured()) {
            return $this->jsonErrorWithToast(
                __('settings.completion.errors.already_configured'),
                409
            );
        }

        try {
            $pdo = $this->setupDatabase->open();
        } catch (SetupDatabaseException $e) {
            $this->logError('SetupCompletion: setup database unavailable during completion', [
                'error' => $e->detail() ?: $e->translationKey(),
            ]);

            return $this->jsonErrorWithToast(
                $e->translatedMessage(),
                $e->httpStatus()
            );
        }

        if (!$this->adminProvisioner->adminExists($pdo)) {
            return $this->jsonErrorWithToast(
                __('settings.completion.errors.admin_required'),
                422
            );
        }

        // -- 6. Flip project_config=true in app.json -------------------------
        $appProject                   = $cfg->section('app')['project'] ?? [];
        $appProject['project_config'] = true;
        $cfg->writeSection('app', ['project' => $appProject]);

        return $this->jsonSuccessWithToast(
            ['admin_created' => false],
            __('settings.completion.success')
        )->withRedirect('/login', 1500);
    }

    /**
     * Reset the setup wizard — flips project_config back to false so the
     * finalize form becomes available again. Admin-only (enforced via route middleware).
     *
     * This does NOT erase any existing config values; it only unlocks the wizard.
     *
     * @param Request $request
     * @return Response  JSON only (AJAX endpoint)
     */
    public function resetConfig(Request $request): Response
    {
        $cfg = ConfigManager::getInstance();

        if (!$cfg->isConfigured()) {
            return $this->jsonErrorWithToast(
                __('settings.completion.errors.not_yet_configured'),
                409
            );
        }

        $appProject                   = $cfg->section('app')['project'] ?? [];
        $appProject['project_config'] = false;
        $cfg->writeSection('app', ['project' => $appProject]);

        return $this->jsonSuccessWithToast(
            null,
            __('settings.completion.reset_success')
        )->withRedirect('/configuration/environment-setup', 1000);
    }
}
