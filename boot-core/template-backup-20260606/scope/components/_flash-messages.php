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

use Catalyst\Framework\Session\FlashMessage;
use Catalyst\Framework\View\InlineJson;
use Catalyst\Framework\View\TrustedHtml;

return static function (): array {
    $flash = FlashMessage::getInstance();
    $regular = $flash->all();
    $persistent = $flash->allPersistent();

    if (empty($regular) && empty($persistent)) {
        return [
            'has_flash_payload' => false,
            'flash_payload_json' => null,
        ];
    }

    return [
        'has_flash_payload' => true,
        'flash_payload_json' => TrustedHtml::fromString(InlineJson::encode([
            'regular' => $regular,
            'persistent' => $persistent,
        ])),
    ];
};
