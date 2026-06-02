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
use Catalyst\Framework\Traits\HasTimestampsTrait;

/**
 * ORM entity for automation rule execution history.
 *
 * @package Catalyst\Entities
 * Responsibility: Maps trigger context, execution status, messages, and results for tenant automation runs.
 */
final class AutomationExecutionLog extends Model
{
    use BelongsToTenantTrait;
    use HasTimestampsTrait;

    protected static string $table = 'automation_execution_logs';

    protected static array $fillable = [
        'tenant_id',
        'rule_id',
        'trigger_source',
        'event_name',
        'status',
        'message',
        'context_json',
        'result_json',
        'created_at',
        'updated_at',
    ];

    protected static array $casts = [
        'tenant_id' => 'int',
        'rule_id' => 'int',
        'context_json' => 'json',
        'result_json' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
