-- Sample future events for testing the schedule display on login page
-- This file adds events with dates in the future (2026) so they appear on the login page

USE kbs_db;

-- SEMPRO events (February 2026)
INSERT INTO events (student_id, type, scheduled_date, scheduled_time, room, status) VALUES
(642, 'SEMPRO', '2026-02-15', '09:00:00', 'R101', 'MENUNGGU'),
(643, 'SEMPRO', '2026-02-16', '10:00:00', 'R102', 'MENUNGGU'),
(644, 'SEMPRO', '2026-02-17', '13:00:00', 'R103', 'MENUNGGU');

-- SEMHAS events (February 2026)
INSERT INTO events (student_id, type, scheduled_date, scheduled_time, room, status) VALUES
(645, 'SEMHAS', '2026-02-20', '09:00:00', 'R201', 'MENUNGGU'),
(646, 'SEMHAS', '2026-02-21', '10:00:00', 'R202', 'MENUNGGU'),
(647, 'SEMHAS', '2026-02-22', '13:00:00', 'R203', 'MENUNGGU');

-- UJIAN_SKRIPSI events (March 2026)
INSERT INTO events (student_id, type, scheduled_date, scheduled_time, room, status) VALUES
(648, 'UJIAN_SKRIPSI', '2026-03-10', '08:00:00', 'R301', 'MENUNGGU'),
(649, 'UJIAN_SKRIPSI', '2026-03-11', '09:00:00', 'R302', 'MENUNGGU'),
(650, 'UJIAN_SKRIPSI', '2026-03-12', '10:00:00', 'R303', 'MENUNGGU');
