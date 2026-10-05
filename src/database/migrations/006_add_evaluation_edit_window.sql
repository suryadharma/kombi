-- Migration 006: Add Evaluation Edit Window and Audit Log
--
-- This migration adds features for:
-- - Edit window for evaluations (configurable duration)
-- - Manual lock functionality
-- - Audit log for all evaluation changes
--
-- Run this script directly in MySQL/MariaDB:
-- docker-compose -f docker-compose.kombi.yml exec -T kbs_db mariadb -ukbs_user -pkbs_pass kbs_db < src/database/migrations/006_add_evaluation_edit_window.sql

-- Add columns to evaluations table for edit window tracking
ALTER TABLE evaluations
ADD COLUMN IF NOT EXISTS submitted_at TIMESTAMP NULL DEFAULT NULL COMMENT 'Timestamp when evaluation was first submitted',
ADD COLUMN IF NOT EXISTS locked_at TIMESTAMP NULL DEFAULT NULL COMMENT 'Timestamp when evaluation was manually locked',
ADD COLUMN IF NOT EXISTS locked_by INT NULL DEFAULT NULL COMMENT 'User ID who locked the evaluation',
ADD COLUMN IF NOT EXISTS lock_reason VARCHAR(255) NULL DEFAULT NULL COMMENT 'Reason for locking the evaluation';

-- Add foreign key for locked_by (skip if already exists)
SET @exist := (SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'evaluations' AND CONSTRAINT_NAME = 'fk_evaluations_locked_by');
SET @sqlstmt := IF(@exist = 0,
    'ALTER TABLE evaluations ADD CONSTRAINT fk_evaluations_locked_by FOREIGN KEY (locked_by) REFERENCES users(id) ON DELETE SET NULL',
    'SELECT ''Constraint already exists'' AS message');
PREPARE stmt FROM @sqlstmt;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Create evaluation_edit_history table for audit log
CREATE TABLE IF NOT EXISTS evaluation_edit_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    evaluation_id INT NOT NULL,
    edited_by INT NOT NULL,
    edited_by_role VARCHAR(50) NULL DEFAULT NULL COMMENT 'Role of user who edited (dosen_pembimbing, dosen_penguji, kombi, superadmin)',
    old_final_score DECIMAL(6,2) NULL,
    new_final_score DECIMAL(6,2) NULL,
    old_total_score DECIMAL(6,2) NULL,
    new_total_score DECIMAL(6,2) NULL,
    edit_reason VARCHAR(500) NULL,
    edited_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (evaluation_id) REFERENCES evaluations(id) ON DELETE CASCADE,
    FOREIGN KEY (edited_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_evaluation_edit_history_evaluation (evaluation_id),
    INDEX idx_evaluation_edit_history_edited_by (edited_by),
    INDEX idx_evaluation_edit_history_edited_at (edited_at)
) COMMENT='Audit log for evaluation score changes';

-- Verify columns added
DESCRIBE evaluations;
SHOW INDEX FROM evaluations;

-- Verify table created
DESCRIBE evaluation_edit_history;
SHOW INDEX FROM evaluation_edit_history;
