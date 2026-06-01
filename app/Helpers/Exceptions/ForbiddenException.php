<?php

declare(strict_types=1);

/**************************************************************************************
 *
 * Catalyst PHP Framework
 * PHP Version 8.4 (Required).
 *
 * @package   Catalyst
 * @subpackage Helpers\Exceptions
 * @see       https://github.com/arcanisgk/catalyst
 *
 * @author    Walter Nuñez (arcanisgk/original founder) <icarosnet@gmail.com>
 * @copyright 2023 - 2025
 * @license   http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 *
 * @note      This program is distributed in the hope that it will be useful
 *            WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 *            or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @category  Framework
 * @filesource
 *
 * @link      https://catalyst.dock Local development URL
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
class ForbiddenException extends RuntimeException
{
    private string $context;
    private string $contextValue;

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

    public function getContext(): string
    {
        return $this->context;
    }

    public function getContextValue(): string
    {
        return $this->contextValue;
    }
}
