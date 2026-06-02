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

namespace Catalyst\Helpers\Debug;

/**
 * DumperColorizer class for handling text coloring in debug output
 *
 * This class is responsible for applying colors to different types of data
 * in the debug output. It supports multiple color themes and can format text
 * for both HTML and CLI output.
 *
 * @package Catalyst\Helpers\Debug;
 */
class DumperColorizer
{
    /**
     * Current color theme
     */
    private string $theme;

    /**
     * Color themes definitions
     * 
     * @var array<string, array<string, array<string, string>>>|null
     */
    private ?array $themes = null;

    /**
     * Constructor
     *
     * @param string $theme Initial color theme to use
     */
    public function __construct(string $theme = 'default')
    {
        // Directly set the theme without validation to avoid circular dependency
        // Validation will be done lazily when the theme is actually used
        $this->theme = $theme;
    }

    /**
     * Lazy load themes when needed
     *
     * @return array<string, array<string, array<string, string>>>
     */
    private function getThemes(): array
    {
        if ($this->themes === null) {
            $this->themes = DumperPalette::getPalette();
            
            // Validate theme after loading palettes to avoid circular dependency
            $this->validateTheme();
        }
    
        return $this->themes;
    }
    
    /**
     * Validate the current theme and set to default if invalid
     * 
     * @return void
     */
    private function validateTheme(): void
    {
        // Use direct array key check instead of ThemeName::exists() to avoid potential circular dependency
        if (!isset($this->themes[$this->theme])) {
            $this->theme = 'default';
        }
    }

    /**
     * Set the current color theme
     *
     * @param string $theme Theme name
     * @return self
     */
    public function setTheme(string $theme): self
    {
        $this->theme = $theme;
        
        // If themes are already loaded, validate the theme immediately
        if ($this->themes !== null) {
            $this->validateTheme();
        }
        // Otherwise, validation will happen lazily when getThemes() is called
        
        return $this;
    }

    /**
     * Get the current theme name
     *
     * @return string
     */
    public function getTheme(): string
    {
        return $this->theme;
    }

    /**
     * Get all available theme names
     *
     * @return array<string> List of available theme names
     */
    public function getAvailableThemes(): array
    {
        // Get theme names from the loaded themes to avoid circular dependency
        $themes = $this->getThemes();
        return array_keys($themes);
    }

    /**
     * Get the color for a specific type in the current theme
     *
     * @param string $type Color type (string, number, boolean, etc.)
     * @param bool $isHtml Whether to return HTML or CLI color
     * @return string Color value
     */
    public function getColor(string $type, bool $isHtml): string
    {
        $themes = $this->getThemes();
    
        if (!isset($themes[$this->theme][$type])) {
            return $isHtml ? '#ffffff' : '2;255;255;255'; // Default to white if type not found
        }
    
        return $themes[$this->theme][$type][$isHtml ? 'html' : 'cli'];
    }

    /**
     * Get background color for the current theme
     *
     * @return string HTML color code
     */
    public function getBackgroundColor(): string
    {
        $themes = $this->getThemes();
        return $themes[$this->theme]['background']['html'];
    }

    /**
     * Get text color for the current theme
     *
     * @return string HTML color code
     */
    public function getTextColor(): string
    {
        $themes = $this->getThemes();
        return $themes[$this->theme]['text']['html'];
    }

    /**
     * Get all HTML colors for the current theme keyed by logical color type.
     *
     * @return array<string, string>
     */
    public function getHtmlColors(): array
    {
        $colors = [];

        foreach ($this->getThemes()[$this->theme] as $type => $formats) {
            if (isset($formats['html'])) {
                $colors[$type] = $formats['html'];
            }
        }

        return $colors;
    }

    /**
     * Get header background color for the current theme
     *
     * @return string HTML color code
     */
    public function getHeaderColor(): string
    {
        $themes = $this->getThemes();
        return $themes[$this->theme]['header']['html'];
    }

    /**
     * Apply color to text based on type
     *
     * @param string $text Text to colorize
     * @param string $type Color type (string, number, boolean, etc.)
     * @param bool $isHtml Whether to format for HTML or CLI
     * @return string Colorized text
     */
    public function colorize(string $text, string $type, bool $isHtml): string
    {
        $color = $this->getColor($type, $isHtml);

        if ($isHtml) {
            return '<span data-dumper-color="' . htmlspecialchars($type, ENT_QUOTES, 'UTF-8') . '">' . $text . '</span>';
        } else {
            // CLI coloring using ANSI escape sequences
            return "\033[38;" . $color . "m" . $text . "\033[0m";
        }
    }

    /**
     * Get the type color based on the variable type
     *
     * @param string $type Variable type
     * @param bool $isHtml Whether to format for HTML
     * @return string Type name for colorizing
     */
    public function getTypeColor(string $type, bool $isHtml): string
    {
        return match ($type) {
            'string' => ColorType::STRING->value,
            'integer', 'double' => ColorType::NUMBER->value,
            'boolean' => ColorType::BOOLEAN->value,
            'NULL' => ColorType::NULL->value,
            'array' => ColorType::ARRAY->value,
            'object' => ColorType::OBJECT->value,
            'resource' => ColorType::RESOURCE->value,
            default => ColorType::META->value
        };
    }
}
