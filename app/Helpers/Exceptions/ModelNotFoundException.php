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

namespace Catalyst\Helpers\Exceptions;

use RuntimeException;

/**
 * Thrown when a Model query expected exactly one result but found none.
 *
 * Typically raised by Model::findOrFail() and ModelQueryBuilder::findOrFail().
 * Callers may catch this to return a 404 response.
 *
 * @package Catalyst\Helpers\Exceptions
 */
class ModelNotFoundException extends RuntimeException
{
    private string $modelClass;
    private int|string|null $id;

    // -------------------------------------------------------------------------
    // Factory methods
    // -------------------------------------------------------------------------

    /**
     * Model::findOrFail($id) — expected a specific record.
     */
    public static function forModel(string $modelClass, int|string $id): self
    {
        $short = basename(str_replace('\\', '/', $modelClass));

        $e            = new self("No [{$short}] record found with key [{$id}].");
        $e->modelClass = $modelClass;
        $e->id         = $id;

        return $e;
    }

    /**
     * Query with firstOrFail() — expected at least one result.
     */
    public static function forQuery(string $modelClass): self
    {
        $short = basename(str_replace('\\', '/', $modelClass));

        $e            = new self("No [{$short}] record matched the given query.");
        $e->modelClass = $modelClass;
        $e->id         = null;

        return $e;
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    /**
     * Returns the model class value.
     */
    public function getModelClass(): string
    {
        return $this->modelClass;
    }

    /**
     * Returns the id value.
     */
    public function getId(): int|string|null
    {
        return $this->id;
    }
}
