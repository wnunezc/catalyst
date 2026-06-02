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

namespace Catalyst\Entities;

use Catalyst\Framework\Database\Model;
use Catalyst\Framework\Traits\BelongsToTenantTrait;
use Catalyst\Framework\Traits\HasAuditLogTrait;
use Catalyst\Framework\Traits\HasTimestampsTrait;

/**
 * Defines the Document Artifact class contract.
 *
 * @package Catalyst\Entities
 * Responsibility: Coordinates the document artifact behavior within its module boundary.
 */
final class DocumentArtifact extends Model
{
    use BelongsToTenantTrait;
    use HasTimestampsTrait;
    use HasAuditLogTrait;

    protected static string $table = 'document_artifacts';

    protected static array $fillable = [
        'tenant_id',
        'document_template_id',
        'workflow_instance_id',
        'name',
        'format',
        'disk',
        'path',
        'public_url',
        'checksum_sha256',
        'payload_snapshot_json',
        'rendered_content',
        'archived_at',
        'archived_by',
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
    ];

    protected static array $casts = [
        'tenant_id' => 'int',
        'document_template_id' => 'int',
        'workflow_instance_id' => 'int',
        'payload_snapshot_json' => 'json',
        'archived_by' => 'int',
        'created_by' => 'int',
        'updated_by' => 'int',
        'archived_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
