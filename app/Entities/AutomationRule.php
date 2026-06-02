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
use Catalyst\Framework\Traits\HasOptimisticLockingTrait;
use Catalyst\Framework\Traits\HasTimestampsTrait;

/**
 * ORM entity for tenant automation rules.
 *
 * @package Catalyst\Entities
 * Responsibility: Maps rule triggers, conditions, actions, schedules, effective windows, and audit metadata.
 */
final class AutomationRule extends Model
{
    use BelongsToTenantTrait;
    use HasTimestampsTrait;
    use HasAuditLogTrait;
    use HasOptimisticLockingTrait;

    protected static string $table = 'automation_rules';

    protected static array $fillable = [
        'tenant_id',
        'name',
        'slug',
        'description',
        'trigger_type',
        'event_name',
        'cron_expression',
        'condition_json',
        'action_type',
        'action_payload_json',
        'is_enabled',
        'valid_from',
        'valid_to',
        'last_run_at',
        'lock_version',
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
    ];

    protected static array $casts = [
        'tenant_id' => 'int',
        'condition_json' => 'json',
        'action_payload_json' => 'json',
        'is_enabled' => 'bool',
        'valid_from' => 'datetime',
        'valid_to' => 'datetime',
        'lock_version' => 'int',
        'created_by' => 'int',
        'updated_by' => 'int',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'last_run_at' => 'datetime',
    ];
}
