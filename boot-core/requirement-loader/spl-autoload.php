<?php

declare(strict_types=1);

/**
 *
 * Catalyst PHP Framework
 * PHP Version 8.4 (Required).
 *
 * @package   Catalyst
 *
 * @see       https://catalyst.lh-2.net
 *
 * @author    Walter Nuñez (arcanisgk/original founder) <wnunez@lh-2.net>
 * @copyright 2024 Walter Francisco Nuñez Cruz and Icaros Net
 * @license   Proprietary - https://catalyst.lh-2.net
 *
 * @note      This program is provided "as is" without a warranty of any kind, too express
 *            or implied, including but not limited to the warranties of merchantability,
 *            fitness for a particular purpose, and non-infringement.
 *
 * @category  Framework
 * @filesource
 *
 * @link      https://catalyst.lh-2.net Project homepage
 *
 */

/**
 * SPL autoloader for bootstrap-phase classes.
 *
 * Handles only the namespaces required before Composer is available.
 * This file is loaded from within the INITIALIZED_BUG_CATCHER guard in
 * error-catcher.php, so it is guaranteed to run exactly once per request.
 *
 * Registered namespaces:
 *   - Catalyst\Framework\Traits\    → app/Framework/Traits
 *   - Catalyst\Helpers\Config\      → app/Helpers/Config
 *   - Catalyst\Helpers\Error\       → app/Helpers/Error
 *   - Catalyst\Helpers\Log\         → app/Helpers/Log
 *   - Catalyst\Helpers\Security\    → app/Helpers/Security
 *   - Catalyst\Helpers\ToolBox\     → app/Helpers/ToolBox
 *   - Catalyst\Helpers\IO\          → app/Helpers/IO
 *   - Catalyst\Helpers\Exceptions\  → app/Helpers/Exceptions
 *   - Catalyst\Framework\Argument\  → app/Framework/Argument
 *   - Catalyst\Framework\Cache\     → app/Framework/Cache
 *   - Catalyst\Framework\Sensitivity\ → app/Framework/Sensitivity
 */

spl_autoload_register(function (string $class): bool {
    $supportedNamespaces = [
        'Catalyst\\Framework\\Traits\\'    => 'app/Framework/Traits',
        'Catalyst\\Helpers\\Config\\'      => 'app/Helpers/Config',
        'Catalyst\\Helpers\\Error\\'        => 'app/Helpers/Error',
        'Catalyst\\Helpers\\Log\\'          => 'app/Helpers/Log',
        'Catalyst\\Helpers\\Security\\'     => 'app/Helpers/Security',
        'Catalyst\\Helpers\\ToolBox\\'      => 'app/Helpers/ToolBox',
        'Catalyst\\Helpers\\IO\\'           => 'app/Helpers/IO',
        'Catalyst\\Helpers\\Exceptions\\'   => 'app/Helpers/Exceptions',
        'Catalyst\\Framework\\Argument\\'   => 'app/Framework/Argument',
        'Catalyst\\Framework\\Cache\\'      => 'app/Framework/Cache',
        'Catalyst\\Framework\\Sensitivity\\' => 'app/Framework/Sensitivity',
    ];

    foreach ($supportedNamespaces as $namespace => $path) {
        if (str_starts_with($class, $namespace)) {
            $relativeClass = substr($class, strlen($namespace));
            $file = implode(DS, [
                PD,
                str_replace('\\', DS, $path),
                str_replace('\\', DS, $relativeClass) . '.php',
            ]);

            if (file_exists($file)) {
                require_once $file;
                return true;
            }
        }
    }

    return false;
});
