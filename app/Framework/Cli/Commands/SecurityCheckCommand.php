<?php

declare(strict_types=1);

namespace Catalyst\Framework\Cli\Commands;

use Catalyst\Framework\Argument\ArgumentBag;
use Catalyst\Framework\Cli\AbstractCommand;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class SecurityCheckCommand extends AbstractCommand
{
    /**
     * @var array<string, string>
     */
    private const FAILURE_PATTERNS = [
        'inline-handler' => '/\son(?:click|submit|change|keyup|keydown|load|mouseover|focus|blur|error)\s*=/i',
        'javascript-uri' => '/javascript\s*:/i',
    ];

    /**
     * @var array<string, string>
     */
    private const WARNING_PATTERNS = [
        'inline-style-attribute' => '/style\s*=\s*["\']/i',
    ];

    public function getName(): string
    {
        return 'security:check';
    }

    public function getDescription(): string
    {
        return 'Scan CSP/frontend hotspots and other low-hanging security regressions';
    }

    public function execute(ArgumentBag $args): int
    {
        $failures = [];
        $warnings = [];

        foreach ($this->targetFiles() as $file) {
            $this->scanFile($file, $failures, $warnings);
        }

        $this->line('');
        $this->info('Security Check');
        $this->line(str_repeat('-', 60));

        if ($failures === []) {
            $this->success('Hard failures: none');
        } else {
            $this->error('Hard failures detected: ' . count($failures));
            foreach ($failures as $failure) {
                $this->line(sprintf(
                    '  [%s] %s:%d',
                    $failure['type'],
                    $this->relativePath($failure['file']),
                    $failure['line']
                ));
            }
        }

        if ($warnings === []) {
            $this->success('Warnings: none');
        } else {
            $this->warn('Warnings detected: ' . count($warnings));
            foreach ($warnings as $warning) {
                $this->line(sprintf(
                    '  [%s] %s:%d',
                    $warning['type'],
                    $this->relativePath($warning['file']),
                    $warning['line']
                ));
            }
        }

        $this->line(str_repeat('-', 60));
        $this->line('Hard failures break CSP or frontend safety directly.');
        $this->line('Warnings are allowed today but still point to inline style attributes to retire.');
        $this->line('');

        return $failures === [] ? 0 : 1;
    }

    /**
     * @return SplFileInfo[]
     */
    private function targetFiles(): array
    {
        $roots = [
            PD . DS . 'boot-core',
            PD . DS . 'Repository',
            PD . DS . 'app' . DS . 'Framework' . DS . 'Middleware',
            PD . DS . 'app' . DS . 'Helpers',
        ];

        $files = [];

        foreach ($roots as $root) {
            if (!is_dir($root)) {
                continue;
            }

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if (!$file instanceof SplFileInfo || $file->getExtension() !== 'php') {
                    continue;
                }

                $path = $file->getPathname();
                if (str_contains($path, DS . 'Controllers' . DS)) {
                    continue;
                }

                $files[] = $file;
            }
        }

        return $files;
    }

    /**
     * @param array<int, array<string, int|string>> $failures
     * @param array<int, array<string, int|string>> $warnings
     */
    private function scanFile(SplFileInfo $file, array &$failures, array &$warnings): void
    {
        $lines = @file($file->getPathname(), FILE_IGNORE_NEW_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $index => $line) {
            if ($this->isIgnorableLine($line)) {
                continue;
            }

            if ($this->isEscapedDemoLine($line)) {
                continue;
            }

            foreach (self::FAILURE_PATTERNS as $type => $pattern) {
                if (!preg_match($pattern, $line)) {
                    continue;
                }

                $failures[] = [
                    'type' => $type,
                    'file' => $file->getPathname(),
                    'line' => $index + 1,
                ];
            }

            if ($this->hasInlineScriptWithoutNonce($line)) {
                $failures[] = [
                    'type' => 'inline-script-without-nonce',
                    'file' => $file->getPathname(),
                    'line' => $index + 1,
                ];
            }

            if ($this->hasInlineStyleBlockWithoutNonce($line)) {
                $failures[] = [
                    'type' => 'inline-style-block-without-nonce',
                    'file' => $file->getPathname(),
                    'line' => $index + 1,
                ];
            }

            foreach (self::WARNING_PATTERNS as $type => $pattern) {
                if (!preg_match($pattern, $line)) {
                    continue;
                }

                $warnings[] = [
                    'type' => $type,
                    'file' => $file->getPathname(),
                    'line' => $index + 1,
                ];
            }
        }
    }

    private function isIgnorableLine(string $line): bool
    {
        $trimmed = ltrim($line);

        if ($trimmed === '') {
            return true;
        }

        foreach (['//', '#', '*', '/*', '*/'] as $prefix) {
            if (str_starts_with($trimmed, $prefix)) {
                return true;
            }
        }

        return false;
    }

    private function isEscapedDemoLine(string $line): bool
    {
        $normalized = strtolower($line);

        if (!str_contains($normalized, 'e(')) {
            return false;
        }

        foreach (['<script', ' onerror=', 'javascript:'] as $needle) {
            if (str_contains($normalized, $needle)) {
                return true;
            }
        }

        return false;
    }

    private function hasInlineScriptWithoutNonce(string $line): bool
    {
        if (stripos($line, '<script') === false) {
            return false;
        }

        if (preg_match('/<script\b/i', $line) !== 1) {
            return false;
        }

        $normalized = strtolower($line);

        if (str_contains($normalized, 'src=')) {
            return false;
        }

        if (str_contains($normalized, 'type="application/json"')
            || str_contains($normalized, "type='application/json'")) {
            return false;
        }

        if (str_contains($normalized, 'nonce=')
            || str_contains($normalized, '<?php')
            || str_contains($normalized, '<?= $__nonce ?>')
            || str_contains($normalized, '<?= $__nonceattr ?>')
            || str_contains($normalized, '$nonceattr')
            || str_contains($normalized, '$__nonceattr')
            || str_contains($normalized, '$__nonce')) {
            return false;
        }

        return true;
    }

    private function hasInlineStyleBlockWithoutNonce(string $line): bool
    {
        if (stripos($line, '<style') === false) {
            return false;
        }

        if (preg_match('/<style\b/i', $line) !== 1) {
            return false;
        }

        $normalized = strtolower($line);

        if (str_contains($normalized, 'nonce=')
            || str_contains($normalized, '<?php')
            || str_contains($normalized, '<?= $__nonce ?>')
            || str_contains($normalized, '<?= $__nonceattr ?>')
            || str_contains($normalized, '$nonceattr')
            || str_contains($normalized, '$__nonceattr')
            || str_contains($normalized, '$__nonce')) {
            return false;
        }

        return true;
    }

    private function relativePath(string $path): string
    {
        $prefix = rtrim(PD, '\\/') . DS;
        if (str_starts_with($path, $prefix)) {
            return substr($path, strlen($prefix));
        }

        return $path;
    }
}
