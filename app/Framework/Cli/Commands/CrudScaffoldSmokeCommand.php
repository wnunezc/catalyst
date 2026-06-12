<?php

declare(strict_types=1);

namespace Catalyst\Framework\Cli\Commands;

use Catalyst\Framework\Argument\ArgumentBag;
use Catalyst\Framework\Argument\Option;
use Catalyst\Framework\Cli\AbstractCommand;
use Catalyst\Framework\Scaffolding\Crud\CrudScaffoldService;
use InvalidArgumentException;
use Throwable;

final class CrudScaffoldSmokeCommand extends AbstractCommand
{
    /**
     * @return Option[]
     */
    public function getOptions(): array
    {
        return [
            new Option(null, 'json', false, false, 'Render as JSON', false),
        ];
    }

    public function getName(): string
    {
        return 'scaffold:crud-smoke';
    }

    public function getDescription(): string
    {
        return 'Verify neutral CRUD scaffolding previews and validation sad paths';
    }

    public function execute(ArgumentBag $args): int
    {
        $json = (bool) ($args->getOptionValue('json') ?? false);
        $service = new CrudScaffoldService();
        $result = ['success' => false, 'steps' => []];

        try {
            $blueprint = $service->preview([
                'module' => 'CrudSmokeFixture',
                'entity' => 'CrudSmokeRecord',
                'fields' => 'name:text!,description:textarea,active:checkbox',
                'surface' => 'workspace',
                'permission' => 'manage-crud-smoke-fixture',
            ]);
            $contents = implode(
                "\n",
                array_map(
                    static fn (array $file): string => (string) ($file['contents'] ?? ''),
                    (array) ($blueprint['files'] ?? [])
                )
            );

            $result['steps'][] = [
                'step' => 'workspace-blueprint-previewed',
                'status' => ($blueprint['surface'] ?? null) === 'workspace'
                    && ($blueprint['permission'] ?? null) === 'manage-crud-smoke-fixture'
                    ? 'ok'
                    : 'failed',
            ];
            $result['steps'][] = [
                'step' => 'global-capabilities-reused',
                'status' => str_contains($contents, 'Catalyst\\Framework\\DataGrid\\DataGrid')
                    && str_contains($contents, 'Catalyst\\Framework\\Form\\FormBuilder')
                    ? 'ok'
                    : 'failed',
            ];
            $result['steps'][] = [
                'step' => 'authorization-kept-separate',
                'status' => str_contains($contents, 'RoleMiddleware')
                    && str_contains($contents, "authorizeResource('view-any'")
                    ? 'ok'
                    : 'failed',
            ];

            $surfaceRejected = false;
            try {
                $service->preview([
                    'module' => 'InvalidCrudFixture',
                    'entity' => 'InvalidCrudRecord',
                    'fields' => 'name:text!',
                    'surface' => 'public',
                ]);
            } catch (InvalidArgumentException) {
                $surfaceRejected = true;
            }
            $result['steps'][] = [
                'step' => 'unsupported-surface-rejected',
                'status' => $surfaceRejected ? 'ok' : 'failed',
            ];
            $result['success'] = !in_array('failed', array_column($result['steps'], 'status'), true);
        } catch (Throwable $e) {
            $result['error'] = $e->getMessage();
        }

        if ($json) {
            $this->line((string) json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return !empty($result['success']) ? 0 : 1;
        }

        $this->line('');
        $this->info('CRUD Scaffold Smoke');
        $this->line('');

        foreach ((array) ($result['steps'] ?? []) as $step) {
            $this->line(sprintf(
                '  %-32s %-8s',
                (string) ($step['step'] ?? 'step'),
                strtoupper((string) ($step['status'] ?? 'unknown'))
            ));
        }

        $this->line('');

        if (!empty($result['success'])) {
            $this->success('CRUD scaffold smoke passed.');

            return 0;
        }

        $this->error((string) ($result['error'] ?? 'CRUD scaffold smoke failed.'));

        return 1;
    }
}
