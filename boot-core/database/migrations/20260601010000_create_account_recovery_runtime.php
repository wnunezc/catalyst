<?php

declare(strict_types=1);

use Catalyst\Framework\Database\Migration;

/**
 * Creates the persistence structures required by the account recovery runtime.
 *
 * @package Catalyst\BootCore\Database\Migrations
 * Responsibility: Provision and remove account recovery requests, tokens, events, and user security states.
 */
return new class extends Migration
{
    /**
     * Returns the migration version identifier.
     *
     * Responsibility: Returns the migration version identifier.
     */
    public function getVersion(): string
    {
        return '20260601010000';
    }

    /**
     * Creates the account recovery runtime tables.
     *
     * Responsibility: Creates the account recovery runtime tables.
     */
    public function up(): void
    {
        $this->createRequestsTable();
        $this->createTokensTable();
        $this->createEventsTable();
        $this->createSecurityStatesTable();
    }

    /**
     * Removes the account recovery runtime tables in dependency-safe order.
     *
     * Responsibility: Removes the account recovery runtime tables in dependency-safe order.
     */
    public function down(): void
    {
        if ($this->tableExists('account_recovery_events')) {
            $this->statement('DROP TABLE `account_recovery_events`');
        }
        if ($this->tableExists('account_recovery_tokens')) {
            $this->statement('DROP TABLE `account_recovery_tokens`');
        }
        if ($this->tableExists('user_security_states')) {
            $this->statement('DROP TABLE `user_security_states`');
        }
        if ($this->tableExists('account_recovery_requests')) {
            $this->statement('DROP TABLE `account_recovery_requests`');
        }
    }

    /**
     * Creates the table that stores account recovery requests.
     *
     * Responsibility: Creates the table that stores account recovery requests.
     */
    private function createRequestsTable(): void
    {
        if ($this->tableExists('account_recovery_requests')) {
            return;
        }

        $this->statement(
            'CREATE TABLE `account_recovery_requests` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
                `user_id` INT UNSIGNED NULL,
                `request_type` VARCHAR(64) NOT NULL,
                `status` VARCHAR(64) NOT NULL DEFAULT \'pending_support_review\',
                `known_email` VARCHAR(255) NOT NULL DEFAULT \'\',
                `alternate_email` VARCHAR(255) NOT NULL DEFAULT \'\',
                `message` TEXT NULL,
                `ip_hash` CHAR(64) NOT NULL DEFAULT \'\',
                `user_agent_hash` CHAR(64) NOT NULL DEFAULT \'\',
                `reviewed_by` INT UNSIGNED NULL,
                `reviewed_at` DATETIME NULL,
                `completed_at` DATETIME NULL,
                `created_at` DATETIME NOT NULL,
                `updated_at` DATETIME NOT NULL,
                PRIMARY KEY (`id`),
                KEY `idx_account_recovery_tenant_status` (`tenant_id`, `status`, `created_at`),
                KEY `idx_account_recovery_tenant_user` (`tenant_id`, `user_id`, `created_at`),
                KEY `idx_account_recovery_known_email` (`tenant_id`, `known_email`, `created_at`),
                CONSTRAINT `fk_account_recovery_requests_user`
                    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
                    ON DELETE SET NULL,
                CONSTRAINT `fk_account_recovery_requests_reviewer`
                    FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`)
                    ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    /**
     * Creates the table that stores account recovery tokens.
     *
     * Responsibility: Creates the table that stores account recovery tokens.
     */
    private function createTokensTable(): void
    {
        if ($this->tableExists('account_recovery_tokens')) {
            return;
        }

        $this->statement(
            'CREATE TABLE `account_recovery_tokens` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
                `request_id` BIGINT UNSIGNED NOT NULL,
                `user_id` INT UNSIGNED NULL,
                `purpose` VARCHAR(64) NOT NULL,
                `token_hash` CHAR(64) NOT NULL,
                `active` TINYINT(1) NOT NULL DEFAULT 1,
                `expires_at` DATETIME NOT NULL,
                `created_at` DATETIME NOT NULL,
                `consumed_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uq_account_recovery_token_hash` (`token_hash`),
                KEY `idx_account_recovery_token_lookup` (`tenant_id`, `purpose`, `active`, `expires_at`),
                KEY `idx_account_recovery_token_request` (`request_id`),
                KEY `idx_account_recovery_token_user` (`tenant_id`, `user_id`),
                CONSTRAINT `fk_account_recovery_tokens_request`
                    FOREIGN KEY (`request_id`) REFERENCES `account_recovery_requests` (`id`)
                    ON DELETE CASCADE,
                CONSTRAINT `fk_account_recovery_tokens_user`
                    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
                    ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    /**
     * Creates the table that records account recovery events.
     *
     * Responsibility: Creates the table that records account recovery events.
     */
    private function createEventsTable(): void
    {
        if ($this->tableExists('account_recovery_events')) {
            return;
        }

        $this->statement(
            'CREATE TABLE `account_recovery_events` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
                `request_id` BIGINT UNSIGNED NULL,
                `user_id` INT UNSIGNED NULL,
                `event_type` VARCHAR(80) NOT NULL,
                `payload_json` JSON NULL,
                `created_at` DATETIME NOT NULL,
                `created_by` INT UNSIGNED NULL,
                PRIMARY KEY (`id`),
                KEY `idx_account_recovery_events_user` (`tenant_id`, `user_id`, `created_at`),
                KEY `idx_account_recovery_events_request` (`request_id`, `created_at`),
                CONSTRAINT `fk_account_recovery_events_request`
                    FOREIGN KEY (`request_id`) REFERENCES `account_recovery_requests` (`id`)
                    ON DELETE SET NULL,
                CONSTRAINT `fk_account_recovery_events_user`
                    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
                    ON DELETE SET NULL,
                CONSTRAINT `fk_account_recovery_events_actor`
                    FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
                    ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    /**
     * Creates the table that stores user security state flags.
     *
     * Responsibility: Creates the table that stores user security state flags.
     */
    private function createSecurityStatesTable(): void
    {
        if ($this->tableExists('user_security_states')) {
            return;
        }

        $this->statement(
            'CREATE TABLE `user_security_states` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
                `user_id` INT UNSIGNED NOT NULL,
                `security_hold` TINYINT(1) NOT NULL DEFAULT 0,
                `security_hold_reason` VARCHAR(191) NULL,
                `password_reset_required` TINYINT(1) NOT NULL DEFAULT 0,
                `mfa_reset_required` TINYINT(1) NOT NULL DEFAULT 0,
                `last_recovery_at` DATETIME NULL,
                `created_at` DATETIME NOT NULL,
                `updated_at` DATETIME NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uq_user_security_states_tenant_user` (`tenant_id`, `user_id`),
                KEY `idx_user_security_states_hold` (`tenant_id`, `security_hold`),
                CONSTRAINT `fk_user_security_states_user`
                    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
                    ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }
};
