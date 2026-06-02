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

namespace Catalyst\Helpers\ToolBox;

/**
 * Defines the Draw Box Html Renderer class contract.
 *
 * @package Catalyst\Helpers\ToolBox
 * Responsibility: Coordinates the draw box html renderer behavior within its module boundary.
 */
final class DrawBoxHtmlRenderer
{
    /**
     * Initializes the Draw Box Html Renderer instance.
     */
    public function __construct(private readonly DrawBoxStylePalette $stylePalette)
    {
    }

    /**
     * @param string[] $content
     * @param array<string, mixed> $options
     */
    public function render(array $content, array $options): string
    {
        $styleClass = $this->stylePalette->getHtmlStyleClass((int) $options['style'], (bool) $options['isError']);

        $html = '<pre class="catalyst-box ' . $styleClass . '">';
        $html .= '<div class="box-content">';

        if ((int) $options['headerLines'] > 0) {
            $html .= '<div class="box-header">';
            for ($i = 0; $i < (int) $options['headerLines']; $i++) {
                $html .= htmlspecialchars($content[$i] ?? '') . "\n";
            }
            $html .= '</div>';
        }

        $html .= '<div class="box-body">';
        $startIdx = (int) $options['headerLines'];
        $endIdx = count($content) - (int) $options['footerLines'];

        for ($i = $startIdx; $i < $endIdx; $i++) {
            $html .= htmlspecialchars($content[$i] ?? '') . "\n";
        }
        $html .= '</div>';

        if ((int) $options['footerLines'] > 0) {
            $html .= '<div class="box-footer">';
            for ($i = $endIdx; $i < count($content); $i++) {
                $html .= htmlspecialchars($content[$i] ?? '') . "\n";
            }
            $html .= '</div>';
        }

        $html .= '</div></pre>';

        return $html;
    }
}
