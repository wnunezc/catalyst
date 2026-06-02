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
 * ORM entity for versioned content snapshots.
 *
 * @package Catalyst\Entities
 * Responsibility: Maps resource version numbers, snapshots, diffs, summaries, and actor metadata.
 */
final class ContentVersion extends Model
{
    use BelongsToTenantTrait;

    protected static string $table = 'content_versions';

    protected static array $fillable = [
        'tenant_id',
        'resource_key',
        'record_id',
        'version_number',
        'summary',
        'snapshot_json',
        'diff_json',
        'actor_id',
        'created_at',
    ];

    protected static array $casts = [
        'tenant_id' => 'int',
        'record_id' => 'int',
        'version_number' => 'int',
        'snapshot_json' => 'json',
        'diff_json' => 'json',
        'actor_id' => 'int',
        'created_at' => 'datetime',
    ];
}
