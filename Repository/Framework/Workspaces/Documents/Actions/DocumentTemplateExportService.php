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

namespace Catalyst\Repository\Workspaces\Documents\Actions;

use Catalyst\Entities\DocumentArtifact;
use Catalyst\Entities\DocumentTemplate;
use Catalyst\Framework\Document\DocumentTemplateManager;

/**
 * Exports rendered artifacts from document templates.
 *
 * @package Catalyst\Repository\Workspaces\Documents\Actions
 * Responsibility: Delegate document artifact generation to the document template manager.
 */
final class DocumentTemplateExportService
{
    /**
     * Initializes the Document Template Export Service instance.
     *
     * Responsibility: Initializes the Document Template Export Service instance.
     */
    public function __construct(
        private readonly DocumentTemplateManager $manager
    ) {
    }

    /**
     * Exports an artifact by rendering the template with the supplied payload.
     *
     * Responsibility: Exports an artifact by rendering the template with the supplied payload.
     * @param array<string, mixed> $payload
     */
    public function export(DocumentTemplate $template, array $payload): DocumentArtifact
    {
        return $this->manager->export($template, $payload);
    }
}
