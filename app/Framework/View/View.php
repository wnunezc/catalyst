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
 * Responsibility: Resolves templates, prepares rendering scope and delegates constrained token rendering.
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
     * Initializes the default framework view paths and token renderer.
     *
     * Responsibility: Prepares the view resolver state used to locate templates and render tokenized output.
     */
    protected function __construct()
    {
        $this->paths = [
            'framework' => implode(DS, [PD, 'boot-core', 'template']),
        ];
        $this->tokenRenderer = new ViewTokenRenderer();
    }

    /**
     * Registers a named directory as a view lookup path.
     *
     * Responsibility: Extends the view namespace map used to resolve templates and partials.
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
     * Returns registered view search paths.
     *
     * Responsibility: Returns registered view search paths.
     * @return array<string, string>
     */
    public function getPaths(): array
    {
        return $this->paths;
    }

    /**
     * Share data with all views.
     *
     * Responsibility: Share data with all views.
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
     * Renders a complete HTML document using the canonical document template.
     *
     * Responsibility: Resolves a page template, prepares the shared document scope and wraps the result in document.phtml.
     *
     * @param string $template Template name using dot notation
     * @param array<string, mixed> $data Data to pass to the template
     */
    public function render(string $template, array $data = [], int $status = 200): Response
    {
        $mergedData = $this->mergedData($data);
        $content = $this->renderResolvedTemplate($template, $mergedData);
        $documentPath = $this->findDocument();

        if ($documentPath === null) {
            throw ViewException::templateNotFound('framework.document');
        }

        $documentScope = DocumentScope::prepare(array_merge(
            $mergedData,
            ['content' => TrustedHtml::fromString($content)]
        ));

        return new Response($this->renderTemplate($documentPath, $documentScope), $status);
    }

    /**
     * Renders a template without the canonical HTML document wrapper.
     *
     * Responsibility: Provides an explicit response path for fragments, modal bodies and other insertable HTML.
     *
     * @param string $template Template name using dot notation
     * @param array<string, mixed> $data Data to pass to the template
     */
    public function renderFragment(string $template, array $data = [], int $status = 200): Response
    {
        $mergedData = $this->mergedData($data);

        return new Response($this->renderResolvedTemplate($template, $mergedData), $status);
    }

    /**
     * Find template file in registered paths Searches module paths registered via addPath() first and keeps the framework template path as the final fallback.
     *
     * Responsibility: Find template file in registered paths Searches module paths registered via addPath() first and keeps the framework template path as the final fallback.
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
     * Resolves the single framework document template.
     *
     * Responsibility: Keeps complete-page rendering bound to boot-core/template/document.phtml.
     */
    protected function findDocument(): ?string
    {
        return $this->findTemplateFile(
            implode(DS, [PD, 'boot-core', 'template', 'document'])
        );
    }

    /**
     * Merges shared, form and caller data for one render operation.
     *
     * Responsibility: Applies the same input scope rules to complete documents and explicit fragments.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function mergedData(array $data): array
    {
        return array_merge($this->shared, $this->consumeFormState(), $data);
    }

    /**
     * Resolves and renders a named template.
     *
     * Responsibility: Centralizes template-not-found handling for document and fragment responses.
     *
     * @param array<string, mixed> $data
     */
    private function renderResolvedTemplate(string $template, array $data): string
    {
        $templatePath = $this->findTemplate($template);

        if ($templatePath === null) {
            throw ViewException::templateNotFound($template);
        }

        return $this->renderTemplate($templatePath, $data);
    }

    /**
     * Render template file with data.
     *
     * Responsibility: Render template file with data.
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
     * Check if a template exists.
     *
     * Responsibility: Check if a template exists.
     * @param string $template Template name (dot notation)
     * @return bool
     */
    public function exists(string $template): bool
    {
        return $this->findTemplate($template) !== null;
    }

    /**
     * Consumes stored form input and validation state for rendering.
     *
     * Responsibility: Consumes stored form input and validation state for rendering.
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
     * Renders an arbitrary constrained token fragment.
     *
     * Responsibility: Renders an arbitrary constrained token fragment.
     * @param array<string, mixed> $scope
     */
    public function renderTokenFragment(string $fragment, array $scope, ?string $templatePath = null): string
    {
        $scope = $this->sanitizeTokenScope($scope);

        return $this->tokenRenderer->render($fragment, $scope, $templatePath, $this);
    }

    /**
     * Renders a partial template referenced by a token template.
     *
     * Responsibility: Renders a partial template referenced by a token template.
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
     * Determines whether a template uses constrained token rendering.
     *
     * Responsibility: Determines whether a template uses constrained token rendering.
     */
    private function isTokenTemplate(string $templatePath): bool
    {
        return strtolower((string) pathinfo($templatePath, PATHINFO_EXTENSION)) === 'phtml';
    }

    /**
     * Finds the first existing template file for a path without extension.
     *
     * Responsibility: Finds the first existing template file for a path without extension.
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
     * Renders a constrained token template file.
     *
     * Responsibility: Renders a constrained token template file.
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
     * Removes reserved keys from a token-template scope.
     *
     * Responsibility: Removes reserved keys from a token-template scope.
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
     * Applies the optional PHP companion for a token template.
     *
     * Responsibility: Applies the optional PHP companion for a token template.
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
     * Resolves the optional PHP companion for a token template.
     *
     * Responsibility: Resolves the optional PHP companion for a token template.
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
     * Resolves a partial template reference relative to its caller when possible.
     *
     * Responsibility: Resolves a partial template reference relative to its caller when possible.
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
     * Determines whether a partial reference is a quoted literal.
     *
     * Responsibility: Determines whether a partial reference is a quoted literal.
     */
    private function isQuotedLiteral(string $value): bool
    {
        return (str_starts_with($value, '\'') && str_ends_with($value, '\''))
            || (str_starts_with($value, '"') && str_ends_with($value, '"'));
    }

    /**
     * Builds candidate template paths for a registered base.
     *
     * Responsibility: Builds candidate template paths for a registered base.
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
     * Builds candidate paths for a module template.
     *
     * Responsibility: Builds candidate paths for a module template.
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
     * Builds candidate paths for a framework template.
     *
     * Responsibility: Builds candidate paths for a framework template.
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
     * Returns normalized registered view bases and allowed directories.
     *
     * Responsibility: Returns normalized registered view bases and allowed directories.
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
     * Returns a template path relative to a registered view base.
     *
     * Responsibility: Returns a template path relative to a registered view base.
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
