<?php

declare(strict_types=1);

/**
 *
 * Catalyst PHP Framework
 * PHP Version 8.4 (Required).
 *
 * @package   Catalyst
 * @subpackage Assets
 * @see       https://github.com/arcanisgk/catalyst
 *
 * @author    Walter Nuñez (arcanisgk/original founder) <icarosnet@gmail.com>
 * @copyright 2023 - 2025
 * @license   http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 *
 * @note      This program is distributed in the hope that it will be useful
 *            WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 *            or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @category  Framework
 * @filesource
 *
 * @link      https://catalyst.dock Local development URL
 *
 * OutputCleanerTrait component for the Catalyst Framework
 *
 */

namespace Catalyst\Framework\Traits;

/**
 * Trait that provides output buffer cleaning functionality
 *
 * @package Catalyst\Framework\Traits;
 */
trait OutputCleanerTrait
{

    /**
     * Clean any output that might have been sent before an error occurred
     *
     * @return void
     */
    protected function cleanOutput(): void
    {
        // Clear the output buffer if it's started
        if (ob_get_level() > 0) {
            ob_end_clean();
        }

        // Start a fresh output buffer
        if (ob_get_level() === 0) {
            ob_start();
        }
    }
}