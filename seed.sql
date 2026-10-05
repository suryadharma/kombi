-- Seed data for KBS Application

USE kbs_db;

-- Insert default users
INSERT INTO users (username, password, name, role) VALUES
('superadmin', '$2y$10$dlLoSiTBCc5qLI/1sYO3Euc5ZaF5KxoyJUQvI6Sd5B1vXeox/7ml.', 'Super Admin', 'superadmin'),
('kombi', '$2y$10$dlLoSiTBCc5qLI/1sYO3Euc5ZaF5KxoyJUQvI6Sd5B1vXeox/7ml.', 'Kombi', 'kombi'),
('pembimbing1', '$2y$10$dlLoSiTBCc5qLI/1sYO3Euc5ZaF5KxoyJUQvI6Sd5B1vXeox/7ml.', 'Dosen Pembimbing 1', 'dosen_pembimbing'),
('pembimbing2', '$2y$10$dlLoSiTBCc5qLI/1sYO3Euc5ZaF5KxoyJUQvI6Sd5B1vXeox/7ml.', 'Dosen Pembimbing 2', 'dosen_pembimbing'),
('penguji1', '$2y$10$dlLoSiTBCc5qLI/1sYO3Euc5ZaF5KxoyJUQvI6Sd5B1vXeox/7ml.', 'Dosen Penguji 1', 'dosen_penguji'),
('penguji2', '$2y$10$dlLoSiTBCc5qLI/1sYO3Euc5ZaF5KxoyJUQvI6Sd5B1vXeox/7ml.', 'Dosen Penguji 2', 'dosen_penguji'),
('mahasiswa1', '$2y$10$dlLoSiTBCc5qLI/1sYO3Euc5ZaF5KxoyJUQvI6Sd5B1vXeox/7ml.', 'Mahasiswa 1', 'mahasiswa'),
('mahasiswa2', '$2y$10$dlLoSiTBCc5qLI/1sYO3Euc5ZaF5KxoyJUQvI6Sd5B1vXeox/7ml.', 'Mahasiswa 2', 'mahasiswa'),
('mahasiswa3', '$2y$10$dlLoSiTBCc5qLI/1sYO3Euc5ZaF5KxoyJUQvI6Sd5B1vXeox/7ml.', 'Mahasiswa 3', 'mahasiswa');

-- Mirror primary roles into user_roles
INSERT INTO user_roles (user_id, role)
SELECT id, role FROM users;

-- Insert students
INSERT INTO students (nim, name, angkatan, semester_masuk, status) VALUES
('NIM001', 'Mahasiswa 1', 2020, 1, 'AKTIF'),
('NIM002', 'Mahasiswa 2', 2021, 1, 'AKTIF'),
('NIM003', 'Mahasiswa 3', 2020, 1, 'CUTI');

-- Link students to users
UPDATE students SET user_id = (SELECT id FROM users WHERE username = 'mahasiswa1') WHERE nim = 'NIM001';
UPDATE students SET user_id = (SELECT id FROM users WHERE username = 'mahasiswa2') WHERE nim = 'NIM002';
UPDATE students SET user_id = (SELECT id FROM users WHERE username = 'mahasiswa3') WHERE nim = 'NIM003';

-- Insert lecturers
INSERT INTO lecturers (user_id, nip, name, prodi, is_external) VALUES
((SELECT id FROM users WHERE username = 'pembimbing1'), 'NIP001', 'Dosen Pembimbing 1', 'Teknik Informatika', 0),
((SELECT id FROM users WHERE username = 'pembimbing2'), 'NIP002', 'Dosen Pembimbing 2', 'Teknik Informatika', 0),
((SELECT id FROM users WHERE username = 'penguji1'), 'NIP003', 'Dosen Penguji 1', 'Teknik Informatika', 0),
((SELECT id FROM users WHERE username = 'penguji2'), 'NIP004', 'Dosen Penguji 2', 'Teknik Informatika', 0);

-- Additional lecturers (without user accounts)
INSERT INTO lecturers (nip, name, prodi, is_external) VALUES
('197107311997022001', 'Dr. Nita Kuswardhani, S.TP., M.Eng, IPM', 'Teknologi Industri Pertanian', 0),
('197008031994031004', 'Prof. Dr. Ida Bagus Suryaningrat, S.TP., MM., IPU, ASEAN.Eng', 'Teknologi Industri Pertanian', 0),
('198503232008011002', 'Miftahul Choiron, S.TP., M.Sc., Ph.D., CIISA', 'Teknologi Industri Pertanian', 0),
('197207301999031001', 'Dr. Yuli Wibowo, S.TP., M.Si., IPM', 'Teknologi Industri Pertanian', 0),
('197505301999031002', 'Dr. Bambang Herry Purnomo, S.TP., M.Si', 'Teknologi Industri Pertanian', 0),
('197902232006042001', 'Dr. Eka Ruriani, S.TP., M.Si', 'Teknologi Industri Pertanian', 0),
('198204222005011002', 'Andrew Setiawan Rusdianto, S.TP., MSi', 'Teknologi Industri Pertanian', 0),
('198303242008012007', 'Winda Amilia, S.TP., M.Sc.', 'Teknologi Industri Pertanian', 0),
('198512012019031007', 'Andi Eko Wiyono, S.TP., M.P', 'Teknologi Industri Pertanian', 0),
('198608172023212057', 'Dr. Nidya Shara Mahardhika, S.TP., M.P', 'Teknologi Industri Pertanian', 0),
('198803122023211022', 'Bertung Suryadharma, S.ST., M.Kom', 'Teknologi Industri Pertanian', 0),
('198910052024061001', 'Leader Firstandika, S.Si., M.T', 'Teknologi Industri Pertanian', 0),
('200209112024062001', 'Lituhayu Sausan Supartiningrum Yudiansyah S.T. M.P.', 'Teknologi Industri Pertanian', 0),
('199712032024061001', 'Muhammad Arga Hita S.T. M.Sc.', 'Teknologi Industri Pertanian', 0),
('199706032024062003', 'Shinta Syafrina Endah Hap Sari S.T. M.P.', 'Teknologi Industri Pertanian', 0),
('199109302025061002', 'Ahib Assadam, S.TP., M.Si', 'Teknologi Industri Pertanian', 0),
('199608082025061005', 'Viko Nurluthfiyadi Ni''maturrakhmat S.T., M.P.', 'Teknologi Industri Pertanian', 0),
('199605092025062008', 'Diana Nurhayati, S.P., M.Si.', 'Teknologi Industri Pertanian', 0),
('199705242025062007', 'Ummu At-Ta''anny, S.T., M.P', 'Teknologi Industri Pertanian', 0),
('200012132025061007', 'Alif Rizki Ulil Albab, S.T., M.T.', 'Teknologi Industri Pertanian', 0),
('199804112025062009', 'Suwita Tri Prihani, S.T.P., M.P.', 'Teknologi Industri Pertanian', 0),
('199607242025062007', 'Shinta Diah Puspaningtyas, S.T., M.T.', 'Teknologi Industri Pertanian', 0);

-- Insert sample titles
INSERT INTO titles (
    student_id,
    title,
    abstract,
    keywords,
    advisor_letter_link,
    examiner_letter_link,
    proposed_pembimbing_1_id,
    proposed_pembimbing_2_id,
    proposed_penguji_1_id,
    proposed_penguji_2_id,
    proposed_penguji_3_id,
    status
) VALUES
(1, 'Pengembangan Sistem Informasi KBS', 'Sistem informasi untuk komisi bimbingan skripsi', 'sistem informasi, kbs, skripsi', '{"pembimbing_1":"https://drive.google.com/sample-pembimbing-1"}', '{"penguji_1":"https://drive.google.com/sample-penguji-1"}', NULL, NULL, NULL, NULL, NULL, 'DITERIMA'),
(2, 'Analisis Algoritma Optimasi', 'Analisis berbagai algoritma optimasi untuk skripsi', 'algoritma, optimasi, skripsi', '{"pembimbing_1":"https://drive.google.com/sample-pembimbing-2"}', '{"penguji_1":"https://drive.google.com/sample-penguji-2"}', NULL, NULL, NULL, NULL, NULL, 'MENUNGGU'),
(3, 'Implementasi Machine Learning', 'Implementasi machine learning dalam sistem prediksi', 'machine learning, prediksi, sistem', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'PERLU_REVISI');

-- Insert sample assignments
INSERT INTO assignments (student_id, lecturer_id, role, reason, assigned_by, effective_date) VALUES
(1, (SELECT id FROM users WHERE username = 'pembimbing1'), 'pembimbing_1', 'Penunjukan pembimbing 1', (SELECT id FROM users WHERE username = 'kombi'), '2023-01-15'),
(1, (SELECT id FROM users WHERE username = 'pembimbing2'), 'pembimbing_2', 'Penunjukan pembimbing 2', (SELECT id FROM users WHERE username = 'kombi'), '2023-01-15'),
(1, (SELECT id FROM users WHERE username = 'penguji1'), 'penguji_1', 'Penunjukan ketua penguji', (SELECT id FROM users WHERE username = 'kombi'), '2023-01-20'),
(1, (SELECT id FROM users WHERE username = 'penguji2'), 'penguji_2', 'Penunjukan penguji anggota', (SELECT id FROM users WHERE username = 'kombi'), '2023-01-20'),
(2, (SELECT id FROM users WHERE username = 'pembimbing1'), 'pembimbing_1', 'Penunjukan pembimbing 1', (SELECT id FROM users WHERE username = 'kombi'), '2023-01-15'),
(2, (SELECT id FROM users WHERE username = 'penguji1'), 'penguji_1', 'Penunjukan ketua penguji', (SELECT id FROM users WHERE username = 'kombi'), '2023-01-20'),
(2, (SELECT id FROM users WHERE username = 'penguji2'), 'penguji_2', 'Penunjukan penguji anggota', (SELECT id FROM users WHERE username = 'kombi'), '2023-01-20');

-- Reset existing components
DELETE FROM evaluation_components;

-- Seminar Proposal (Sempro) components
INSERT INTO evaluation_components (name, stage, weight, description, sort_order) VALUES
('Kerapian dan kesiapan pemrasaran', 'sempro', 0.05, 'Performansi', 1),
('Kelengkapan bahan seminar', 'sempro', 0.05, 'Performansi', 2),
('Kemampuan penggunaan bahasa pengantar', 'sempro', 0.05, 'Performansi', 3),
('Keterampilan penggunaan alat bantu seminar', 'sempro', 0.05, 'Performansi', 4),
('Kecakapan memberikan argumentasi dan menjawab pertanyaan', 'sempro', 0.25, 'Performansi', 5),
('Sistematika penulisan/penyajian materi seminar', 'sempro', 0.15, 'Isi Materi Seminar', 6),
('Kejelasan perumusan, tujuan dan metode penelitian', 'sempro', 0.20, 'Isi Materi Seminar', 7),
('Kecukupan dan kesesuaian penggunaan sumber referensi', 'sempro', 0.20, 'Isi Materi Seminar', 8);

-- Seminar Hasil (Semhas) components
INSERT INTO evaluation_components (name, stage, weight, description, sort_order) VALUES
('Kerapian dan kesiapan pemrasaran', 'semhas', 0.05, 'Performansi', 1),
('Kelengkapan bahan seminar', 'semhas', 0.05, 'Performansi', 2),
('Kemampuan penggunaan bahasa pengantar', 'semhas', 0.05, 'Performansi', 3),
('Keterampilan penggunaan alat bantu seminar', 'semhas', 0.05, 'Performansi', 4),
('Kecakapan memberikan argumentasi dan menjawab pertanyaan', 'semhas', 0.20, 'Performansi', 5),
('Sistematika penulisan/penyajian materi seminar', 'semhas', 0.10, 'Isi Materi Seminar', 6),
('Kejelasan perumusan, tujuan dan metode penelitian', 'semhas', 0.10, 'Isi Materi Seminar', 7),
('Kecukupan dan kesesuaian penggunaan sumber referensi', 'semhas', 0.15, 'Isi Materi Seminar', 8),
('Kejelasan dan ketajaman analisis data dan deskripsi hasil', 'semhas', 0.20, 'Isi Materi Seminar', 9),
('Kesesuaian simpulan dan tujuan', 'semhas', 0.05, 'Isi Materi Seminar', 10);

-- Pra-Ujian components (Pembimbing)
INSERT INTO evaluation_components (name, stage, weight, description, sort_order) VALUES
('Seminar Proposal Penelitian', 'pra-ujian', 0.05, 'Rekap nilai seminar proposal', 1),
('Konsultasi', 'pra-ujian', 0.075, 'Pelaksanaan Penelitian', 2),
('Percobaan / Penelitian', 'pra-ujian', 0.30, 'Pelaksanaan Penelitian', 3),
('Penyusunan KTI', 'pra-ujian', 0.15, 'Pelaksanaan Penelitian', 4),
('Kejujuran / Kesungguhan', 'pra-ujian', 0.075, 'Pelaksanaan Penelitian', 5),
('Seminar Hasil', 'pra-ujian', 0.05, 'Rekap nilai seminar hasil', 6);

-- Ujian Skripsi components (Penguji)
INSERT INTO evaluation_components (name, stage, weight, description, sort_order) VALUES
('Seminar Proposal Penelitian', 'ujian', 0.075, 'Rekap nilai seminar proposal', 1),
('Seminar Hasil', 'ujian', 0.075, 'Rekap nilai seminar hasil', 2),
('Ketajaman latar belakang, perumusan masalah, tujuan penelitian, sumber referensi dan ketepatan metode yang digunakan', 'ujian', 0.012, 'Penulisan Naskah', 3),
('Teknik penulisan karya ilmiah (tata bahasa, EYD, kesesuaian dengan PPKI Universitas Jember)', 'ujian', 0.012, 'Penulisan Naskah', 4),
('Kecukupan dan kesesuaian isi (keterkaitan masalah, tujuan, metode, pembahasan, dan kesimpulan)', 'ujian', 0.012, 'Penulisan Naskah', 5),
('Etika akademik (originalitas, bebas plagiarism, terbuka terhadap masukan, saran, dan kritik)', 'ujian', 0.018, 'Penulisan Naskah', 6),
('Pemahaman konsep penelitian', 'ujian', 0.054, 'Penguasaan Materi', 7),
('Pemahaman metodologi yang digunakan', 'ujian', 0.042, 'Penguasaan Materi', 8);

-- Insert sample events
INSERT INTO events (student_id, type, scheduled_date, scheduled_time, room) VALUES
(1, 'SEMPRO', '2023-06-15', '09:00:00', 'Room A'),
(1, 'SEMHAS', '2023-09-20', '10:00:00', 'Room B'),
(2, 'SEMPRO', '2023-06-16', '13:00:00', 'Room C');

-- Insert sample scores
INSERT INTO scores (student_id, event_type, scorer_id, score_type, scoring_method, final_score, source) VALUES
(1, 'SEMPRO', 1, 'PEMBIMBING', 'AKHIR', 85.5, 'DOSEN'),
(1, 'SEMPRO', 3, 'PENGUJI', 'AKHIR', 87.0, 'DOSEN');
