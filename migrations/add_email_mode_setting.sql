-- Add email_mode setting to support instant/batch/hybrid email delivery
-- Run: mysql -u root -p kbs_db < migrations/add_email_mode_setting.sql

-- Insert email_mode setting (default: hybrid)
INSERT INTO settings (`key_name`, `value`, `description`, `updated_at`)
VALUES ('email_mode', 'hybrid', 'Email delivery mode: instant (send immediately after each submission), batch (send when all evaluators complete), or hybrid (both)', NOW())
ON DUPLICATE KEY UPDATE
    `value` = 'hybrid',
    `description` = 'Email delivery mode: instant (send immediately after each submission), batch (send when all evaluators complete), or hybrid (both)',
    `updated_at` = NOW();
