<?php

declare(strict_types=1);

namespace Catalyst\Repository\DevTools\Models;

use Catalyst\Framework\Database\Model;

/**
 * DemoEmail — lightweight model for ORM / Entities test harness.
 *
 * Maps to `validator_demo_emails` (created in Etapa 3).
 * Used exclusively at /test-features to exercise CRUD, dirty tracking,
 * Collection ops, and Pagination without touching real application data.
 *
 * Table schema:
 *   id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
 *   email      VARCHAR(255) NOT NULL UNIQUE
 *   created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
 *
 * @package Catalyst\Repository\DevTools\Models
 */
class DemoEmail extends Model
{
    protected static string $table = 'validator_demo_emails';

    protected static array $fillable = ['email'];

    protected static array $casts = ['created_at' => 'datetime'];
}
