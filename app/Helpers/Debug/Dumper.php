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

use Catalyst\Framework\Traits\SingletonTrait;

/**
 * Dumper class for debugging variables
 *
 * This class serves as the main entry point for the debugging system.
 * It coordinates the process of dumping variables for inspection.
 *
 * @package Catalyst\Helpers\Debug;
 * Responsibility: Coordinates dumper configuration, formatting, and rendering for each debug inspection.
 */
class Dumper
{
    use SingletonTrait;

    /**
     * DumperConfig instance
     */
    private DumperConfig $config;

    /**
     * DumperColorizer instance
     */
    private DumperColorizer $colorizer;

    /**
     * DumperCollapsible instance
     */
    private DumperCollapsible $collapsible;

    /**
     * MainFormatter instance
     */
    private MainFormatter $formatter;

    /**
     * DumperRenderer instance
     */
    private DumperRenderer $renderer;

    /**
     * Initializes the dumper with formatter and rendering collaborators.
     *
     * Responsibility: Constructor with dependency injection.
     * @param DumperConfig|null $config Configuration instance
     * @param DumperColorizer|null $colorizer Colorizer instance
     * @param DumperCollapsible|null $collapsible Collapsible instance
     * @param MainFormatter|null $formatter Formatter instance
     * @param DumperRenderer|null $renderer Renderer instance
     */
    protected function __construct(
        ?DumperConfig $config = null,
        ?DumperColorizer $colorizer = null,
        ?DumperCollapsible $collapsible = null,
        ?MainFormatter $formatter = null,
        ?DumperRenderer $renderer = null
    ) {
        $this->initialize($config, $colorizer, $collapsible, $formatter, $renderer);
    }

    /**
     * Initialize the Dumper instance with dependencies.
     *
     * Responsibility: Initialize the Dumper instance with dependencies.
     * @param DumperConfig|null $config Configuration instance
     * @param DumperColorizer|null $colorizer Colorizer instance
     * @param DumperCollapsible|null $collapsible Collapsible instance
     * @param MainFormatter|null $formatter Formatter instance
     * @param DumperRenderer|null $renderer Renderer instance
     * @return void
     */
    protected function initialize(
        ?DumperConfig $config = null,
        ?DumperColorizer $colorizer = null,
        ?DumperCollapsible $collapsible = null,
        ?MainFormatter $formatter = null,
        ?DumperRenderer $renderer = null
    ): void {
        // Create or use provided dependencies
        $this->config = $config ?? new DumperConfig();
        $this->colorizer = $colorizer ?? new DumperColorizer($this->config->getColorTheme());
        $this->collapsible = $collapsible ?? new DumperCollapsible($this->colorizer);
        $this->formatter = $formatter ?? new MainFormatter($this->config, $this->colorizer, $this->collapsible);
        $this->renderer = $renderer ?? new DumperRenderer($this->config, $this->colorizer, $this->collapsible);
    }

    /**
     * Dump variables for inspection
     *
     * @param array $options Options for dumping with the following structure:
     *                      - 'data': array of variables to dump
     *                      - 'caller': (optional) array with 'file' and 'line' keys
     *                      - 'config': (optional) array of configuration options
     * @return void
     */
    public static function dump(array $options): void
    {
        $instance = self::getInstance();

        $data = $options['data'] ?? [];
        $caller = $options['caller'] ?? null;
        $config = $options['config'] ?? [];

        if (empty($data)) {
            return;
        }

        // Apply any custom configuration
        if (!empty($config)) {
            $instance->config->applyOptions($config);
        }

        // Set color theme if specified
        if (isset($config['colorTheme'])) {
            $instance->colorizer->setTheme($config['colorTheme']);
        }

        // Reset collapse counter for each dump call
        $instance->collapsible->resetCounter();

        // Format each variable
        $formattedData = [];
        foreach ($data as $var) {
            $formattedData[] = $instance->formatter->formatVar($var, 'Output', IS_REQUEST);
        }

        // Render the output
        echo $instance->renderer->render($formattedData, $caller, IS_REQUEST);
    }

    /**
     * Configure the dumper
     *
     * @param array $options Configuration options
     * @return void
     */
    public static function configure(array $options): void
    {
        $instance = self::getInstance();
        
        $instance->config->applyOptions($options);

        if (isset($options['colorTheme'])) {
            $instance->colorizer->setTheme($options['colorTheme']);
        }
    }

    /**
     * Get available color themes
     *
     * @return array<string> List of available theme names
     */
    public static function getAvailableThemes(): array
    {
        $instance = self::getInstance();
        return $instance->config->getAvailableThemes();
    }

    /**
     * Set the current color theme
     *
     * @param string $theme Theme name
     * @return void
     */
    public static function setTheme(string $theme): void
    {
        $instance = self::getInstance();

        $instance->colorizer->setTheme($theme);
        $instance->config->setColorTheme($theme);
    }

    /**
     * Get the current color theme
     *
     * @return string Theme name
     */
    public static function getTheme(): string
    {
        $instance = self::getInstance();

        return $instance->colorizer->getTheme();
    }

    /**
     * Get a comma-separated list of all available theme names
     *
     * @return string Comma-separated list of theme names
     */
    public static function getThemesNameList(): string
    {
        return implode(', ', self::getAvailableThemes());
    }
}
