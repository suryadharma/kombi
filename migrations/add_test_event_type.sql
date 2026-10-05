-- Migration: Add TEST to email_logs event_type ENUM
-- Date: 2025-02-07
-- Description: Add 'TEST' value to event_type ENUM and make student_id nullable in email_logs table

-- Modify event_type column to include 'TEST'
ALTER TABLE email_logs
MODIFY COLUMN event_type ENUM('SEMPRO', 'SEMHAS', 'PRA_UJIAN', 'UJIAN_SKRIPSI', 'TEST') NOT NULL;

-- Make student_id nullable for test emails
ALTER TABLE email_logs
MODIFY COLUMN student_id INT NULL;
