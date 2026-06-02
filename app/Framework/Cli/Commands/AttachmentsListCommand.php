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

namespace Catalyst\Framework\Cli\Commands;

use Catalyst\Framework\Argument\ArgumentBag;
use Catalyst\Framework\Argument\Option;
use Catalyst\Framework\Cli\AbstractCommand;
use Catalyst\Framework\Attachment\AttachmentManager;

/**
 * Defines the Attachments List Command class contract.
 *
 * @package Catalyst\Framework\Cli\Commands
 * Responsibility: Coordinates the attachments list command behavior within its module boundary.
 */
final class AttachmentsListCommand extends AbstractCommand
{
    /** @return Option[] */
    public function getOptions(): array
    {
        return [
            new Option(null, 'resource', '', true, 'Resource key to inspect', true),
            new Option(null, 'record-id', 0, true, 'Record ID to inspect', true),
            new Option(null, 'include-detached', false, false, 'Include detached links', false),
            new Option(null, 'json', false, false, 'Render as JSON', false),
        ];
    }

    /**
     * Returns the name value.
     */
    public function getName(): string
    {
        return 'attachments:list';
    }

    /**
     * Returns the description value.
     */
    public function getDescription(): string
    {
        return 'List canonical resource attachments for one resource_key + record_id pair';
    }

    /**
     * Executes the service workflow.
     */
    public function execute(ArgumentBag $args): int
    {
        $resourceKey = trim((string) ($args->getOptionValue('resource') ?? ''));
        $recordId = (int) ($args->getOptionValue('record-id') ?? 0);
        $includeDetached = (bool) ($args->getOptionValue('include-detached') ?? false);
        $json = (bool) ($args->getOptionValue('json') ?? false);

        $rows = AttachmentManager::getInstance()->listForResource($resourceKey, $recordId, $includeDetached);

        if ($json) {
            $this->line((string) json_encode([
                'resource_key' => $resourceKey,
                'record_id' => $recordId,
                'rows' => $rows,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return 0;
        }

        $this->line('');
        $this->info('Attachments');
        $this->line('');

        foreach ($rows as $row) {
            $asset = (int) ($row['media_item_id'] ?? 0) > 0
                ? (string) ($row['media_name'] ?? $row['media_original_name'] ?? 'media')
                : (string) ($row['artifact_name'] ?? 'artifact');

            $this->line(sprintf(
                '  #%d %-14s %-14s %-10s %s',
                (int) ($row['id'] ?? 0),
                (string) ($row['purpose'] ?? 'attachment'),
                (string) ($row['attachment_type'] ?? 'file'),
                empty($row['detached_at']) ? 'ACTIVE' : 'DETACHED',
                $asset
            ));
        }

        $this->line('');

        return 0;
    }
}
