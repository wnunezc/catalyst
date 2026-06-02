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

namespace Catalyst\Repository\Documents\Actions;

use Catalyst\Entities\DocumentArtifact;
use Catalyst\Entities\DocumentTemplate;
use Catalyst\Framework\Document\DocumentTemplateManager;

/**
 * Defines the Document Template Export Service class contract.
 *
 * @package Catalyst\Repository\Documents\Actions
 * Responsibility: Coordinates the document template export service behavior within its module boundary.
 */
final class DocumentTemplateExportService
{
    /**
     * Initializes the Document Template Export Service instance.
     */
    public function __construct(
        private readonly DocumentTemplateManager $manager
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function export(DocumentTemplate $template, array $payload): DocumentArtifact
    {
        return $this->manager->export($template, $payload);
    }
}
