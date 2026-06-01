<?php

declare(strict_types=1);

namespace Catalyst\Helpers\Log;

use Catalyst\Helpers\ToolBox\DrawBox;
use Exception;

final class LoggerInlineDisplay
{
    /**
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
