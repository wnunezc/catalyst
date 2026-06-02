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

namespace Catalyst\Framework\Document\Pdf;

/**
 * Contract for PDF renderer implementations.
 *
 * @package Catalyst\Framework\Document\Pdf
 * Responsibility: Defines the boundary for rendering document title and HTML/text content into PDF bytes.
 */
interface PdfRendererInterface
{
    /**
     * Coordinates the render method responsibility within its owning class.
     *
     * Responsibility: Coordinates the render method responsibility within its owning class.
     * @param array<string, mixed> $watermark
     */
    public function render(string $title, string $body, array $watermark = []): string;
}
