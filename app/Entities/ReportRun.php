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
 * ORM entity for asynchronous report runs.
 *
 * @package Catalyst\Entities
 * Responsibility: Maps report criteria, output attachments, queue linkage, execution status, and timing.
 */
final class ReportRun extends Model
{
    use BelongsToTenantTrait;
    use HasTimestampsTrait;
    use HasAuditLogTrait;

    protected static string $table = 'report_runs';

    protected static array $fillable = [
        'tenant_id',
        'report_key',
        'format',
        'status',
        'criteria_json',
        'attach_resource_key',
        'attach_record_id',
        'queued_job_id',
        'output_media_item_id',
        'output_attachment_id',
        'error_message',
        'started_at',
        'completed_at',
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
    ];

    protected static array $casts = [
        'tenant_id' => 'int',
        'criteria_json' => 'json',
        'attach_record_id' => 'int',
        'queued_job_id' => 'int',
        'output_media_item_id' => 'int',
        'output_attachment_id' => 'int',
        'created_by' => 'int',
        'updated_by' => 'int',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
