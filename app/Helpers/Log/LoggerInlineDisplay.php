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

namespace Catalyst\Helpers\Log;

use Catalyst\Helpers\ToolBox\DrawBox;
use Exception;

/**
 * Renders enabled log output in CLI boxes.
 *
 * @package Catalyst\Helpers\Log
 * Responsibility: Maps logger levels to CLI styles and emits formatted diagnostic output.
 */
final class LoggerInlineDisplay
{
    /**
     * Renders a formatted log entry when inline CLI display is enabled.
     *
     * Responsibility: Renders a formatted log entry when inline CLI display is enabled.
     * @throws Exception
     */
    public function render(string $level, string $logEntry): void
    {
        $style = match ($level) {
            'EMERGENCY', 'ALERT', 'CRITICAL', 'ERROR' => 2,
            'WARNING' => 3,
            'NOTICE' => 4,
            'INFO' => 7,
            'DEBUG' => 0,
            default => 0,
        };

        $drawBox = DrawBox::getInstance();

        echo $drawBox->draw($logEntry, [
            'headerLines' => 0,
            'footerLines' => 0,
            'highlight' => true,
            'maxWidth' => 0,
            'style' => $style,
            'isError' => $level === 'ERROR',
        ]);
    }
}
