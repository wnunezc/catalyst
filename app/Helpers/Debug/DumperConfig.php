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
 * DumperConfig class for managing dumper configuration
 *
 * This class stores and manages configuration settings for the Dumper system.
 * It handles settings like maximum string length, maximum array/object children,
 * maximum nesting depth, and other configuration options.
 *
 * @package Catalyst\Helpers\Debug;
 */
class DumperConfig
{
    /**
     * Maximum string length in output
     */
    private int $maxStrLength;

    /**
     * Maximum array/object children to show
     */
    private int $maxChildren;

    /**
     * Maximum nesting level
     */
    private int $maxDepth;

    /**
     * Whether to show the floating button in HTML mode
     */
    private bool $showFloatingButton;

    /**
     * Whether to initially expand arrays and objects
     */
    private bool $initiallyExpanded;

    /**
     * Selected color theme
     */
    private string $colorTheme;
    
    /**
     * Available color themes
     */
    private array $availableThemes;

    /**
     * Constructor
     *
     * @param array $options Optional configuration options
     */
    public function __construct(array $options = [])
    {
        // Load configuration from a file if not already loaded
        if (!defined('DUMPER_MAX_STR_LENGTH')) {
            $configFile = implode(DS, [PD, 'boot-core', 'config','debug', 'dumper.php']);
            if (file_exists($configFile)) {
                require_once $configFile;
            }
        }
        
        // Set default values from configuration constants
        $this->maxStrLength = defined('DUMPER_MAX_STR_LENGTH') ? DUMPER_MAX_STR_LENGTH : 150;
        $this->maxChildren = defined('DUMPER_MAX_CHILDREN') ? DUMPER_MAX_CHILDREN : 50;
        $this->maxDepth = defined('DUMPER_MAX_DEPTH') ? DUMPER_MAX_DEPTH : 10;
        $this->showFloatingButton = !defined('DUMPER_SHOW_FLOATING_BUTTON') || DUMPER_SHOW_FLOATING_BUTTON;
        $this->initiallyExpanded = !defined('DUMPER_INITIALLY_EXPANDED') || DUMPER_INITIALLY_EXPANDED;
        $this->colorTheme = defined('DUMPER_DEFAULT_THEME') ? DUMPER_DEFAULT_THEME : 'dark';
        $this->availableThemes = defined('DUMPER_AVAILABLE_THEMES') ? DUMPER_AVAILABLE_THEMES : ThemeName::getNames();
        
        // Apply custom options if provided
        if (!empty($options)) {
            $this->applyOptions($options);
        }
    }

    /**
     * Apply configuration options
     *
     * @param array $options Configuration options
     * @return void
     */
    public function applyOptions(array $options): void
    {
        // Apply each option if it exists
        if (isset($options['maxStrLength'])) {
            $this->setMaxStrLength($options['maxStrLength']);
        }

        if (isset($options['maxChildren'])) {
            $this->setMaxChildren($options['maxChildren']);
        }

        if (isset($options['maxDepth'])) {
            $this->setMaxDepth($options['maxDepth']);
        }

        if (isset($options['showFloatingButton'])) {
            $this->setShowFloatingButton($options['showFloatingButton']);
        }

        if (isset($options['initiallyExpanded'])) {
            $this->setInitiallyExpanded($options['initiallyExpanded']);
        }

        if (isset($options['colorTheme'])) {
            $this->setColorTheme($options['colorTheme']);
        }
    }

    /**
     * Get maximum string length
     *
     * @return int
     */
    public function getMaxStrLength(): int
    {
        return $this->maxStrLength;
    }

    /**
     * Set maximum string length
     *
     * @param int $maxStrLength
     * @return self
     */
    public function setMaxStrLength(int $maxStrLength): self
    {
        $this->maxStrLength = max(10, $maxStrLength); // Ensure a minimum of 10
        return $this;
    }

    /**
     * Get maximum children
     *
     * @return int
     */
    public function getMaxChildren(): int
    {
        return $this->maxChildren;
    }

    /**
     * Set maximum children
     *
     * @param int $maxChildren
     * @return self
     */
    public function setMaxChildren(int $maxChildren): self
    {
        $this->maxChildren = max(5, $maxChildren); // Ensure a minimum of 5
        return $this;
    }

    /**
     * Get maximum depth
     *
     * @return int
     */
    public function getMaxDepth(): int
    {
        return $this->maxDepth;
    }

    /**
     * Set maximum depth
     *
     * @param int $maxDepth
     * @return self
     */
    public function setMaxDepth(int $maxDepth): self
    {
        $this->maxDepth = max(1, $maxDepth); // Ensure a minimum of 1
        return $this;
    }

    /**
     * Get whether to show a floating button
     *
     * @return bool
     */
    public function getShowFloatingButton(): bool
    {
        return $this->showFloatingButton;
    }

    /**
     * Set whether to show a floating button
     *
     * @param bool $showFloatingButton
     * @return self
     */
    public function setShowFloatingButton(bool $showFloatingButton): self
    {
        $this->showFloatingButton = $showFloatingButton;
        return $this;
    }

    /**
     * Get whether arrays and objects are initially expanded
     *
     * @return bool
     */
    public function getInitiallyExpanded(): bool
    {
        return $this->initiallyExpanded;
    }

    /**
     * Set whether arrays and objects are initially expanded
     *
     * @param bool $initiallyExpanded
     * @return self
     */
    public function setInitiallyExpanded(bool $initiallyExpanded): self
    {
        $this->initiallyExpanded = $initiallyExpanded;
        return $this;
    }

    /**
     * Get a color theme
     *
     * @return string
     */
    public function getColorTheme(): string
    {
        return $this->colorTheme;
    }

    /**
     * Set a color theme
     *
     * @param string $colorTheme
     * @return self
     */
    public function setColorTheme(string $colorTheme): self
    {
        if (in_array($colorTheme, $this->availableThemes)) {
            $this->colorTheme = $colorTheme;
        }

        return $this;
    }
    
    /**
     * Get available color themes
     *
     * @return array
     */
    public function getAvailableThemes(): array
    {
        return $this->availableThemes;
    }
}
