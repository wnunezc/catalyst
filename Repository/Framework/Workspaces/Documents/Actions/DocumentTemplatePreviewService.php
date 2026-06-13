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

use Catalyst\Entities\DocumentTemplate;
use Catalyst\Framework\Document\DocumentTemplateManager;

/**
 * Renders non-persistent previews from document templates.
 *
 * @package Catalyst\Repository\Workspaces\Documents\Actions
 * Responsibility: Delegate document preview rendering to the document template manager.
 */
final class DocumentTemplatePreviewService
{
    /**
     * Initializes the Document Template Preview Service instance.
     *
     * Responsibility: Initializes the Document Template Preview Service instance.
     */
    public function __construct(
        private readonly DocumentTemplateManager $manager
    ) {
    }

    /**
     * Renders a preview of the template with the supplied payload.
     *
     * Responsibility: Renders a preview of the template with the supplied payload.
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function preview(DocumentTemplate $template, array $payload): array
    {
        return $this->manager->preview($template, $payload);
    }
}
