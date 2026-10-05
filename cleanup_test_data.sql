-- SQL Script to Clean Up Test Data (NIM 222222222222)
-- 
-- Usage: Run this SQL script directly in your database
-- 
-- WARNING: This will DELETE data permanently!
-- Make sure you have a backup before running this!

-- Start transaction for safety
START TRANSACTION;

-- Show what will be deleted
SELECT '=== Students to be deleted ===' as info;
SELECT id, nim, name, angkatan, status FROM students 
WHERE nim LIKE '%2222%' OR nim LIKE '%test%' OR nim LIKE '%TEST%';

SELECT '=== Events to be deleted ===' as info;
SELECT e.id, e.student_id, s.nim, s.name, e.event_type, e.event_date, e.venue 
FROM events e
JOIN students s ON s.id = e.student_id
WHERE s.nim LIKE '%2222%' OR s.nim LIKE '%test%' OR s.nim LIKE '%TEST%';

SELECT '=== Assignments to be deleted ===' as info;
SELECT a.id, a.student_id, s.nim as student_nim, a.lecturer_id, u.name as lecturer_name, a.role
FROM assignments a
JOIN students s ON s.id = a.student_id
LEFT JOIN users u ON u.id = a.lecturer_id
WHERE s.nim LIKE '%2222%' OR s.nim LIKE '%test%' OR s.nim LIKE '%TEST%';

-- Delete events for test students
DELETE FROM events 
WHERE student_id IN (
    SELECT id FROM students 
    WHERE nim LIKE '%2222%' OR nim LIKE '%test%' OR nim LIKE '%TEST%'
);

-- Delete assignments for test students
DELETE FROM assignments 
WHERE student_id IN (
    SELECT id FROM students 
    WHERE nim LIKE '%2222%' OR nim LIKE '%test%' OR nim LIKE '%TEST%'
);

-- Delete users that are linked to test students
DELETE FROM users 
WHERE username IN (
    SELECT nim FROM students 
    WHERE nim LIKE '%2222%' OR nim LIKE '%test%' OR nim LIKE '%TEST%'
);

-- Delete test students
DELETE FROM students 
WHERE nim LIKE '%2222%' OR nim LIKE '%test%' OR nim LIKE '%TEST%';

-- Show verification
SELECT '=== Verification - remaining test students ===' as info;
SELECT COUNT(*) as remaining_test_students FROM students 
WHERE nim LIKE '%2222%' OR nim LIKE '%test%' OR nim LIKE '%TEST%';

-- Commit the transaction
-- Uncomment the line below to actually delete the data
-- COMMIT;
-- ROLLBACK; -- Use this to undo if needed
