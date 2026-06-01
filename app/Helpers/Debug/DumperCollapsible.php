<?php

declare(strict_types=1);

/**
 *
 * Catalyst PHP Framework
 * PHP Version 8.4 (Required).
 *
 * @package   Catalyst
 *
 * @see       https://catalyst.lh-2.net
 *
 * @author    Walter Nuñez (arcanisgk/original founder) <wnunez@lh-2.net>
 * @copyright 2024 Walter Francisco Nuñez Cruz and Icaros Net
 * @license   Proprietary - https://catalyst.lh-2.net
 *
 * @note      This program is provided "as is" without a warranty of any kind, too express
 *            or implied, including but not limited to the warranties of merchantability,
 *            fitness for a particular purpose, and non-infringement.
 *
 * @category  Framework
 * @filesource
 *
 * @link      https://catalyst.lh-2.net Project homepage
 *
 */

namespace Catalyst\Helpers\Debug;

/**
 * DumperCollapsible class for handling collapsible sections in debug output
 *
 * This class is responsible for creating collapsible sections in the debug output,
 * allowing users to expand and collapse complex data structures for better readability.
 *
 * @package Catalyst\Helpers\Debug;
 */
class DumperCollapsible
{
    /**
     * Counter for generating unique IDs for collapsible elements
     */
    private int $collapseCounter = 0;

    /**
     * DumperColorizer instance for theme-aware coloring
     */
    private DumperColorizer $colorizer;

    /**
     * Constructor
     *
     * @param DumperColorizer $colorizer Colorizer instance for text coloring
     */
    public function __construct(DumperColorizer $colorizer)
    {
        $this->colorizer = $colorizer;
    }

    /**
     * Reset the collapse counter
     *
     * @return void
     */
    public function resetCounter(): void
    {
        $this->collapseCounter = 0;
    }

    /**
     * Create a collapsible section with a chevron toggle
     *
     * @param string $header Header content
     * @param string $content Content to be collapsed/expanded
     * @param bool $isHtml Whether to format for HTML output
     * @param bool $initiallyExpanded Whether the content should be initially expanded
     * @param int $depth Current nesting depth for indentation
     * @return string Formatted output with collapsible functionality
     */
    public function create(
        string $header,
        string $content,
        bool   $isHtml,
        bool   $initiallyExpanded = true,
        int    $depth = 0
    ): string
    {
        $indent = str_repeat('    ', $depth);

        if (!$isHtml) {
            // For CLI, just return the content without collapsible functionality
            // Make sure there's no extra newline before the closing brace
            return $indent . $header . " {" . PHP_EOL . rtrim($content) . PHP_EOL . $indent . "}";
        }

        // Generate a unique ID for this collapsible section
        $id = ++$this->collapseCounter;

        // Determine initial state
        $contentStateClass = $initiallyExpanded
            ? 'catalyst-dump-collapse-content--open'
            : 'catalyst-dump-collapse-content--closed';
        $chevronChar = $initiallyExpanded ? '&#9660;' : '&#9658;';
        $chevronTitle = $initiallyExpanded ? 'Collapse' : 'Expand';

        // Create the collapsible HTML structure (CSP-safe: data attr + delegation)
        $result = $indent . '<span class="catalyst-dump-collapse-trigger" data-dumper-collapse="' . $id . '">';
        $result .= '<span id="chevron-' . $id . '" class="catalyst-dump-collapse-chevron" title="' . $chevronTitle . '">' . $chevronChar . '</span>';
        $result .= $header . ' {</span>' . PHP_EOL;

        $trimmedContent = rtrim($content);

        $result .= '<div id="content-' . $id . '" class="catalyst-dump-collapse-content ' . $contentStateClass . '">' . $trimmedContent . '</div>';
        $result .= $indent . '}';

        return $result;
    }

    /**
     * Generate JavaScript code for collapsible functionality.
     *
     * CSP-safe: returns a function + a global click delegate on
     * [data-dumper-collapse]. The delegate is attached only once per page
     * even if multiple dumps are rendered (guarded by a window flag).
     *
     * @return string JavaScript code
     */
    public function getJavaScript(): string
    {
        return '
        function toggleCollapse(id) {
            const content = document.getElementById("content-" + id);
            const chevron = document.getElementById("chevron-" + id);
            if (!content || !chevron) return;
            if (content.classList.contains("catalyst-dump-collapse-content--closed")) {
                content.classList.remove("catalyst-dump-collapse-content--closed");
                content.classList.add("catalyst-dump-collapse-content--open");
                chevron.innerHTML = "&#9660;";
                chevron.title = "Collapse";
            } else {
                content.classList.remove("catalyst-dump-collapse-content--open");
                content.classList.add("catalyst-dump-collapse-content--closed");
                chevron.innerHTML = "&#9658;";
                chevron.title = "Expand";
            }
        }
        if (!window.__catalystDumperCollapseBound) {
            window.__catalystDumperCollapseBound = true;
            document.addEventListener("click", function (e) {
                var t = e.target;
                while (t && t !== document.body) {
                    if (t.hasAttribute && t.hasAttribute("data-dumper-collapse")) {
                        toggleCollapse(t.getAttribute("data-dumper-collapse"));
                        return;
                    }
                    t = t.parentElement;
                }
            });
        }';
    }
}
