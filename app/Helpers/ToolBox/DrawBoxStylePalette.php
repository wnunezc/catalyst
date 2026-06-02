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
 * Defines the Draw Box Style Palette class contract.
 *
 * @package Catalyst\Helpers\ToolBox
 * Responsibility: Coordinates the draw box style palette behavior within its module boundary.
 */
final class DrawBoxStylePalette
{
    /**
     * @return array{r: string, c: string}
     */
    public function getCliScheme(int $styleType): array
    {
        $colorScheme = ['r' => '[0m'];

        $colorScheme['c'] = match ($styleType) {
            1 => '[1;42;30m',
            2 => '[1;41m',
            3 => '[1;43;30m',
            4 => '[1;44;30m',
            5 => '[1;32m',
            6 => '[1;31m',
            7 => '[1;46;30m',
            8 => '[1;37m',
            9 => '[1;45m',
            default => '[0m',
        };

        if ($styleType === 0) {
            $envStyle = $this->getEnvironmentBasedStyle();
            if ($envStyle['color'] > 0) {
                $colorScheme['c'] = match ($envStyle['color']) {
                    1 => '[1;42;30m',
                    2 => '[1;44;30m',
                    default => '[0m',
                };
            }
        }

        return $colorScheme;
    }

    /**
     * Returns the html style class value.
     */
    public function getHtmlStyleClass(int $styleType, bool $isError): string
    {
        if ($isError) {
            return 'error-box';
        }

        return match ($styleType) {
            1 => 'success-box',
            2 => 'error-box',
            3 => 'warning-box',
            4 => 'info-box',
            5 => 'success-text-box',
            6 => 'error-text-box',
            7 => 'info-alt-box',
            8 => 'highlight-box',
            9 => 'special-box',
            default => 'default-box',
        };
    }

    /**
     * @return array{color: int, label: string}
     */
    private function getEnvironmentBasedStyle(): array
    {
        if (defined('IS_DEVELOPMENT') && IS_DEVELOPMENT) {
            return ['color' => 1, 'label' => 'DEVELOPMENT'];
        }

        if (defined('IS_PRODUCTION') && IS_PRODUCTION) {
            return ['color' => 2, 'label' => 'PRODUCTION'];
        }

        return ['color' => 0, 'label' => ''];
    }
}
