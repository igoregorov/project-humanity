-- migrations/2026_09_21_001_add_avatar_path_to_users.sql
ALTER TABLE users
    ADD COLUMN avatar_path VARCHAR(255) NULL DEFAULT NULL AFTER last_login;
