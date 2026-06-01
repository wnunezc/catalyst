<?php

declare(strict_types=1);

use Catalyst\Framework\Database\Migration;

return new class extends Migration
{
    public function getVersion(): string
    {
        return '20260519190000';
    }

    public function up(): void
    {
        $this->ensureTenantColumn('users', 'id');
        $this->ensureTenantColumn('roles', 'id');
        $this->ensureTenantColumn('permissions', 'id');
        $this->ensureTenantColumn('user_roles', 'role_id');
        $this->ensureTenantColumn('role_permissions', 'permission_id');
        $this->ensureTenantColumn('metadata_field_definitions', 'id');
        $this->ensureTenantColumn('media_library', 'id');
        $this->ensureTenantColumn('metadata_field_values', 'id');
        $this->ensureTenantColumn('workflow_instances', 'id');
        $this->ensureTenantColumn('workflow_transitions', 'id');
        $this->ensureTenantColumn('document_templates', 'id');
        $this->ensureTenantColumn('document_artifacts', 'id');
        $this->ensureTenantColumn('automation_rules', 'id');
        $this->ensureTenantColumn('automation_execution_logs', 'id');
        $this->ensureTenantColumn('content_versions', 'id');
        $this->ensureTenantColumn('api_tokens', 'id');
        $this->ensureTenantColumn('record_claims', 'id');
        $this->ensureTenantColumn('audit_logs', 'id');
        $this->ensureAuditTenantKey();

        $this->dropIndexIfExists('users', 'email');
        $this->dropIndexIfExists('roles', 'name');
        $this->dropIndexIfExists('roles', 'slug');
        $this->dropIndexIfExists('permissions', 'name');
        $this->dropIndexIfExists('permissions', 'slug');
        $this->dropIndexIfExists('metadata_field_definitions', 'uniq_metadata_field_resource_key');
        $this->dropIndexIfExists('metadata_field_values', 'uniq_metadata_field_record');
        $this->dropIndexIfExists('workflow_instances', 'uniq_workflow_record');
        $this->dropIndexIfExists('document_templates', 'uniq_document_template_slug');
        $this->dropIndexIfExists('automation_rules', 'uniq_automation_rule_slug');
        $this->dropIndexIfExists('content_versions', 'uniq_content_version');
        $this->dropIndexIfExists('record_claims', 'uniq_record_claim_resource');

        $this->ensureUniqueIndex('users', 'uq_users_tenant_email', ['tenant_id', 'email']);
        $this->ensureUniqueIndex('roles', 'uq_roles_tenant_name', ['tenant_id', 'name']);
        $this->ensureUniqueIndex('roles', 'uq_roles_tenant_slug', ['tenant_id', 'slug']);
        $this->ensureUniqueIndex('permissions', 'uq_permissions_tenant_name', ['tenant_id', 'name']);
        $this->ensureUniqueIndex('permissions', 'uq_permissions_tenant_slug', ['tenant_id', 'slug']);
        $this->ensureUniqueIndex('metadata_field_definitions', 'uq_metadata_field_tenant_resource_key', ['tenant_id', 'resource_key', 'field_key']);
        $this->ensureUniqueIndex('metadata_field_values', 'uq_metadata_value_tenant_record', ['tenant_id', 'resource_key', 'record_id', 'field_definition_id']);
        $this->ensureUniqueIndex('workflow_instances', 'uq_workflow_tenant_record', ['tenant_id', 'definition_key', 'resource_key', 'record_id']);
        $this->ensureUniqueIndex('document_templates', 'uq_document_template_tenant_slug', ['tenant_id', 'slug']);
        $this->ensureUniqueIndex('automation_rules', 'uq_automation_rule_tenant_slug', ['tenant_id', 'slug']);
        $this->ensureUniqueIndex('content_versions', 'uq_content_version_tenant_record', ['tenant_id', 'resource_key', 'record_id', 'version_number']);
        $this->ensureUniqueIndex('record_claims', 'uq_record_claim_tenant_resource', ['tenant_id', 'resource_key', 'record_id']);

        $this->ensureIndex('users', 'idx_users_tenant_active', ['tenant_id', 'active']);
        $this->ensureIndex('roles', 'idx_roles_tenant_id', ['tenant_id']);
        $this->ensureIndex('permissions', 'idx_permissions_tenant_id', ['tenant_id']);
        $this->ensureIndex('user_roles', 'idx_user_roles_tenant_role', ['tenant_id', 'role_id']);
        $this->ensureIndex('role_permissions', 'idx_role_permissions_tenant_permission', ['tenant_id', 'permission_id']);
        $this->ensureIndex('metadata_field_definitions', 'idx_metadata_field_tenant_resource', ['tenant_id', 'resource_key']);
        $this->ensureIndex('metadata_field_values', 'idx_metadata_value_tenant_resource_record', ['tenant_id', 'resource_key', 'record_id']);
        $this->ensureIndex('media_library', 'idx_media_library_tenant_created_at', ['tenant_id', 'created_at']);
        $this->ensureIndex('workflow_instances', 'idx_workflow_tenant_resource', ['tenant_id', 'resource_key', 'record_id']);
        $this->ensureIndex('workflow_transitions', 'idx_workflow_transitions_tenant_instance', ['tenant_id', 'workflow_instance_id']);
        $this->ensureIndex('document_artifacts', 'idx_document_artifacts_tenant_template', ['tenant_id', 'document_template_id']);
        $this->ensureIndex('automation_execution_logs', 'idx_automation_logs_tenant_rule', ['tenant_id', 'rule_id']);
        $this->ensureIndex('content_versions', 'idx_content_versions_tenant_resource', ['tenant_id', 'resource_key', 'record_id']);
        $this->ensureIndex('api_tokens', 'idx_api_tokens_tenant_user', ['tenant_id', 'user_id']);
        $this->ensureIndex('api_tokens', 'idx_api_tokens_tenant_revoked_at', ['tenant_id', 'revoked_at']);
        $this->ensureIndex('record_claims', 'idx_record_claim_tenant_claimed_by', ['tenant_id', 'claimed_by']);
        $this->ensureIndex('audit_logs', 'idx_audit_logs_tenant_occurred_at', ['tenant_id', 'occurred_at']);
        $this->ensureIndex('audit_logs', 'idx_audit_logs_tenant_resource', ['tenant_id', 'resource']);
        $this->ensureIndex('audit_logs', 'idx_audit_logs_tenant_action', ['tenant_id', 'action']);
        $this->ensureIndex('audit_logs', 'idx_audit_logs_tenant_channel', ['tenant_id', 'channel']);
    }

    public function down(): void
    {
        // PA-02 is a forward-only schema hardening step. Rolling it back would
        // require destructive index and data-shape changes across live records.
    }

    private function ensureTenantColumn(string $table, string $after): void
    {
        if (!$this->tableExists($table) || $this->columnExists($table, 'tenant_id')) {
            return;
        }

        $this->statement(sprintf(
            'ALTER TABLE %s ADD COLUMN `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1 AFTER %s',
            $this->quoteIdentifier($table),
            $this->quoteIdentifier($after)
        ));
    }

    private function ensureAuditTenantKey(): void
    {
        if (!$this->tableExists('audit_logs')) {
            return;
        }

        if (!$this->columnExists('audit_logs', 'tenant_key')) {
            $this->statement(
                'ALTER TABLE `audit_logs`
                 ADD COLUMN `tenant_key` VARCHAR(120) NOT NULL DEFAULT \'default\' AFTER `tenant_id`'
            );
        }

        $this->execute(
            'UPDATE `audit_logs`
             SET `tenant_key` = :tenant_key
             WHERE `tenant_key` IS NULL OR TRIM(`tenant_key`) = \'\'',
            [':tenant_key' => 'default']
        );
    }

    private function ensureUniqueIndex(string $table, string $index, array $columns): void
    {
        if (!$this->tableExists($table) || $this->indexExists($table, $index)) {
            return;
        }

        $this->statement(sprintf(
            'ALTER TABLE %s ADD UNIQUE KEY %s (%s)',
            $this->quoteIdentifier($table),
            $this->quoteIdentifier($index),
            implode(', ', array_map([$this, 'quoteIdentifier'], $columns))
        ));
    }

    private function ensureIndex(string $table, string $index, array $columns): void
    {
        if (!$this->tableExists($table) || $this->indexExists($table, $index)) {
            return;
        }

        $this->statement(sprintf(
            'ALTER TABLE %s ADD KEY %s (%s)',
            $this->quoteIdentifier($table),
            $this->quoteIdentifier($index),
            implode(', ', array_map([$this, 'quoteIdentifier'], $columns))
        ));
    }

    private function dropIndexIfExists(string $table, string $index): void
    {
        if (!$this->tableExists($table) || !$this->indexExists($table, $index)) {
            return;
        }

        $this->statement(sprintf(
            'ALTER TABLE %s DROP INDEX %s',
            $this->quoteIdentifier($table),
            $this->quoteIdentifier($index)
        ));
    }

    private function columnExists(string $table, string $column): bool
    {
        $row = $this->selectOne(
            'SELECT 1
             FROM information_schema.columns
             WHERE table_schema = DATABASE()
               AND table_name = :table
               AND column_name = :column
             LIMIT 1',
            [
                ':table' => $table,
                ':column' => $column,
            ]
        );

        return $row !== null;
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
