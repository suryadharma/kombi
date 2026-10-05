-- Migration to fix email_queue body column charset to support emoji (utf8mb4)
-- Run this migration to allow emoji characters in email body

-- Convert the body column to utf8mb4 charset
ALTER TABLE email_queue MODIFY COLUMN body TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Convert the subject column as well for consistency
ALTER TABLE email_queue MODIFY COLUMN subject VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Convert the error_message column as well
ALTER TABLE email_queue MODIFY COLUMN error_message TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Verify the changes
SELECT 
    COLUMN_NAME, 
    CHARACTER_SET_NAME, 
    COLLATION_NAME 
FROM 
    INFORMATION_SCHEMA.COLUMNS 
WHERE 
    TABLE_SCHEMA = 'kbs_db' 
    AND TABLE_NAME = 'email_queue' 
    AND COLUMN_NAME IN ('body', 'subject', 'error_message');
