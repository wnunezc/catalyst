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
 * Wraps HTML that has been explicitly approved for raw rendering.
 *
 * @package Catalyst\Framework\View
 * Responsibility: Marks trusted HTML fragments so renderers can distinguish them from escaped values.
 */
final readonly class TrustedHtml
{
    /**
     * Initializes the Trusted Html instance.
     *
     * Responsibility: Initializes the Trusted Html instance.
     */
    public function __construct(
        private string $html
    ) {
    }

    /**
     * Creates a trusted HTML wrapper from a string.
     */
    public static function fromString(string $html): self
    {
        return new self($html);
    }

    /**
     * Returns the wrapped HTML fragment.
     *
     * Responsibility: Returns the wrapped HTML fragment.
     */
    public function toHtml(): string
    {
        return $this->html;
    }

    /**
     * Returns the wrapped HTML fragment when cast to string.
     *
     * Responsibility: Returns the wrapped HTML fragment when cast to string.
     */
    public function __toString(): string
    {
        return $this->html;
    }
}
