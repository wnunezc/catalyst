<?php

declare(strict_types=1);

namespace Catalyst\Framework\Mail;

use Catalyst\Framework\Appearance\PlatformAppearanceManager;
use Catalyst\Helpers\I18n\Translator;
use InvalidArgumentException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use Throwable;

/**
 * Resolves and safely manages framework-owned system and managed email templates.
 */
final class EmailTemplateManager
{
    private const DEFAULT_LOCALE = 'en';

    private string $mailRoot;

    private Translator $translator;

    /**
     * @param string|array<int, string>|null $mailRoot
     */
    public function __construct(string|array|null $mailRoot = null, ?Translator $translator = null)
    {
        if (is_array($mailRoot)) {
            $mailRoot = $this->inferRootFromLegacyRoots($mailRoot);
        }

        $this->mailRoot = rtrim(
            $mailRoot ?? dirname(__DIR__, 3) . '/Repository/Framework/Mail',
            '/\\'
        );
        $this->translator = $translator ?? Translator::getInstance();
        $this->registerLanguageRoots();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function list(): array
    {
        $templates = [];

        foreach (['system', 'managed'] as $origin) {
            $root = $this->originRoot($origin) . '/templates';
            if (!is_dir($root)) {
                continue;
            }

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS)
            );
            foreach ($iterator as $entry) {
                if (!$entry->isFile() || $entry->getFilename() !== 'template.json') {
                    continue;
                }

                $manifest = $this->readJson($entry->getPathname());
                $key = $this->validateKey((string) ($manifest['key'] ?? ''));
                $templates[$key] ??= [
                    'key' => $key,
                    'name' => (string) ($manifest['name'] ?? $key),
                    'domain' => explode('.', $key, 2)[0],
                    'origin' => $origin,
                    'has_system' => false,
                    'has_override' => false,
                    'locales' => [],
                ];
                $templates[$key]['name'] = (string) ($manifest['name'] ?? $templates[$key]['name']);
                $templates[$key]['has_' . ($origin === 'managed' ? 'override' : 'system')] = true;
                if ($origin === 'managed') {
                    $templates[$key]['origin'] = 'managed';
                }
                $templates[$key]['locales'] = $this->catalogLocales(
                    (string) ($manifest['translation_catalog'] ?? '')
                );
            }
        }

        ksort($templates);

        return array_values($templates);
    }

    /**
     * @return array<string, mixed>
     */
    public function inspect(string $key): array
    {
        $key = $this->validateKey($key);
        $managedDirectory = $this->templateDirectory('managed', $key);
        $systemDirectory = $this->templateDirectory('system', $key);
        $origin = is_file($managedDirectory . '/template.json') ? 'managed' : 'system';
        $directory = $origin === 'managed' ? $managedDirectory : $systemDirectory;

        if (!is_file($directory . '/template.json')) {
            throw new RuntimeException(sprintf('Email template "%s" was not found.', $key));
        }

        $manifest = $this->validateManifest($this->readJson($directory . '/template.json'), $key);
        $htmlFile = $this->safeChildPath($directory, (string) $manifest['html_template']);
        $textFile = $this->safeChildPath($directory, (string) $manifest['text_template']);

        return [
            'key' => $key,
            'origin' => $origin,
            'has_system' => is_file($systemDirectory . '/template.json'),
            'has_override' => is_file($managedDirectory . '/template.json'),
            'manifest' => $manifest,
            'html' => $this->readFile($htmlFile),
            'text' => $this->readFile($textFile),
            'locales' => $this->catalogLocales((string) $manifest['translation_catalog']),
        ];
    }

    /**
     * @param array<string, mixed> $payload
     * @return array{key:string, origin:string, locale:string, subject:string, html:string, text:string, placeholders:list<string>}
     */
    public function render(string $key, array $payload, string $locale = self::DEFAULT_LOCALE): array
    {
        $template = $this->inspect($key);
        $manifest = (array) $template['manifest'];
        $payload = $this->withBranding($payload);
        $required = array_values(array_unique(array_map(
            'strval',
            (array) ($manifest['required_placeholders'] ?? [])
        )));
        $missing = array_values(array_filter(
            $required,
            static fn (string $placeholder): bool => !array_key_exists($placeholder, $payload)
                || $payload[$placeholder] === null
                || $payload[$placeholder] === ''
        ));
        if ($missing !== []) {
            throw new InvalidArgumentException(sprintf(
                'Email template "%s" is missing required placeholder(s): %s.',
                $key,
                implode(', ', $missing)
            ));
        }

        $locale = $this->normalizeLocale($locale);
        $catalog = (string) $manifest['translation_catalog'];
        $namespace = (string) $manifest['translation_namespace'];
        $subjectKey = $catalog . '.' . $namespace . '.subject';
        $effectiveLocale = $this->translator->has($subjectKey, $locale)
            ? $locale
            : $this->translator->getDefaultLocale();
        $replacements = $this->scalarPayload($payload);
        $subject = $this->translation($subjectKey, $replacements, $effectiveLocale);

        return [
            'key' => $key,
            'origin' => (string) $template['origin'],
            'locale' => $effectiveLocale,
            'subject' => $subject,
            'html' => $this->renderBody(
                (string) $template['html'],
                $catalog,
                $payload,
                $effectiveLocale,
                true
            ),
            'text' => $this->renderBody(
                (string) $template['text'],
                $catalog,
                $payload,
                $effectiveLocale,
                false
            ),
            'placeholders' => $required,
        ];
    }

    /**
     * @param array<string, mixed> $manifest
     * @param array<string, mixed> $catalog
     */
    public function saveManaged(
        string $key,
        array $manifest,
        string $html,
        string $text,
        string $locale,
        array $catalog
    ): void {
        $key = $this->validateKey($key);
        $manifest = $this->validateManifest($manifest, $key);
        $this->validateHtml($html);
        $this->validateBodyTokens($html, $text, $manifest);
        $locale = $this->normalizeLocale($locale);
        if ($catalog === []) {
            throw new InvalidArgumentException('Email translation catalog cannot be empty.');
        }
        $this->validateCatalog($catalog, $manifest, $html, $text);

        $directory = $this->templateDirectory('managed', $key);
        $catalogPath = $this->originRoot('managed') . '/lang/' . $locale . '/'
            . $manifest['translation_catalog'] . '.json';
        $writes = [
            $directory . '/template.json' => $this->encodeJson($manifest),
            $directory . '/' . $manifest['html_template'] => $html,
            $directory . '/' . $manifest['text_template'] => $text,
            $catalogPath => $this->encodeJson($catalog),
        ];

        $this->atomicWrite($writes);
        $this->registerLanguageRoots();
        $this->translator->clearCache($locale, (string) $manifest['translation_catalog']);
    }

    public function restoreSystem(string $key): void
    {
        $key = $this->validateKey($key);
        $managedDirectory = $this->templateDirectory('managed', $key);
        if (!is_dir($managedDirectory)) {
            return;
        }

        $manifest = $this->readJson($managedDirectory . '/template.json');
        $this->removeDirectory($managedDirectory, $this->originRoot('managed'));
        $catalog = (string) ($manifest['translation_catalog'] ?? '');
        if ($catalog !== '') {
            foreach (glob($this->originRoot('managed') . '/lang/*/' . $catalog . '.json') ?: [] as $path) {
                $this->safeUnlink($path, $this->originRoot('managed'));
            }
            $this->translator->clearCache(null, $catalog);
        }
    }

    public function deleteManaged(string $key): void
    {
        $key = $this->validateKey($key);
        if (is_file($this->templateDirectory('system', $key) . '/template.json')) {
            $this->restoreSystem($key);

            return;
        }

        $this->restoreSystem($key);
    }

    /**
     * @return list<string>
     */
    public function assetReferences(string $assetName): array
    {
        $references = [];
        $token = '{{ asset:' . $assetName . ' }}';
        $compactToken = '{{asset:' . $assetName . '}}';

        foreach ($this->list() as $template) {
            $detail = $this->inspect((string) $template['key']);
            if (
                str_contains((string) $detail['html'], $token)
                || str_contains((string) $detail['html'], $compactToken)
                || str_contains((string) $detail['text'], $token)
                || str_contains((string) $detail['text'], $compactToken)
            ) {
                $references[] = (string) $template['key'];
            }
        }

        return $references;
    }

    public function root(): string
    {
        return $this->mailRoot;
    }

    /**
     * @return array<string, mixed>
     */
    public function catalog(string $key, string $locale): array
    {
        $template = $this->inspect($key);
        $catalog = (string) ((array) $template['manifest'])['translation_catalog'];
        $locale = $this->normalizeLocale($locale);

        foreach (['managed', 'system'] as $origin) {
            $path = $this->originRoot($origin) . '/lang/' . $locale . '/' . $catalog . '.json';
            if (is_file($path)) {
                return $this->readJson($path);
            }
        }

        return [];
    }

    private function registerLanguageRoots(): void
    {
        $this->translator->addPath($this->originRoot('system') . '/lang');
        $this->translator->addPath($this->originRoot('managed') . '/lang');
    }

    private function originRoot(string $origin): string
    {
        if (!in_array($origin, ['system', 'managed'], true)) {
            throw new InvalidArgumentException('Unknown email template origin.');
        }

        return $this->mailRoot . '/' . $origin;
    }

    private function templateDirectory(string $origin, string $key): string
    {
        [$domain, $name] = explode('.', $this->validateKey($key), 2);

        return $this->originRoot($origin) . '/templates/' . $domain . '/'
            . str_replace(['.', '_'], '-', $name);
    }

    private function validateKey(string $key): string
    {
        $key = trim($key);
        if (
            preg_match('/^[a-z0-9]+(?:[._-][a-z0-9]+)+$/', $key) !== 1
            || str_contains($key, '..')
            || str_contains($key, '/')
            || str_contains($key, '\\')
        ) {
            throw new InvalidArgumentException('Invalid email template key.');
        }

        return $key;
    }

    /**
     * @param array<string, mixed> $manifest
     * @return array<string, mixed>
     */
    private function validateManifest(array $manifest, string $expectedKey): array
    {
        $key = $this->validateKey((string) ($manifest['key'] ?? ''));
        if ($key !== $expectedKey) {
            throw new InvalidArgumentException('Email template manifest key does not match its target.');
        }

        foreach (['name', 'translation_catalog', 'translation_namespace', 'html_template', 'text_template'] as $field) {
            if (trim((string) ($manifest[$field] ?? '')) === '') {
                throw new InvalidArgumentException(sprintf('Email template manifest requires "%s".', $field));
            }
        }
        if (preg_match('/^[a-z0-9_]+$/', (string) $manifest['translation_catalog']) !== 1) {
            throw new InvalidArgumentException('Invalid email translation catalog.');
        }
        if (preg_match('/^[a-z0-9]+(?:[._-][a-z0-9]+)*$/', (string) $manifest['translation_namespace']) !== 1) {
            throw new InvalidArgumentException('Invalid email translation namespace.');
        }
        foreach (['html_template', 'text_template'] as $field) {
            $filename = (string) $manifest[$field];
            if (basename($filename) !== $filename || str_contains($filename, '..')) {
                throw new InvalidArgumentException('Email template filenames must be local safe names.');
            }
        }

        $manifest['required_placeholders'] = array_values(array_unique(array_filter(array_map(
            static fn (mixed $value): string => trim((string) $value),
            (array) ($manifest['required_placeholders'] ?? [])
        ))));
        $manifest['sample_payload'] = is_array($manifest['sample_payload'] ?? null)
            ? $manifest['sample_payload']
            : [];

        return $manifest;
    }

    private function validateHtml(string $html): void
    {
        $blocked = [
            '/<\s*(script|iframe|object|embed)\b/i',
            '/\son[a-z]+\s*=/i',
            '/javascript\s*:/i',
            '/file\s*:/i',
            '/(?:src|href)\s*=\s*["\'](?:[a-zA-Z]:\\\\|\/(?!\/))/i',
            '/data\s*:\s*image\//i',
        ];
        foreach ($blocked as $pattern) {
            if (preg_match($pattern, $html) === 1) {
                throw new InvalidArgumentException('Email template HTML contains blocked executable or local content.');
            }
        }
    }

    /**
     * @param array<string, mixed> $manifest
     */
    private function validateBodyTokens(string $html, string $text, array $manifest): void
    {
        $source = $html . "\n" . $text;
        preg_match_all('/{{\s*([a-zA-Z0-9_.-]+)\s*}}/', $source, $matches);
        $declared = array_map('strval', (array) ($manifest['required_placeholders'] ?? []));
        $reserved = ['brand_name', 'brand_logo_url'];
        foreach (array_unique(array_map('strval', $matches[1] ?? [])) as $token) {
            if (!in_array($token, $declared, true) && !in_array($token, $reserved, true)) {
                throw new InvalidArgumentException(sprintf(
                    'Email template token "%s" is not declared as a required placeholder.',
                    $token
                ));
            }
        }
    }

    /**
     * @param array<string, mixed> $catalog
     * @param array<string, mixed> $manifest
     */
    private function validateCatalog(array $catalog, array $manifest, string $html, string $text): void
    {
        $leaves = $this->flattenCatalog($catalog);
        $requiredKeys = [(string) $manifest['translation_namespace'] . '.subject'];
        preg_match_all('/{{\s*t:([a-zA-Z0-9_.-]+)\s*}}/', $html . "\n" . $text, $matches);
        $requiredKeys = array_values(array_unique(array_merge(
            $requiredKeys,
            array_map('strval', $matches[1] ?? [])
        )));

        $missing = array_values(array_filter(
            $requiredKeys,
            static fn (string $key): bool => !isset($leaves[$key]) || trim((string) $leaves[$key]) === ''
        ));
        if ($missing !== []) {
            throw new InvalidArgumentException(
                'Email translation catalog is missing key(s): ' . implode(', ', $missing)
            );
        }
    }

    /**
     * @param array<string, mixed> $catalog
     * @return array<string, scalar>
     */
    private function flattenCatalog(array $catalog, string $prefix = ''): array
    {
        $leaves = [];
        foreach ($catalog as $key => $value) {
            $path = $prefix === '' ? (string) $key : $prefix . '.' . $key;
            if (is_array($value)) {
                $leaves += $this->flattenCatalog($value, $path);
            } elseif (is_scalar($value)) {
                $leaves[$path] = $value;
            }
        }

        return $leaves;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function renderBody(
        string $body,
        string $catalog,
        array $payload,
        string $locale,
        bool $html
    ): string {
        $replacements = $this->scalarPayload($payload);
        $body = (string) preg_replace_callback(
            '/{{\s*t:([a-zA-Z0-9_.-]+)\s*}}/',
            function (array $matches) use ($catalog, $replacements, $locale, $html): string {
                $key = $catalog . '.' . (string) $matches[1];
                $value = $this->translation($key, $replacements, $locale);

                return $html ? htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') : $value;
            },
            $body
        );
        $assets = new EmailAssetManager($this->mailRoot);
        $body = (string) preg_replace_callback(
            '/{{\s*asset:([a-zA-Z0-9._-]+)\s*}}/',
            static fn (array $matches): string => $assets->url((string) $matches[1]),
            $body
        );

        return (string) preg_replace_callback(
            '/{{\s*([a-zA-Z0-9_.-]+)\s*}}/',
            static function (array $matches) use ($payload, $html): string {
                $value = (string) ($payload[(string) $matches[1]] ?? '');

                return $html
                    ? htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                    : $value;
            },
            $body
        );
    }

    /**
     * @param array<string, scalar> $replacements
     */
    private function translation(string $key, array $replacements, string $locale): string
    {
        $value = $this->translator->get($key, $replacements, $locale);
        if ($value === $key) {
            throw new RuntimeException(sprintf('Required email translation "%s" is missing.', $key));
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, scalar>
     */
    private function scalarPayload(array $payload): array
    {
        return array_filter(
            $payload,
            static fn (mixed $value): bool => is_scalar($value),
        );
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function withBranding(array $payload): array
    {
        $brandName = 'Catalyst';
        $brandLogoUrl = '';

        try {
            $branding = PlatformAppearanceManager::getInstance()->brandingViewModel();
            $brandName = trim((string) ($branding['brand_name'] ?? $brandName)) ?: $brandName;
            $brandLogoUrl = (string) ($branding['logo_light_url'] ?? '');
        } catch (Throwable) {
        }

        return array_merge([
            'brand_name' => $brandName,
            'brand_logo_url' => $brandLogoUrl,
        ], $payload);
    }

    /**
     * @return list<string>
     */
    private function catalogLocales(string $catalog): array
    {
        if ($catalog === '') {
            return [];
        }

        $locales = [];
        foreach (['system', 'managed'] as $origin) {
            foreach (glob($this->originRoot($origin) . '/lang/*/' . $catalog . '.json') ?: [] as $path) {
                $locales[] = basename(dirname($path));
            }
        }
        $locales = array_values(array_unique($locales));
        sort($locales);

        return $locales;
    }

    /**
     * @return array<string, mixed>
     */
    private function readJson(string $path): array
    {
        $decoded = json_decode($this->readFile($path), true);
        if (!is_array($decoded)) {
            throw new RuntimeException(sprintf('Email template JSON "%s" is invalid.', basename($path)));
        }

        return $decoded;
    }

    private function readFile(string $path): string
    {
        $contents = is_file($path) ? file_get_contents($path) : false;
        if (!is_string($contents)) {
            throw new RuntimeException(sprintf('Required email template file "%s" is unavailable.', basename($path)));
        }

        return $contents;
    }

    private function safeChildPath(string $directory, string $filename): string
    {
        if (basename($filename) !== $filename || str_contains($filename, '..')) {
            throw new InvalidArgumentException('Unsafe email template child path.');
        }

        return $directory . '/' . $filename;
    }

    /**
     * @param array<string, string> $writes
     */
    private function atomicWrite(array $writes): void
    {
        $snapshots = [];
        $temps = [];

        try {
            foreach ($writes as $path => $contents) {
                $this->assertSafeManagedWritePath($path);
                $directory = dirname($path);
                if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
                    throw new RuntimeException('Unable to create managed email template directory.');
                }
                $snapshots[$path] = is_file($path) ? file_get_contents($path) : null;
                $temp = $path . '.tmp-' . bin2hex(random_bytes(6));
                if (file_put_contents($temp, $contents, LOCK_EX) === false) {
                    throw new RuntimeException('Unable to stage managed email template source.');
                }
                $temps[$path] = $temp;
            }
            foreach ($temps as $path => $temp) {
                if (!rename($temp, $path)) {
                    throw new RuntimeException('Unable to publish managed email template source.');
                }
                unset($temps[$path]);
            }
        } catch (Throwable $throwable) {
            foreach ($temps as $temp) {
                @unlink($temp);
            }
            foreach ($snapshots as $path => $contents) {
                if ($contents === null) {
                    @unlink($path);
                } else {
                    @file_put_contents($path, $contents, LOCK_EX);
                }
            }

            throw new RuntimeException('Managed email template write was rolled back.', 0, $throwable);
        }
    }

    private function removeDirectory(string $directory, string $allowedRoot): void
    {
        $this->assertInsideRoot($directory, $allowedRoot);
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($iterator as $entry) {
            $entry->isDir() ? rmdir($entry->getPathname()) : unlink($entry->getPathname());
        }
        rmdir($directory);
    }

    private function safeUnlink(string $path, string $allowedRoot): void
    {
        $this->assertInsideRoot($path, $allowedRoot);
        if (is_file($path) && !unlink($path)) {
            throw new RuntimeException('Unable to remove managed email template catalog.');
        }
    }

    private function assertInsideRoot(string $path, string $root): void
    {
        $normalizedPath = str_replace('\\', '/', $path);
        $normalizedRoot = rtrim(str_replace('\\', '/', $root), '/') . '/';
        if (!str_starts_with($normalizedPath . (is_dir($path) ? '/' : ''), $normalizedRoot)) {
            throw new RuntimeException('Email template path escaped its managed root.');
        }
    }

    private function assertSafeManagedWritePath(string $path): void
    {
        $managedRoot = $this->originRoot('managed');
        if (!is_dir($managedRoot) && !mkdir($managedRoot, 0775, true) && !is_dir($managedRoot)) {
            throw new RuntimeException('Unable to create managed email template root.');
        }

        $rootReal = realpath($managedRoot);
        $parent = dirname($path);
        while (!is_dir($parent) && dirname($parent) !== $parent) {
            $parent = dirname($parent);
        }
        $parentReal = realpath($parent);
        if (!is_string($rootReal) || !is_string($parentReal)) {
            throw new RuntimeException('Unable to validate managed email template path.');
        }

        $rootReal = rtrim(str_replace('\\', '/', $rootReal), '/') . '/';
        $parentReal = rtrim(str_replace('\\', '/', $parentReal), '/') . '/';
        if (!str_starts_with($parentReal, $rootReal)) {
            throw new RuntimeException('Managed email template path escaped through a linked directory.');
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    private function encodeJson(array $data): string
    {
        $encoded = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (!is_string($encoded)) {
            throw new RuntimeException('Unable to encode email template JSON.');
        }

        return $encoded . PHP_EOL;
    }

    private function normalizeLocale(string $locale): string
    {
        $locale = strtolower(trim(str_replace('_', '-', $locale)));

        return preg_match('/^[a-z]{2,3}(?:-[a-z0-9]{2,8})?$/', $locale) === 1
            ? $locale
            : self::DEFAULT_LOCALE;
    }

    /**
     * @param array<int, string> $roots
     */
    private function inferRootFromLegacyRoots(array $roots): string
    {
        $first = rtrim((string) ($roots[0] ?? ''), '/\\');
        if ($first === '') {
            return dirname(__DIR__, 3) . '/Repository/Framework/Mail';
        }

        return dirname($first);
    }
}
