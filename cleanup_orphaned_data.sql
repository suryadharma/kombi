-- Script untuk membersihkan data yatim piatu (orphaned data)
-- Data yatim piatu adalah data evaluations/events yang tidak memiliki title terkait

-- Tampilkan mahasiswa yang tidak memiliki title tapi memiliki data lain
SELECT 
    s.id as student_id,
    s.nim,
    s.name,
    COUNT(DISTINCT e.id) as evaluation_count,
    COUNT(DISTINCT ev.id) as event_count,
    COUNT(DISTINCT a.id) as assignment_count
FROM students s
LEFT JOIN titles t ON s.id = t.student_id
LEFT JOIN evaluations e ON s.id = e.student_id
LEFT JOIN events ev ON s.id = ev.student_id
LEFT JOIN assignments a ON s.id = a.student_id
WHERE t.id IS NULL
  AND (e.id IS NOT NULL OR ev.id IS NOT NULL OR a.id IS NOT NULL)
GROUP BY s.id, s.nim, s.name;

-- Hapus evaluations untuk mahasiswa yang tidak memiliki title
DELETE e FROM evaluations e
INNER JOIN students s ON e.student_id = s.id
LEFT JOIN titles t ON s.id = t.student_id
WHERE t.id IS NULL;

-- Hapus events untuk mahasiswa yang tidak memiliki title
DELETE ev FROM events ev
INNER JOIN students s ON ev.student_id = s.id
LEFT JOIN titles t ON s.id = t.student_id
WHERE t.id IS NULL;

-- Hapus assignments untuk mahasiswa yang tidak memiliki title
DELETE a FROM assignments a
INNER JOIN students s ON a.student_id = s.id
LEFT JOIN titles t ON s.id = t.student_id
WHERE t.id IS NULL;

-- Hapus assignment_history untuk mahasiswa yang tidak memiliki title
DELETE ah FROM assignment_history ah
INNER JOIN students s ON ah.student_id = s.id
LEFT JOIN titles t ON s.id = t.student_id
WHERE t.id IS NULL;
