<?php

declare(strict_types=1);

namespace Catalyst\Framework\Cli\Commands;

use Catalyst\Framework\Argument\ArgumentBag;
use Catalyst\Framework\Argument\Option;
use Catalyst\Framework\Cli\AbstractCommand;
use Catalyst\Helpers\Config\ConfigManager;
use Random\RandomException;
use RuntimeException;

class KeyGenerateCommand extends AbstractCommand
{
    public function getName(): string
    {
        return 'key:generate';
    }

    public function getDescription(): string
    {
        return 'Generate a new APP_KEY and persist it to .env plus the managed secret config store';
    }

    /** @return Option[] */
    public function getOptions(): array
    {
        return [
            new Option(null, 'show', false, false, 'Print the generated key without modifying files', false),
        ];
    }

    public function execute(ArgumentBag $args): int
    {
        try {
            $key = $this->generateKey();
        } catch (RandomException $e) {
            $this->error('Unable to generate a secure APP_KEY: ' . $e->getMessage());
            return 1;
        }

        if ((bool) ($args->getOptionValue('show') ?? false)) {
            $this->line($key);
            return 0;
        }

        try {
            $this->persistEnvKey($key);
            $this->persistAppConfigKey($key);
        } catch (RuntimeException $e) {
            $this->error($e->getMessage());
            return 1;
        }

        $this->success('APP_KEY rotated successfully.');
        $this->line('  Updated: boot-core/config/env/.env');
        $this->line('  Updated: boot-core/config/' . ConfigManager::getInstance()->getEnvironment() . '/secrets.json');
        $this->line('  Public app.json remains free of managed secret keys.');
        $this->warn('Existing sessions and token signatures that depend on APP_KEY may become invalid.');
        $this->line('');

        return 0;
    }

    /**
     * @throws RandomException
     */
    private function generateKey(): string
    {
        return 'base64:' . base64_encode(random_bytes(32));
    }

    /**
     * @throws RuntimeException
     */
    private function persistEnvKey(string $key): void
    {
        $path = implode(DS, [PD, 'boot-core', 'config', 'env', '.env']);

        if (!is_file($path)) {
            throw new RuntimeException('Cannot rotate APP_KEY because .env was not found: ' . $path);
        }

        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new RuntimeException('Unable to read .env file: ' . $path);
        }

        if (preg_match('/^APP_KEY=.*$/m', $contents) === 1) {
            $contents = (string) preg_replace('/^APP_KEY=.*$/m', 'APP_KEY=' . $key, $contents, 1);
        } else {
            $contents = rtrim($contents) . PHP_EOL . 'APP_KEY=' . $key . PHP_EOL;
        }

        if (file_put_contents($path, $contents) === false) {
            throw new RuntimeException('Unable to write .env file: ' . $path);
        }
    }

    /**
     * @throws RuntimeException
     */
    private function persistAppConfigKey(string $key): void
    {
        $config = ConfigManager::getInstance();
        $app    = $config->entry('app', 'project');

        $config->writeSection('app', [
            'project' => array_replace($app, [
                'project_env' => $config->getEnvironment(),
                'project_key' => $key,
            ]),
        ]);
    }
}
