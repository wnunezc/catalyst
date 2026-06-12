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

namespace Catalyst\Repository\Configuration\Support;

use Catalyst\Helpers\Config\ConfigManager;

/**
 * Writes legacy developer-tool compatibility settings.
 *
 * @package Catalyst\Repository\Configuration\Support
 * Responsibility: Mirrors debug and log-display flags into their canonical sections and the deprecated compatibility section.
 */
final class DevToolsConfigWriter
{
    /**
     * Saves debug and log-display compatibility settings.
     *
     * Responsibility: Saves debug and log-display compatibility settings.
     * @param array<string, mixed> $data
     */
    public function save(array $data): void
    {
        $config = ConfigManager::getInstance();
        $app = $config->entry('app', 'project');
        $logging = $config->entry('logging', 'logging');
        $appDebug = (bool) ($data['app_debug'] ?? false);
        $displayLogs = (bool) ($data['display_logs'] ?? false);

        $config->writeSection('app', [
            'project' => array_replace($app, [
                'project_env' => $config->getEnvironment(),
                'project_debug' => $appDebug,
            ]),
        ]);

        $config->writeSection('logging', [
            'logging' => array_replace($logging, [
                'display_logs' => $displayLogs,
            ]),
        ]);

        $config->writeSection('devtools', [
            'devtools' => [
                'deprecated' => true,
                'app_debug' => $appDebug,
                'display_logs' => $displayLogs,
            ],
        ]);
    }
}
