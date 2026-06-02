<?php

declare(strict_types=1);

use Catalyst\Framework\Database\Migration;

/**
 * Adds cascading user foreign keys to authentication-related tables.
 *
 * @package Catalyst\BootCore\Database\Migrations
 * Responsibility: Enforce and remove user ownership constraints for authentication and notification records.
 */
return new class extends Migration
{
    /**
     * Returns the timestamp identifier used by the migration runner to order and track this migration.
     *
     * Responsibility: Returns the timestamp identifier used by the migration runner to order and track this migration.
     */
    public function getVersion(): string
    {
        return '20260421000000';
    }

    /**
     * Applies the user ownership constraints required by the authentication runtime.
     *
     * Responsibility: Applies the user ownership constraints required by the authentication runtime.
     */
    public function up(): void
    {
        foreach ($this->definitions() as $definition) {
            $table      = $definition['table'];
            $constraint = $definition['constraint'];

            if (!$this->tableExists($table) || !$this->tableExists($definition['reference_table'])) {
                continue;
            }

            $deleteRule = $this->foreignKeyDeleteRule($table, $constraint);
            if ($deleteRule === 'CASCADE') {
                continue;
            }

            if ($deleteRule !== null) {
                $this->dropForeignKey($table, $constraint);
            }

            $this->addForeignKey(
                $table,
                $constraint,
                $definition['column'],
                $definition['reference_table'],
                $definition['reference_column'],
                'CASCADE'
            );
        }
    }

    /**
     * Removes the user ownership constraints managed by this migration during rollback.
     *
     * Responsibility: Removes the user ownership constraints managed by this migration during rollback.
     */
    public function down(): void
    {
        foreach ($this->definitions() as $definition) {
            if (!$this->tableExists($definition['table'])) {
                continue;
            }

            if ($this->foreignKeyExists($definition['table'], $definition['constraint'])) {
                $this->dropForeignKey($definition['table'], $definition['constraint']);
            }
        }
    }

    /**
     * Returns the user ownership constraints managed by this migration.
     *
     * Responsibility: Returns the user ownership constraints managed by this migration.
     * @return array<int, array{table:string,constraint:string,column:string,reference_table:string,reference_column:string}>
     */
    private function definitions(): array
    {
        return [
            [
                'table'            => 'remember_tokens',
                'constraint'       => 'fk_remember_tokens_user',
                'column'           => 'user_id',
                'reference_table'  => 'users',
                'reference_column' => 'id',
            ],
            [
                'table'            => 'email_verification_tokens',
                'constraint'       => 'fk_email_verification_tokens_user',
                'column'           => 'user_id',
                'reference_table'  => 'users',
                'reference_column' => 'id',
            ],
            [
                'table'            => 'password_reset_tokens',
                'constraint'       => 'fk_password_reset_tokens_user',
                'column'           => 'user_id',
                'reference_table'  => 'users',
                'reference_column' => 'id',
            ],
            [
                'table'            => 'user_social_accounts',
                'constraint'       => 'fk_user_social_accounts_user',
                'column'           => 'user_id',
                'reference_table'  => 'users',
                'reference_column' => 'id',
            ],
            [
                'table'            => 'notifications',
                'constraint'       => 'fk_notifications_user',
                'column'           => 'user_id',
                'reference_table'  => 'users',
                'reference_column' => 'id',
            ],
        ];
    }
};
