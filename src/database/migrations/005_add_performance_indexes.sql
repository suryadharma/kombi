-- Migration 005: Add performance indexes for common queries
-- 
-- This migration adds database indexes to optimize queries for:
-- - Timeline page (student lookup by user_id)
-- - Score recap pages (filter by stage, evaluator_id)
-- - Bypass evaluation queries (filter by mode)
--
-- Run this script directly in MySQL/MariaDB:
-- mysql -u your_user -p kbs_db < src/database/migrations/005_add_performance_indexes.sql
--
-- Or run via Docker:
-- docker-compose -f docker-compose.kombi.yml exec kbs_db mariadb -ukbs_user -pkbs_password kbs_db < src/database/migrations/005_add_performance_indexes.sql

-- Index for students.user_id
-- Used in: TimelineController, ReportController (PDF export for students)
-- Query: SELECT id FROM students WHERE user_id = ?
CREATE INDEX IF NOT EXISTS idx_students_user_id ON students(user_id);

-- Index for evaluations.stage
-- Used in: ScoreController, ReportController, TimelineController
-- Query: WHERE stage = 'sempro' (filter by stage)
CREATE INDEX IF NOT EXISTS idx_evaluations_stage ON evaluations(stage);

-- Index for evaluations.evaluator_id
-- Used in: Rekap Nilai Dosen (lecturer recap page)
-- Query: WHERE evaluator_id = ? (filter by lecturer)
CREATE INDEX IF NOT EXISTS idx_evaluations_evaluator_id ON evaluations(evaluator_id);

-- Index for evaluations.mode
-- Used in: Bypass evaluation queries
-- Query: WHERE mode = 'bypass'
CREATE INDEX IF NOT EXISTS idx_evaluations_mode ON evaluations(mode);

-- Composite index for evaluations (student_id, stage)
-- Used in: Score recap, timeline queries
-- Query: WHERE student_id = ? AND stage = ?
CREATE INDEX IF NOT EXISTS idx_evaluations_student_stage ON evaluations(student_id, stage);

-- Verify indexes created
SHOW INDEX FROM students;
SHOW INDEX FROM evaluations;
