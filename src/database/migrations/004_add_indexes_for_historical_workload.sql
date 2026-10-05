-- Migration 004: Add indexes for historical workload report performance
-- 
-- This migration adds database indexes to optimize queries for the historical workload report,
-- especially when handling concurrent access by hundreds of users.
--
-- Run this script directly in MySQL/MariaDB:
-- mysql -u your_user -p kbs_db < src/database/migrations/004_add_indexes_for_historical_workload.sql

-- Indexes for assignments table
CREATE INDEX IF NOT EXISTS idx_assignments_lecturer ON assignments(lecturer_id);
CREATE INDEX IF NOT EXISTS idx_assignments_student ON assignments(student_id);
CREATE INDEX IF NOT EXISTS idx_assignments_role ON assignments(role);

-- Indexes for students table
CREATE INDEX IF NOT EXISTS idx_students_angkatan ON students(angkatan);
CREATE INDEX IF NOT EXISTS idx_students_status ON students(status);
CREATE INDEX IF NOT EXISTS idx_students_semester_lulus ON students(semester_lulus);
CREATE INDEX IF NOT EXISTS idx_students_composite_status_angkatan ON students(status, angkatan);

-- Verify indexes created
SHOW INDEX FROM assignments;
SHOW INDEX FROM students;
