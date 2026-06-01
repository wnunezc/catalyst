<?php

declare(strict_types=1);

namespace Catalyst\Framework\View;

use ArrayAccess;
use Catalyst\Helpers\I18n\Translator;
use Traversable;

final class ViewTokenRenderer
{
    private const ESCAPED_DOUBLE = "\u{E100}";
    private const ESCAPED_TRIPLE = "\u{E101}";

    public function __construct(
        ?Translator $translator = null
    ) {
        $this->translator = $translator ?? Translator::getInstance();
    }

    private readonly Translator $translator;

    /**
     * @param array<string, mixed> $scope
     */
    public function render(string $fragment, array $scope, ?string $templatePath = null, ?View $view = null): string
    {
        if (!str_contains($fragment, '{{') && !str_contains($fragment, '@{{')) {
            return $fragment;
        }

        $escaped = str_replace(
            ['@{{{', '@{{'],
            [self::ESCAPED_TRIPLE, self::ESCAPED_DOUBLE],
            $fragment
        );

        $rendered = $this->renderSegment($escaped, $scope, $templatePath, $view);

        return str_replace(
            [self::ESCAPED_TRIPLE, self::ESCAPED_DOUBLE],
            ['{{{', '{{'],
            $rendered
        );
    }

    /**
     * @param array<string, mixed> $scope
     */
    private function renderSegment(string $fragment, array $scope, ?string $templatePath, ?View $view): string
    {
        $output = '';
        $cursor = 0;

        while (true) {
            $block = $this->findNextBlock($fragment, $cursor);

            if ($block === null) {
                $output .= $this->renderInline(substr($fragment, $cursor), $scope, $templatePath, $view);
                break;
            }

            $output .= $this->renderInline(substr($fragment, $cursor, $block['start'] - $cursor), $scope, $templatePath, $view);

            $closing = $this->findClosingBlock($fragment, $block['content_start'], $block['type']);
            if ($closing === null) {
                $output .= $this->renderInline(substr($fragment, $block['start']), $scope, $templatePath, $view);
                break;
            }

            $body = substr($fragment, $block['content_start'], $closing['start'] - $block['content_start']);
            $output .= $this->renderBlock($block['type'], $block['expression'], $body, $scope, $templatePath, $view);

            $cursor = $closing['start'] + $closing['length'];
        }

        return $output;
    }

    /**
     * @param array<string, mixed> $scope
     */
    private function renderInline(string $fragment, array $scope, ?string $templatePath, ?View $view): string
    {
        $rendered = preg_replace_callback(
            '/{{>\s*(.+?)\s*}}/s',
            fn (array $matches): string => $this->renderPartial(
                (string) ($matches[1] ?? ''),
                $scope,
                $templatePath,
                $view
            ),
            $fragment
        );

        if (!is_string($rendered)) {
            $rendered = $fragment;
        }

        $rendered = preg_replace_callback(
            '/{{{\s*(.+?)\s*}}}/s',
            fn (array $matches): string => $this->renderValue((string) ($matches[1] ?? ''), $scope, true),
            $rendered
        );

        if (!is_string($rendered)) {
            $rendered = $fragment;
        }

        $rendered = preg_replace_callback(
            '/{{\s*(.+?)\s*}}/s',
            fn (array $matches): string => $this->renderValue((string) ($matches[1] ?? ''), $scope, false),
            $rendered
        );

        return is_string($rendered) ? $rendered : $fragment;
    }

    /**
     * @param array<string, mixed> $scope
     */
    private function renderBlock(string $type, string $expression, string $body, array $scope, ?string $templatePath, ?View $view): string
    {
        $value = $this->resolveExpression($expression, $scope);
        ['truthy' => $truthyBody, 'falsey' => $falseyBody] = $this->splitBlockBody($body);

        if ($type === 'if') {
            return $this->isTruthy($value)
                ? $this->renderSegment($truthyBody, $scope, $templatePath, $view)
                : $this->renderSegment($falseyBody, $scope, $templatePath, $view);
        }

        if ($type === 'unless') {
            return !$this->isTruthy($value)
                ? $this->renderSegment($truthyBody, $scope, $templatePath, $view)
                : $this->renderSegment($falseyBody, $scope, $templatePath, $view);
        }

        if ($type !== 'each') {
            return '';
        }

        $items = $this->iterableToArray($value);
        if ($items === []) {
            return $this->renderSegment($falseyBody, $scope, $templatePath, $view);
        }

        $output = '';
        $index = 0;

        foreach ($items as $key => $item) {
            $loopScope = $scope;
            $loopScope['this'] = $item;
            $loopScope['@key'] = $key;
            $loopScope['@index'] = $index;
            $loopScope['@first'] = $index === 0;
            $loopScope['@last'] = $index === array_key_last($items);

            $output .= $this->renderSegment($truthyBody, $loopScope, $templatePath, $view);
            $index++;
        }

        return $output;
    }

    /**
     * @param array<string, mixed> $scope
     * @return array{type: string, expression: string, start: int, content_start: int}|null
     */
    private function findNextBlock(string $fragment, int $offset): ?array
    {
        $matched = preg_match(
            '/{{#(if|unless|each)\s+(.+?)}}/s',
            $fragment,
            $matches,
            PREG_OFFSET_CAPTURE,
            $offset
        );

        if ($matched !== 1) {
            return null;
        }

        $token = $matches[0][0] ?? '';
        $start = (int) ($matches[0][1] ?? 0);

        return [
            'type' => (string) ($matches[1][0] ?? ''),
            'expression' => trim((string) ($matches[2][0] ?? '')),
            'start' => $start,
            'content_start' => $start + strlen($token),
        ];
    }

    /**
     * @return array{start: int, length: int}|null
     */
    private function findClosingBlock(string $fragment, int $offset, string $outerType): ?array
    {
        $pattern = '/{{#(if|unless|each)\s+.+?}}|{{\/(if|unless|each)}}/s';
        $depth = 1;

        while (preg_match($pattern, $fragment, $matches, PREG_OFFSET_CAPTURE, $offset) === 1) {
            $token = (string) ($matches[0][0] ?? '');
            $start = (int) ($matches[0][1] ?? 0);
            $nestedType = (string) ($matches[1][0] ?? '');
            $closingType = (string) ($matches[2][0] ?? '');

            if ($nestedType !== '') {
                $depth++;
            } elseif ($closingType !== '') {
                $depth--;

                if ($depth === 0 && $closingType === $outerType) {
                    return [
                        'start' => $start,
                        'length' => strlen($token),
                    ];
                }
            }

            $offset = $start + strlen($token);
        }

        return null;
    }

    /**
     * @return array{truthy: string, falsey: string}
     */
    private function splitBlockBody(string $body): array
    {
        $pattern = '/{{#(if|unless|each)\s+.+?}}|{{\/(if|unless|each)}}|{{else}}/s';
        $depth = 0;
        $offset = 0;

        while (preg_match($pattern, $body, $matches, PREG_OFFSET_CAPTURE, $offset) === 1) {
            $token = (string) ($matches[0][0] ?? '');
            $start = (int) ($matches[0][1] ?? 0);
            $nestedType = (string) ($matches[1][0] ?? '');
            $closingType = (string) ($matches[2][0] ?? '');

            if ($nestedType !== '') {
                $depth++;
            } elseif ($closingType !== '') {
                $depth = max(0, $depth - 1);
            } elseif ($token === '{{else}}' && $depth === 0) {
                return [
                    'truthy' => substr($body, 0, $start),
                    'falsey' => substr($body, $start + strlen($token)),
                ];
            }

            $offset = $start + strlen($token);
        }

        return ['truthy' => $body, 'falsey' => ''];
    }

    /**
     * @param array<string, mixed> $scope
     */
    private function renderValue(string $expression, array $scope, bool $raw): string
    {
        $value = $this->resolveExpression($expression, $scope);

        if ($raw && $value instanceof TrustedHtml) {
            return $value->toHtml();
        }

        return e($this->stringify($value));
    }

    /**
     * @param array<string, mixed> $scope
     */
    private function renderPartial(string $expression, array $scope, ?string $templatePath, ?View $view): string
    {
        if ($view === null) {
            return '';
        }

        return $view->renderPartial(trim($expression), $scope, $templatePath);
    }

    /**
     * @param array<string, mixed> $scope
     */
    private function resolveExpression(string $expression, array $scope): mixed
    {
        $expression = trim($expression);

        if ($expression === '') {
            return null;
        }

        if ($this->isQuotedLiteral($expression)) {
            return substr($expression, 1, -1);
        }

        return str_starts_with($expression, 't:')
            ? $this->translate(substr($expression, 2), $scope)
            : $this->resolvePath($scope, $expression);
    }

    /**
     * @param array<string, mixed> $scope
     */
    private function translate(string $expression, array $scope): string
    {
        $parts = preg_split('/\s+/', trim($expression)) ?: [];
        $key = trim((string) array_shift($parts));

        if ($key === '') {
            return '';
        }

        $replacements = [];
        foreach ($parts as $part) {
            if (!str_contains($part, '=')) {
                continue;
            }

            [$name, $valueExpression] = explode('=', $part, 2);
            $name = trim($name);
            $valueExpression = trim($valueExpression);

            if ($name === '' || $valueExpression === '') {
                continue;
            }

            $value = $this->resolveExpression($valueExpression, $scope);
            $replacements[$name] = $this->stringify($value);
        }

        return $this->translator->get($key, $replacements);
    }

    /**
     * @param array<string, mixed> $scope
     */
    private function resolvePath(array $scope, string $path): mixed
    {
        if (array_key_exists($path, $scope)) {
            return $scope[$path];
        }

        if (str_starts_with($path, 'this.')) {
            return $this->resolveNested($scope['this'] ?? null, substr($path, 5));
        }

        if (str_starts_with($path, '@root.')) {
            return $this->resolveNested($scope['@root'] ?? null, substr($path, 6));
        }

        if (isset($scope['this'])) {
            $resolved = $this->resolveNestedWithExistence($scope['this'], $path);
            if ($resolved['exists']) {
                return $resolved['value'];
            }
        }

        return $this->resolveNested($scope['@root'] ?? $scope, $path);
    }

    private function resolveNested(mixed $value, string $path): mixed
    {
        return $this->resolveNestedWithExistence($value, $path)['value'];
    }

    /**
     * @return array{exists: bool, value: mixed}
     */
    private function resolveNestedWithExistence(mixed $value, string $path): array
    {
        $path = trim($path);

        if ($path === '') {
            return ['exists' => true, 'value' => $value];
        }

        $segments = array_values(array_filter(
            explode('.', $path),
            static fn (string $segment): bool => $segment !== ''
        ));

        $current = $value;

        foreach ($segments as $segment) {
            $lookup = $this->resolveSegment($current, $segment);
            if (!$lookup['exists']) {
                return ['exists' => false, 'value' => null];
            }

            $current = $lookup['value'];
        }

        return ['exists' => true, 'value' => $current];
    }

    /**
     * @return array{exists: bool, value: mixed}
     */
    private function resolveSegment(mixed $value, string $segment): array
    {
        if (is_array($value) && array_key_exists($segment, $value)) {
            return ['exists' => true, 'value' => $value[$segment]];
        }

        if ($value instanceof ArrayAccess && $value->offsetExists($segment)) {
            return ['exists' => true, 'value' => $value[$segment]];
        }

        if ($value instanceof Traversable) {
            $arrayValue = iterator_to_array($value);
            if (array_key_exists($segment, $arrayValue)) {
                return ['exists' => true, 'value' => $arrayValue[$segment]];
            }
        }

        if (!is_object($value)) {
            return ['exists' => false, 'value' => null];
        }

        $vars = get_object_vars($value);
        if (array_key_exists($segment, $vars)) {
            return ['exists' => true, 'value' => $vars[$segment]];
        }

        $camel = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $segment)));
        foreach (['get' . $camel, 'is' . $camel, 'has' . $camel] as $method) {
            if (method_exists($value, $method)) {
                return ['exists' => true, 'value' => $value->{$method}()];
            }
        }

        if (method_exists($value, 'toArray')) {
            $arrayValue = $value->toArray();
            if (is_array($arrayValue) && array_key_exists($segment, $arrayValue)) {
                return ['exists' => true, 'value' => $arrayValue[$segment]];
            }
        }

        return ['exists' => false, 'value' => null];
    }

    /**
     * @return array<int|string, mixed>
     */
    private function iterableToArray(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if ($value instanceof Traversable) {
            return iterator_to_array($value);
        }

        return [];
    }

    private function stringify(mixed $value): string
    {
        if ($value instanceof TrustedHtml) {
            return $value->toHtml();
        }

        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        if (is_object($value) && method_exists($value, '__toString')) {
            return (string) $value;
        }

        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '';
    }

    private function isQuotedLiteral(string $expression): bool
    {
        return (str_starts_with($expression, '\'') && str_ends_with($expression, '\''))
            || (str_starts_with($expression, '"') && str_ends_with($expression, '"'));
    }

    private function isTruthy(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_array($value)) {
            return $value !== [];
        }

        if ($value instanceof Traversable) {
            foreach ($value as $_) {
                return true;
            }

            return false;
        }

        return $value !== null && $value !== '' && $value !== 0 && $value !== '0';
    }
}
