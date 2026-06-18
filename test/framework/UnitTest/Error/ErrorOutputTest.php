<?php

declare(strict_types=1);

namespace CatalystTest\Error;

use Catalyst\Helpers\Error\ErrorOutput;
use CatalystTest\Support\Assert;
use CatalystTest\TestCase;

final class ErrorOutputTest extends TestCase
{
    public function testBootstrapErrorTemplatesExist(): void
    {
        $root = dirname(__DIR__, 4);
        $production = (string) file_get_contents(
            $root . '/boot-core/template/errors/handler_error_no.phtml'
        );
        $output = (string) file_get_contents($root . '/app/Helpers/Error/ErrorOutput.php');

        Assert::true(is_file($root . '/boot-core/template/errors/handler_error.phtml'));
        Assert::true(is_file($root . '/boot-core/template/errors/handler_error_no.phtml'));
        Assert::contains("errorArray['ticket']", $production);
        Assert::contains("errorArray['occurred_at']", $production);
        Assert::false(str_contains($production, "errorArray['description']"));
        Assert::false(str_contains($production, "errorArray['trace_msg']"));
        Assert::false(str_contains($production, '$source'));
        Assert::false(str_contains($output, 'print_r($errorData, true)'));
    }

    public function testWebOutputBoundsLargeErrorPayloads(): void
    {
        if (!defined('PD')) {
            define('PD', dirname(__DIR__, 4));
        }
        if (!defined('IS_DEVELOPMENT')) {
            define('IS_DEVELOPMENT', true);
        }
        if (!defined('IS_CLI')) {
            define('IS_CLI', false);
        }

        $payloadMarker = str_repeat('large-error-payload-', 10000);
        $method = new \ReflectionMethod(ErrorOutput::class, 'displayWeb');

        ob_start();
        $method->invoke(ErrorOutput::getInstance(), [
            'class' => 'RuntimeException',
            'type' => 'Uncaught Exception',
            'description' => $payloadMarker,
            'file' => __FILE__,
            'line' => __LINE__,
            'trace' => array_fill(0, 500, ['file' => __FILE__, 'line' => __LINE__]),
            'trace_msg' => $payloadMarker,
            'ticket' => 'error-ticket-123',
            'occurred_at' => '2026-06-18 01:23:45 UTC',
        ]);
        $output = (string) ob_get_clean();

        Assert::true(strlen($output) < 25000, 'Error fallback output must remain bounded.');
        Assert::contains('RuntimeException', $output);
        Assert::contains('error-ticket-123', $output);
        Assert::contains('2026-06-18 01:23:45 UTC', $output);
        Assert::false(str_contains($output, $payloadMarker));
    }
}
