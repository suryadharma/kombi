-- Migration: Add Performance Indexes
-- Date: 2026-10-06
-- Description: Add indexes on frequently filtered/aggregated columns to keep
--              dashboard and list queries fast as data volume grows.
--              All statements are idempotent (IF NOT EXISTS) and non-destructive.

-- Students: filter by angkatan (IN) + status (!= 'LULUS') + GROUP BY angkatan
CREATE INDEX IF NOT EXISTS idx_students_angkatan_status ON students (angkatan, status);

-- Events: upcoming-events dashboard query + per-angkatan aggregation
CREATE INDEX IF NOT EXISTS idx_events_status_scheduled_date ON events (status, scheduled_date);
CREATE INDEX IF NOT EXISTS idx_events_type_status ON events (type, status);

-- Titles: pending verification count/list (status = 'MENUNGGU')
CREATE INDEX IF NOT EXISTS idx_titles_status ON titles (status);

-- Evaluations: filter/group by stage
CREATE INDEX IF NOT EXISTS idx_evaluations_stage ON evaluations (stage);
