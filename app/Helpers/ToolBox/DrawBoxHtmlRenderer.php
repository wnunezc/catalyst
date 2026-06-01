<?php

declare(strict_types=1);

namespace Catalyst\Helpers\ToolBox;

final class DrawBoxHtmlRenderer
{
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
