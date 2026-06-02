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

/**
 * Resolve bootstrap-phase classes from the supported namespace map.
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
