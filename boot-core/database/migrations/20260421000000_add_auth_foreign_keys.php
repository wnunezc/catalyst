<?php

declare(strict_types=1);

use Catalyst\Framework\Database\Migration;

return new class extends Migration
{
    public function getVersion(): string
    {
        return '20260421000000';
    }

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
