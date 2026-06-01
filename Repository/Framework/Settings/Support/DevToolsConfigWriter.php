<?php

declare(strict_types=1);

namespace Catalyst\Repository\Settings\Support;

use Catalyst\Helpers\Config\ConfigManager;

final class DevToolsConfigWriter
{
    /**
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
