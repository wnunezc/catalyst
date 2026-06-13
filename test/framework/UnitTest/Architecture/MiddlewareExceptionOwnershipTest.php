<?php

declare(strict_types=1);

namespace CatalystTest\Architecture;

use CatalystTest\Support\Assert;
use CatalystTest\TestCase;

final class MiddlewareExceptionOwnershipTest extends TestCase
{
    private string $root;

    public function setUp(): void
    {
        $this->root = dirname(__DIR__, 4);
    }

    public function testKernelIsTheSingleOwnerOfPropagatedExceptionLogging(): void
    {
        $core = $this->read('app/Framework/Middleware/CoreMiddleware.php');
        $stack = $this->read('app/Framework/Middleware/MiddlewareStack.php');
        $kernel = $this->read('app/Kernel.php');

        Assert::false(str_contains($core, 'Middleware exception'));
        Assert::false(str_contains($stack, 'Middleware execution error'));
        Assert::contains("catch (RouteNotFoundException \$e)", $kernel);
        Assert::contains("catch (Throwable \$e)", $kernel);
        Assert::contains("'Application execution failed'", $kernel);
    }

    private function read(string $relative): string
    {
        $path = $this->root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
        $source = file_get_contents($path);

        if (!is_string($source)) {
            throw new \RuntimeException("Unable to read {$relative}.");
        }

        return $source;
    }
}
