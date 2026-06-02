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

/**************************************************************************************
 * ForbiddenException — HTTP 403 Forbidden
 *
 * Thrown when an authenticated user lacks the role or permission required
 * to access a resource or perform an action.
 *
 * ExceptionHandler converts this to a 403 JSON response (API) or 403 page (web).
 *
 * Factory methods:
 *   ForbiddenException::role('admin')                 — route requires a role
 *   ForbiddenException::permission('manage-users')    — route requires a permission
 *   ForbiddenException::action('edit-post')           — Gate/Policy denied an ability
 *
 * @package Catalyst\Helpers\Exceptions
 */
/**
 * Defines the Forbidden Exception class contract.
 *
 * @package Catalyst\Helpers\Exceptions
 * Responsibility: Coordinates the forbidden exception behavior within its module boundary.
 */
class ForbiddenException extends RuntimeException
{
    private string $context;
    private string $contextValue;

    /**
     * Initializes the Forbidden Exception instance.
     */
    private function __construct(string $message, string $context, string $contextValue)
    {
        parent::__construct($message, 403);
        $this->context      = $context;
        $this->contextValue = $contextValue;
    }

    // -- Factory methods -------------------------------------------------------

    /**
     * User does not have the required role.
     */
    public static function role(string $role): self
    {
        return new self(
            "Access denied. Required role: {$role}.",
            'role',
            $role
        );
    }

    /**
     * User does not have the required permission.
     */
    public static function permission(string $permission): self
    {
        return new self(
            "Access denied. Required permission: {$permission}.",
            'permission',
            $permission
        );
    }

    /**
     * Gate or Policy denied the ability.
     */
    public static function action(string $ability): self
    {
        return new self(
            "Access denied. Ability: {$ability}.",
            'action',
            $ability
        );
    }

    /**
     * Generic 403 without specific context.
     */
    public static function forbidden(string $message = 'Forbidden.'): self
    {
        return new self($message, 'generic', '');
    }

    // -- Accessors -------------------------------------------------------------

    /**
     * Returns the context value.
     */
    public function getContext(): string
    {
        return $this->context;
    }

    /**
     * Returns the context value value.
     */
    public function getContextValue(): string
    {
        return $this->contextValue;
    }
}
