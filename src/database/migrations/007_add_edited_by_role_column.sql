-- Migration 007: Add edited_by_role column to evaluation_edit_history
--
-- This migration adds the edited_by_role column that was missing from the original
-- evaluation_edit_history table definition.
--
-- Run this script directly in MySQL/MariaDB:
-- docker-compose -f docker-compose.kombi.yml exec -T kbs_db mariadb -ukbs_user -pkbs_pass kbs_db < src/database/migrations/007_add_edited_by_role_column.sql

-- Add edited_by_role column if it doesn't exist
SET @exist := (SELECT COUNT(*) FROM information_schema.COLUMNS 
               WHERE TABLE_SCHEMA = DATABASE() 
               AND TABLE_NAME = 'evaluation_edit_history' 
               AND COLUMN_NAME = 'edited_by_role');

SET @sqlstmt := IF(@exist = 0,
    'ALTER TABLE evaluation_edit_history 
     ADD COLUMN edited_by_role VARCHAR(50) NULL DEFAULT NULL 
     COMMENT ''Role of user who edited (dosen_pembimbing, dosen_penguji, kombi, superadmin)'' 
     AFTER edited_by',
    'SELECT ''Column edited_by_role already exists'' AS message');

PREPARE stmt FROM @sqlstmt;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verify column added
DESCRIBE evaluation_edit_history;
