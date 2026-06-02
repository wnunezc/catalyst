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

namespace Catalyst\Framework\View;

/**
 * Defines the Trusted Html class contract.
 *
 * @package Catalyst\Framework\View
 * Responsibility: Coordinates the trusted html behavior within its module boundary.
 */
final readonly class TrustedHtml
{
    /**
     * Initializes the Trusted Html instance.
     */
    public function __construct(
        private string $html
    ) {
    }

    /**
     * Handles the from string workflow.
     */
    public static function fromString(string $html): self
    {
        return new self($html);
    }

    /**
     * Handles the to html workflow.
     */
    public function toHtml(): string
    {
        return $this->html;
    }

    /**
     * Handles the to string workflow.
     */
    public function __toString(): string
    {
        return $this->html;
    }
}
