-- ============================================================
-- SUPERSEDED — 2026-04-24
--
-- Este migration ya está incorporado en create-catalyst-db.sql.
-- Las FK constraints están en los CREATE TABLE de cada tabla.
--
-- Para una DB nueva: usar create-catalyst-db.sql directamente.
--
-- Para una DB existente creada ANTES de 2026-04-21 que aún
-- no tenga estas FK, ejecutar los ALTER TABLE de abajo.
-- Verificar primero con:
--   SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
--   WHERE TABLE_SCHEMA = 'catalyst' AND CONSTRAINT_TYPE = 'FOREIGN KEY';
-- ============================================================

ALTER TABLE remember_tokens
    ADD CONSTRAINT fk_remember_tokens_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

ALTER TABLE email_verification_tokens
    ADD CONSTRAINT fk_email_verification_tokens_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

ALTER TABLE password_reset_tokens
    ADD CONSTRAINT fk_password_reset_tokens_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

ALTER TABLE user_social_accounts
    ADD CONSTRAINT fk_user_social_accounts_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

ALTER TABLE notifications
    ADD CONSTRAINT fk_notifications_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

-- Rollback:
-- ALTER TABLE remember_tokens           DROP FOREIGN KEY fk_remember_tokens_user;
-- ALTER TABLE email_verification_tokens DROP FOREIGN KEY fk_email_verification_tokens_user;
-- ALTER TABLE password_reset_tokens     DROP FOREIGN KEY fk_password_reset_tokens_user;
-- ALTER TABLE user_social_accounts      DROP FOREIGN KEY fk_user_social_accounts_user;
-- ALTER TABLE notifications             DROP FOREIGN KEY fk_notifications_user;
