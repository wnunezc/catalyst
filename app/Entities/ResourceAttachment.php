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
 * ORM entity for files attached to resource records.
 *
 * @package Catalyst\Entities
 * Responsibility: Maps media or document attachments, purpose, primary marker, detach state, and audit metadata.
 */
final class ResourceAttachment extends Model
{
    use BelongsToTenantTrait;
    use HasTimestampsTrait;
    use HasAuditLogTrait;

    protected static string $table = 'resource_attachments';

    protected static array $fillable = [
        'tenant_id',
        'resource_key',
        'record_id',
        'media_item_id',
        'document_artifact_id',
        'purpose',
        'attachment_type',
        'is_primary',
        'detached_at',
        'detached_by',
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
    ];

    protected static array $casts = [
        'tenant_id' => 'int',
        'record_id' => 'int',
        'media_item_id' => 'int',
        'document_artifact_id' => 'int',
        'is_primary' => 'bool',
        'detached_by' => 'int',
        'created_by' => 'int',
        'updated_by' => 'int',
        'detached_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
