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

namespace Catalyst\Framework\View;

use Catalyst\Framework\Http\Response;
use Catalyst\Framework\Session\SessionManager;
use Catalyst\Framework\Traits\SingletonTrait;
use Catalyst\Helpers\Exceptions\ViewException;

/**
 * View - Template rendering system
 *
 * Handles loading and rendering of view templates with data binding.
 *
 * @package Catalyst\Framework\View
 */
class View
{
    use SingletonTrait;

    private const TEMPLATE_EXTENSIONS = ['phtml', 'php'];
    private const MODULE_TEMPLATE_DIRECTORIES = ['pages', 'partials', 'components'];
    private const FRAMEWORK_TEMPLATE_DIRECTORIES = ['layouts', 'pages', 'components', 'errors', 'debug'];
    private const SCOPE_DIRECTORY = 'scope';

    /**
     * Base paths for view templates
     *
     * @var array<string, string>
     */
    protected array $paths = [];

    /**
     * Shared data available to all views
     *
     * @var array
     */
    protected array $shared = [];

    protected ViewTokenRenderer $tokenRenderer;

    /**
     * Constructor - Initialize default paths
     */
    protected function __construct()
    {
        $this->paths = [
            'framework' => implode(DS, [PD, 'boot-core', 'template']),
        ];
        $this->tokenRenderer = new ViewTokenRenderer();
    }

    /**
     * Add a view path
     *
     * @param string $name Path identifier
     * @param string $path Absolute path to views directory
     * @return self
     */
    public function addPath(string $name, string $path): self
    {
        $this->paths[$name] = $path;
        return $this;
    }

    /**
     * @return array<string, string>
     */
    public function getPaths(): array
    {
        return $this->paths;
    }

    /**
     * Share data with all views
     *
     * @param string $key Variable name
     * @param mixed $value Variable value
     * @return self
     */
    public function share(string $key, mixed $value): self
    {
        $this->shared[$key] = $value;
        return $this;
    }

    /**
     * Render a view template, optionally wrapped in a layout.
     *
     * Template naming convention:
     * - "pages.welcome" → looks for "pages/welcome.phtml" then "pages/welcome.php"
     * - "error.404" → looks for "errors/404.phtml" then "errors/404.php"
     *
     * `.phtml` is the canonical declarative template format and is rendered
     * through the token pipeline without allowing inline PHP.
     * `.php` remains available as the backwards-compatible executable template
     * fallback while the framework completes migration.
     *
     * Layout convention:
     * - "base" → looks for "boot-core/template/layouts/base.phtml" then "base.php"
     * - Layout receives merged $data + $content (rendered template output)
     *
     * @param string      $template Template name using dot notation
     * @param array       $data     Data to pass to the template
     * @param int         $status   HTTP status code
     * @param string|null $layout   Layout name, or null for no layout
     * @return Response
     * @throws ViewException If template or layout is not found
     */
    public function render(string $template, array $data = [], int $status = 200, ?string $layout = null): Response
    {
        $templatePath = $this->findTemplate($template);

        if ($templatePath === null) {
            throw ViewException::templateNotFound($template);
        }

        $mergedData = array_merge($this->shared, $this->consumeFormState(), $data);
        $content    = $this->renderTemplate($templatePath, $mergedData);

        if ($layout !== null) {
            $layoutPath = $this->findLayout($layout);

            if ($layoutPath === null) {
                throw ViewException::layoutNotFound($layout);
            }

            $content = $this->renderTemplate($layoutPath, array_merge($mergedData, ['content' => TrustedHtml::fromString($content)]));
        }

        return new Response($content, $status);
    }

    /**
     * Find template file in registered paths
     *
     * Searches module paths registered via addPath() first and keeps the
     * framework template path as the final fallback.
     *
     * @param string $template Template name (dot notation)
     * @return string|null Full path to template or null if not found
     */
    protected function findTemplate(string $template): ?string
    {
        // Convert dot notation to directory path without extension.
        $relativePath = str_replace('.', DS, $template);

        // Decompose namespace prefix (first segment before the first dot)
        $segments = explode('.', $template);
        $prefix   = $segments[0];

        // Build search order: registered module paths first, framework last
        $prioritized = [];
        $rest        = [];

        foreach ($this->paths as $key => $path) {
            if ($key === 'framework') {
                $rest[] = [$key, $path];
            } else {
                $prioritized[] = [$key, $path];
            }
        }

        $searchOrder = array_merge($prioritized, $rest);

        foreach ($searchOrder as [$pathKey, $basePath]) {
            // Namespace-aware lookup: when the path key matches the template's
            // first segment (e.g. 'auth' for 'auth.login'), strip the prefix
            // and look for 'login.phtml' directly inside that path's directory.
            if ($pathKey === $prefix && count($segments) > 1) {
                $stripped = str_replace('.', DS, implode('.', array_slice($segments, 1)));
                foreach ($this->buildTemplateCandidates($basePath, $stripped, $pathKey === 'framework') as $candidate) {
                    $fullPath = $this->findTemplateFile($candidate);
                    if ($fullPath !== null) {
                        return $fullPath;
                    }
                }
            }

            // Standard lookup: full relative path in this base directory
            foreach ($this->buildTemplateCandidates($basePath, $relativePath, $pathKey === 'framework') as $candidate) {
                $fullPath = $this->findTemplateFile($candidate);
                if ($fullPath !== null) {
                    return $fullPath;
                }
            }
        }

        return null;
    }

    /**
     * Find layout file in the layouts directory.
     *
     * Layouts are located in: boot-core/template/layouts/{name}.phtml
     * with backwards-compatible fallback to .php
     *
     * @param string $layout Layout name (without template extension)
     * @return string|null Full path to layout file, or null if not found
     */
    protected function findLayout(string $layout): ?string
    {
        return $this->findTemplateFile(
            implode(DS, [PD, 'boot-core', 'template', 'layouts', $layout])
        );
    }

    /**
     * Render template file with data
     *
     * @param string $templatePath Full path to template
     * @param array $data Data to extract into template scope
     * @return string Rendered content
     */
    protected function renderTemplate(string $templatePath, array $data): string
    {
        $previousOld = $GLOBALS['CATALYST_VIEW_OLD_INPUT'] ?? null;
        $previousErrors = $GLOBALS['CATALYST_VIEW_VALIDATION_ERRORS'] ?? null;
        $GLOBALS['CATALYST_VIEW_OLD_INPUT'] = $data['__old'] ?? [];
        $GLOBALS['CATALYST_VIEW_VALIDATION_ERRORS'] = $data['__validationErrors'] ?? ['default' => []];

        try {
            if ($this->isTokenTemplate($templatePath)) {
                return $this->renderTokenTemplate($templatePath, $data);
            }

            ob_start();
            extract($data, EXTR_SKIP);
            require $templatePath;

            return ob_get_clean() ?: '';
        } finally {
            if ($previousOld !== null) {
                $GLOBALS['CATALYST_VIEW_OLD_INPUT'] = $previousOld;
            } else {
                unset($GLOBALS['CATALYST_VIEW_OLD_INPUT']);
            }

            if ($previousErrors !== null) {
                $GLOBALS['CATALYST_VIEW_VALIDATION_ERRORS'] = $previousErrors;
            } else {
                unset($GLOBALS['CATALYST_VIEW_VALIDATION_ERRORS']);
            }
        }
    }

    /**
     * Check if a template exists
     *
     * @param string $template Template name (dot notation)
     * @return bool
     */
    public function exists(string $template): bool
    {
        return $this->findTemplate($template) !== null;
    }

    /**
     * @return array{__old: array<string, mixed>, __validationErrors: array<string, array<string, string[]>>}
     */
    private function consumeFormState(): array
    {
        try {
            $session = SessionManager::getInstance();

            if (!$session->isInitialized()) {
                return ['__old' => [], '__validationErrors' => ['default' => []]];
            }

            return [
                '__old' => $session->consumeOldInput(),
                '__validationErrors' => $session->consumeValidationErrors(),
            ];
        } catch (\Throwable) {
            return ['__old' => [], '__validationErrors' => ['default' => []]];
        }
    }

    /**
     * @param array<string, mixed> $scope
     */
    public function renderTokenFragment(string $fragment, array $scope, ?string $templatePath = null): string
    {
        $scope = $this->sanitizeTokenScope($scope);

        return $this->tokenRenderer->render($fragment, $scope, $templatePath, $this);
    }

    /**
     * @param array<string, mixed> $scope
     */
    public function renderPartial(string $reference, array $scope, ?string $fromTemplatePath = null): string
    {
        $templatePath = $this->resolvePartialTemplatePath($reference, $fromTemplatePath);
        if ($templatePath === null) {
            throw ViewException::templateNotFound($reference);
        }

        return $this->renderTemplate($templatePath, $scope);
    }

    /**
     * Determines whether is Token Template.
     */
    private function isTokenTemplate(string $templatePath): bool
    {
        return strtolower((string) pathinfo($templatePath, PATHINFO_EXTENSION)) === 'phtml';
    }

    /**
     * Finds the requested record.
     */
    private function findTemplateFile(string $basePathWithoutExtension): ?string
    {
        foreach (self::TEMPLATE_EXTENSIONS as $extension) {
            $candidate = $basePathWithoutExtension . '.' . $extension;
            if (file_exists($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $scope
     */
    private function renderTokenTemplate(string $templatePath, array $scope): string
    {
        $preparedScope = $this->sanitizeTokenScope(
            $this->applyTokenTemplateCompanion($templatePath, $scope)
        );

        $source = file_get_contents($templatePath);
        if (!is_string($source)) {
            throw ViewException::templateNotFound($templatePath);
        }

        if (preg_match('/<\?(?:php|=)?/i', $source) === 1) {
            throw ViewException::invalidTokenTemplate($templatePath);
        }

        return $this->tokenRenderer->render($source, $preparedScope, $templatePath, $this);
    }

    /**
     * @param array<string, mixed> $scope
     * @return array<string, mixed>
     */
    private function sanitizeTokenScope(array $scope): array
    {
        unset($scope['GLOBALS']);

        foreach (array_keys($scope) as $key) {
            if (str_starts_with((string) $key, '__catalyst')) {
                unset($scope[$key]);
            }
        }

        $scope['@root'] ??= $scope;

        return $scope;
    }

    /**
     * @param array<string, mixed> $scope
     * @return array<string, mixed>
     */
    private function applyTokenTemplateCompanion(string $templatePath, array $scope): array
    {
        $companionPath = $this->resolveTokenTemplateCompanionPath($templatePath);
        if ($companionPath === null || !file_exists($companionPath)) {
            return $scope;
        }

        $companion = require $companionPath;

        if (is_callable($companion)) {
            $companion = $companion($scope, $this, $templatePath);
        }

        if ($companion === null) {
            return $scope;
        }

        if (!is_array($companion)) {
            throw new \RuntimeException("View companion must return an array, callable, or null: {$companionPath}");
        }

        return array_merge($scope, $companion);
    }

    /**
     * Resolves the requested value.
     */
    private function resolveTokenTemplateCompanionPath(string $templatePath): ?string
    {
        if (!$this->isTokenTemplate($templatePath)) {
            return null;
        }

        foreach ($this->registeredViewBases() as $basePath => $directories) {
            $relativePath = $this->relativeTemplatePath($templatePath, $basePath);
            if ($relativePath === null) {
                continue;
            }

            $normalized = str_replace(['/', '\\'], DS, $relativePath);
            $segments = array_values(array_filter(explode(DS, $normalized), static fn (string $segment): bool => $segment !== ''));
            if ($segments === []) {
                continue;
            }

            $directory = $segments[0];
            if (!in_array($directory, $directories, true)) {
                continue;
            }

            array_unshift($segments, self::SCOPE_DIRECTORY);
            $segments[count($segments) - 1] = pathinfo($segments[count($segments) - 1], PATHINFO_FILENAME) . '.php';

            return $basePath . DS . implode(DS, $segments);
        }

        return null;
    }

    /**
     * Resolves the requested value.
     */
    private function resolvePartialTemplatePath(string $reference, ?string $fromTemplatePath): ?string
    {
        $reference = trim($reference);
        if ($reference === '') {
            return null;
        }

        if ($this->isQuotedLiteral($reference)) {
            $reference = substr($reference, 1, -1);
        }

        if ($fromTemplatePath !== null && preg_match('#^(?:\./|\.\./)#', $reference) === 1) {
            $relativeReference = str_replace(['/', '\\'], DS, $reference);
            $candidate = dirname($fromTemplatePath) . DS . $relativeReference;
            $resolved = pathinfo($candidate, PATHINFO_EXTENSION) === ''
                ? $this->findTemplateFile($candidate)
                : (file_exists($candidate) ? $candidate : null);

            if ($resolved !== null) {
                return $resolved;
            }
        }

        return $this->findTemplate($reference);
    }

    /**
     * Determines whether is Quoted Literal.
     */
    private function isQuotedLiteral(string $value): bool
    {
        return (str_starts_with($value, '\'') && str_ends_with($value, '\''))
            || (str_starts_with($value, '"') && str_ends_with($value, '"'));
    }

    /**
     * @return list<string>
     */
    private function buildTemplateCandidates(string $basePath, string $relativePath, bool $isFramework): array
    {
        $relativePath = trim(str_replace(['/', '\\'], DS, $relativePath), DS);
        if ($relativePath === '') {
            return [];
        }

        $segments = array_values(array_filter(explode(DS, $relativePath), static fn (string $segment): bool => $segment !== ''));
        if ($segments === []) {
            return [];
        }

        if ($isFramework) {
            return $this->buildFrameworkTemplateCandidates($basePath, $segments);
        }

        return $this->buildModuleTemplateCandidates($basePath, $segments);
    }

    /**
     * @param list<string> $segments
     * @return list<string>
     */
    private function buildModuleTemplateCandidates(string $basePath, array $segments): array
    {
        $relativePath = implode(DS, $segments);
        $firstSegment = $segments[0];

        if (in_array($firstSegment, self::MODULE_TEMPLATE_DIRECTORIES, true)) {
            return [$basePath . DS . $relativePath];
        }

        return [$basePath . DS . 'pages' . DS . $relativePath];
    }

    /**
     * @param list<string> $segments
     * @return list<string>
     */
    private function buildFrameworkTemplateCandidates(string $basePath, array $segments): array
    {
        $relativePath = implode(DS, $segments);
        $firstSegment = $segments[0];

        if ($firstSegment === 'error') {
            $segments[0] = 'errors';
            $relativePath = implode(DS, $segments);
            return [$basePath . DS . $relativePath];
        }

        if (in_array($firstSegment, self::FRAMEWORK_TEMPLATE_DIRECTORIES, true)) {
            return [$basePath . DS . $relativePath];
        }

        return [$basePath . DS . 'pages' . DS . $relativePath];
    }

    /**
     * @return array<string, list<string>>
     */
    private function registeredViewBases(): array
    {
        $bases = [];

        foreach ($this->paths as $key => $path) {
            $normalizedPath = rtrim(str_replace(['/', '\\'], DS, $path), DS);
            $bases[$normalizedPath] = $key === 'framework'
                ? self::FRAMEWORK_TEMPLATE_DIRECTORIES
                : self::MODULE_TEMPLATE_DIRECTORIES;
        }

        uksort($bases, static fn (string $left, string $right): int => strlen($right) <=> strlen($left));

        return $bases;
    }

    /**
     * Handles the relative template path workflow.
     */
    private function relativeTemplatePath(string $templatePath, string $basePath): ?string
    {
        $normalizedTemplate = rtrim(str_replace(['/', '\\'], DS, $templatePath), DS);
        $normalizedBase = rtrim(str_replace(['/', '\\'], DS, $basePath), DS);

        if ($normalizedTemplate === $normalizedBase) {
            return '';
        }

        $prefix = $normalizedBase . DS;
        if (!str_starts_with($normalizedTemplate, $prefix)) {
            return null;
        }

        return substr($normalizedTemplate, strlen($prefix));
    }
}
