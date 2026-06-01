<?php

declare(strict_types=1);

use Catalyst\Framework\Database\Migration;

return new class extends Migration
{
    private const string USERS_TENANT_KEY_INDEX = 'uq_users_tenant_id_id';
    private const string API_TOKENS_OWNER_FK = 'fk_api_tokens_tenant_user';

    public function getVersion(): string
    {
        return '20260521153000';
    }

    public function up(): void
    {
        if (!$this->tableExists('users') || !$this->tableExists('api_tokens')) {
            return;
        }

        $this->deleteIntegrityViolations();
        $this->ensureUsersTenantKeyIndex();

        if (!$this->foreignKeyExists('api_tokens', self::API_TOKENS_OWNER_FK)) {
            $this->statement(
                'ALTER TABLE `api_tokens`
                 ADD CONSTRAINT `fk_api_tokens_tenant_user`
                 FOREIGN KEY (`tenant_id`, `user_id`)
                 REFERENCES `users` (`tenant_id`, `id`)
                 ON DELETE CASCADE
                 ON UPDATE RESTRICT'
            );
        }
    }

    public function down(): void
    {
        if ($this->tableExists('api_tokens') && $this->foreignKeyExists('api_tokens', self::API_TOKENS_OWNER_FK)) {
            $this->dropForeignKey('api_tokens', self::API_TOKENS_OWNER_FK);
        }
    }

    private function deleteIntegrityViolations(): void
    {
        $this->execute(
            'DELETE tokens
             FROM api_tokens tokens
             LEFT JOIN users
               ON users.id = tokens.user_id
              AND users.tenant_id = tokens.tenant_id
             WHERE users.id IS NULL'
        );
    }

    private function ensureUsersTenantKeyIndex(): void
    {
        if ($this->indexExists('users', self::USERS_TENANT_KEY_INDEX)) {
            return;
        }

        $this->statement(
            'ALTER TABLE `users`
             ADD UNIQUE KEY `uq_users_tenant_id_id` (`tenant_id`, `id`)'
        );
    }

    private function indexExists(string $table, string $index): bool
    {
        $row = $this->selectOne(
            'SELECT 1
             FROM information_schema.statistics
             WHERE table_schema = DATABASE()
               AND table_name = :table
               AND index_name = :index
             LIMIT 1',
            [
                ':table' => $table,
                ':index' => $index,
            ]
        );

        return $row !== null;
    }
};
