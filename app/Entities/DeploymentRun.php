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
use Catalyst\Framework\Traits\HasAuditLogTrait;
use Catalyst\Framework\Traits\HasTimestampsTrait;

/**
 * ORM entity for deployment execution records.
 *
 * @package Catalyst\Entities
 * Responsibility: Maps deployment profile runs, status, artifact paths, remote paths, summaries, and timing.
 */
final class DeploymentRun extends Model
{
    use HasTimestampsTrait;
    use HasAuditLogTrait;

    protected static string $table = 'deployment_runs';

    protected static array $fillable = [
        'profile_key',
        'release_id',
        'environment',
        'status',
        'dry_run',
        'artifact_path',
        'remote_path',
        'summary_json',
        'error_message',
        'started_at',
        'finished_at',
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
    ];

    protected static array $casts = [
        'dry_run' => 'bool',
        'summary_json' => 'json',
        'created_by' => 'int',
        'updated_by' => 'int',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
