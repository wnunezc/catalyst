<?php

declare(strict_types=1);

namespace Catalyst\Framework\Cli\Commands;

use Catalyst\Framework\Argument\ArgumentBag;
use Catalyst\Framework\Argument\Parameter;
use Catalyst\Framework\Cli\AbstractCommand;
use Catalyst\Framework\Cli\ScaffoldManager;
use InvalidArgumentException;
use RuntimeException;

class MakeMiddlewareCommand extends AbstractCommand
{
    public function getName(): string
    {
        return 'make:middleware';
    }

    public function getDescription(): string
    {
        return 'Scaffold a new middleware in Repository/App/Middleware/';
    }

    /** @return Parameter[] */
    public function getParameters(): array
    {
        return [
            new Parameter(
                0,
                null,
                true,
                null,
                'ClassName',
                'Middleware class name (e.g. AuditMiddleware)'
            ),
        ];
    }

    public function execute(ArgumentBag $args): int
    {
        $manager = new ScaffoldManager();

        try {
            $className  = $manager->normalizeClassName((string) ($args->getParameterValue(0) ?? ''), 'Middleware');
            $namespace  = 'App\\Middleware';
            $targetDir  = implode(DS, [PD, 'Repository', 'App', 'Middleware']);
            $targetFile = $targetDir . DS . $className . '.php';

            if (file_exists($targetFile)) {
                $this->warn('Middleware already exists: ' . $targetFile);
                return 1;
            }

            $stub = $manager->renderStub('middleware.php.stub', [
                'namespace' => $namespace,
                'ClassName' => $className,
            ]);

            $manager->writeFile($targetFile, $stub);
        } catch (InvalidArgumentException|RuntimeException $e) {
            $this->error($e->getMessage());
            $this->line('Usage: php cli.php make:middleware <ClassName>');

            return 1;
        }

        $this->success('Middleware created → ' . $targetFile);
        $this->line('  Namespace : ' . $namespace);
        $this->line('  Class     : ' . $className);
        $this->line('');

        return 0;
    }
}
