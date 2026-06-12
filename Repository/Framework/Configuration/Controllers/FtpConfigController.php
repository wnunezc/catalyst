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
use Catalyst\Repository\Configuration\Requests\FtpConfigRequest;
use Catalyst\Repository\Configuration\Support\FtpConnectionProbe;
use Throwable;

/**
 * Manages FTP, FTPS and SFTP settings for the setup surface.
 *
 * @package Catalyst\Repository\Configuration\Controllers
 * Responsibility: Validates transfer settings, preserves stored credentials and runs upload-cleanup connectivity pretests.
 */
final class FtpConfigController extends Controller
{
    /**
     * Initializes the Ftp Config Controller instance.
     *
     * Responsibility: Initializes the Ftp Config Controller instance.
     */
    public function __construct(
        private readonly FtpConnectionProbe $probe = new FtpConnectionProbe()
    ) {
        parent::__construct();
    }

    /**
     * Validates and saves transfer settings.
     *
     * Responsibility: Validates and saves transfer settings.
     */
    public function saveFtp(FtpConfigRequest $request): Response
    {
        $cfg = ConfigManager::getInstance();
        $existing = $cfg->entry('ftp', 'ftp1');

        $cfg->writeSection('ftp', [
            'ftp1' => $request->resolved($existing),
        ]);

        return $this->jsonSuccessWithToast(null, __('settings.messages.saved'));
    }

    /**
     * Runs an upload-cleanup pretest without persisting the submitted settings.
     *
     * Responsibility: Runs an upload-cleanup pretest without persisting the submitted settings.
     */
    public function pretest(FtpConfigRequest $request): Response
    {
        $cfg = ConfigManager::getInstance();
        $existing = $cfg->entry('ftp', 'ftp1');
        $payload = $request->resolved($existing);

        try {
            $result = $this->probe->pretest($payload);
        } catch (Throwable $e) {
            return $this->jsonErrorWithToast($e->getMessage(), 422);
        }

        $message = $result['cleanup_warning'] === null
            ? __('settings.messages.ftp_pretest_success')
            : __('settings.messages.ftp_pretest_success_cleanup_warning');

        return $this->jsonSuccessWithToast($result, $message);
    }
}
