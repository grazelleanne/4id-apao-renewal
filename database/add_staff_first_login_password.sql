-- Run once in Adminer on the application's database before deploying.
-- Default 0 preserves the login flow for all existing accounts.
SET @first_login_ddl = IF(
    EXISTS(SELECT 1 FROM information_schema.columns
           WHERE table_schema=DATABASE() AND table_name='users' AND column_name='must_change_password'),
    'SELECT 1',
    'ALTER TABLE users ADD COLUMN must_change_password TINYINT(1) NOT NULL DEFAULT 0'
);
PREPARE first_login_statement FROM @first_login_ddl;
EXECUTE first_login_statement;
DEALLOCATE PREPARE first_login_statement;
