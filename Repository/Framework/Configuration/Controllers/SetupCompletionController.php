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
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;
use Catalyst\Helpers\Config\ConfigManager;
use Catalyst\Repository\Configuration\Requests\SetupPrivilegedAccountRequest;
use Catalyst\Repository\Configuration\Services\SetupPrivilegedAccountProvisioner;
use Catalyst\Repository\Configuration\Services\SetupDatabaseException;
use Catalyst\Repository\Configuration\Services\SetupDatabaseService;
use Throwable;

/**
 * Provisions the initial privileged account and finalizes environment setup.
 *
 * @package Catalyst\Repository\Configuration\Controllers
 * Responsibility: Creates the first privileged account, validates setup readiness and toggles the configured state.
 */
class SetupCompletionController extends Controller
{

    private SetupDatabaseService $setupDatabase;
    private SetupPrivilegedAccountProvisioner $privilegedAccountProvisioner;

    /**
     * Initializes the Setup Completion Controller instance.
     *
     * Responsibility: Initializes the Setup Completion Controller instance.
     */
    public function __construct()
    {
        parent::__construct();

        $this->setupDatabase = SetupDatabaseService::make();
        $this->privilegedAccountProvisioner = SetupPrivilegedAccountProvisioner::make();
    }

    /**
     * Create the initial active privileged account without finalizing setup.
     *
     * Responsibility: Create the initial active privileged account without finalizing setup.
     * @param Request $request
     * @return Response  JSON only (AJAX endpoint)
     */
    public function createPrivilegedAccount(SetupPrivilegedAccountRequest $request): Response
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

        if ($this->privilegedAccountProvisioner->privilegedAccountExists($pdo)) {
            return $this->jsonSuccessWithToast(
                ['privileged_account_exists' => true],
                __('settings.completion.privileged_account_exists_success')
            )->withRefresh();
        }

        $payload = $request->validated();
        $accountName = (string) $payload['account_name'];
        $accountEmail = (string) $payload['account_email'];
        $accountPassword = (string) $payload['account_password'];

        if ($this->privilegedAccountProvisioner->userExistsByEmail($pdo, $accountEmail)) {
            return $this->jsonValidationError([
                'account_email' => __('settings.completion.errors.account_email_exists'),
            ]);
        }

        try {
            $this->privilegedAccountProvisioner->createPrivilegedAccount(
                $pdo,
                $accountName,
                $accountEmail,
                $accountPassword
            );
        } catch (Throwable $e) {
            $this->logError('SetupCompletion: initial privileged account creation failed', [
                'exception' => $e::class,
                'error' => $e->getMessage(),
            ]);

            return $this->jsonErrorWithToast(
                __('settings.completion.errors.privileged_account_create_failed'),
                500
            );
        }

        if (!$this->privilegedAccountProvisioner->privilegedAccountExists($pdo)) {
            return $this->jsonErrorWithToast(
                __('settings.completion.errors.privileged_account_create_failed'),
                500
            );
        }

        return $this->jsonSuccessWithToast(
            ['privileged_account_created' => true],
            __('settings.completion.privileged_account_create_success')
        )->withRefresh();
    }

    /**
     * Finalize the setup wizard.
     *
     * Responsibility: Finalize the setup wizard.
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

        if (!$this->privilegedAccountProvisioner->privilegedAccountExists($pdo)) {
            return $this->jsonErrorWithToast(
                __('settings.completion.errors.privileged_account_required'),
                422
            );
        }

        // -- 6. Flip project_config=true in app.json -------------------------
        $appProject                   = $cfg->section('app')['project'] ?? [];
        $appProject['project_config'] = true;
        $cfg->writeSection('app', ['project' => $appProject]);

        return $this->jsonSuccessWithToast(
            ['privileged_account_created' => false],
            __('settings.completion.success')
        )->withRedirect('/login');
    }

    /**
     * Reset the setup wizard while preserving existing configuration values.
     *
     * Responsibility: Resets the configured flag; route middleware enforces the required role.
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
        )->withRedirect('/configuration/environment-setup');
    }
}
