<?php

declare(strict_types=1);

namespace Catalyst\Framework\View;

use DOMDocument;
use DOMElement;
use DOMNode;

final class HtmlAllowlistSanitizer
{
    /** @var array<string, true> */
    private const ALLOWED_TAGS = [
        'a' => true,
        'article' => true,
        'b' => true,
        'blockquote' => true,
        'br' => true,
        'code' => true,
        'div' => true,
        'em' => true,
        'h1' => true,
        'h2' => true,
        'h3' => true,
        'h4' => true,
        'h5' => true,
        'h6' => true,
        'hr' => true,
        'i' => true,
        'li' => true,
        'ol' => true,
        'p' => true,
        'pre' => true,
        'section' => true,
        'span' => true,
        'strong' => true,
        'table' => true,
        'tbody' => true,
        'td' => true,
        'th' => true,
        'thead' => true,
        'tr' => true,
        'u' => true,
        'ul' => true,
    ];

    /** @var array<string, true> */
    private const DROP_CONTENT_TAGS = [
        'base' => true,
        'button' => true,
        'embed' => true,
        'form' => true,
        'iframe' => true,
        'input' => true,
        'link' => true,
        'meta' => true,
        'object' => true,
        'script' => true,
        'style' => true,
        'textarea' => true,
    ];

    /** @var array<string, true> */
    private const GLOBAL_ATTRIBUTES = [
        'class' => true,
        'title' => true,
    ];

    /** @var array<string, array<string, true>> */
    private const TAG_ATTRIBUTES = [
        'a' => [
            'href' => true,
            'rel' => true,
            'target' => true,
        ],
        'td' => [
            'colspan' => true,
            'rowspan' => true,
        ],
        'th' => [
            'colspan' => true,
            'rowspan' => true,
        ],
    ];

    public function sanitize(string $html): string
    {
        if (trim($html) === '') {
            return '';
        }

        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML(
            '<?xml encoding="UTF-8"><div data-catalyst-sanitizer-root="1">' . $html . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $document->getElementsByTagName('div')->item(0);
        if (!$root instanceof DOMElement) {
            return '';
        }

        $this->sanitizeChildren($root);

        $result = '';
        foreach (iterator_to_array($root->childNodes) as $child) {
            $result .= $document->saveHTML($child);
        }

        return $result;
    }

    private function sanitizeChildren(DOMNode $parent): void
    {
        foreach (iterator_to_array($parent->childNodes) as $child) {
            if (!$child instanceof DOMElement) {
                if ($child->nodeType === XML_COMMENT_NODE) {
                    $parent->removeChild($child);
                }

                continue;
            }

            $tag = strtolower($child->tagName);

            if (isset(self::DROP_CONTENT_TAGS[$tag])) {
                $parent->removeChild($child);

                continue;
            }

            if (!isset(self::ALLOWED_TAGS[$tag])) {
                $this->sanitizeChildren($child);
                while ($child->firstChild !== null) {
                    $parent->insertBefore($child->firstChild, $child);
                }
                $parent->removeChild($child);

                continue;
            }

            $this->sanitizeAttributes($child, $tag);
            $this->sanitizeChildren($child);
        }
    }

    private function sanitizeAttributes(DOMElement $element, string $tag): void
    {
        foreach (iterator_to_array($element->attributes) as $attribute) {
            $name = strtolower($attribute->name);
            $value = trim($attribute->value);
            $allowed = isset(self::GLOBAL_ATTRIBUTES[$name])
                || isset(self::TAG_ATTRIBUTES[$tag][$name]);

            if (!$allowed || str_starts_with($name, 'on')) {
                $element->removeAttribute($attribute->name);

                continue;
            }

            if ($name === 'href' && !$this->isSafeUrl($value)) {
                $element->removeAttribute($attribute->name);

                continue;
            }

            if ($name === 'target' && !in_array($value, ['_blank', '_self'], true)) {
                $element->removeAttribute($attribute->name);
            }
        }

        if ($tag === 'a' && $element->getAttribute('target') === '_blank') {
            $element->setAttribute('rel', 'noopener noreferrer');
        }
    }

    private function isSafeUrl(string $url): bool
    {
        if ($url === '' || str_starts_with($url, '/') || str_starts_with($url, '#')) {
            return true;
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);

        return is_string($scheme) && in_array(strtolower($scheme), ['http', 'https', 'mailto', 'tel'], true);
    }
}
