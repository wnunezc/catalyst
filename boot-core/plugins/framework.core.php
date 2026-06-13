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

return [
    'key' => 'framework.core',
    'label' => 'Framework Core',
    'version' => '1.0.0',
    'required' => true,
    'description' => 'Mandatory operational core for auth, setup, RBAC and platform operations.',
    'modules' => [
        'framework.auth',
        'framework.configuration',
        'framework.roles',
        'framework.api',
        'framework.operations',
        'framework.notification',
    ],
];
