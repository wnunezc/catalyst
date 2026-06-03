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

namespace Catalyst\Framework\Reporting;

use Catalyst\Framework\Attachment\AttachmentManager;
use Catalyst\Framework\Attachment\AttachmentRepository;
use RuntimeException;

/**
 * Provides the built-in resource-attachments report.
 *
 * @package Catalyst\Framework\Reporting
 * Responsibility: Keeps the legacy attachment report available through the provider-based reporting contract.
 */
final class AttachmentReportProvider implements ReportProviderInterface
{
    /**
     * Initializes the provider with its attachment repository.
     *
     * Responsibility: Keeps attachment reporting reads behind the repository boundary.
     */
    public function __construct(private readonly AttachmentRepository $repository)
    {
    }

    /**
     * Returns the provider report definition.
     *
     * Responsibility: Declares the report metadata, columns and supported formats exposed by the provider.
     */
    public function definition(): ReportDefinition
    {
        return new ReportDefinition(
            key: 'framework.attachments.by-resource',
            label: 'Resource attachments',
            filename: 'resource-attachments-report',
            resourceKey: AttachmentManager::RESOURCE_KEY,
            columns: [
                ['key' => 'resource_key', 'label' => 'Resource'],
                ['key' => 'record_id', 'label' => 'Record ID'],
                ['key' => 'purpose', 'label' => 'Purpose'],
                ['key' => 'attachment_type', 'label' => 'Type'],
                ['key' => 'attachment_kind', 'label' => 'Kind'],
                ['key' => 'asset_name', 'label' => 'Asset'],
                ['key' => 'active', 'label' => 'Active'],
                ['key' => 'detached_at', 'label' => 'Detached At'],
                ['key' => 'created_at', 'label' => 'Attached At'],
            ],
            formats: [ReportFormat::CSV, ReportFormat::XLS, ReportFormat::PDF],
            filters: [
                ['key' => 'resource_key', 'required' => true],
                ['key' => 'record_id', 'required' => true],
            ]
        );
    }

    /**
     * Resolves export rows for the provided criteria.
     *
     * Responsibility: Transforms provider criteria into tabular rows without performing transport or file delivery.
     * @param array<string, mixed> $criteria
     * @return array<int, array<string, mixed>>
     */
    public function rows(array $criteria): array
    {
        $resourceKey = trim((string) ($criteria['resource_key'] ?? ''));
        $recordId = (int) ($criteria['record_id'] ?? 0);

        if ($resourceKey === '' || $recordId <= 0) {
            throw new RuntimeException('Attachment report requires resource_key and record_id.');
        }

        return $this->repository->reportRows($criteria);
    }
}