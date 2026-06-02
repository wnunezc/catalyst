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

/**
 * ORM entity for timeline events attached to resource records.
 *
 * @package Catalyst\Entities
 * Responsibility: Maps tenant resource milestones, event labels, metadata, and occurrence timestamps.
 */
final class TimelineEvent extends Model
{
    use BelongsToTenantTrait;
    use HasAuditLogTrait;

    protected static string $table = 'timeline_events';

    protected static array $fillable = [
        'tenant_id',
        'resource_key',
        'record_id',
        'event_key',
        'event_type',
        'label',
        'metadata_json',
        'occurred_at',
    ];

    protected static array $casts = [
        'tenant_id' => 'int',
        'record_id' => 'int',
        'metadata_json' => 'json',
        'occurred_at' => 'datetime',
    ];
}
