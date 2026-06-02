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

/**
 * ORM entity for workflow state transitions.
 *
 * @package Catalyst\Entities
 * Responsibility: Maps transition keys, state movement, notes, metadata, actor, and occurrence timestamp.
 */
final class WorkflowTransition extends Model
{
    use BelongsToTenantTrait;

    protected static string $table = 'workflow_transitions';

    protected static array $fillable = [
        'tenant_id',
        'workflow_instance_id',
        'transition_key',
        'from_state',
        'to_state',
        'notes',
        'metadata',
        'actor_id',
        'occurred_at',
    ];

    protected static array $casts = [
        'tenant_id' => 'int',
        'workflow_instance_id' => 'int',
        'metadata' => 'json',
        'actor_id' => 'int',
        'occurred_at' => 'datetime',
    ];
}
