-- Migration: Treat external lecturers as normal lecturers
-- Description:
--   Dosen eksternal secara fungsional sama dengan dosen internal. Status
--   "eksternal" murni atribut (lecturers.is_external), bukan role terpisah.
--   Fungsi (pembimbing vs penguji) ditentukan oleh assignments.role.
--
--   Migrasi ini mengubah akun lama ber-role 'penguji_eksternal' menjadi akun
--   dosen biasa ('dosen_pembimbing') supaya bisa ditetapkan sebagai pembimbing
--   maupun penguji. Enum 'penguji_eksternal' tetap dipertahankan karena masih
--   dipakai untuk penilaian lewat jalur token.
--
-- Run: mysql -u root -p kbs_db < migrations/external_lecturers_as_lecturer_role.sql
--
-- Aman dijalankan berulang (idempoten). Dibungkus transaksi agar atomik.
-- Semua langkah hanya menyentuh baris dengan role 'penguji_eksternal'.

START TRANSACTION;

-- 1. Pastikan akun eksternal punya capability 'dosen_pembimbing' di user_roles
INSERT INTO user_roles (user_id, role)
SELECT u.id, 'dosen_pembimbing'
FROM users u
WHERE u.role = 'penguji_eksternal'
  AND NOT EXISTS (
      SELECT 1 FROM user_roles ur
      WHERE ur.user_id = u.id AND ur.role = 'dosen_pembimbing'
  );

-- 2. Konversi role utama akun eksternal menjadi dosen biasa
UPDATE users
SET role = 'dosen_pembimbing'
WHERE role = 'penguji_eksternal';

-- 3. Bersihkan sisa role 'penguji_eksternal' pada user_roles.
--    Setelah akun memakai 'dosen_pembimbing', role token lama tidak lagi
--    diperlukan sebagai role akun. Jalur token tetap berjalan lewat
--    evaluations.evaluator_role = 'penguji_eksternal'.
DELETE ur
FROM user_roles ur
JOIN users u ON u.id = ur.user_id
WHERE ur.role = 'penguji_eksternal'
  AND u.role = 'dosen_pembimbing';

COMMIT;
