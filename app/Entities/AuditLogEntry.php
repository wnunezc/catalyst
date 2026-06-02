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
 * ORM entity for captured audit log records.
 *
 * @package Catalyst\Entities
 * Responsibility: Maps actor, tenant, request, resource, and payload fields written by audit logging.
 */
final class AuditLogEntry extends Model
{
    use BelongsToTenantTrait;

    protected static string $table = 'audit_logs';

    protected static array $fillable = [
        'channel',
        'event_name',
        'action',
        'resource',
        'resource_id',
        'resource_label',
        'actor_id',
        'actor_type',
        'tenant_id',
        'tenant_key',
        'request_method',
        'request_uri',
        'ip_address',
        'user_agent',
        'before_payload',
        'after_payload',
        'metadata',
        'occurred_at',
    ];

    protected static array $casts = [
        'actor_id' => 'int',
        'tenant_id' => 'int',
        'before_payload' => 'json',
        'after_payload' => 'json',
        'metadata' => 'json',
        'occurred_at' => 'datetime',
    ];
}
