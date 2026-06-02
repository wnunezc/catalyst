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

namespace Catalyst\Framework\Documentation;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Defines the Runtime Inventory Generator class contract.
 *
 * @package Catalyst\Framework\Documentation
 * Responsibility: Coordinates the runtime inventory generator behavior within its module boundary.
 */
final class RuntimeInventoryGenerator
{
    /**
     * @return array<string, mixed>
     */
    public function inspect(): array
    {
        $symbols = $this->inspectSymbols();
        $templates = $this->inspectTemplates();
        $scripts = $this->inspectScripts();

        return [
            'generated_at' => date('Y-m-d H:i:s'),
            'counts' => [
                'symbols' => count($symbols),
                'templates' => count($templates),
                'scripts' => count($scripts),
            ],
            'symbols' => $symbols,
            'templates' => $templates,
            'scripts' => $scripts,
        ];
    }

    /**
     * Handles the generate markdown workflow.
     */
    public function generateMarkdown(): string
    {
        $report = $this->inspect();
        $counts = (array) ($report['counts'] ?? []);

        $lines = [
            '# Runtime Inventory',
            '',
            '> Auto-generated from filesystem and PHP tokens. Run `php public/cli.php docs:inventory` to refresh.',
            '> Last generated: ' . (string) ($report['generated_at'] ?? ''),
            '',
            '## Summary',
            '',
            '- Symbols: ' . (int) ($counts['symbols'] ?? 0),
            '- Templates: ' . (int) ($counts['templates'] ?? 0),
            '- Scripts: ' . (int) ($counts['scripts'] ?? 0),
            '',
            '## Symbol Roots',
            '',
            '| Root | Count |',
            '|---|---:|',
        ];

        foreach ($this->countByKey((array) ($report['symbols'] ?? []), 'root') as $root => $count) {
            $lines[] = sprintf('| `%s` | %d |', $root, $count);
        }

        $lines[] = '';
        $lines[] = '## Template Roots';
        $lines[] = '';
        $lines[] = '| Root | Count |';
        $lines[] = '|---|---:|';

        foreach ($this->countByKey((array) ($report['templates'] ?? []), 'root') as $root => $count) {
            $lines[] = sprintf('| `%s` | %d |', $root, $count);
        }

        $lines[] = '';
        $lines[] = '## Script Roots';
        $lines[] = '';
        $lines[] = '| Root | Count |';
        $lines[] = '|---|---:|';

        foreach ($this->countByKey((array) ($report['scripts'] ?? []), 'root') as $root => $count) {
            $lines[] = sprintf('| `%s` | %d |', $root, $count);
        }

        $lines[] = '';
        $lines[] = '## Symbols';
        $lines[] = '';
        $lines[] = '| FQN | Type | File | Line |';
        $lines[] = '|---|---|---|---:|';

        foreach ((array) ($report['symbols'] ?? []) as $symbol) {
            $lines[] = sprintf(
                '| `%s` | `%s` | `%s` | %d |',
                (string) ($symbol['fqn'] ?? ''),
                (string) ($symbol['type'] ?? ''),
                (string) ($symbol['file'] ?? ''),
                (int) ($symbol['line'] ?? 0)
            );
        }

        $lines[] = '';
        $lines[] = '## Templates';
        $lines[] = '';
        $lines[] = '| File | Root | Extension |';
        $lines[] = '|---|---|---|';

        foreach ((array) ($report['templates'] ?? []) as $template) {
            $lines[] = sprintf(
                '| `%s` | `%s` | `%s` |',
                (string) ($template['file'] ?? ''),
                (string) ($template['root'] ?? ''),
                (string) ($template['extension'] ?? '')
            );
        }

        $lines[] = '';
        $lines[] = '## Scripts';
        $lines[] = '';
        $lines[] = '| File | Root | Bytes |';
        $lines[] = '|---|---|---:|';

        foreach ((array) ($report['scripts'] ?? []) as $script) {
            $lines[] = sprintf(
                '| `%s` | `%s` | %d |',
                (string) ($script['file'] ?? ''),
                (string) ($script['root'] ?? ''),
                (int) ($script['bytes'] ?? 0)
            );
        }

        $lines[] = '';

        return implode(PHP_EOL, $lines);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function inspectSymbols(): array
    {
        $symbols = [];

        foreach ($this->files([
            'app' => PD . DS . 'app',
            'Repository' => PD . DS . 'Repository',
            'boot-core' => PD . DS . 'boot-core',
        ], ['php']) as $file) {
            $symbols = array_merge($symbols, $this->parsePhpSymbols($file));
        }

        usort($symbols, static fn (array $a, array $b): int => strcmp((string) $a['fqn'], (string) $b['fqn']));

        return $symbols;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function inspectTemplates(): array
    {
        return iterator_to_array($this->files([
            'boot-core/template' => PD . DS . 'boot-core' . DS . 'template',
            'Repository views' => PD . DS . 'Repository',
        ], ['php', 'phtml'], static function (SplFileInfo $file): bool {
            $path = $file->getPathname();

            return str_contains($path, DS . 'Views' . DS)
                || str_contains($path, DS . 'template' . DS);
        }));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function inspectScripts(): array
    {
        return iterator_to_array($this->files([
            'Repository front' => PD . DS . 'Repository',
            'public catalyst js' => PD . DS . 'public' . DS . 'assets' . DS . 'js' . DS . 'catalyst',
        ], ['js'], static function (SplFileInfo $file): bool {
            $path = $file->getPathname();

            return str_contains($path, DS . 'front' . DS)
                || str_contains($path, DS . 'public' . DS . 'assets' . DS . 'js' . DS . 'catalyst' . DS);
        }));
    }

    /**
     * @param array<string, string> $roots
     * @param array<int, string> $extensions
     * @return iterable<array<string, mixed>>
     */
    private function files(array $roots, array $extensions, ?callable $filter = null): iterable
    {
        foreach ($roots as $rootLabel => $rootPath) {
            if (!is_dir($rootPath)) {
                continue;
            }

            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($rootPath));
            foreach ($iterator as $file) {
                if (!$file instanceof SplFileInfo || !$file->isFile()) {
                    continue;
                }

                if (!in_array(strtolower($file->getExtension()), $extensions, true)) {
                    continue;
                }

                if ($this->isIgnoredPath($file->getPathname())) {
                    continue;
                }

                if ($filter !== null && !$filter($file)) {
                    continue;
                }

                yield [
                    'root' => $rootLabel,
                    'file' => $this->relativePath($file->getPathname()),
                    'extension' => strtolower($file->getExtension()),
                    'bytes' => $file->getSize(),
                ];
            }
        }
    }

    /**
     * @param array<string, mixed> $file
     * @return array<int, array<string, mixed>>
     */
    private function parsePhpSymbols(array $file): array
    {
        $path = PD . DS . str_replace('/', DS, (string) ($file['file'] ?? ''));
        $source = (string) file_get_contents($path);
        $tokens = token_get_all($source);
        $namespace = '';
        $symbols = [];

        foreach ($tokens as $index => $token) {
            if (!is_array($token)) {
                continue;
            }

            if ($token[0] === T_NAMESPACE) {
                $namespace = $this->readNamespace($tokens, $index + 1);
                continue;
            }

            $type = match ($token[0]) {
                T_CLASS => 'class',
                T_INTERFACE => 'interface',
                T_TRAIT => 'trait',
                defined('T_ENUM') ? constant('T_ENUM') : -1 => 'enum',
                default => null,
            };

            if ($type === null || $this->isNonDeclarationClassToken($tokens, $index)) {
                continue;
            }

            $name = $this->readSymbolName($tokens, $index + 1);
            if ($name === '') {
                continue;
            }

            $symbols[] = [
                'root' => (string) ($file['root'] ?? ''),
                'type' => $type,
                'name' => $name,
                'namespace' => $namespace,
                'fqn' => ltrim($namespace . '\\' . $name, '\\'),
                'file' => (string) ($file['file'] ?? ''),
                'line' => (int) ($token[2] ?? 0),
            ];
        }

        return $symbols;
    }

    /**
     * @param array<int, mixed> $tokens
     */
    private function readNamespace(array $tokens, int $start): string
    {
        $namespace = '';

        for ($i = $start; $i < count($tokens); $i++) {
            $token = $tokens[$i];
            if ($token === ';' || $token === '{') {
                break;
            }

            if (is_array($token) && in_array($token[0], [T_STRING, T_NAME_QUALIFIED, T_NAME_FULLY_QUALIFIED], true)) {
                $namespace .= $token[1];
            } elseif ($token === '\\') {
                $namespace .= '\\';
            }
        }

        return trim($namespace, '\\');
    }

    /**
     * @param array<int, mixed> $tokens
     */
    private function readSymbolName(array $tokens, int $start): string
    {
        for ($i = $start; $i < count($tokens); $i++) {
            $token = $tokens[$i];
            if (is_array($token) && $token[0] === T_STRING) {
                return $token[1];
            }

            if ($token === '{' || $token === '(') {
                break;
            }
        }

        return '';
    }

    /**
     * @param array<int, mixed> $tokens
     */
    private function isNonDeclarationClassToken(array $tokens, int $index): bool
    {
        for ($i = $index - 1; $i >= 0; $i--) {
            $token = $tokens[$i];
            if (is_array($token) && in_array($token[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
                continue;
            }

            return is_array($token) && in_array($token[0], [T_NEW, T_DOUBLE_COLON], true);
        }

        return false;
    }

    /**
     * Handles the relative path workflow.
     */
    private function relativePath(string $path): string
    {
        return str_replace('\\', '/', ltrim(str_replace(PD, '', $path), '\\/'));
    }

    /**
     * Determines whether is Ignored Path.
     */
    private function isIgnoredPath(string $path): bool
    {
        $normalized = str_replace('\\', '/', $path);

        foreach (['/vendor/', '/public/assets/vendor/', '/public/uploads/', '/storage/'] as $ignored) {
            if (str_contains($normalized, $ignored)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<int, array<string, mixed>> $items
     * @return array<string, int>
     */
    private function countByKey(array $items, string $key): array
    {
        $counts = [];

        foreach ($items as $item) {
            $value = (string) ($item[$key] ?? 'unknown');
            $counts[$value] = ($counts[$value] ?? 0) + 1;
        }

        ksort($counts);

        return $counts;
    }
}
