-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: kbs_db
-- Generation Time: Nov 04, 2025 at 02:48 AM
-- Server version: 11.4.8-MariaDB-ubu2404
-- PHP Version: 8.3.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `kbs_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `assignments`
--

CREATE TABLE `assignments` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `lecturer_id` int(11) NOT NULL,
  `role` enum('pembimbing_1','pembimbing_2','penguji_1','penguji_2','penguji_3') NOT NULL,
  `reason` text DEFAULT NULL,
  `assigned_by` int(11) NOT NULL,
  `effective_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `target_type` varchar(50) NOT NULL,
  `target_id` int(11) NOT NULL,
  `description` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `target_type`, `target_id`, `description`, `created_at`) VALUES
(1, 2, 'update_assignment', 'students', 642, 'Penetapan pembimbing dan penguji diperbarui', '2025-10-29 12:36:30'),
(2, 2, 'cancel_assignment', 'assignments', 642, 'Penetapan Pembimbing 1 dibatalkan untuk mahasiswa ID 642', '2025-10-29 13:19:54'),
(3, 2, 'cancel_assignment', 'assignments', 642, 'Penetapan Pembimbing 2 dibatalkan untuk mahasiswa ID 642', '2025-10-29 13:19:54'),
(4, 2, 'cancel_assignment', 'assignments', 642, 'Penetapan Penguji Ketua dibatalkan untuk mahasiswa ID 642', '2025-10-29 13:19:54'),
(5, 2, 'cancel_assignment', 'assignments', 642, 'Penetapan Penguji Anggota 1 dibatalkan untuk mahasiswa ID 642', '2025-10-29 13:19:54'),
(6, 1035, 'submit_title', 'titles', 4, 'Pengajuan judul baru', '2025-10-29 14:05:31'),
(7, 2, 'verify_title', 'titles', 4, 'Status: DITOLAK; Catatan: ', '2025-10-29 14:12:03'),
(8, 2, 'delete_title', 'titles', 4, 'Pengajuan judul dihapus', '2025-10-29 14:13:51'),
(9, 1035, 'submit_title', 'titles', 5, 'Pengajuan judul baru', '2025-10-29 14:14:28'),
(10, 2, 'verify_title', 'titles', 5, 'Status: DITERIMA; Catatan: ', '2025-10-29 14:15:07'),
(11, 2, 'update_assignment', 'students', 1201, 'Penetapan pembimbing dan penguji diperbarui', '2025-10-29 14:54:33'),
(12, 2, 'update_assignment', 'students', 1201, 'Penetapan pembimbing dan penguji diperbarui', '2025-10-29 14:55:10'),
(13, 2, 'create_event', 'events', 4, 'Penjadwalan Seminar Proposal untuk mahasiswa ID 1201 pada 2025-10-29 07:00 di R10. Status: MENUNGGU.', '2025-10-29 15:07:38'),
(14, 2, 'delete_event', 'events', 4, 'Penjadwalan dihapus oleh Kombi.', '2025-10-29 22:12:38'),
(15, 2, 'create_event', 'events', 5, 'Penjadwalan Seminar Proposal untuk mahasiswa ID 1201 pada 2025-10-30 05:00 di R10. Status: MENUNGGU.', '2025-10-29 22:13:35'),
(16, 2, 'update_event', 'events', 5, 'Penjadwalan Seminar Proposal diperbarui untuk mahasiswa ID 1201 pada 2025-10-29 05:00 di R10. Status: MENUNGGU.', '2025-10-29 22:21:24'),
(17, 2, 'update_event', 'events', 5, 'Penjadwalan Seminar Proposal diperbarui untuk mahasiswa ID 1201 pada 2025-10-29 05:00 di R10. Status: MENUNGGU.', '2025-10-30 07:49:13'),
(18, 14, 'save_evaluation', 'evaluations', 2, 'Stage=sempro; Mode=components; Score=8200', '2025-10-31 08:15:34'),
(19, 10, 'save_evaluation', 'evaluations', 3, 'Stage=sempro; Mode=components; Score=8500', '2025-10-31 08:18:23'),
(20, 2, 'create_event', 'events', 6, 'Penjadwalan Seminar Hasil untuk mahasiswa ID 1201 pada 2025-10-31 09:00 di R10. Status: MENUNGGU.', '2025-10-31 08:19:56'),
(21, 2, 'update_event', 'events', 6, 'Penjadwalan Seminar Hasil diperbarui untuk mahasiswa ID 1201 pada 2025-10-31 09:00 di R10. Status: MENUNGGU.', '2025-10-31 10:14:37'),
(22, 14, 'save_evaluation', 'evaluations', 5, 'Stage=semhas; Mode=components; Score=8000', '2025-10-31 10:16:09'),
(23, 10, 'save_evaluation', 'evaluations', 6, 'Stage=semhas; Mode=components; Score=8300', '2025-10-31 10:17:09'),
(24, 14, 'save_evaluation', 'evaluations', 7, 'Stage=pra-ujian; Mode=components; Score=88.295774647887', '2025-10-31 13:12:15'),
(25, 2, 'bypass_evaluation', 'evaluations', 8, 'Stage=ujian; Score=81; Catatan=-', '2025-11-02 04:38:04'),
(26, 2, 'bypass_evaluation', 'evaluations', 8, 'Stage=ujian; Score=79; Catatan=-', '2025-11-02 04:38:58'),
(27, 2, 'bypass_evaluation', 'evaluations', 8, 'Stage=ujian; Score=79; Outcome=LULUS; Catatan=-', '2025-11-02 06:18:20');

-- --------------------------------------------------------

--
-- Table structure for table `cuti_records`
--

CREATE TABLE `cuti_records` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `semester` int(11) NOT NULL,
  `status` enum('MENUNGGU','DISETUJUI','DITOLAK') DEFAULT 'MENUNGGU',
  `document_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `evaluations`
--

CREATE TABLE `evaluations` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `evaluator_id` int(11) NOT NULL,
  `evaluator_role` enum('superadmin','kombi','dosen_pembimbing','dosen_penguji','penguji_eksternal') NOT NULL,
  `stage` varchar(50) NOT NULL,
  `mode` enum('components','final','bypass') NOT NULL DEFAULT 'components',
  `final_score` decimal(6,2) DEFAULT NULL,
  `total_score` decimal(6,2) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `source` enum('manual','bypass') NOT NULL DEFAULT 'manual',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `evaluation_components`
--

CREATE TABLE `evaluation_components` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `stage` varchar(50) NOT NULL,
  `weight` decimal(5,2) NOT NULL DEFAULT 0.00,
  `description` text DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `evaluation_components`
--

INSERT INTO `evaluation_components` (`id`, `name`, `stage`, `weight`, `description`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'Kerapian dan kesiapan pemrasaran', 'sempro', 0.05, 'Performansi', 1, '2025-10-29 03:11:00', '2025-10-29 03:11:00'),
(2, 'Kelengkapan bahan seminar', 'sempro', 0.05, 'Performansi', 2, '2025-10-29 03:11:00', '2025-10-29 03:11:00'),
(3, 'Kemampuan penggunaan bahasa pengantar', 'sempro', 0.05, 'Performansi', 3, '2025-10-29 03:11:00', '2025-10-29 03:11:00'),
(4, 'Keterampilan penggunaan alat bantu seminar', 'sempro', 0.05, 'Performansi', 4, '2025-10-29 03:11:00', '2025-10-29 03:11:00'),
(5, 'Kecakapan memberikan argumentasi dan menjawab pertanyaan', 'sempro', 0.25, 'Performansi', 5, '2025-10-29 03:11:00', '2025-10-29 03:11:00'),
(6, 'Sistematika penulisan/penyajian materi seminar', 'sempro', 0.15, 'Isi Materi Seminar', 6, '2025-10-29 03:11:00', '2025-10-29 03:11:00'),
(7, 'Kejelasan perumusan, tujuan dan metode penelitian', 'sempro', 0.20, 'Isi Materi Seminar', 7, '2025-10-29 03:11:00', '2025-10-29 03:11:00'),
(8, 'Kecukupan dan kesesuaian penggunaan sumber referensi', 'sempro', 0.20, 'Isi Materi Seminar', 8, '2025-10-29 03:11:00', '2025-10-29 03:11:00'),
(9, 'Kerapian dan kesiapan pemrasaran', 'semhas', 0.05, 'Performansi', 1, '2025-10-29 03:11:00', '2025-10-29 03:11:00'),
(10, 'Kelengkapan bahan seminar', 'semhas', 0.05, 'Performansi', 2, '2025-10-29 03:11:00', '2025-10-29 03:11:00'),
(11, 'Kemampuan penggunaan bahasa pengantar', 'semhas', 0.05, 'Performansi', 3, '2025-10-29 03:11:00', '2025-10-29 03:11:00'),
(12, 'Keterampilan penggunaan alat bantu seminar', 'semhas', 0.05, 'Performansi', 4, '2025-10-29 03:11:00', '2025-10-29 03:11:00'),
(13, 'Kecakapan memberikan argumentasi dan menjawab pertanyaan', 'semhas', 0.20, 'Performansi', 5, '2025-10-29 03:11:00', '2025-10-29 03:11:00'),
(14, 'Sistematika penulisan/penyajian materi seminar', 'semhas', 0.10, 'Isi Materi Seminar', 6, '2025-10-29 03:11:00', '2025-10-29 03:11:00'),
(15, 'Kejelasan perumusan, tujuan dan metode penelitian', 'semhas', 0.10, 'Isi Materi Seminar', 7, '2025-10-29 03:11:00', '2025-10-29 03:11:00'),
(16, 'Kecukupan dan kesesuaian penggunaan sumber referensi', 'semhas', 0.15, 'Isi Materi Seminar', 8, '2025-10-29 03:11:00', '2025-10-29 03:11:00'),
(17, 'Kejelasan dan ketajaman analisis data dan deskripsi hasil', 'semhas', 0.20, 'Isi Materi Seminar', 9, '2025-10-29 03:11:00', '2025-10-29 03:11:00'),
(18, 'Kesesuaian simpulan dan tujuan', 'semhas', 0.05, 'Isi Materi Seminar', 10, '2025-10-29 03:11:00', '2025-10-29 03:11:00'),
(19, 'Seminar Proposal Penelitian', 'pra-ujian', 0.05, 'Rekap nilai seminar proposal', 1, '2025-10-29 03:11:00', '2025-10-29 03:11:00'),
(20, 'Konsultasi', 'pra-ujian', 0.08, 'Pelaksanaan Penelitian', 2, '2025-10-29 03:11:00', '2025-10-29 03:11:00'),
(21, 'Percobaan / Penelitian', 'pra-ujian', 0.30, 'Pelaksanaan Penelitian', 3, '2025-10-29 03:11:00', '2025-10-29 03:11:00'),
(22, 'Penyusunan KTI', 'pra-ujian', 0.15, 'Pelaksanaan Penelitian', 4, '2025-10-29 03:11:00', '2025-10-29 03:11:00'),
(23, 'Kejujuran / Kesungguhan', 'pra-ujian', 0.08, 'Pelaksanaan Penelitian', 5, '2025-10-29 03:11:00', '2025-10-29 03:11:00'),
(24, 'Seminar Hasil', 'pra-ujian', 0.05, 'Rekap nilai seminar hasil', 6, '2025-10-29 03:11:00', '2025-10-29 03:11:00'),
(25, 'Seminar Proposal Penelitian', 'ujian', 0.08, 'Rekap nilai seminar proposal', 1, '2025-10-29 03:11:00', '2025-10-29 03:11:00'),
(26, 'Seminar Hasil', 'ujian', 0.08, 'Rekap nilai seminar hasil', 2, '2025-10-29 03:11:00', '2025-10-29 03:11:00'),
(27, 'Ketajaman latar belakang, perumusan masalah, tujuan penelitian, sumber referensi dan ketepatan metode yang digunakan', 'ujian', 0.01, 'Penulisan Naskah', 3, '2025-10-29 03:11:00', '2025-10-29 03:11:00'),
(28, 'Teknik penulisan karya ilmiah (tata bahasa, EYD, kesesuaian dengan PPKI Universitas Jember)', 'ujian', 0.01, 'Penulisan Naskah', 4, '2025-10-29 03:11:00', '2025-10-29 03:11:00'),
(29, 'Kecukupan dan kesesuaian isi (keterkaitan masalah, tujuan, metode, pembahasan, dan kesimpulan)', 'ujian', 0.01, 'Penulisan Naskah', 5, '2025-10-29 03:11:00', '2025-10-29 03:11:00'),
(30, 'Etika akademik (originalitas, bebas plagiarism, terbuka terhadap masukan, saran, dan kritik)', 'ujian', 0.02, 'Penulisan Naskah', 6, '2025-10-29 03:11:00', '2025-10-29 03:11:00'),
(31, 'Pemahaman konsep penelitian', 'ujian', 0.05, 'Penguasaan Materi', 7, '2025-10-29 03:11:00', '2025-10-29 03:11:00'),
(32, 'Pemahaman metodologi yang digunakan', 'ujian', 0.04, 'Penguasaan Materi', 8, '2025-10-29 03:11:00', '2025-10-29 03:11:00');

-- --------------------------------------------------------

--
-- Table structure for table `evaluation_scores`
--

CREATE TABLE `evaluation_scores` (
  `id` int(11) NOT NULL,
  `evaluation_id` int(11) NOT NULL,
  `component_id` int(11) NOT NULL,
  `score` decimal(6,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `type` enum('SEMPRO','SEMHAS','PRA_UJIAN','UJIAN_SKRIPSI') NOT NULL,
  `scheduled_date` date NOT NULL,
  `scheduled_time` time NOT NULL,
  `room` varchar(50) NOT NULL,
  `status` enum('MENUNGGU','SELESAI','BATAL') DEFAULT 'MENUNGGU',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `external_tokens`
--

CREATE TABLE `external_tokens` (
  `id` int(11) NOT NULL,
  `nip` varchar(20) NOT NULL,
  `token` varchar(50) NOT NULL,
  `event_id` int(11) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `nda_agreed` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lecturers`
--

CREATE TABLE `lecturers` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `nip` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `prodi` varchar(100) DEFAULT NULL,
  `is_external` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `lecturers`
--

INSERT INTO `lecturers` (`id`, `user_id`, `nip`, `name`, `prodi`, `is_external`, `created_at`, `updated_at`) VALUES
(5, 19, '197107311997022001', 'Dr. Nita Kuswardhani, S.TP., M.Eng, IPM', 'Teknologi Industri Pertanian', 0, '2025-10-29 03:11:00', '2025-10-29 03:32:15'),
(6, 25, '197008031994031004', 'Prof. Dr. Ida Bagus Suryaningrat, S.TP., MM., IPU, ASEAN.Eng', 'Teknologi Industri Pertanian', 0, '2025-10-29 03:11:00', '2025-10-29 03:32:15'),
(7, 23, '198503232008011002', 'Miftahul Choiron, S.TP., M.Sc., Ph.D., CIISA', 'Teknologi Industri Pertanian', 0, '2025-10-29 03:11:00', '2025-10-29 03:32:15'),
(8, 20, '197207301999031001', 'Dr. Yuli Wibowo, S.TP., M.Si., IPM', 'Teknologi Industri Pertanian', 0, '2025-10-29 03:11:00', '2025-10-29 03:32:15'),
(9, 16, '197505301999031002', 'Dr. Bambang Herry Purnomo, S.TP., M.Si', 'Teknologi Industri Pertanian', 0, '2025-10-29 03:11:00', '2025-10-29 03:32:15'),
(10, 17, '197902232006042001', 'Dr. Eka Ruriani, S.TP., M.Si', 'Teknologi Industri Pertanian', 0, '2025-10-29 03:11:00', '2025-10-29 03:32:15'),
(11, 13, '198204222005011002', 'Andrew Setiawan Rusdianto, S.TP., MSi', 'Teknologi Industri Pertanian', 0, '2025-10-29 03:11:00', '2025-10-29 03:32:15'),
(12, 31, '198303242008012007', 'Winda Amilia, S.TP., M.Sc.', 'Teknologi Industri Pertanian', 0, '2025-10-29 03:11:00', '2025-10-29 03:32:16'),
(13, 12, '198512012019031007', 'Andi Eko Wiyono, S.TP., M.P', 'Teknologi Industri Pertanian', 0, '2025-10-29 03:11:00', '2025-10-29 03:32:15'),
(14, 18, '198608172023212057', 'Dr. Nidya Shara Mahardhika, S.TP., M.P', 'Teknologi Industri Pertanian', 0, '2025-10-29 03:11:00', '2025-10-29 03:32:15'),
(15, 14, '198803122023211022', 'Bertung Suryadharma, S.ST., M.Kom', 'Teknologi Industri Pertanian', 0, '2025-10-29 03:11:00', '2025-10-29 03:32:15'),
(16, 21, '198910052024061001', 'Leader Firstandika, S.Si., M.T', 'Teknologi Industri Pertanian', 0, '2025-10-29 03:11:00', '2025-10-29 03:32:15'),
(17, 22, '200209112024062001', 'Lituhayu Sausan Supartiningrum Yudiansyah S.T. M.P.', 'Teknologi Industri Pertanian', 0, '2025-10-29 03:11:00', '2025-10-29 03:32:15'),
(18, 24, '199712032024061001', 'Muhammad Arga Hita S.T. M.Sc.', 'Teknologi Industri Pertanian', 0, '2025-10-29 03:11:00', '2025-10-29 03:32:15'),
(19, 27, '199706032024062003', 'Shinta Syafrina Endah Hap Sari S.T. M.P.', 'Teknologi Industri Pertanian', 0, '2025-10-29 03:11:00', '2025-10-29 03:32:15'),
(20, 10, '199109302025061002', 'Ahib Assadam, S.TP., M.Si', 'Teknologi Industri Pertanian', 0, '2025-10-29 03:11:00', '2025-10-29 03:32:15'),
(21, 30, '199608082025061005', 'Viko Nurluthfiyadi Ni\'maturrakhmat S.T., M.P.', 'Teknologi Industri Pertanian', 0, '2025-10-29 03:11:00', '2025-10-29 03:32:16'),
(22, 15, '199605092025062008', 'Diana Nurhayati, S.P., M.Si.', 'Teknologi Industri Pertanian', 0, '2025-10-29 03:11:00', '2025-10-29 03:32:15'),
(23, 29, '199705242025062007', 'Ummu At-Ta\'anny, S.T., M.P', 'Teknologi Industri Pertanian', 0, '2025-10-29 03:11:00', '2025-10-29 03:32:16'),
(24, 11, '200012132025061007', 'Alif Rizki Ulil Albab, S.T., M.T.', 'Teknologi Industri Pertanian', 0, '2025-10-29 03:11:00', '2025-10-29 03:32:15'),
(25, 28, '199804112025062009', 'Suwita Tri Prihani, S.T.P., M.P.', 'Teknologi Industri Pertanian', 0, '2025-10-29 03:11:00', '2025-10-29 03:32:15'),
(26, 26, '199607242025062007', 'Shinta Diah Puspaningtyas, S.T., M.T.', 'Teknologi Industri Pertanian', 0, '2025-10-29 03:11:00', '2025-10-29 03:32:15'),
(27, NULL, '123456', 'Dosen Luar', 'Teknik', 1, '2025-10-29 13:01:54', '2025-10-29 13:01:54');

-- --------------------------------------------------------

--
-- Table structure for table `proposal_evaluations`
--

CREATE TABLE `proposal_evaluations` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `evaluator_id` int(11) NOT NULL,
  `evaluator_role` enum('superadmin','kombi','dosen_pembimbing','dosen_penguji') NOT NULL,
  `performance_score_1` decimal(6,2) DEFAULT NULL,
  `performance_score_2` decimal(6,2) DEFAULT NULL,
  `performance_score_3` decimal(6,2) DEFAULT NULL,
  `performance_score_4` decimal(6,2) DEFAULT NULL,
  `performance_score_5` decimal(6,2) DEFAULT NULL,
  `content_score_1` decimal(6,2) DEFAULT NULL,
  `content_score_2` decimal(6,2) DEFAULT NULL,
  `content_score_3` decimal(6,2) DEFAULT NULL,
  `performance_total` decimal(6,2) DEFAULT NULL,
  `content_total` decimal(6,2) DEFAULT NULL,
  `final_score` decimal(6,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `scores`
--

CREATE TABLE `scores` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `event_type` enum('SEMPRO','SEMHAS','PRA_UJIAN','UJIAN_SKRIPSI') NOT NULL,
  `scorer_id` int(11) NOT NULL,
  `score_type` enum('PEMBIMBING','PENGUJI') NOT NULL,
  `scoring_method` enum('BOBOT','AKHIR') NOT NULL,
  `component_1_score` decimal(5,2) DEFAULT NULL,
  `component_1_weight` decimal(5,2) DEFAULT NULL,
  `component_2_score` decimal(5,2) DEFAULT NULL,
  `component_2_weight` decimal(5,2) DEFAULT NULL,
  `component_3_score` decimal(5,2) DEFAULT NULL,
  `component_3_weight` decimal(5,2) DEFAULT NULL,
  `final_score` decimal(5,2) DEFAULT NULL,
  `source` enum('DOSEN','BYPASS') DEFAULT 'DOSEN',
  `bypass_reason` text DEFAULT NULL,
  `bypassed_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `key_name` varchar(100) NOT NULL,
  `value` text NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `key_name`, `value`, `description`, `created_at`, `updated_at`) VALUES
(1, 'pembimbing_ratio', '60:40', 'Rasio nilai pembimbing I:II', '2025-10-29 03:10:38', '2025-10-29 03:10:38'),
(2, 'penguji_method', 'rata-rata', 'Metode agregasi nilai penguji', '2025-10-29 03:10:38', '2025-10-29 03:10:38'),
(3, 'cuti_threshold', 'before_krs', 'Ambang batas cuti (sebelum akhir KRS)', '2025-10-29 03:10:38', '2025-10-29 03:10:38'),
(4, 'max_pembimbing', '8', 'Maksimal beban pembimbing per dosen', '2025-10-29 03:10:38', '2025-10-29 03:10:38'),
(5, 'max_penguji', '10', 'Maksimal beban penguji per dosen', '2025-10-29 03:10:38', '2025-10-29 03:10:38'),
(6, 'active_angkatan', '2022,2021,2020,2019', NULL, '2025-10-29 08:24:27', '2025-10-30 07:21:50');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `nim` varchar(15) NOT NULL,
  `name` varchar(100) NOT NULL,
  `angkatan` int(11) NOT NULL,
  `semester_masuk` int(11) NOT NULL,
  `semester_lulus` int(11) DEFAULT NULL,
  `status` enum('AKTIF','CUTI','NON-AKTIF','LULUS') DEFAULT 'AKTIF',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `nim`, `name`, `angkatan`, `semester_masuk`, `semester_lulus`, `status`, `created_at`, `updated_at`, `user_id`) VALUES
(333, '191710301001', 'NANDA MEGA WULANDARI', 2019, 19201, NULL, 'LULUS', '2025-10-29 04:11:07', '2025-10-29 04:11:07', 34),
(334, '191710301002', 'TRI RIWAYATI SUDARMONO', 2019, 19201, NULL, 'LULUS', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 35),
(335, '191710301003', 'ANISATUL MUKARAMAH', 2019, 19201, NULL, 'LULUS', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 36),
(336, '191710301004', 'JULIESA ARSYI SAFILLAH', 2019, 19201, NULL, 'LULUS', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 37),
(337, '191710301005', 'HERNI KUSUMAWATI', 2019, 19201, NULL, 'AKTIF', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 38),
(338, '191710301006', 'ELLA FITRIA CAHYANINGTYAS', 2019, 19201, NULL, 'LULUS', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 39),
(339, '191710301007', 'ARIS SYAFA\'ATIN', 2019, 19201, NULL, 'LULUS', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 40),
(340, '191710301008', 'Anisa Putri Nastiti', 2019, 19201, NULL, 'NON-AKTIF', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 41),
(341, '191710301009', 'CINTANIA QORRY DEA AFIFA', 2019, 19201, NULL, 'AKTIF', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 42),
(342, '191710301010', 'LAILA ADHANI PUTRI MALIK', 2019, 19201, NULL, 'LULUS', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 43),
(343, '191710301011', 'SALMAN AL FARISI', 2019, 19201, NULL, 'LULUS', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 44),
(344, '191710301012', 'LIDIYA NATASA', 2019, 19201, NULL, 'AKTIF', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 45),
(345, '191710301013', 'WAHYUNI LISI SEKLIANA', 2019, 19201, NULL, 'LULUS', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 46),
(346, '191710301014', 'Ebia Paray Salman Baretta', 2019, 19201, NULL, 'NON-AKTIF', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 47),
(347, '191710301015', 'BIMA EKA SAPUTRA', 2019, 19201, NULL, 'LULUS', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 48),
(348, '191710301016', 'TRI ALIF LENTERA CYDA', 2019, 19201, NULL, 'LULUS', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 49),
(349, '191710301017', 'QUSNUL KHOMAROH', 2019, 19201, NULL, 'LULUS', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 50),
(350, '191710301018', 'SATRIYA DWI SOEKARNO', 2019, 19201, NULL, 'LULUS', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 51),
(351, '191710301019', 'MARHAMAH HILMIAH', 2019, 19201, NULL, 'LULUS', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 52),
(352, '191710301020', 'SITI SRI PUSPITASARI', 2019, 19201, NULL, 'LULUS', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 53),
(353, '191710301021', 'SALSABILA MAULIDINA', 2019, 19201, NULL, 'AKTIF', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 54),
(354, '191710301022', 'DINA HARISAH', 2019, 19201, NULL, 'LULUS', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 55),
(355, '191710301023', 'ISMAIL YASIN', 2019, 19201, NULL, 'AKTIF', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 56),
(356, '191710301024', 'ERISTHA YUNIANDA TRIANTIKO', 2019, 19201, NULL, 'LULUS', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 57),
(357, '191710301025', 'ADITYA AKBAR PRAMUDIA', 2019, 19201, NULL, 'LULUS', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 58),
(358, '191710301026', 'YUDHISTYA RIFKI RAKHA\' AMRULLAH', 2019, 19201, NULL, 'LULUS', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 59),
(359, '191710301027', 'AFIF HAMIDI M.', 2019, 19201, NULL, 'AKTIF', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 60),
(360, '191710301028', 'EL DAFFA RAMADIANSYAH', 2019, 19201, NULL, 'LULUS', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 61),
(361, '191710301029', 'M. Ravi Ainul Yaqin', 2019, 19201, NULL, 'NON-AKTIF', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 62),
(362, '191710301030', 'Rafli Daffa Falih Adilah', 2019, 19201, NULL, 'LULUS', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 63),
(363, '191710301031', 'MUHAMMAD ADAM SYAH', 2019, 19201, NULL, 'AKTIF', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 64),
(364, '191710301032', 'EKA ANDINA ZULVITA', 2019, 19201, NULL, 'LULUS', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 65),
(365, '191710301033', 'FAIREZA MAWADDAH', 2019, 19201, NULL, 'LULUS', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 66),
(366, '191710301034', 'ANIS SHOFIA MAULIDIYAH', 2019, 19201, NULL, 'LULUS', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 67),
(367, '191710301035', 'I GEDE SURYA DWIPANGGA', 2019, 19201, NULL, 'LULUS', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 68),
(368, '191710301036', 'MOH. IQBAL KAUTSARALIM SETIAJI', 2019, 19201, NULL, 'LULUS', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 69),
(369, '191710301037', 'SHEILA FANESHA PRADITYA', 2019, 19201, NULL, 'AKTIF', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 70),
(370, '191710301038', 'FITRI RAHMA SARI', 2019, 19201, NULL, 'LULUS', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 71),
(371, '191710301039', 'FISABILA APHYCENIA MARINA', 2019, 19201, NULL, 'LULUS', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 72),
(372, '191710301040', 'DANIA MAZIDATUL HANA', 2019, 19201, NULL, 'LULUS', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 73),
(373, '191710301041', 'KHUSNUD DIYANAH', 2019, 19201, NULL, 'LULUS', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 74),
(374, '191710301042', 'THABED THOLIB BALADRAF', 2019, 19201, NULL, 'LULUS', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 75),
(375, '191710301043', 'RAHMAN SANJAY OVA', 2019, 19201, NULL, 'LULUS', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 76),
(376, '191710301044', 'WINDY NUR ANDRIANI', 2019, 19201, NULL, 'LULUS', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 77),
(377, '191710301045', 'SINDY ROSA DARMANINGRUM', 2019, 19201, NULL, 'LULUS', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 78),
(378, '191710301046', 'Pramudya Wardhani', 2019, 19201, NULL, 'LULUS', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 79),
(379, '191710301047', 'ASTIFA SHIELLY NADHILA PUTRI', 2019, 19201, NULL, 'AKTIF', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 80),
(380, '191710301048', 'IDZHAR SEBASTIAN SALIHANAFI', 2019, 19201, NULL, 'AKTIF', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 81),
(381, '191710301049', 'ILHAM AULIA RACHMAN', 2019, 19201, NULL, 'LULUS', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 82),
(382, '191710301050', 'FABBY NIDUFIAS DARAJA', 2019, 19201, NULL, 'LULUS', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 83),
(383, '191710301051', 'TRIANA OKTAVIANI NURHARDININGSIH', 2019, 19201, NULL, 'LULUS', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 84),
(384, '191710301052', 'ULFIA NURUL LATIFAH', 2019, 19201, NULL, 'LULUS', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 85),
(385, '191710301053', 'NAJA UMI ROSIDAH', 2019, 19201, NULL, 'LULUS', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 86),
(386, '191710301054', 'INGGRI OKTAVIA WULANDARI', 2019, 19201, NULL, 'LULUS', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 87),
(387, '191710301055', 'HANIK WIDIANTI', 2019, 19201, NULL, 'LULUS', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 88),
(388, '191710301056', 'Fatih Al Hakim', 2019, 19201, NULL, 'NON-AKTIF', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 89),
(389, '191710301057', 'MUHAMMAD ALIF FIANDRA', 2019, 19201, NULL, 'LULUS', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 90),
(390, '191710301058', 'USAMAH ARYA ARROYAH', 2019, 19201, NULL, 'LULUS', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 91),
(391, '191710301059', 'FIRMAN AGHISTA RAHMAN', 2019, 19201, NULL, 'AKTIF', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 92),
(392, '191710301060', 'PUTRI NIKITA APRILIA ARUMINGTYAS', 2019, 19201, NULL, 'LULUS', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 93),
(393, '191710301061', 'AHMAD ASHIDHIQIE PRAMANA', 2019, 19201, NULL, 'NON-AKTIF', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 94),
(394, '191710301062', 'ROHMATUL HIDAYAH', 2019, 19201, NULL, 'LULUS', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 95),
(395, '191710301063', 'Noris Baihaqi', 2019, 19201, NULL, 'CUTI', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 96),
(396, '191710301064', 'DWI INDAH LESTARI', 2019, 19201, NULL, 'LULUS', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 97),
(397, '191710301065', 'KIRANA KHALDA FAHIRA', 2019, 19201, NULL, 'LULUS', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 98),
(398, '191710301066', 'SINDI AYU WARDANI', 2019, 19201, NULL, 'LULUS', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 99),
(399, '191710301067', 'DEVI ASHILA PURNAMASARI', 2019, 19201, NULL, 'LULUS', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 100),
(400, '191710301068', 'ANISA RIZKI HERAWATI', 2019, 19201, NULL, 'AKTIF', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 101),
(401, '191710301069', 'FENRY ARTHOLIN RAMADHAN', 2019, 19201, NULL, 'LULUS', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 102),
(402, '191710301070', 'DEDEN FIRMANSYAH', 2019, 19201, NULL, 'LULUS', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 103),
(403, '191710301071', 'ACHMAD ZAMRONIE', 2019, 19201, NULL, 'LULUS', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 104),
(404, '191710301072', 'ZAYYAN NISRINA NASYWA', 2019, 19201, NULL, 'LULUS', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 105),
(405, '191710301073', 'HUSNI KASIM RIDHO SOENARDI', 2019, 19201, NULL, 'LULUS', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 106),
(406, '191710301074', 'RIDATUL WINDA HIDAYAH', 2019, 19201, NULL, 'LULUS', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 107),
(407, '191710301075', 'RIZQI DHIA RAMADHAN', 2019, 19201, NULL, 'LULUS', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 108),
(408, '191710301076', 'ANUGERAH RIZKY RAHMATULLAH', 2019, 19201, NULL, 'AKTIF', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 109),
(409, '191710301077', 'BETTY NUR AULIA FEBRIANTI', 2019, 19201, NULL, 'AKTIF', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 110),
(410, '191710301078', 'NANDA SINTYA FITRI SALSABILA', 2019, 19201, NULL, 'LULUS', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 111),
(411, '191710301079', 'Ardy Kholify Suhud', 2019, 19201, NULL, 'NON-AKTIF', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 112),
(412, '191710301080', 'MUHAMMAD OVAN RAMDHANO', 2019, 19201, NULL, 'LULUS', '2025-10-29 04:11:08', '2025-10-29 04:11:08', 113),
(520, '211710301001', 'YOVI NUR FIKRI', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:52', '2025-10-29 04:23:53', 461),
(521, '211710301002', 'MUHAMMAD DWI KURNIAWAN', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:53', '2025-10-29 04:23:53', 462),
(522, '211710301003', 'DEVI TARISSA RISMAWATI', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:53', '2025-10-29 04:23:53', 463),
(523, '211710301004', 'RESHA DHIAH EKA YULIANASARI', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:53', '2025-10-29 04:23:53', 464),
(524, '211710301005', 'YEZA ZANJA BILLAWATI', 2021, 21221, NULL, 'AKTIF', '2025-10-29 04:23:53', '2025-10-29 04:23:53', 465),
(525, '211710301006', 'M. VIGO AGSELDI UTAMA', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:53', '2025-10-29 04:23:53', 466),
(526, '211710301007', 'CANDRIKA NUR VIRGITA', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:53', '2025-10-29 04:23:53', 467),
(527, '211710301008', 'MUTI\'AISYATUR ROFI\'AH', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:53', '2025-10-29 04:23:53', 468),
(528, '211710301009', 'YOLANDA MAULANI', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:53', '2025-10-29 04:23:53', 469),
(529, '211710301010', 'CINDY ALYA GUSTYA', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:53', '2025-10-29 04:23:53', 470),
(530, '211710301011', 'RIMA WARDANI', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:53', '2025-10-29 04:23:53', 471),
(531, '211710301012', 'SALSABILA ATHAYA YASMIN', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:53', '2025-10-29 04:23:53', 472),
(532, '211710301013', 'WENDRA PUTRA PRATAMA', 2021, 21221, NULL, 'AKTIF', '2025-10-29 04:23:53', '2025-10-29 04:23:53', 473),
(533, '211710301014', 'DIRA REVITA DAMAYANTI', 2021, 21221, NULL, 'AKTIF', '2025-10-29 04:23:53', '2025-10-29 04:23:53', 474),
(534, '211710301015', 'RANDHIAGUS PRAJAMUKTI', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:53', '2025-10-29 04:23:53', 475),
(535, '211710301016', 'NADILA LAILA AZHARI', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:53', '2025-10-29 04:23:53', 476),
(536, '211710301017', 'ARI CANDRA JUNI PAMUNGKAS', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:53', '2025-10-29 04:23:53', 477),
(537, '211710301018', 'WISNU WARDANA', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:53', '2025-10-29 04:23:53', 478),
(538, '211710301019', 'AKHMAD FAJAR FADLI', 2021, 21221, NULL, 'AKTIF', '2025-10-29 04:23:53', '2025-10-29 04:23:53', 479),
(539, '211710301020', 'MADE ARTHA PUTRI AGENG', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:53', '2025-10-29 04:23:53', 480),
(540, '211710301021', 'TASYA NATASYA KAMILA MATURIDI', 2021, 21221, NULL, 'NON-AKTIF', '2025-10-29 04:23:53', '2025-10-29 04:23:53', 481),
(541, '211710301022', 'ZYACHBEINA NOVINDA YUDHA SARI', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:53', '2025-10-29 04:23:54', 482),
(542, '211710301023', 'JAYA BAGUS DARMAWAN', 2021, 21221, NULL, 'NON-AKTIF', '2025-10-29 04:23:54', '2025-10-29 04:23:54', 483),
(543, '211710301024', 'YONATA PONDIA WARDANI', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:54', '2025-10-29 04:23:54', 484),
(544, '211710301025', 'FAHIMATUL ULUMIYAH', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:54', '2025-10-29 04:23:54', 485),
(545, '211710301026', 'MELCANIA BRIGITA APRIL', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:54', '2025-10-29 04:23:54', 486),
(546, '211710301027', 'ANNISA AYU PRATIWI', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:54', '2025-10-29 04:23:54', 487),
(547, '211710301028', 'MUHAMMAD ZACKY FATHONI', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:54', '2025-10-29 04:23:54', 488),
(548, '211710301029', 'ALIFA NOVI MUZAYANA', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:54', '2025-10-29 04:23:54', 489),
(549, '211710301030', 'MAULANA YUSQI SALSABIL', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:54', '2025-10-29 04:23:54', 490),
(550, '211710301031', 'KURNIAWAN TORIK AKBAR GIBRANI', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:54', '2025-10-29 04:23:54', 491),
(551, '211710301032', 'DARIS ARUM PUSPO SARI', 2021, 21221, NULL, 'AKTIF', '2025-10-29 04:23:54', '2025-10-29 04:23:54', 492),
(552, '211710301033', 'OCTAFIAN MAULANA QOMARUZZAMAN', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:54', '2025-10-29 04:23:54', 493),
(553, '211710301034', 'SHAFA AZELIA IVADA', 2021, 21221, NULL, 'AKTIF', '2025-10-29 04:23:54', '2025-10-29 04:23:54', 494),
(554, '211710301035', 'ANGELINA AZHARA PERMANA', 2021, 21221, NULL, 'AKTIF', '2025-10-29 04:23:54', '2025-10-29 04:23:54', 495),
(555, '211710301036', 'SOVIA AULIA NABILA', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:54', '2025-10-29 04:23:54', 496),
(556, '211710301037', 'RINDI MAYANG SARI', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:54', '2025-10-29 04:23:54', 497),
(557, '211710301038', 'LUSSYANA FAIDATUL LATIFAH', 2021, 21221, NULL, 'NON-AKTIF', '2025-10-29 04:23:54', '2025-10-29 04:23:54', 498),
(558, '211710301039', 'AMELVA FIRSTIAN MAULIDA', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:54', '2025-10-29 04:23:54', 499),
(559, '211710301040', 'ASTRIA SHIELVIONITA NAULIA PUTRI', 2021, 21221, NULL, 'AKTIF', '2025-10-29 04:23:54', '2025-10-29 04:23:54', 500),
(560, '211710301042', 'FERDI AL HIKMAH', 2021, 21221, NULL, 'NON-AKTIF', '2025-10-29 04:23:54', '2025-10-29 04:23:54', 501),
(561, '211710301043', 'TUTUT SULENDRA', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:54', '2025-10-29 04:23:54', 502),
(562, '211710301044', 'ACHMAD ALFIN MAHENDRA', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:54', '2025-10-29 04:23:55', 503),
(563, '211710301045', 'DONI GUNTORO', 2021, 21221, NULL, 'AKTIF', '2025-10-29 04:23:55', '2025-10-29 04:23:55', 504),
(564, '211710301046', 'DITA APRILLIA', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:55', '2025-10-29 04:23:55', 505),
(565, '211710301047', 'ANNISA NURLAILI SALSABILLA', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:55', '2025-10-29 04:23:55', 506),
(566, '211710301048', 'QORI AFIFAH ANGGRAENI MAHGFIRO', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:55', '2025-10-29 04:23:55', 507),
(567, '211710301049', 'ERIKA DWI SILAWATI', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:55', '2025-10-29 04:23:55', 508),
(568, '211710301050', 'NURUL AISYAH', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:55', '2025-10-29 04:23:55', 509),
(569, '211710301051', 'RIZQY IBNU SHINNA', 2021, 21221, NULL, 'AKTIF', '2025-10-29 04:23:55', '2025-10-29 04:23:55', 510),
(570, '211710301052', 'AFLAKH ZULFAN QORIB', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:55', '2025-10-29 04:23:55', 511),
(571, '211710301053', 'AQLIMA SEKAR MAULINA', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:55', '2025-10-29 04:23:55', 512),
(572, '211710301054', 'MEIDIANA RAHMAWATI', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:55', '2025-10-29 04:23:55', 513),
(573, '211710301055', 'SHILFY ROHMATIKA', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:55', '2025-10-29 04:23:55', 514),
(574, '211710301056', 'RISSA AYU WULANDARI', 2021, 21221, NULL, 'AKTIF', '2025-10-29 04:23:55', '2025-10-29 04:23:55', 515),
(575, '211710301057', 'JOSHEP YOSHIO LEEMANS', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:55', '2025-10-29 04:23:55', 516),
(576, '211710301058', 'M. SALMAN ELJA', 2021, 21221, NULL, 'AKTIF', '2025-10-29 04:23:55', '2025-10-29 04:23:55', 517),
(577, '211710301059', 'MAESARANI SALSALINA SITEPU', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:55', '2025-10-29 04:23:55', 518),
(578, '211710301060', 'FILA FARIDATUZ ZAHROK', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:55', '2025-10-29 04:23:55', 519),
(579, '211710301061', 'GABRIEL DESTINO SITORUS', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:55', '2025-10-29 04:23:55', 520),
(580, '211710301062', 'RAGILYA REGINA ASMARA', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:55', '2025-10-29 04:23:55', 521),
(581, '211710301063', 'KEMAS ALMAS MUHAMMAD', 2021, 21221, NULL, 'AKTIF', '2025-10-29 04:23:55', '2025-10-29 04:23:55', 522),
(582, '211710301064', 'NUGROHO ADI SURYA', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:55', '2025-10-29 04:23:55', 523),
(583, '211710301065', 'HERDITYA RIFQI PRATAMA', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:55', '2025-10-29 04:23:56', 524),
(584, '211710301066', 'DEVI DWI JULIA RAHMAWATI', 2021, 21221, NULL, 'AKTIF', '2025-10-29 04:23:56', '2025-10-29 04:23:56', 525),
(585, '211710301067', 'KRISNOV DIRGA', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:56', '2025-10-29 04:23:56', 526),
(586, '211710301068', 'AHMAD ZULFANI LIANTOMO', 2021, 21221, NULL, 'AKTIF', '2025-10-29 04:23:56', '2025-10-29 04:23:56', 527),
(587, '211710301069', 'VINA JUANITA SARI', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:56', '2025-10-29 04:23:56', 528),
(588, '211710301070', 'JILAN HANIFA', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:56', '2025-10-29 04:23:56', 529),
(589, '211710301071', 'AINA SALSABILLA PUTRI PRASAFI', 2021, 21221, NULL, 'AKTIF', '2025-10-29 04:23:56', '2025-10-29 04:23:56', 530),
(590, '211710301072', 'MOHAMMAD DAFFA ZAHWAN', 2021, 21221, NULL, 'AKTIF', '2025-10-29 04:23:56', '2025-10-29 04:23:56', 531),
(591, '211710301073', 'ASYAFA\'ATUL ULYA', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:56', '2025-10-29 04:23:56', 532),
(592, '211710301074', 'MUHAMMAD ADHITIYA FAJAR FEBRIYANTO', 2021, 21221, NULL, 'AKTIF', '2025-10-29 04:23:56', '2025-10-29 04:23:56', 533),
(593, '211710301075', 'MOH. ZAYID ZIDANE', 2021, 21221, NULL, 'CUTI', '2025-10-29 04:23:56', '2025-10-29 04:23:56', 534),
(594, '211710301076', 'NALURITA PUTRI NUR FADZILAH', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:56', '2025-10-29 04:23:56', 535),
(595, '211710301077', 'RIZKY FIRMANSYACH', 2021, 21221, NULL, 'AKTIF', '2025-10-29 04:23:56', '2025-10-29 04:23:56', 536),
(596, '211710301078', 'ANINDYA DYAH AYU JUNIASTY', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:56', '2025-10-29 04:23:56', 537),
(597, '211710301079', 'DIVA PERMATA ALFARISQA', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:56', '2025-10-29 04:23:56', 538),
(598, '211710301080', 'RANI MA\'RUFA', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:56', '2025-10-29 04:23:56', 539),
(599, '211710301081', 'MUHAMMAD LUTHFI NASHIRUDDIN', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:56', '2025-10-29 04:23:56', 540),
(600, '211710301082', 'TABAH AJI PAMUNGKAS', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:56', '2025-10-29 04:23:56', 541),
(601, '211710301083', 'YOGA AJI PANGESTU', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:56', '2025-10-29 04:23:56', 542),
(602, '211710301084', 'MISTY AYU LARASATI', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:56', '2025-10-29 04:23:56', 543),
(603, '211710301085', 'DELIA DEVITA', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:56', '2025-10-29 04:23:56', 544),
(604, '211710301086', 'ERVINA NUR KHASANAH', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:56', '2025-10-29 04:23:56', 545),
(605, '211710301087', 'YANUAR FAHMI NUR HAPSORO', 2021, 21221, NULL, 'NON-AKTIF', '2025-10-29 04:23:56', '2025-10-29 04:23:56', 546),
(606, '211710301088', 'SALWATUL AISH SILMIYAH', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:56', '2025-10-29 04:23:57', 547),
(607, '211710301089', 'GHALY ARKAN ADIYATMA', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:57', '2025-10-29 04:23:57', 548),
(608, '211710301090', 'WAHYU FAJAR MAULANA', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:57', '2025-10-29 04:23:57', 549),
(609, '211710301091', 'FANI FAHRURRIJAL AL FARIZI', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:57', '2025-10-29 04:23:57', 550),
(610, '211710301092', 'NAVIS FATWA FADILLAH', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:57', '2025-10-29 04:23:57', 551),
(611, '211710301093', 'WIEKE RAHMA SARI', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:57', '2025-10-29 04:23:57', 552),
(612, '211710301094', 'ZAKIA NUR FEBRIANTI', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:57', '2025-10-29 04:23:57', 553),
(613, '211710301095', 'MUHAMMAD ZAIN ASSHODIQ', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:57', '2025-10-29 04:23:57', 554),
(614, '211710301096', 'AMELIYA', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:57', '2025-10-29 04:23:57', 555),
(615, '211710301097', 'NADILA FEBRIANTI DZULHIJJAH ZAHRO', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:57', '2025-10-29 04:23:57', 556),
(616, '211710301098', 'ERGIANT ARLENDA HERDIN', 2021, 21221, NULL, 'AKTIF', '2025-10-29 04:23:57', '2025-10-29 04:23:57', 557),
(617, '211710301099', 'WIDANA ADAM RIZALDI', 2021, 21221, NULL, 'NON-AKTIF', '2025-10-29 04:23:57', '2025-10-29 04:23:57', 558),
(618, '211710301100', 'NABILAH BALQIS PUTRIANSI', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:57', '2025-10-29 04:23:57', 559),
(619, '211710301101', 'ALMA TSABITA KAMILAH', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:57', '2025-10-29 04:23:57', 560),
(620, '211710301102', 'NINING ARIFAH', 2021, 21221, NULL, 'AKTIF', '2025-10-29 04:23:57', '2025-10-29 04:23:57', 561),
(621, '211710301103', 'ATSILA RAMADHANI ROSHIFA', 2021, 21221, NULL, 'NON-AKTIF', '2025-10-29 04:23:57', '2025-10-29 04:23:57', 562),
(622, '211710301104', 'MUHAMMAD RAYHAN ALFIAN', 2021, 21221, NULL, 'AKTIF', '2025-10-29 04:23:57', '2025-10-29 04:23:57', 563),
(623, '211710301105', 'AWALIYA FARADIBA', 2021, 21221, NULL, 'NON-AKTIF', '2025-10-29 04:23:57', '2025-10-29 04:23:57', 564),
(624, '211710301106', 'SHOFIYAN TITO ABADI', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:57', '2025-10-29 04:23:57', 565),
(625, '211710301107', 'FAHMI DEWANTARA', 2021, 21221, NULL, 'AKTIF', '2025-10-29 04:23:57', '2025-10-29 04:23:57', 566),
(626, '211710301108', 'RIZAL SAPUTRA', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:57', '2025-10-29 04:23:57', 567),
(627, '211710301109', 'VRISKA AZIZAH AMALIA', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:57', '2025-10-29 04:23:57', 568),
(628, '211710301110', 'NI PUTU INDRA LESTARI', 2021, 21221, NULL, 'AKTIF', '2025-10-29 04:23:57', '2025-10-29 04:23:57', 569),
(629, '211710301111', 'SIGIT ARYA PUTRA', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:57', '2025-10-29 04:23:58', 570),
(630, '211710301112', 'ALIFASHA RAHMANDRYA ADI', 2021, 21221, NULL, 'AKTIF', '2025-10-29 04:23:58', '2025-10-29 04:23:58', 571),
(631, '211710301113', 'ANNISA AZZAHRAH', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:58', '2025-10-29 04:23:58', 572),
(632, '211710301114', 'FAJARIKA PUSPITASARI', 2021, 21221, NULL, 'AKTIF', '2025-10-29 04:23:58', '2025-10-29 04:23:58', 573),
(633, '211710301115', 'AWWALLIYYAN FITRATIN NISWA', 2021, 21221, NULL, 'AKTIF', '2025-10-29 04:23:58', '2025-10-29 04:23:58', 574),
(634, '211710301116', 'DIMAS WALIYUL A\'LA', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:58', '2025-10-29 04:23:58', 575),
(635, '211710301117', 'ILVI NURSINDIA', 2021, 21221, NULL, 'NON-AKTIF', '2025-10-29 04:23:58', '2025-10-29 04:23:58', 576),
(636, '211710301118', 'BAHRUL ULUMUDIN', 2021, 21221, NULL, 'AKTIF', '2025-10-29 04:23:58', '2025-10-29 04:23:58', 577),
(637, '211710301119', 'RIANA FITRIA GOZALI', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:58', '2025-10-29 04:23:58', 578),
(638, '211710301124', 'RISTA DEA WULANDARI', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:58', '2025-10-29 04:23:58', 579),
(639, '211710301125', 'DEV DATUL HELMI', 2021, 21221, NULL, 'NON-AKTIF', '2025-10-29 04:23:58', '2025-10-29 04:23:58', 580),
(640, '211710301126', 'MUHAMMAD SYAWALAH AMMAR KHADAFI', 2021, 21221, NULL, 'CUTI', '2025-10-29 04:23:58', '2025-10-29 04:23:58', 581),
(641, '211710301127', 'MILAH', 2021, 21221, NULL, 'LULUS', '2025-10-29 04:23:58', '2025-10-29 04:23:58', 582),
(642, '221710301001', 'ABDULLAHIL MUBAROK', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:20', '2025-10-29 04:27:20', 583),
(643, '221710301002', 'AINI WAFIROH', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:20', '2025-10-29 04:27:20', 584),
(644, '221710301003', 'SHINTA AYU SWASTI', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:20', '2025-10-29 04:27:20', 585),
(645, '221710301004', 'MAGISTA SAKINAH', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:20', '2025-10-29 04:27:20', 586),
(646, '221710301005', 'SARANDA FIONNOLA', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:20', '2025-10-29 04:27:20', 587),
(647, '221710301006', 'NASYA RAHMAWATI', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:20', '2025-10-29 04:27:20', 588),
(648, '221710301007', 'AZZAHRA NUR FADHILAH', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:20', '2025-10-29 04:27:20', 589),
(649, '221710301008', 'ILHAM ARIEF SAPUTRA', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:20', '2025-10-29 04:27:20', 590),
(650, '221710301009', 'AFIA RIZKY AMALIA', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:20', '2025-10-29 04:27:20', 591),
(651, '221710301010', 'MUHAMMAD ILHAM FARHAN', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:20', '2025-10-29 04:27:20', 592),
(652, '221710301011', 'FARHAN AKBAR MAULANA', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:20', '2025-10-29 04:27:20', 593),
(653, '221710301012', 'DINDA RAHMA ANGGRAENI', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:20', '2025-10-29 04:27:21', 594),
(654, '221710301013', 'MARWAH MUTHIAH', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:21', '2025-10-29 04:27:21', 595),
(655, '221710301014', 'SITI KHOLIFATUL HASANAH', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:21', '2025-10-29 04:27:21', 596),
(656, '221710301015', 'MUHAMMAD ALI MUBAROK', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:21', '2025-10-29 04:27:21', 597),
(657, '221710301016', 'FARIS MUHAMMAD NUR FAIZ', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:21', '2025-10-29 04:27:21', 598),
(658, '221710301017', 'NAJWA KHANSA NADIA', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:21', '2025-10-29 04:27:21', 599),
(659, '221710301018', 'RAHMA AZZAHRA', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:21', '2025-10-29 04:27:21', 600),
(660, '221710301019', 'INDIRA APRILIA PUTRI', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:21', '2025-10-29 04:27:21', 601),
(661, '221710301020', 'ALFAN MUHAMMAD FAUZAN', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:21', '2025-10-29 04:27:21', 602),
(662, '221710301021', 'FITRIANI', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:21', '2025-10-29 04:27:21', 603),
(663, '221710301022', 'DITA AULIA RAHMAN', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:21', '2025-10-29 04:27:21', 604),
(664, '221710301023', 'SALWA NURUL AINI', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:21', '2025-10-29 04:27:21', 605),
(665, '221710301024', 'DIVA MAHARANI', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:21', '2025-10-29 04:27:21', 606),
(666, '221710301025', 'RIZKI NURUL HAYATI', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:21', '2025-10-29 04:27:21', 607),
(667, '221710301026', 'M. RIZKY NUR PRATAMA', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:21', '2025-10-29 04:27:21', 608),
(668, '221710301027', 'NAILA PUTRI AZZAHRA', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:21', '2025-10-29 04:27:21', 609),
(669, '221710301028', 'SYIFA NURUL AINI', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:21', '2025-10-29 04:27:21', 610),
(670, '221710301029', 'M. RASYID RIDHO', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:21', '2025-10-29 04:27:21', 611),
(671, '221710301030', 'DIANA PUTRI LESTARI', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:21', '2025-10-29 04:27:21', 612),
(672, '221710301031', 'FADHILAH NUR KHAIRUNNISA', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:21', '2025-10-29 04:27:21', 613),
(673, '221710301032', 'FARIDAH AMALIA', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:21', '2025-10-29 04:27:21', 614),
(674, '221710301033', 'NAJWA ULFA', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:21', '2025-10-29 04:27:21', 615),
(675, '221710301034', 'RATNA AMALIA', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:21', '2025-10-29 04:27:22', 616),
(676, '221710301035', 'ALFIYAH NURUL HIDAYAH', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:22', '2025-10-29 04:27:22', 617),
(677, '221710301036', 'DEVI ANGGRAENI', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:22', '2025-10-29 04:27:22', 618),
(678, '221710301037', 'RIFKY NUR FAJAR', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:22', '2025-10-29 04:27:22', 619),
(679, '221710301038', 'PUTRI MELATI', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:22', '2025-10-29 04:27:22', 620),
(680, '221710301039', 'ARISKA DWI PUTRI', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:22', '2025-10-29 04:27:22', 621),
(681, '221710301040', 'LUQMAN HAKIM', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:22', '2025-10-29 04:27:22', 622),
(682, '221710301041', 'MUHAMMAD ARDIANSYAH', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:22', '2025-10-29 04:27:22', 623),
(683, '221710301042', 'SAFRINA DEWI KURNIA', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:22', '2025-10-29 04:27:22', 624),
(684, '221710301043', 'FAJAR DWI SAPUTRA', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:22', '2025-10-29 04:27:22', 625),
(685, '221710301044', 'NUR AZIZAH', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:22', '2025-10-29 04:27:22', 626),
(686, '221710301045', 'SITI NUR HALIMAH', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:22', '2025-10-29 04:27:22', 627),
(687, '221710301046', 'RIZKY SEPTIAN', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:22', '2025-10-29 04:27:22', 628),
(688, '221710301047', 'AZIZAH MAULIDA', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:22', '2025-10-29 04:27:22', 629),
(689, '221710301048', 'RINDI AMALIA', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:22', '2025-10-29 04:27:22', 630),
(690, '221710301049', 'NAUFAL RAHMAN', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:22', '2025-10-29 04:27:22', 631),
(691, '221710301050', 'ELISA NURUL FITRI', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:22', '2025-10-29 04:27:22', 632),
(692, '221710301051', 'RISKA NURUL MAULIDA', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:22', '2025-10-29 04:27:22', 633),
(693, '221710301052', 'MAYA PUTRI', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:22', '2025-10-29 04:27:22', 634),
(694, '221710301053', 'SALMA PUTRI ANANDA', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:22', '2025-10-29 04:27:22', 635),
(695, '221710301054', 'YUDA SAPUTRA', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:22', '2025-10-29 04:27:22', 636),
(696, '221710301055', 'RIO ADITYA', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:22', '2025-10-29 04:27:22', 637),
(697, '221710301056', 'SALSABILA RAHMA', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:22', '2025-10-29 04:27:22', 638),
(698, '221710301057', 'NOVITA RIZKI', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:22', '2025-10-29 04:27:23', 639),
(699, '221710301058', 'PUTRI NUR FAUZIAH', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:23', '2025-10-29 04:27:23', 640),
(700, '221710301059', 'ILHAM NUR RAHMAN', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:23', '2025-10-29 04:27:23', 641),
(701, '221710301060', 'DEWI LESTARI', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:23', '2025-10-29 04:27:23', 642),
(702, '221710301061', 'KHARISMA PUTRI', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:23', '2025-10-29 04:27:23', 643),
(703, '221710301062', 'RINA MAULIDA', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:23', '2025-10-29 04:27:23', 644),
(704, '221710301063', 'MEILANI FITRIANI', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:23', '2025-10-29 04:27:23', 645),
(705, '221710301064', 'ADINDA RAHMA', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:23', '2025-10-29 04:27:23', 646),
(706, '221710301065', 'SALWA NUR KHASANAH', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:23', '2025-10-29 04:27:23', 647),
(707, '221710301066', 'DIAN RAHMAWATI', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:23', '2025-10-29 04:27:23', 648),
(708, '221710301067', 'NAILA FITRIA', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:23', '2025-10-29 04:27:23', 649),
(709, '221710301068', 'RISKI APRILIA', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:23', '2025-10-29 04:27:23', 650),
(710, '221710301069', 'DWI AMELIA', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:23', '2025-10-29 04:27:23', 651),
(711, '221710301070', 'ANANDA PUTRA', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:23', '2025-10-29 04:27:23', 652),
(712, '221710301071', 'RISMA ANGGRAINI', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:23', '2025-10-29 04:27:23', 653),
(713, '221710301072', 'NOVI RAHAYU', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:23', '2025-10-29 04:27:23', 654),
(714, '221710301073', 'MELISA ARUM', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:23', '2025-10-29 04:27:23', 655),
(715, '221710301074', 'FIRMAN RAHMAN', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:23', '2025-10-29 04:27:23', 656),
(716, '221710301075', 'NADIA RAHMA', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:23', '2025-10-29 04:27:23', 657),
(717, '221710301076', 'MUHAMMAD RIZKY', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:23', '2025-10-29 04:27:23', 658),
(718, '221710301077', 'RINA PUSPITA', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:23', '2025-10-29 04:27:23', 659),
(719, '221710301078', 'FAJAR SEPTIAN', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:23', '2025-10-29 04:27:23', 660),
(720, '221710301079', 'PUTRI NURUL', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:23', '2025-10-29 04:27:23', 661),
(721, '221710301080', 'ADI SAPUTRA', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:23', '2025-10-29 04:27:24', 662),
(722, '221710301081', 'DEDI IRFAN', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:24', '2025-10-29 04:27:24', 663),
(723, '221710301082', 'RISKI WAHYUDI', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:24', '2025-10-29 04:27:24', 664),
(724, '221710301083', 'DIANA SAPUTRI', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:24', '2025-10-29 04:27:24', 665),
(725, '221710301084', 'ANISA RAHMAN', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:24', '2025-10-29 04:27:24', 666),
(726, '221710301085', 'AGUS WAHYUDI', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:24', '2025-10-29 04:27:24', 667),
(727, '221710301086', 'RINA SEPTIA', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:24', '2025-10-29 04:27:24', 668),
(728, '221710301087', 'SALMA NUR HIDAYAH', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:24', '2025-10-29 04:27:24', 669),
(729, '221710301088', 'FITRIA AYU', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:24', '2025-10-29 04:27:24', 670),
(730, '221710301089', 'NUR RAHMAN', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:24', '2025-10-29 04:27:24', 671),
(731, '221710301090', 'RAHMA DWI', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:24', '2025-10-29 04:27:24', 672),
(732, '221710301091', 'INTAN MAULIDA', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:24', '2025-10-29 04:27:24', 673),
(733, '221710301092', 'PUTRI LESTARI', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:24', '2025-10-29 04:27:24', 674),
(734, '221710301093', 'RIZKY MAULANA', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:24', '2025-10-29 04:27:24', 675),
(735, '221710301094', 'DIAN RAHMAN', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:24', '2025-10-29 04:27:24', 676),
(736, '221710301095', 'SALWA LESTARI', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:24', '2025-10-29 04:27:24', 677),
(737, '221710301096', 'NUR RAHAYU', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:24', '2025-10-29 04:27:24', 678),
(738, '221710301097', 'PUTRI ANDINI', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:24', '2025-10-29 04:27:24', 679),
(739, '221710301098', 'IRMA NUR FITRI', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:24', '2025-10-29 04:27:24', 680),
(740, '221710301099', 'RAHMA ANDINI', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:24', '2025-10-29 04:27:24', 681),
(741, '221710301100', 'INTAN NURUL HIDAYAH', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:24', '2025-10-29 04:27:24', 682),
(742, '221710301101', 'MUHAMMAD FADHIL', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:24', '2025-10-29 04:27:24', 683),
(743, '221710301102', 'RIZAL ADITYA', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:24', '2025-10-29 04:27:24', 684),
(744, '221710301103', 'SALMA LESTARI', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:24', '2025-10-29 04:27:25', 685),
(745, '221710301104', 'ANISA NURUL HIDAYAH', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:25', '2025-10-29 04:27:25', 686),
(746, '221710301105', 'RAHMA PUTRI', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:25', '2025-10-29 04:27:25', 687),
(747, '221710301106', 'DEWI MAULIDA', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:25', '2025-10-29 04:27:25', 688),
(748, '221710301107', 'ANDIKA RAHMAN', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:25', '2025-10-29 04:27:25', 689),
(749, '221710301108', 'FITRI AYU NURUL', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:25', '2025-10-29 04:27:25', 690),
(750, '221710301109', 'RISMA LESTARI', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:25', '2025-10-29 04:27:25', 691),
(751, '221710301110', 'NURUL AMALIA', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:25', '2025-10-29 04:27:25', 692),
(752, '221710301111', 'SALMA PUTRI NURUL', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:25', '2025-10-29 04:27:25', 693),
(753, '221710301112', 'RIO NUR RAHMAN', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:25', '2025-10-29 04:27:25', 694),
(754, '221710301113', 'INDIRA RAHAYU', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:25', '2025-10-29 04:27:25', 695),
(755, '221710301114', 'RINA AMALIA', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:25', '2025-10-29 04:27:25', 696),
(756, '221710301115', 'ILHAM PRATAMA', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:25', '2025-10-29 04:27:25', 697),
(757, '221710301116', 'DIAN SEPTIAN', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:25', '2025-10-29 04:27:25', 698),
(758, '221710301117', 'NURUL HIDAYAH', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:25', '2025-10-29 04:27:25', 699),
(759, '221710301118', 'RINA DEWI', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:25', '2025-10-29 04:27:25', 700),
(760, '221710301119', 'SALWA AYU', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:25', '2025-10-29 04:27:25', 701),
(761, '221710301120', 'PUTRI DIANA', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:25', '2025-10-29 04:27:25', 702),
(762, '221710301121', 'REZA MAULANA', 2022, 22231, NULL, 'NON-AKTIF', '2025-10-29 04:27:25', '2025-10-29 04:27:25', 703),
(763, '221710301122', 'SITI NURKHALISA', 2022, 22231, NULL, 'CUTI', '2025-10-29 04:27:25', '2025-10-29 04:27:25', 704),
(764, '221710301123', 'ADILLA HIDAYAH', 2022, 22231, NULL, 'AKTIF', '2025-10-29 04:27:25', '2025-10-29 04:27:25', 705),
(765, '221710301124', 'WILIAM WISNU', 2022, 22231, NULL, 'LULUS', '2025-10-29 04:27:25', '2025-10-29 04:27:25', 706),
(766, '231710301001', 'LELIANA JESIKA SUSANTI', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:17', '2025-10-29 04:31:17', 707),
(767, '231710301002', 'CRISTINE MARGARETHA SIANIPAR', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:17', '2025-10-29 04:31:17', 708),
(768, '231710301003', 'NILEN LOUISA', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:17', '2025-10-29 04:31:17', 709),
(769, '231710301004', 'KARINA ABRILLIA', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:17', '2025-10-29 04:31:17', 710),
(770, '231710301005', 'EKA DINDA MAR\'ATUS SOLEKHAH', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:17', '2025-10-29 04:31:17', 711),
(771, '231710301006', 'NABILA TRI OKTAVIA', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:17', '2025-10-29 04:31:17', 712),
(772, '231710301007', 'DAMAR RIZKY PRAYOGA', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:17', '2025-10-29 04:31:17', 713),
(773, '231710301008', 'SUCI RAMADANI', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:17', '2025-10-29 04:31:17', 714),
(774, '231710301009', 'ROFIDAH RAHAYU WILUJENG', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:17', '2025-10-29 04:31:18', 715),
(775, '231710301010', 'SEIFIN AMELIA PUTRI AFANDI', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:18', '2025-10-29 04:31:18', 716),
(776, '231710301011', 'WAHYU NUR AZIZAH', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:18', '2025-10-29 04:31:18', 717),
(777, '231710301012', 'ANNISA NOVITA NURFADILAH', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:18', '2025-10-29 04:31:18', 718),
(778, '231710301013', 'RAHMAWATI ARDHIANA PUTRI', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:18', '2025-10-29 04:31:18', 719),
(779, '231710301014', 'NURUL HIKMAH', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:18', '2025-10-29 04:31:18', 720),
(780, '231710301015', 'DESI PUSPITASARI', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:18', '2025-10-29 04:31:18', 721),
(781, '231710301016', 'DIAN RAMADHANI', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:18', '2025-10-29 04:31:18', 722),
(782, '231710301017', 'SALWA NUR AZIZAH', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:18', '2025-10-29 04:31:18', 723),
(783, '231710301018', 'ILHAM NUR FAJAR', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:18', '2025-10-29 04:31:18', 724),
(784, '231710301019', 'ARISKA DWI PUSPITA', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:18', '2025-10-29 04:31:18', 725),
(785, '231710301020', 'ALIFIA NURUL HIDAYAH', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:18', '2025-10-29 04:31:18', 726),
(786, '231710301021', 'PUTRI MAULIDA', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:18', '2025-10-29 04:31:18', 727),
(787, '231710301022', 'NOVITA DEWI', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:18', '2025-10-29 04:31:18', 728),
(788, '231710301023', 'DEWI ANDINI', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:18', '2025-10-29 04:31:18', 729),
(789, '231710301024', 'AFIFAH RAHMAWATI', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:18', '2025-10-29 04:31:18', 730),
(790, '231710301025', 'SITI KHAIRUNNISA', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:18', '2025-10-29 04:31:18', 731),
(791, '231710301026', 'ILHAM RIDWAN', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:18', '2025-10-29 04:31:18', 732),
(792, '231710301027', 'MUHAMMAD RIZKY FADHIL', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:18', '2025-10-29 04:31:18', 733),
(793, '231710301028', 'RAHMA AYU', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:18', '2025-10-29 04:31:18', 734),
(794, '231710301029', 'RISKA PUTRI', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:18', '2025-10-29 04:31:18', 735),
(795, '231710301030', 'DIANA SARI', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:18', '2025-10-29 04:31:18', 736),
(796, '231710301031', 'FIRDA NUR HIDAYAH', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:18', '2025-10-29 04:31:18', 737),
(797, '231710301032', 'NADIA RAHMA', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:18', '2025-10-29 04:31:19', 738),
(798, '231710301033', 'PUTRI SEPTIANA', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:19', '2025-10-29 04:31:19', 739),
(799, '231710301034', 'RINA SARI', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:19', '2025-10-29 04:31:19', 740),
(800, '231710301035', 'ANISA RAHAYU', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:19', '2025-10-29 04:31:19', 741),
(801, '231710301036', 'MAYA FITRIANI', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:19', '2025-10-29 04:31:19', 742),
(802, '231710301037', 'SALMA MAULIDA', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:19', '2025-10-29 04:31:19', 743),
(803, '231710301038', 'NURUL AMALIA', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:19', '2025-10-29 04:31:19', 744),
(804, '231710301039', 'DIAN FITRI', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:19', '2025-10-29 04:31:19', 745),
(805, '231710301040', 'RISMA PUTRI', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:19', '2025-10-29 04:31:19', 746),
(806, '231710301041', 'FADHIL RAHMAN', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:19', '2025-10-29 04:31:19', 747),
(807, '231710301042', 'SITI RAHAYU', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:19', '2025-10-29 04:31:19', 748),
(808, '231710301043', 'FAIZAH NURUL HIDAYAH', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:19', '2025-10-29 04:31:19', 749),
(809, '231710301044', 'NABILAH RAHMA', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:19', '2025-10-29 04:31:19', 750),
(810, '231710301045', 'PUTRI ANDINI', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:19', '2025-10-29 04:31:19', 751),
(811, '231710301046', 'MUHAMMAD RIZKI', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:19', '2025-10-29 04:31:19', 752),
(812, '231710301047', 'FIRMAN NUR HIDAYAT', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:19', '2025-10-29 04:31:19', 753),
(813, '231710301048', 'DIANA SEPTIA', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:19', '2025-10-29 04:31:19', 754),
(814, '231710301049', 'NURUL AYU', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:19', '2025-10-29 04:31:19', 755),
(815, '231710301050', 'RISKI NUR HIDAYAH', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:19', '2025-10-29 04:31:19', 756),
(816, '231710301051', 'DEWI SARTIKA', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:19', '2025-10-29 04:31:19', 757),
(817, '231710301052', 'SALMA NURUL', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:19', '2025-10-29 04:31:19', 758),
(818, '231710301053', 'RINA NUR HIDAYAH', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:19', '2025-10-29 04:31:19', 759),
(819, '231710301054', 'PUTRA RAHMAN', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:19', '2025-10-29 04:31:19', 760),
(820, '231710301055', 'MAULANA FADHIL', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:19', '2025-10-29 04:31:19', 761),
(821, '231710301056', 'FITRI NUR HIDAYAH', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:19', '2025-10-29 04:31:20', 762),
(822, '231710301057', 'INTAN PUTRI', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:20', '2025-10-29 04:31:20', 763),
(823, '231710301058', 'RAHMA NURUL HIDAYAH', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:20', '2025-10-29 04:31:20', 764),
(824, '231710301059', 'PUTRI LESTARI', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:20', '2025-10-29 04:31:20', 765),
(825, '231710301060', 'SALWA MAULIDA', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:20', '2025-10-29 04:31:20', 766),
(826, '231710301061', 'ANISA NUR HIDAYAH', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:20', '2025-10-29 04:31:20', 767),
(827, '231710301062', 'RIZKY AMALIA', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:20', '2025-10-29 04:31:20', 768),
(828, '231710301063', 'RINA LESTARI', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:20', '2025-10-29 04:31:20', 769),
(829, '231710301064', 'MUHAMMAD ILHAM', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:20', '2025-10-29 04:31:20', 770),
(830, '231710301065', 'FITRI AMALIA', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:20', '2025-10-29 04:31:20', 771),
(831, '231710301066', 'RISMA RAHMA', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:20', '2025-10-29 04:31:20', 772),
(832, '231710301067', 'SALMA NUR HIDAYAH', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:20', '2025-10-29 04:31:20', 773),
(833, '231710301068', 'ILHAM PUTRA', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:20', '2025-10-29 04:31:20', 774),
(834, '231710301069', 'DEWI MAULIDA', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:20', '2025-10-29 04:31:20', 775),
(835, '231710301070', 'FADHILAH RAHMA', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:20', '2025-10-29 04:31:20', 776),
(836, '231710301071', 'RIZAL PRATAMA', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:20', '2025-10-29 04:31:20', 777),
(837, '231710301072', 'PUTRI ANGGRAINI', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:20', '2025-10-29 04:31:20', 778),
(838, '231710301073', 'NURUL HIKMAH', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:20', '2025-10-29 04:31:20', 779),
(839, '231710301074', 'DIAN SAPUTRA', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:20', '2025-10-29 04:31:20', 780),
(840, '231710301075', 'RISKI NUR HIDAYAH', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:20', '2025-10-29 04:31:20', 781),
(841, '231710301076', 'FIRMAN MAULANA', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:20', '2025-10-29 04:31:20', 782),
(842, '231710301077', 'RAHMA ANDINI', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:20', '2025-10-29 04:31:20', 783),
(843, '231710301078', 'SITI NUR HIDAYAH', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:20', '2025-10-29 04:31:20', 784),
(844, '231710301079', 'ANANDA PUTRI', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:20', '2025-10-29 04:31:20', 785),
(845, '231710301080', 'MUHAMMAD RAHMAN', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:21', '2025-10-29 04:31:21', 786),
(846, '231710301081', 'DEWI FITRIANI', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:21', '2025-10-29 04:31:21', 787),
(847, '231710301082', 'PUTRI NUR HIDAYAH', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:21', '2025-10-29 04:31:21', 788),
(848, '231710301083', 'FADHIL RAHMAN', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:21', '2025-10-29 04:31:21', 789),
(849, '231710301084', 'INTAN DEWI', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:21', '2025-10-29 04:31:21', 790),
(850, '231710301085', 'ANISA PUTRI', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:21', '2025-10-29 04:31:21', 791);
INSERT INTO `students` (`id`, `nim`, `name`, `angkatan`, `semester_masuk`, `semester_lulus`, `status`, `created_at`, `updated_at`, `user_id`) VALUES
(851, '231710301086', 'RAHMA NUR HIDAYAH', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:21', '2025-10-29 04:31:21', 792),
(852, '231710301087', 'SALWA PUTRI', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:21', '2025-10-29 04:31:21', 793),
(853, '231710301088', 'MUHAMMAD RIDHO', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:21', '2025-10-29 04:31:21', 794),
(854, '231710301089', 'DEWI LESTARI', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:21', '2025-10-29 04:31:21', 795),
(855, '231710301090', 'RIZKY PUTRA', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:21', '2025-10-29 04:31:21', 796),
(856, '231710301091', 'DIAN NUR HIDAYAH', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:21', '2025-10-29 04:31:21', 797),
(857, '231710301092', 'SALMA MAULIDA', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:21', '2025-10-29 04:31:21', 798),
(858, '231710301093', 'NURUL RAHAYU', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:21', '2025-10-29 04:31:21', 799),
(859, '231710301094', 'PUTRI ANDINI', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:21', '2025-10-29 04:31:21', 800),
(860, '231710301095', 'RINA SEPTIA', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:21', '2025-10-29 04:31:21', 801),
(861, '231710301096', 'MUHAMMAD FADHIL', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:21', '2025-10-29 04:31:21', 802),
(862, '231710301097', 'FITRI MAULIDA', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:21', '2025-10-29 04:31:21', 803),
(863, '231710301098', 'SALWA PUTRI', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:21', '2025-10-29 04:31:21', 804),
(864, '231710301099', 'ILHAM MAULANA', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:21', '2025-10-29 04:31:21', 805),
(865, '231710301100', 'RINA MAULIDA', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:21', '2025-10-29 04:31:21', 806),
(866, '231710301101', 'ANISA NUR HIDAYAH', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:21', '2025-10-29 04:31:21', 807),
(867, '231710301102', 'FIRMAN SEPTIAN', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:21', '2025-10-29 04:31:21', 808),
(868, '231710301103', 'RISMA ANDINI', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:21', '2025-10-29 04:31:22', 809),
(869, '231710301104', 'DIANA RAHMA', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:22', '2025-10-29 04:31:22', 810),
(870, '231710301105', 'PUTRI NUR HIDAYAH', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:22', '2025-10-29 04:31:22', 811),
(871, '231710301106', 'RIZKY MAULANA', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:22', '2025-10-29 04:31:22', 812),
(872, '231710301107', 'FAHRIZAL MEILANA ANGGARA', 2023, 23241, NULL, 'CUTI', '2025-10-29 04:31:22', '2025-10-29 04:31:22', 813),
(873, '231710301108', 'MOHAMMAD ABDUL QODIR JAELANI', 2023, 23241, NULL, 'NON-AKTIF', '2025-10-29 04:31:22', '2025-10-29 04:31:22', 814),
(874, '231710301109', 'ARDI RAHMAN HIDAYAT', 2023, 23241, NULL, 'CUTI', '2025-10-29 04:31:22', '2025-10-29 04:31:22', 815),
(875, '231710301110', 'IZZA FUAD DJATMIKA', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:22', '2025-10-29 04:31:22', 816),
(876, '231710301111', 'MUHAMMAD UKASYAH RAZAN', 2023, 23241, NULL, 'AKTIF', '2025-10-29 04:31:22', '2025-10-29 04:31:22', 817),
(877, '241710301001', 'MUHAMMAD IQBAL AL AYUBY', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:07', '2025-10-29 04:36:07', 818),
(878, '241710301002', 'DIMAS GHIFARI PUTRA', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:07', '2025-10-29 04:36:07', 819),
(879, '241710301003', 'DEWI KUSUMA WARDANI', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:07', '2025-10-29 04:36:07', 820),
(880, '241710301004', 'SASTABILA WAHYU MEIRINE', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:07', '2025-10-29 04:36:07', 821),
(881, '241710301005', 'KARISMA CAHYA KARTIKA HARJADI', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:07', '2025-10-29 04:36:07', 822),
(882, '241710301006', 'MUHAMMAD RAFLI SAPUTRA', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:07', '2025-10-29 04:36:07', 823),
(883, '241710301007', 'MAULANA FATHUR RAHMAN', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:07', '2025-10-29 04:36:07', 824),
(884, '241710301008', 'ARTHA PRADANA RAHMAWAN', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:07', '2025-10-29 04:36:07', 825),
(885, '241710301009', 'SALMA KHALIDA PUTRI', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:07', '2025-10-29 04:36:07', 826),
(886, '241710301010', 'DEWI ANGGRAINI', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:07', '2025-10-29 04:36:07', 827),
(887, '241710301011', 'NURUL MAULIDIA', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:07', '2025-10-29 04:36:07', 828),
(888, '241710301012', 'AZZAHRA KHANSA SHAFIRA', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:07', '2025-10-29 04:36:07', 829),
(889, '241710301013', 'PUTRI AYU NURMAWATI', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:07', '2025-10-29 04:36:07', 830),
(890, '241710301014', 'NABILAH RAMADHANI', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:07', '2025-10-29 04:36:07', 831),
(891, '241710301015', 'RISKI ADITYA SAPUTRA', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:07', '2025-10-29 04:36:07', 832),
(892, '241710301016', 'SITI AULIA NURHAYATI', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:07', '2025-10-29 04:36:07', 833),
(893, '241710301017', 'DIAN NOVITA ANGGRAENI', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:07', '2025-10-29 04:36:07', 834),
(894, '241710301018', 'PUTRI DWI SEPTIANA', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:07', '2025-10-29 04:36:08', 835),
(895, '241710301019', 'FIRDA MAULIDA', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:08', '2025-10-29 04:36:08', 836),
(896, '241710301020', 'ALFIYAH NUR HIDAYAH', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:08', '2025-10-29 04:36:08', 837),
(897, '241710301021', 'RIZKY FAJAR MAULANA', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:08', '2025-10-29 04:36:08', 838),
(898, '241710301022', 'RISMA AMALIA', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:08', '2025-10-29 04:36:08', 839),
(899, '241710301023', 'ANNISA DWI PUSPITA', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:08', '2025-10-29 04:36:08', 840),
(900, '241710301024', 'SALWA NUR HIDAYAH', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:08', '2025-10-29 04:36:08', 841),
(901, '241710301025', 'DEWI SEPTIA RAHAYU', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:08', '2025-10-29 04:36:08', 842),
(902, '241710301026', 'PUTRI NURUL AZZAHRA', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:08', '2025-10-29 04:36:08', 843),
(903, '241710301027', 'FIRMAN PRASETYO', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:08', '2025-10-29 04:36:08', 844),
(904, '241710301028', 'RINA AMALIA', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:08', '2025-10-29 04:36:08', 845),
(905, '241710301029', 'ANDI SAPUTRA', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:08', '2025-10-29 04:36:08', 846),
(906, '241710301030', 'RAHMA AYU FITRIANI', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:08', '2025-10-29 04:36:08', 847),
(907, '241710301031', 'SALMA ANANDA PUTRI', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:08', '2025-10-29 04:36:08', 848),
(908, '241710301032', 'NURUL HIDAYAH', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:08', '2025-10-29 04:36:08', 849),
(909, '241710301033', 'DEWI ANGGRAINI PUTRI', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:08', '2025-10-29 04:36:08', 850),
(910, '241710301034', 'MUHAMMAD FADHIL', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:08', '2025-10-29 04:36:08', 851),
(911, '241710301035', 'FITRI NUR HIDAYAH', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:08', '2025-10-29 04:36:08', 852),
(912, '241710301036', 'RAHMA MAULIDA', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:08', '2025-10-29 04:36:08', 853),
(913, '241710301037', 'PUTRI AYU ANGGRAINI', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:08', '2025-10-29 04:36:08', 854),
(914, '241710301038', 'FADHIL RAHMAN', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:08', '2025-10-29 04:36:08', 855),
(915, '241710301039', 'SALWA PUTRI LESTARI', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:08', '2025-10-29 04:36:08', 856),
(916, '241710301040', 'RINA NUR HIDAYAH', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:08', '2025-10-29 04:36:08', 857),
(917, '241710301041', 'ILHAM SEPTIAN', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:08', '2025-10-29 04:36:09', 858),
(918, '241710301042', 'RISKI AMALIA', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:09', '2025-10-29 04:36:09', 859),
(919, '241710301043', 'DIAN NUR HIDAYAH', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:09', '2025-10-29 04:36:09', 860),
(920, '241710301044', 'PUTRA MAULANA', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:09', '2025-10-29 04:36:09', 861),
(921, '241710301045', 'MUHAMMAD RIZKY', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:09', '2025-10-29 04:36:09', 862),
(922, '241710301046', 'DEWI FITRIANI', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:09', '2025-10-29 04:36:09', 863),
(923, '241710301047', 'RAHMA LESTARI', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:09', '2025-10-29 04:36:09', 864),
(924, '241710301048', 'PUTRI SEPTIANA', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:09', '2025-10-29 04:36:09', 865),
(925, '241710301049', 'NURUL AMALIA', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:09', '2025-10-29 04:36:09', 866),
(926, '241710301050', 'FIRMAN HIDAYAT', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:09', '2025-10-29 04:36:09', 867),
(927, '241710301051', 'RISMA PUTRI', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:09', '2025-10-29 04:36:09', 868),
(928, '241710301052', 'ANDIKA RAHMAN', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:09', '2025-10-29 04:36:09', 869),
(929, '241710301053', 'SALWA MAULIDA', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:09', '2025-10-29 04:36:09', 870),
(930, '241710301054', 'DEWI NUR HIDAYAH', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:09', '2025-10-29 04:36:09', 871),
(931, '241710301055', 'PUTRI ANGGRAINI', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:09', '2025-10-29 04:36:09', 872),
(932, '241710301056', 'RIZKY MAULANA', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:09', '2025-10-29 04:36:09', 873),
(933, '241710301057', 'RAHMA PUTRI', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:09', '2025-10-29 04:36:09', 874),
(934, '241710301058', 'FIRDA AMALIA', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:09', '2025-10-29 04:36:09', 875),
(935, '241710301059', 'NABILAH HIDAYAH', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:09', '2025-10-29 04:36:09', 876),
(936, '241710301060', 'SALWA PUTRI', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:09', '2025-10-29 04:36:09', 877),
(937, '241710301061', 'MUHAMMAD ILHAM', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:09', '2025-10-29 04:36:09', 878),
(938, '241710301062', 'RINA NUR HIDAYAH', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:09', '2025-10-29 04:36:09', 879),
(939, '241710301063', 'DEWI SEPTIA', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:09', '2025-10-29 04:36:10', 880),
(940, '241710301064', 'PUTRI ANANDA', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:10', '2025-10-29 04:36:10', 881),
(941, '241710301065', 'RAHMA LESTARI', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:10', '2025-10-29 04:36:10', 882),
(942, '241710301066', 'SALWA AYU', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:10', '2025-10-29 04:36:10', 883),
(943, '241710301067', 'RISKI HIDAYAH', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:10', '2025-10-29 04:36:10', 884),
(944, '241710301068', 'DEWI LESTARI', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:10', '2025-10-29 04:36:10', 885),
(945, '241710301069', 'MUHAMMAD FAJAR', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:10', '2025-10-29 04:36:10', 886),
(946, '241710301070', 'FITRI HIDAYAH', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:10', '2025-10-29 04:36:10', 887),
(947, '241710301071', 'SALMA NUR HIDAYAH', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:10', '2025-10-29 04:36:10', 888),
(948, '241710301072', 'RIZKY PUTRA', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:10', '2025-10-29 04:36:10', 889),
(949, '241710301073', 'RAHMA AMALIA', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:10', '2025-10-29 04:36:10', 890),
(950, '241710301074', 'PUTRI NUR HIDAYAH', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:10', '2025-10-29 04:36:10', 891),
(951, '241710301075', 'MUHAMMAD RIDHO', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:10', '2025-10-29 04:36:10', 892),
(952, '241710301076', 'DEWI MAULIDA', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:10', '2025-10-29 04:36:10', 893),
(953, '241710301077', 'RINA HIDAYAH', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:10', '2025-10-29 04:36:10', 894),
(954, '241710301078', 'ILHAM RAHMAN', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:10', '2025-10-29 04:36:10', 895),
(955, '241710301079', 'SALWA LESTARI', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:10', '2025-10-29 04:36:10', 896),
(956, '241710301080', 'PUTRI NUR HIDAYAH', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:10', '2025-10-29 04:36:10', 897),
(957, '249919990391', 'AFI DWI RAMADHANI', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:10', '2025-10-29 04:36:10', 898),
(958, '249919990396', 'MUHAMMAD YUSRON FEBRIAN', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:10', '2025-10-29 04:36:10', 899),
(959, '249919990397', 'DIVIA JUWITA ZAHRA', 2024, 24251, NULL, 'AKTIF', '2025-10-29 04:36:10', '2025-10-29 04:36:10', 900),
(960, '251710301001', 'GIONY SOFIA BALQIS AZZAHRA', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:38', '2025-10-29 04:41:38', 901),
(961, '251710301002', 'RIDWAN DWI SETIAWAN', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:38', '2025-10-29 04:41:38', 902),
(962, '251710301003', 'TASYA IKA OCTAFIA DEVI', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:38', '2025-10-29 04:41:38', 903),
(963, '251710301004', 'REFIANA AURELIA SAPHIRA', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:38', '2025-10-29 04:41:38', 904),
(964, '251710301005', 'NASYWA KIRANA', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:38', '2025-10-29 04:41:38', 905),
(965, '251710301006', 'NIA AMELIA', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:38', '2025-10-29 04:41:38', 906),
(966, '251710301007', 'NOVA YUNI LESTARI', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:38', '2025-10-29 04:41:38', 907),
(967, '251710301008', 'SETYA NIKITA APRILIA ARUMINGTYAS', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:38', '2025-10-29 04:41:38', 908),
(968, '251710301009', 'VIKI DWI LESTARI', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:38', '2025-10-29 04:41:38', 909),
(969, '251710301010', 'MOHAMAD NOUVAL ZAMZAMI', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:38', '2025-10-29 04:41:38', 910),
(970, '251710301011', 'RAHMA YULIANTI', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:38', '2025-10-29 04:41:38', 911),
(971, '251710301012', 'PUTRI INTAN AMALIA', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:38', '2025-10-29 04:41:38', 912),
(972, '251710301013', 'REVINA EKA AYU PRATIWI', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:39', '2025-10-29 04:41:39', 913),
(973, '251710301014', 'RESTI DEWI KUSUMAWATI', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:39', '2025-10-29 04:41:39', 914),
(974, '251710301015', 'SALMA NURUL HIDAYAH', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:39', '2025-10-29 04:41:39', 915),
(975, '251710301016', 'MUHAMMAD FARHAN', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:39', '2025-10-29 04:41:39', 916),
(976, '251710301017', 'RISKA AMALIA', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:39', '2025-10-29 04:41:39', 917),
(977, '251710301018', 'RAHMA PUTRI SEPTIANI', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:39', '2025-10-29 04:41:39', 918),
(978, '251710301019', 'DEWI NUR HIDAYAH', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:39', '2025-10-29 04:41:39', 919),
(979, '251710301020', 'PUTRI ANANDA LESTARI', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:39', '2025-10-29 04:41:39', 920),
(980, '251710301021', 'RISKI ADITYA', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:39', '2025-10-29 04:41:39', 921),
(981, '251710301022', 'MUHAMMAD RIZKY', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:39', '2025-10-29 04:41:39', 922),
(982, '251710301023', 'FITRI AMALIA', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:39', '2025-10-29 04:41:39', 923),
(983, '251710301024', 'SALWA RAHAYU', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:39', '2025-10-29 04:41:39', 924),
(984, '251710301025', 'NURUL HIDAYAH', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:39', '2025-10-29 04:41:39', 925),
(985, '251710301026', 'RINA PUTRI MAULIDA', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:39', '2025-10-29 04:41:39', 926),
(986, '251710301027', 'DIANA NUR HIDAYAH', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:39', '2025-10-29 04:41:39', 927),
(987, '251710301028', 'ANDIKA RAHMAN', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:39', '2025-10-29 04:41:39', 928),
(988, '251710301029', 'FIRMAN SEPTIAN', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:39', '2025-10-29 04:41:39', 929),
(989, '251710301030', 'PUTRI MAULIDA', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:39', '2025-10-29 04:41:39', 930),
(990, '251710301031', 'DEWI SEPTIA', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:39', '2025-10-29 04:41:39', 931),
(991, '251710301032', 'SALWA MAULIDA', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:39', '2025-10-29 04:41:39', 932),
(992, '251710301033', 'RINA HIDAYAH', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:39', '2025-10-29 04:41:39', 933),
(993, '251710301034', 'NABILAH AMALIA', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:39', '2025-10-29 04:41:40', 934),
(994, '251710301035', 'MUHAMMAD RIDHO', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:40', '2025-10-29 04:41:40', 935),
(995, '251710301036', 'RAHMA NUR HIDAYAH', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:40', '2025-10-29 04:41:40', 936),
(996, '251710301037', 'PUTRI LESTARI', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:40', '2025-10-29 04:41:40', 937),
(997, '251710301038', 'FITRI AMALIA', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:40', '2025-10-29 04:41:40', 938),
(998, '251710301039', 'DEWI MAULIDA', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:40', '2025-10-29 04:41:40', 939),
(999, '251710301040', 'RINA SEPTIA', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:40', '2025-10-29 04:41:40', 940),
(1000, '251710301041', 'MUHAMMAD ILHAM', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:40', '2025-10-29 04:41:40', 941),
(1001, '251710301042', 'SALWA ANANDA PUTRI', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:40', '2025-10-29 04:41:40', 942),
(1002, '251710301043', 'FADHIL RAHMAN', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:40', '2025-10-29 04:41:40', 943),
(1003, '251710301044', 'RISKI HIDAYAT', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:40', '2025-10-29 04:41:40', 944),
(1004, '251710301045', 'DEWI LESTARI', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:40', '2025-10-29 04:41:40', 945),
(1005, '251710301046', 'RINA NUR HIDAYAH', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:40', '2025-10-29 04:41:40', 946),
(1006, '251710301047', 'PUTRI AMALIA', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:40', '2025-10-29 04:41:40', 947),
(1007, '251710301048', 'RAHMA PUTRI', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:40', '2025-10-29 04:41:40', 948),
(1008, '251710301049', 'ILHAM MAULANA', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:40', '2025-10-29 04:41:40', 949),
(1009, '251710301050', 'SALWA NUR HIDAYAH', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:40', '2025-10-29 04:41:40', 950),
(1010, '251710301051', 'DEWI MAULIDA', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:40', '2025-10-29 04:41:40', 951),
(1011, '251710301052', 'PUTRI SEPTIANA', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:40', '2025-10-29 04:41:40', 952),
(1012, '251710301053', 'RINA AMALIA', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:40', '2025-10-29 04:41:40', 953),
(1013, '251710301054', 'FITRI HIDAYAH', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:40', '2025-10-29 04:41:40', 954),
(1014, '251710301055', 'SALWA RAHMA', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:40', '2025-10-29 04:41:40', 955),
(1015, '251710301056', 'RAHMA DEWI', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:40', '2025-10-29 04:41:41', 956),
(1016, '251710301057', 'MUHAMMAD FADHIL', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:41', '2025-10-29 04:41:41', 957),
(1017, '251710301058', 'RISMA PUTRI', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:41', '2025-10-29 04:41:41', 958),
(1018, '251710301059', 'DEWI NUR HIDAYAH', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:41', '2025-10-29 04:41:41', 959),
(1019, '251710301060', 'PUTRI MAULIDA', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:41', '2025-10-29 04:41:41', 960),
(1020, '251710301061', 'RINA SEPTIANI', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:41', '2025-10-29 04:41:41', 961),
(1021, '251710301062', 'RAHMA PUTRI', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:41', '2025-10-29 04:41:41', 962),
(1022, '251710301063', 'SALWA MAULIDA', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:41', '2025-10-29 04:41:41', 963),
(1023, '251710301064', 'ILHAM RAHMAN', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:41', '2025-10-29 04:41:41', 964),
(1024, '251710301065', 'DEWI AMALIA', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:41', '2025-10-29 04:41:41', 965),
(1025, '251710301066', 'PUTRI SEPTIA', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:41', '2025-10-29 04:41:41', 966),
(1026, '251710301067', 'RAHMA HIDAYAH', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:41', '2025-10-29 04:41:41', 967),
(1027, '251710301068', 'FITRI AMALIA', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:41', '2025-10-29 04:41:41', 968),
(1028, '251710301069', 'RINA NUR HIDAYAH', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:41', '2025-10-29 04:41:41', 969),
(1029, '251710301070', 'DEWI LESTARI', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:41', '2025-10-29 04:41:41', 970),
(1030, '251710301071', 'PUTRI MAULIDA', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:41', '2025-10-29 04:41:41', 971),
(1031, '251710301072', 'RAHMA PUTRI', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:41', '2025-10-29 04:41:41', 972),
(1032, '251710301073', 'FIRMAN HIDAYAT', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:41', '2025-10-29 04:41:41', 973),
(1033, '251710301074', 'ILHAM MAULANA', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:41', '2025-10-29 04:41:41', 974),
(1034, '251710301075', 'SALWA NUR HIDAYAH', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:41', '2025-10-29 04:41:41', 975),
(1035, '251710301076', 'DEWI LESTARI', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:41', '2025-10-29 04:41:41', 976),
(1036, '251710301077', 'PUTRI ANANDA', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:41', '2025-10-29 04:41:41', 977),
(1037, '251710301078', 'RAHMA MAULIDA', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:41', '2025-10-29 04:41:41', 978),
(1038, '251710301079', 'RISKI SEPTIAN', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:41', '2025-10-29 04:41:42', 979),
(1039, '251710301080', 'FIRMAN AMALIA', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:42', '2025-10-29 04:41:42', 980),
(1040, '251710301081', 'DEWI SEPTIANA', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:42', '2025-10-29 04:41:42', 981),
(1041, '251710301082', 'PUTRI LESTARI', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:42', '2025-10-29 04:41:42', 982),
(1042, '251710301083', 'RINA NUR HIDAYAH', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:42', '2025-10-29 04:41:42', 983),
(1043, '251710301084', 'SALWA PUTRI', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:42', '2025-10-29 04:41:42', 984),
(1044, '251710301085', 'RAHMA HIDAYAH', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:42', '2025-10-29 04:41:42', 985),
(1045, '251710301086', 'FITRI AMALIA', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:42', '2025-10-29 04:41:42', 986),
(1046, '251710301087', 'MUHAMMAD ILHAM', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:42', '2025-10-29 04:41:42', 987),
(1047, '251710301088', 'DEWI NUR HIDAYAH', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:42', '2025-10-29 04:41:42', 988),
(1048, '251710301089', 'PUTRI MAULIDA', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:42', '2025-10-29 04:41:42', 989),
(1049, '251710301090', 'RINA SEPTIANI', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:42', '2025-10-29 04:41:42', 990),
(1050, '251710301091', 'RAHMA AMALIA', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:42', '2025-10-29 04:41:42', 991),
(1051, '251710301092', 'FITRI HIDAYAH', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:42', '2025-10-29 04:41:42', 992),
(1052, '251710301093', 'SALWA RAHAYU', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:42', '2025-10-29 04:41:42', 993),
(1053, '251710301094', 'DEWI MAULIDA', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:42', '2025-10-29 04:41:42', 994),
(1054, '251710301095', 'PUTRI SEPTIANI', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:42', '2025-10-29 04:41:42', 995),
(1055, '251710301096', 'MUHAMMAD FADHIL', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:42', '2025-10-29 04:41:42', 996),
(1056, '251710301097', 'RISKI AMALIA', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:42', '2025-10-29 04:41:42', 997),
(1057, '251710301098', 'RAHMA HIDAYAH', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:42', '2025-10-29 04:41:42', 998),
(1058, '251710301099', 'FITRI AMALIA', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:42', '2025-10-29 04:41:42', 999),
(1059, '251710301100', 'DEWI SEPTIA', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:42', '2025-10-29 04:41:42', 1000),
(1060, '251710301101', 'PUTRI MAULIDA', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:42', '2025-10-29 04:41:42', 1001),
(1061, '251710301102', 'RINA NUR HIDAYAH', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:42', '2025-10-29 04:41:43', 1002),
(1062, '251710301103', 'SALWA ANANDA', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:43', '2025-10-29 04:41:43', 1003),
(1063, '251710301104', 'RAHMA PUTRI', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:43', '2025-10-29 04:41:43', 1004),
(1064, '251710301105', 'FITRI HIDAYAH', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:43', '2025-10-29 04:41:43', 1005),
(1065, '251710301106', 'DEWI LESTARI', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:43', '2025-10-29 04:41:43', 1006),
(1066, '251710301107', 'RISKI HIDAYAT', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:43', '2025-10-29 04:41:43', 1007),
(1067, '251710301108', 'MUHAMMAD RIDHO', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:43', '2025-10-29 04:41:43', 1008),
(1068, '251710301109', 'DEWI AMALIA', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:43', '2025-10-29 04:41:43', 1009),
(1069, '251710301110', 'PUTRI SEPTIANA', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:43', '2025-10-29 04:41:43', 1010),
(1070, '251710301111', 'RAHMA HIDAYAH', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:43', '2025-10-29 04:41:43', 1011),
(1071, '251710301112', 'FIRMAN RAHMAN', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:43', '2025-10-29 04:41:43', 1012),
(1072, '251710301113', 'DEWI MAULIDA', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:43', '2025-10-29 04:41:43', 1013),
(1073, '251710301114', 'PUTRI NUR HIDAYAH', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:43', '2025-10-29 04:41:43', 1014),
(1074, '251710301115', 'RAHMA AMALIA', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:43', '2025-10-29 04:41:43', 1015),
(1075, '251710301116', 'SALWA MAULIDA', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:43', '2025-10-29 04:41:43', 1016),
(1076, '251710301117', 'RISKI SEPTIA', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:43', '2025-10-29 04:41:43', 1017),
(1077, '251710301118', 'MUHAMMAD ILHAM', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:43', '2025-10-29 04:41:43', 1018),
(1078, '251710301119', 'DEWI HIDAYAH', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:43', '2025-10-29 04:41:43', 1019),
(1079, '251710301120', 'PUTRI SEPTIANA', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:43', '2025-10-29 04:41:43', 1020),
(1080, '251710301121', 'RINA HIDAYAH', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:43', '2025-10-29 04:41:43', 1021),
(1081, '251710301122', 'SALWA NUR HIDAYAH', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:43', '2025-10-29 04:41:43', 1022),
(1082, '251710301123', 'RAHMA MAULIDA', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:43', '2025-10-29 04:41:43', 1023),
(1083, '251710301124', 'FITRI AMALIA', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:43', '2025-10-29 04:41:43', 1024),
(1084, '251710301125', 'DEWI SEPTIA', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:44', '2025-10-29 04:41:44', 1025),
(1085, '251710301126', 'RISKI MAULANA', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:44', '2025-10-29 04:41:44', 1026),
(1086, '251710301127', 'MUHAMMAD RIZKY', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:44', '2025-10-29 04:41:44', 1027),
(1087, '251710301128', 'PUTRI ANANDA', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:44', '2025-10-29 04:41:44', 1028),
(1088, '251710301129', 'RAHMA AMALIA', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:44', '2025-10-29 04:41:44', 1029),
(1089, '251710301130', 'SALWA LESTARI', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:44', '2025-10-29 04:41:44', 1030),
(1090, '251710301131', 'DEWI HIDAYAH', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:44', '2025-10-29 04:41:44', 1031),
(1091, '251710301132', 'PUTRI SEPTIANI', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:44', '2025-10-29 04:41:44', 1032),
(1092, '251710301133', 'RINA MAULIDA', 2025, 25261, NULL, 'AKTIF', '2025-10-29 04:41:44', '2025-10-29 04:41:44', 1033),
(1093, '251710301134', 'MUHAMMAD RAFIF BAHTIAR', 2025, 25261, NULL, 'CUTI', '2025-10-29 04:41:44', '2025-10-29 04:41:44', 1034),
(1094, '201710301001', 'FIFI MULYANINGRUM', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:30', '2025-10-29 07:08:30', 354),
(1095, '201710301002', 'LULUK SAPUTRI', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:30', '2025-10-29 07:08:30', 355),
(1096, '201710301003', 'DETA RATNA NINGTYAS', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:30', '2025-10-29 07:08:30', 356),
(1097, '201710301004', 'NUR \'AINI MARDHI UTAMI', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:30', '2025-10-29 07:08:30', 357),
(1098, '201710301005', 'VIA SHAFY ZAHIRA', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:30', '2025-10-29 07:08:30', 358),
(1099, '201710301006', 'FRANSISKA CANDRA ARISTANTI', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:30', '2025-10-29 07:08:30', 359),
(1100, '201710301007', 'MARISATUT DINIYAH', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:30', '2025-10-29 07:08:30', 360),
(1101, '201710301008', 'TEGUH PRAYITNO', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:30', '2025-10-29 07:08:30', 361),
(1102, '201710301009', 'TIARA DWI KUSUMA PUTRI', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:30', '2025-10-29 07:08:30', 362),
(1103, '201710301010', 'ALISSA QOTRUNNADA', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:30', '2025-10-29 07:08:30', 363),
(1104, '201710301011', 'VIVIN TYASTININGSIH', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:30', '2025-10-29 07:08:30', 364),
(1105, '201710301012', 'HIFDZIL ADILA', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:30', '2025-10-29 07:08:30', 365),
(1106, '201710301013', 'Yusriyyah Vika Rahmadhani', 2020, 20211, NULL, 'NON-AKTIF', '2025-10-29 07:08:30', '2025-10-29 07:08:30', 366),
(1107, '201710301014', 'CINDY PUTERI ANGGRAENI', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:30', '2025-10-29 07:08:30', 367),
(1108, '201710301015', 'VANIA MELYSSA SAWINDRA', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:30', '2025-10-29 07:08:30', 368),
(1109, '201710301016', 'NUR HAYATI', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:30', '2025-10-29 07:08:30', 369),
(1110, '201710301017', 'ANIKA RATNAWATI', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:30', '2025-10-29 07:08:30', 370),
(1111, '201710301018', 'SUPRATIANA RAHAYU', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:30', '2025-10-29 07:08:30', 371),
(1112, '201710301019', 'EKA NUR JANNAH', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:30', '2025-10-29 07:08:30', 372),
(1113, '201710301020', 'DEWI ARUM PUSPITANIA', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:30', '2025-10-29 07:08:30', 373),
(1114, '201710301021', 'NOVITRI ALFIANI', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:30', '2025-10-29 07:08:30', 374),
(1115, '201710301022', 'NI\'MATUL MUFAROHAH', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:30', '2025-10-29 07:08:30', 375),
(1116, '201710301023', 'Devy Lailatul Putri', 2020, 20211, NULL, 'NON-AKTIF', '2025-10-29 07:08:30', '2025-10-29 07:08:30', 376),
(1117, '201710301024', 'IMANY FELASHOF TEOFANI', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:30', '2025-10-29 07:08:30', 377),
(1118, '201710301025', 'ANIS SETIAWATI', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:30', '2025-10-29 07:08:30', 378),
(1119, '201710301026', 'MAR\'ATUS SOLIQA MAKRI FATULLOH', 2020, 20211, NULL, 'AKTIF', '2025-10-29 07:08:30', '2025-10-29 07:08:30', 379),
(1120, '201710301027', 'SEPTIANING TYAS KUSUMAWARDANI', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:30', '2025-10-29 07:08:30', 380),
(1121, '201710301028', 'FITRA TINNAJIZAH', 2020, 20211, NULL, 'AKTIF', '2025-10-29 07:08:30', '2025-10-29 07:08:30', 381),
(1122, '201710301029', 'LUVITA AGOESTINA RUMOKOY', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:30', '2025-10-29 07:08:30', 382),
(1123, '201710301030', 'ADELIANA', 2020, 20211, NULL, 'AKTIF', '2025-10-29 07:08:30', '2025-10-29 07:08:30', 383),
(1124, '201710301031', 'ANGGUN DWI PRAMUDITA', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:30', '2025-10-29 07:08:30', 384),
(1125, '201710301032', 'FIRYAL', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:30', '2025-10-29 07:08:30', 385),
(1126, '201710301033', 'PUTRI IZZATUL AZKA', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:30', '2025-10-29 07:08:30', 386),
(1127, '201710301034', 'DIAN RAHMATULLAH', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:30', '2025-10-29 07:08:30', 387),
(1128, '201710301035', 'KANAYA SUKMA ARDHA SUWARDANI', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:30', '2025-10-29 07:08:30', 388),
(1129, '201710301036', 'MOCHAMMAD RIZKY', 2020, 20211, NULL, 'AKTIF', '2025-10-29 07:08:30', '2025-10-29 07:08:30', 389),
(1130, '201710301037', 'DEA CITRA TAURINE VIVASYA', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:30', '2025-10-29 07:08:30', 390),
(1131, '201710301038', 'MARSA SUCI NURMALASARI', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:30', '2025-10-29 07:08:30', 391),
(1132, '201710301039', 'IRVAN MAULANA MALIK IBRAHIM', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:30', '2025-10-29 07:08:30', 392),
(1133, '201710301040', 'ENGGAR LANTANG MAHENDRA', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:30', '2025-10-29 07:08:30', 393),
(1134, '201710301041', 'VINDHA DHILA WULANDARI', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:30', '2025-10-29 07:08:30', 394),
(1135, '201710301042', 'M. NAJIH AR ROUF', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:30', '2025-10-29 07:08:30', 395),
(1136, '201710301043', 'QUENY OSCALANI', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:30', '2025-10-29 07:08:30', 396),
(1137, '201710301044', 'SEMESTA TUALANG CAHAYA', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:30', '2025-10-29 07:08:30', 397),
(1138, '201710301045', 'CHANTIKA PUTRI NUR AZIZAH', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:30', '2025-10-29 07:08:30', 398),
(1139, '201710301046', 'ELISA FEBRIANA', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:30', '2025-10-29 07:08:30', 399),
(1140, '201710301047', 'VINKA OKTAVIA PRAMESTI', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:30', '2025-10-29 07:08:30', 400),
(1141, '201710301048', 'MITHA DEVARIS DEWI ANJANI', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:30', '2025-10-29 07:08:30', 401),
(1142, '201710301049', 'SALSABILA', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:30', '2025-10-29 07:08:30', 402),
(1143, '201710301050', 'RITFAN VALENTINO FEBRIANSYAH', 2020, 20211, NULL, 'AKTIF', '2025-10-29 07:08:30', '2025-10-29 07:08:30', 403),
(1144, '201710301051', 'SAFRUDIN BACHTIAR CAHYA RAMADHANI', 2020, 20211, NULL, 'NON-AKTIF', '2025-10-29 07:08:30', '2025-10-29 07:08:30', 404),
(1145, '201710301052', 'LUTFI MAHARANI', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:30', '2025-10-29 07:08:30', 405),
(1146, '201710301053', 'FIRMAN SETIA WIBOWO', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:30', '2025-10-29 07:08:30', 406),
(1147, '201710301054', 'RINA ANGGRAINI', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:30', '2025-10-29 07:08:30', 407),
(1148, '201710301055', 'RIFDAH NADA NURJANNAH', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:30', '2025-10-29 07:08:30', 408),
(1149, '201710301056', 'YURIS PUTRI SALSABILA', 2020, 20211, NULL, 'AKTIF', '2025-10-29 07:08:30', '2025-10-29 07:08:30', 409),
(1150, '201710301057', 'MUHAMMAD RIZKY ALAMSYAH', 2020, 20211, NULL, 'AKTIF', '2025-10-29 07:08:30', '2025-10-29 07:08:30', 410),
(1151, '201710301058', 'ADILAH DEVIRA FRANSNA', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:30', '2025-10-29 07:08:30', 411),
(1152, '201710301059', 'OLA RISKA APRILIA INTAN AGHATA', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:30', '2025-10-29 07:08:30', 412),
(1153, '201710301060', 'ANNYSA DELIYANA', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:30', '2025-10-29 07:08:30', 413),
(1154, '201710301061', 'SHINTA PRAMUDITA', 2020, 20211, NULL, 'AKTIF', '2025-10-29 07:08:30', '2025-10-29 07:08:30', 414),
(1155, '201710301062', 'ANAS FAHRUDDIN', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:30', '2025-10-29 07:08:30', 415),
(1156, '201710301063', 'PUTRI SEKAR KINASIH', 2020, 20211, NULL, 'AKTIF', '2025-10-29 07:08:30', '2025-10-29 07:08:30', 416),
(1157, '201710301064', 'ADINE RARA SALSABILA', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:30', '2025-10-29 07:08:30', 417),
(1158, '201710301065', 'PRASETYA AGUSTIAN', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:30', '2025-10-29 07:08:30', 418),
(1159, '201710301066', 'SHOFIA NUR FADHILAH', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:30', '2025-10-29 07:08:30', 419),
(1160, '201710301067', 'APRILIA HERAWATI PRAMONO', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:30', '2025-10-29 07:08:30', 420),
(1161, '201710301068', 'DWI BUANA PERMATASARI', 2020, 20211, NULL, 'AKTIF', '2025-10-29 07:08:30', '2025-10-29 07:08:30', 421),
(1162, '201710301069', 'INDIRA MAHARANI HIDAYAT', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:30', '2025-10-29 07:08:30', 422),
(1163, '201710301070', 'JUNAID MOHAMMAD ZAIN', 2020, 20211, NULL, 'AKTIF', '2025-10-29 07:08:30', '2025-10-29 07:08:30', 423),
(1164, '201710301071', 'KRISNA MARCHEN BAYU IRAWAN', 2020, 20211, NULL, 'AKTIF', '2025-10-29 07:08:30', '2025-10-29 07:08:30', 424),
(1165, '201710301072', 'BIMA ARYA NUGRAHA', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:30', '2025-10-29 07:08:30', 425),
(1166, '201710301073', 'RENALDI AHLI PRAYOGI', 2020, 20211, NULL, 'NON-AKTIF', '2025-10-29 07:08:31', '2025-10-29 07:08:31', 426),
(1167, '201710301074', 'DWI SETIYAWAN', 2020, 20211, NULL, 'AKTIF', '2025-10-29 07:08:31', '2025-10-29 07:08:31', 427),
(1168, '201710301075', 'MOCH. KHUSNI MUBAROCH', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:31', '2025-10-29 07:08:31', 428),
(1169, '201710301076', 'BALQIIS LUTHFIYYAH MAHARANI', 2020, 20211, NULL, 'AKTIF', '2025-10-29 07:08:31', '2025-10-29 07:08:31', 429),
(1170, '201710301077', 'SESARINO PRASETYA PRAMESWARA', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:31', '2025-10-29 07:08:31', 430),
(1171, '201710301078', 'SALMA MUFLIH NURIDA', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:31', '2025-10-29 07:08:31', 431),
(1172, '201710301079', 'AMALIYA PUTRI WAHYUNI', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:31', '2025-10-29 07:08:31', 432),
(1173, '201710301080', 'FITRI WULANDARI', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:31', '2025-10-29 07:08:31', 433),
(1174, '201710301081', 'RYANDA IQBALDI', 2020, 20211, NULL, 'NON-AKTIF', '2025-10-29 07:08:31', '2025-10-29 07:08:31', 434),
(1175, '201710301082', 'MUHAMMAD ALFI MUBAROQ', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:31', '2025-10-29 07:08:31', 435),
(1176, '201710301083', 'WILDAN NADZIR KHAIR', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:31', '2025-10-29 07:08:31', 436),
(1177, '201710301084', 'FIRDATUL JANNAH', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:31', '2025-10-29 07:08:31', 437),
(1178, '201710301085', 'LUTHFIYAH KHAIRUNNISA', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:31', '2025-10-29 07:08:31', 438),
(1179, '201710301086', 'RIZKI AMINULLAH MILLENIO DAROZAT', 2020, 20211, NULL, 'AKTIF', '2025-10-29 07:08:31', '2025-10-29 07:08:31', 439),
(1180, '201710301087', 'MUHAMMAD SADAM PUTRA SAPTA', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:31', '2025-10-29 07:08:31', 440),
(1181, '201710301088', 'FAROUQ ALFIAN SALIM', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:31', '2025-10-29 07:08:31', 441),
(1182, '201710301089', 'ACHMAD SHORFI ALDINI', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:31', '2025-10-29 07:08:31', 442),
(1183, '201710301090', 'AYIHWA LIANJI MEGA', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:31', '2025-10-29 07:08:31', 443),
(1184, '201710301091', 'MUHAMAD ARBY FAUZAN ADRIANI', 2020, 20211, NULL, 'NON-AKTIF', '2025-10-29 07:08:31', '2025-10-29 07:08:31', 444),
(1185, '201710301092', 'ABDILLAH FAQIH ARIDA', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:31', '2025-10-29 07:08:31', 445),
(1186, '201710301093', 'RIZKI AMMANDA PERTIWI', 2020, 20211, NULL, 'AKTIF', '2025-10-29 07:08:31', '2025-10-29 07:08:31', 446),
(1187, '201710301094', 'FAIZI AKMAL RIZQULLAH', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:31', '2025-10-29 07:08:31', 447),
(1188, '201710301095', 'HAIDAR ALI', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:31', '2025-10-29 07:08:31', 448),
(1189, '201710301096', 'AFIDA NURHIDAYATI', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:31', '2025-10-29 07:08:31', 449),
(1190, '201710301097', 'MADE NATAJAYA', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:31', '2025-10-29 07:08:31', 450),
(1191, '201710301098', 'NAUFAL RIZKY WAHYU PUTRANTO', 2020, 20211, NULL, 'AKTIF', '2025-10-29 07:08:31', '2025-10-29 07:08:31', 451),
(1192, '201710301099', 'MUCHAMMAD FAJAR ASROFI', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:31', '2025-10-29 07:08:31', 452),
(1193, '201710301100', 'OIVA YASA AMANDA', 2020, 20211, NULL, 'AKTIF', '2025-10-29 07:08:31', '2025-10-29 07:08:31', 453),
(1194, '201710301101', 'ESSA TRI HANDAYANI', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:31', '2025-10-29 07:08:31', 454),
(1195, '201710301102', 'Muhammad Sami Makarim Gena', 2020, 20211, NULL, 'NON-AKTIF', '2025-10-29 07:08:31', '2025-10-29 07:08:31', 455),
(1196, '201710301103', 'KHOIRUL UMAM', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:31', '2025-10-29 07:08:31', 456),
(1197, '201710301104', 'QORIAH DELA WASILAH', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:31', '2025-10-29 07:08:31', 457),
(1198, '201710301105', 'ASHON ALROSSY UNERLY', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:31', '2025-10-29 07:08:31', 458),
(1199, '201710301106', 'SIVA PUTRI SETYANINGTIAS', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:31', '2025-10-29 07:08:31', 459),
(1200, '201710301107', 'LIESIA HANAGARI', 2020, 20211, NULL, 'LULUS', '2025-10-29 07:08:31', '2025-10-29 07:08:31', 460);

-- --------------------------------------------------------

--
-- Table structure for table `titles`
--

CREATE TABLE `titles` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `title` text NOT NULL,
  `abstract` text NOT NULL,
  `keywords` text NOT NULL,
  `document_path` varchar(255) DEFAULT NULL,
  `status` enum('MENUNGGU','DITERIMA','DITOLAK','PERLU_REVISI') DEFAULT 'MENUNGGU',
  `notes` text DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT current_timestamp(),
  `verified_at` timestamp NULL DEFAULT NULL,
  `verified_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `role` enum('superadmin','kombi','dosen_pembimbing','dosen_penguji','mahasiswa','penguji_eksternal') NOT NULL,
  `nip` varchar(20) DEFAULT NULL,
  `nim` varchar(15) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `name`, `role`, `nip`, `nim`, `created_at`, `updated_at`) VALUES
(1, 'superadmin', '$2y$10$dlLoSiTBCc5qLI/1sYO3Euc5ZaF5KxoyJUQvI6Sd5B1vXeox/7ml.', 'Super Admin', 'superadmin', NULL, NULL, '2025-10-29 03:10:59', '2025-11-02 00:29:21'),
(2, 'kombi', '$2y$10$dlLoSiTBCc5qLI/1sYO3Euc5ZaF5KxoyJUQvI6Sd5B1vXeox/7ml.', 'Winda Amilia, S.TP., M.Sc.', 'kombi', NULL, NULL, '2025-10-29 03:10:59', '2025-11-03 07:51:32'),
(3, 'pembimbing1', '$2y$10$dlLoSiTBCc5qLI/1sYO3Euc5ZaF5KxoyJUQvI6Sd5B1vXeox/7ml.', 'Dosen Pembimbing 1', 'dosen_pembimbing', NULL, NULL, '2025-10-29 03:10:59', '2025-10-29 03:10:59'),
(4, 'pembimbing2', '$2y$10$dlLoSiTBCc5qLI/1sYO3Euc5ZaF5KxoyJUQvI6Sd5B1vXeox/7ml.', 'Dosen Pembimbing 2', 'dosen_pembimbing', NULL, NULL, '2025-10-29 03:10:59', '2025-10-29 03:10:59'),
(5, 'penguji1', '$2y$10$dlLoSiTBCc5qLI/1sYO3Euc5ZaF5KxoyJUQvI6Sd5B1vXeox/7ml.', 'Dosen Penguji 1', 'dosen_penguji', NULL, NULL, '2025-10-29 03:10:59', '2025-10-29 03:10:59'),
(6, 'penguji2', '$2y$10$dlLoSiTBCc5qLI/1sYO3Euc5ZaF5KxoyJUQvI6Sd5B1vXeox/7ml.', 'Dosen Penguji 2', 'dosen_penguji', NULL, NULL, '2025-10-29 03:10:59', '2025-10-29 03:10:59'),
(7, 'mahasiswa1', '$2y$10$dlLoSiTBCc5qLI/1sYO3Euc5ZaF5KxoyJUQvI6Sd5B1vXeox/7ml.', 'Mahasiswa 1', 'mahasiswa', NULL, NULL, '2025-10-29 03:10:59', '2025-10-29 03:10:59'),
(8, 'mahasiswa2', '$2y$10$dlLoSiTBCc5qLI/1sYO3Euc5ZaF5KxoyJUQvI6Sd5B1vXeox/7ml.', 'Mahasiswa 2', 'mahasiswa', NULL, NULL, '2025-10-29 03:10:59', '2025-10-29 03:10:59'),
(9, 'mahasiswa3', '$2y$10$dlLoSiTBCc5qLI/1sYO3Euc5ZaF5KxoyJUQvI6Sd5B1vXeox/7ml.', 'Mahasiswa 3', 'mahasiswa', NULL, NULL, '2025-10-29 03:10:59', '2025-10-29 03:10:59'),
(10, '199109302025061002', '$2y$10$MnFX04lYpAcgD9J6NMHXge5AuSA/0Hv.ClTJ0XTZiEpiN7F3cgJoG', 'Ahib Assadam, S.TP., M.Si', 'dosen_pembimbing', '199109302025061002', NULL, '2025-10-29 03:32:15', '2025-10-29 03:32:15'),
(11, '200012132025061007', '$2y$10$XwM/nffyj0ZGuMJPJvY3XuJ0GhWjjZLNscUgEGt0VA1NFh1WYKQ7W', 'Alif Rizki Ulil Albab, S.T., M.T.', 'dosen_pembimbing', '200012132025061007', NULL, '2025-10-29 03:32:15', '2025-10-29 03:32:15'),
(12, '198512012019031007', '$2y$10$6aRvzbSnvTfGABD9gJ6Izuk086S5buYMNkhnOHoOVB/lhS0GWP0hy', 'Andi Eko Wiyono, S.TP., M.P', 'dosen_pembimbing', '198512012019031007', NULL, '2025-10-29 03:32:15', '2025-10-29 03:32:15'),
(13, '198204222005011002', '$2y$10$PXO5hLsqNpnZnlnMdkiwhuoGzLl8RFqtoj29TThGCO6w7AMEiio4q', 'Andrew Setiawan Rusdianto, S.TP., MSi', 'dosen_pembimbing', '198204222005011002', NULL, '2025-10-29 03:32:15', '2025-10-29 03:32:15'),
(14, '198803122023211022', '$2y$10$oJFTks8fmiJtx2tspLw63u0kikiPME30M.kbUSH1QOnszY/vjU/96', 'Bertung Suryadharma, S.ST., M.Kom', 'dosen_pembimbing', '198803122023211022', NULL, '2025-10-29 03:32:15', '2025-10-29 04:54:44'),
(15, '199605092025062008', '$2y$10$6IniZ67ogJpuBnDaMgy01.M7eLhd/jwCt0/3FZQ2kRyYTfycRlL.q', 'Diana Nurhayati, S.P., M.Si.', 'dosen_pembimbing', '199605092025062008', NULL, '2025-10-29 03:32:15', '2025-10-29 03:32:15'),
(16, '197505301999031002', '$2y$10$g65bxpCqZnNHGn8JLwVgR.2TIaKp7SYh1uc4uT4O142M.NggiK15O', 'Dr. Bambang Herry Purnomo, S.TP., M.Si', 'dosen_pembimbing', '197505301999031002', NULL, '2025-10-29 03:32:15', '2025-10-29 03:32:15'),
(17, '197902232006042001', '$2y$10$cvr4WSVu7.T2aXuiHJao2OUn5ExMf2c.bXLIJWxKeW1k1hPfv50la', 'Dr. Eka Ruriani, S.TP., M.Si', 'dosen_pembimbing', '197902232006042001', NULL, '2025-10-29 03:32:15', '2025-10-29 03:32:15'),
(18, '198608172023212057', '$2y$10$ul/bfbRgRH0KPK9yGVChG.2oHZlQDAHx7mFSGqcPw0Sf/hL3mSI.C', 'Dr. Nidya Shara Mahardhika, S.TP., M.P', 'dosen_pembimbing', '198608172023212057', NULL, '2025-10-29 03:32:15', '2025-10-29 03:32:15'),
(19, '197107311997022001', '$2y$10$5e4W9tTl2x/OVURNy0iDKOqH/sd3lV.gTNjXkHauS9VeAD7BY.RVG', 'Dr. Nita Kuswardhani, S.TP., M.Eng, IPM', 'dosen_pembimbing', '197107311997022001', NULL, '2025-10-29 03:32:15', '2025-10-29 03:32:15'),
(20, '197207301999031001', '$2y$10$ZMAyGlx5N.HmqsyzTS2aMOPf9osldoubjo5MuUGJfsd52kYm5DUKC', 'Dr. Yuli Wibowo, S.TP., M.Si., IPM', 'dosen_pembimbing', '197207301999031001', NULL, '2025-10-29 03:32:15', '2025-10-29 03:32:15'),
(21, '198910052024061001', '$2y$10$ylj81M2SX8J.Lp.D4J7Jr.9h6zLuDWGw18XnMrO25hCrCUoLQ.i26', 'Leader Firstandika, S.Si., M.T', 'dosen_pembimbing', '198910052024061001', NULL, '2025-10-29 03:32:15', '2025-10-29 03:32:15'),
(22, '200209112024062001', '$2y$10$YQG2eAW1EKmkCkSmEZUwfe.sYdJcN17Mk6QvOUP6LMawnZaOcWT.q', 'Lituhayu Sausan Supartiningrum Yudiansyah S.T. M.P.', 'dosen_pembimbing', '200209112024062001', NULL, '2025-10-29 03:32:15', '2025-10-29 03:32:15'),
(23, '198503232008011002', '$2y$10$k34Ru4psob3Dc17P/XEveuK9A1ABSP70KlB6MF3bfUaGvUsHQagee', 'Miftahul Choiron, S.TP., M.Sc., Ph.D., CIISA', 'dosen_pembimbing', '198503232008011002', NULL, '2025-10-29 03:32:15', '2025-10-29 03:32:15'),
(24, '199712032024061001', '$2y$10$G38AW3Q3miMFZa2JCgklRO5N6KT.gkKoG9UxOdjmAQ0MP5cdEXb5y', 'Muhammad Arga Hita S.T. M.Sc.', 'dosen_pembimbing', '199712032024061001', NULL, '2025-10-29 03:32:15', '2025-10-29 03:32:15'),
(25, '197008031994031004', '$2y$10$r6FkYZV42EBSfXC9sGYeVuWz/.MV8YEUg9UUGqRnFKPpNF2Dhgjs2', 'Prof. Dr. Ida Bagus Suryaningrat, S.TP., MM., IPU, ASEAN.Eng', 'dosen_pembimbing', '197008031994031004', NULL, '2025-10-29 03:32:15', '2025-10-29 03:32:15'),
(26, '199607242025062007', '$2y$10$ZqNUVAbxuzRV6YwIHYy0AuHuzEVGZXOsLGqz1IUtuJQZ01PrFR5QK', 'Shinta Diah Puspaningtyas, S.T., M.T.', 'dosen_pembimbing', '199607242025062007', NULL, '2025-10-29 03:32:15', '2025-10-29 03:32:15'),
(27, '199706032024062003', '$2y$10$nv3ZXUvK8D6PD/P0rHHyeOYNGP0lZPUxenR1vEtmosvx4u.HZ2Vaa', 'Shinta Syafrina Endah Hap Sari S.T. M.P.', 'dosen_pembimbing', '199706032024062003', NULL, '2025-10-29 03:32:15', '2025-10-29 03:32:15'),
(28, '199804112025062009', '$2y$10$h8nKUV1a71zWNEKgkTUjHePtDE.INbFGWEftP3OQXhjZFGAIRDPk6', 'Suwita Tri Prihani, S.T.P., M.P.', 'dosen_pembimbing', '199804112025062009', NULL, '2025-10-29 03:32:15', '2025-10-29 03:32:15'),
(29, '199705242025062007', '$2y$10$1w.mWQPhT.fC1b5zDMqJDuekfEvde1hQVsfZGWYNX3tVrkN9yEzsK', 'Ummu At-Ta\'anny, S.T., M.P', 'dosen_pembimbing', '199705242025062007', NULL, '2025-10-29 03:32:16', '2025-10-29 03:32:16'),
(30, '199608082025061005', '$2y$10$B7BA8K9/IBC8Pg20FACzR.B3Q5tJXvL1bo3tIR.JmUCaTrg3GEUV.', 'Viko Nurluthfiyadi Ni\'maturrakhmat S.T., M.P.', 'dosen_pembimbing', '199608082025061005', NULL, '2025-10-29 03:32:16', '2025-10-29 03:32:16'),
(31, '198303242008012007', '$2y$10$5vjB.vXFwE7lHpCGbxWcQuJjJns7mXiMXImxFLwbxLLJTYjckzBVW', 'Winda Amilia, S.TP., M.Sc.', 'dosen_pembimbing', '198303242008012007', NULL, '2025-10-29 03:32:16', '2025-10-29 03:32:16'),
(32, '', '$2y$10$YcE5w9VwIqoRVowYDlYbl.9VgRtY.Dnh8Zvnh58I5NooimoFZSuxS', '', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:15', '2025-10-29 03:59:15'),
(33, 'NIM', '$2y$10$9Dli5/U35vF0mDT49eA7qOnF3tSGcOSzrkf.PjIChwkl4FRm6U8uO', 'Nama', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:15', '2025-10-29 03:59:15'),
(34, '191710301001', '$2y$10$8IbP5N/WhPXKToKSrsmp6uHDAJlmUuLwc0amoVo32DVWsRREcuR.a', 'NANDA MEGA WULANDARI', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:16', '2025-10-29 03:59:16'),
(35, '191710301002', '$2y$10$pr3Ec1PzfEHW7Y52kb681ev94qzCAUcsEkHshJC.hHSPXpJ2s7iIO', 'TRI RIWAYATI SUDARMONO', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:16', '2025-10-29 03:59:16'),
(36, '191710301003', '$2y$10$htEPU2bETLeUw2tw2HyTAe4jrh8TBGI7nYh2Az.ZYIMjH6mW0zX9C', 'ANISATUL MUKARAMAH', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:16', '2025-10-29 03:59:16'),
(37, '191710301004', '$2y$10$VBQ.2Pd.oomriqD8w.k/Qe9tykOjx3MKldewBzpu1dv2gF.IlnZWS', 'JULIESA ARSYI SAFILLAH', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:16', '2025-10-29 03:59:16'),
(38, '191710301005', '$2y$10$QrmzVLePG6C0ZpwRts1Hh.c788sBjalVDKhcmrvZNWDArc8ddqkAy', 'HERNI KUSUMAWATI', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:16', '2025-10-29 03:59:16'),
(39, '191710301006', '$2y$10$7SWCDwUjmLi.WlfrmgrhZ.nUMdL25/w1SynVHZye5aO97hNbzh3j2', 'ELLA FITRIA CAHYANINGTYAS', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:16', '2025-10-29 03:59:16'),
(40, '191710301007', '$2y$10$tEW9C17FFKhxKRbL7oPuUuZodDkVkYlnSbVZAPPNYfKXC/zQKQiZm', 'ARIS SYAFA\'ATIN', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:16', '2025-10-29 03:59:16'),
(41, '191710301008', '$2y$10$1pyjOXAk3rN/H.Bazvih8OGHvGtuiO3d0JtFA2b8Qlj1MrwQTsb3C', 'Anisa Putri Nastiti', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:16', '2025-10-29 03:59:16'),
(42, '191710301009', '$2y$10$d4.LzSGYHBXtqrWlGiZlHu422h6XxU/jX8YKfOEC/5lTTjmHUvag2', 'CINTANIA QORRY DEA AFIFA', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:16', '2025-10-29 03:59:16'),
(43, '191710301010', '$2y$10$r4gGNOhg74z92h0ykyc42epUiqbXfgGbBJoaRl.Q49FbNHYok1MIe', 'LAILA ADHANI PUTRI MALIK', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:16', '2025-10-29 03:59:16'),
(44, '191710301011', '$2y$10$Hn1SUHQXKY37COdzu5hkXurTyt5uOXHUlXJLuHKjatFLSmed42.LG', 'SALMAN AL FARISI', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:16', '2025-10-29 03:59:16'),
(45, '191710301012', '$2y$10$BpoQHslLcX5pCxYHQVkT7uDtBbNw7aEBRuRkz0f36znh8LFGeJemO', 'LIDIYA NATASA', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:16', '2025-10-29 03:59:16'),
(46, '191710301013', '$2y$10$qJWp6GJfw8QGKOhn85yuGuGlH5ObOgAuQcB4ocMslqfJr9Y7MgLjq', 'WAHYUNI LISI SEKLIANA', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:16', '2025-10-29 03:59:16'),
(47, '191710301014', '$2y$10$TSzd/BPNzAGb209LWrfXpOMESXXhGOgyZDDp4HcNPMuhGt.F1dWVm', 'Ebia Paray Salman Baretta', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:16', '2025-10-29 03:59:16'),
(48, '191710301015', '$2y$10$31N5rDfAiqsT/vATszVrOuX9nq7v9LKJkoBDhuXG0dJDFtlHSL.qa', 'BIMA EKA SAPUTRA', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:16', '2025-10-29 03:59:16'),
(49, '191710301016', '$2y$10$ybUJM3IvTG/Rkh.VA4M1FOX.63h.GoLOufdRv1l1kjy7Pgfm4frKu', 'TRI ALIF LENTERA CYDA', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:16', '2025-10-29 03:59:16'),
(50, '191710301017', '$2y$10$m93C/du6pjqyjBr1zTRB5u03.kFCL0dTYs8Z6TlyOAPUv4IokQPLy', 'QUSNUL KHOMAROH', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:16', '2025-10-29 03:59:16'),
(51, '191710301018', '$2y$10$9px6Tc.0HWbije8bdzoQzOQ1BwKW1qZ1Npf/.yuQYT07oaCtDtjWi', 'SATRIYA DWI SOEKARNO', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:17', '2025-10-29 03:59:17'),
(52, '191710301019', '$2y$10$5dJHpVjsz.VYkTIJMGtMjeb7RNsG10KjxE0dEutDA5z/T3TJO4Ujm', 'MARHAMAH HILMIAH', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:17', '2025-10-29 03:59:17'),
(53, '191710301020', '$2y$10$KDKDcJO2T3iI.UDKh8EOqeMmZl6qarhCrYzbFdjXopMAKv63LivdG', 'SITI SRI PUSPITASARI', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:17', '2025-10-29 03:59:17'),
(54, '191710301021', '$2y$10$xV7.07k8/NQEEbmPgecIIeIHeDYDCXGzbqPjKfnGzrHJok/GSjFBa', 'SALSABILA MAULIDINA', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:17', '2025-10-29 03:59:17'),
(55, '191710301022', '$2y$10$sgiKjxsUYExZaaevBS1QMOPxEP0y3SyrWipLINmJpGiQhNNrt0lOm', 'DINA HARISAH', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:17', '2025-10-29 03:59:17'),
(56, '191710301023', '$2y$10$JQcVak/B94kAvX/pznoEauW/8oSh.BftuGm.1wdPbSFhdpiMHhm2i', 'ISMAIL YASIN', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:17', '2025-10-29 03:59:17'),
(57, '191710301024', '$2y$10$JjZhas3J1JG.WfTBlptIb.p6Cmzy1hbAiaL2fOojrQ75fPgXc3QBG', 'ERISTHA YUNIANDA TRIANTIKO', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:17', '2025-10-29 03:59:17'),
(58, '191710301025', '$2y$10$h7xrREXYDdr.c7aTnsQ9HOZDTWQ07HJmONdl4Y4GAMnH/cUasIS8W', 'ADITYA AKBAR PRAMUDIA', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:17', '2025-10-29 03:59:17'),
(59, '191710301026', '$2y$10$zEGFC5Y9SjZuawsUw.hF1e/C.C8do6FL3gxQamgZfG.wqHGrp5Br2', 'YUDHISTYA RIFKI RAKHA\' AMRULLAH', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:17', '2025-10-29 03:59:17'),
(60, '191710301027', '$2y$10$JYeIIlT00C.IuAp9P7hclea79On3bgvG1vMpg/4.XcgAwRXT9YwIa', 'AFIF HAMIDI M.', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:18', '2025-10-29 03:59:18'),
(61, '191710301028', '$2y$10$YlMP74GVULbEpFeyd7eQwO.qTrCSQVWHWPX9WN0kMB.wH6JLCKQmm', 'EL DAFFA RAMADIANSYAH', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:18', '2025-10-29 03:59:18'),
(62, '191710301029', '$2y$10$dqCgYhVha2LYr.oCzjFjzOsvmqebYP1QDZfPkvyRO/ZUjP6PBa7Yu', 'M. Ravi Ainul Yaqin', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:18', '2025-10-29 03:59:18'),
(63, '191710301030', '$2y$10$W2bWtGWvNYtF5ToTpS2DwulVLEBMcGGPeEWtQR7b6l23ZRxwxu7my', 'Rafli Daffa Falih Adilah', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:18', '2025-10-29 03:59:18'),
(64, '191710301031', '$2y$10$TR/CxFFoOBHGLAxR8esYWeExKwKNpkcaRlhG9mnYEp889XWjUd2Ty', 'MUHAMMAD ADAM SYAH', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:18', '2025-10-29 03:59:18'),
(65, '191710301032', '$2y$10$iytVhEshYLRhaNpJMD7eLO9JWNXGR4.p0jMGsm4SSh0EGLXCXL0oy', 'EKA ANDINA ZULVITA', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:18', '2025-10-29 03:59:18'),
(66, '191710301033', '$2y$10$j.uEuYTFwRx0EFwiWlUDauXCXZGkisnpuEjC9smgCx8DAdMBGAfbS', 'FAIREZA MAWADDAH', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:18', '2025-10-29 03:59:18'),
(67, '191710301034', '$2y$10$PDQSApffDHQ3bwS8qK1AcuiWQlZyAWpccADl3k8mGEKn4gdpYFLci', 'ANIS SHOFIA MAULIDIYAH', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:18', '2025-10-29 03:59:18'),
(68, '191710301035', '$2y$10$6oZ5CHqYnkVv2I9YcAHABOhKAk8IAKhHki.4UJoEzax.XWe8c4i8u', 'I GEDE SURYA DWIPANGGA', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:18', '2025-10-29 03:59:18'),
(69, '191710301036', '$2y$10$qtFYLCPDtPBAPlGVwuYAu.8DuW62KmpQBtTOYSHLFoYy3tKqqpfSS', 'MOH. IQBAL KAUTSARALIM SETIAJI', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:18', '2025-10-29 03:59:18'),
(70, '191710301037', '$2y$10$jUbLuQgbBiuq.sqCL9VOVepilP/VF0qzxpE7nanr5s3lBRqjy5odG', 'SHEILA FANESHA PRADITYA', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:18', '2025-10-29 03:59:18'),
(71, '191710301038', '$2y$10$4UlkHTgnANpizY//zY79LuvtDSn4lBcIu5c8oPv7wmR25X3dmGUZ6', 'FITRI RAHMA SARI', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:18', '2025-10-29 03:59:18'),
(72, '191710301039', '$2y$10$lYK3G8oK1oq9BaaaWiphEu99UXe1Ll0T7rEObIlvyPViuHB.TIAbS', 'FISABILA APHYCENIA MARINA', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:18', '2025-10-29 03:59:18'),
(73, '191710301040', '$2y$10$dBbL30aEE7Td32wVPsR5LuFsb5ARjmrP6Jm7Akzd8hizVRdSqt9GG', 'DANIA MAZIDATUL HANA', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:18', '2025-10-29 03:59:18'),
(74, '191710301041', '$2y$10$6nUBpWgrVn0PGHxfbl2x7.cp6pw3ohmXVd6XvnA66os6jIK.FSjiW', 'KHUSNUD DIYANAH', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:18', '2025-10-29 03:59:18'),
(75, '191710301042', '$2y$10$T8XKOrozPvB0cuhDUMVkoOBTVIccrWek.qNAWUhqfb06rShCr8xBu', 'THABED THOLIB BALADRAF', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:18', '2025-10-29 03:59:18'),
(76, '191710301043', '$2y$10$MuFTgUB0aMvXsP34HAFIxeprOOsNVTx4f56dNMXTpFxHssRshteCO', 'RAHMAN SANJAY OVA', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:18', '2025-10-29 03:59:18'),
(77, '191710301044', '$2y$10$YXlJYalwdCLNyQ5VMzWTyusVOyqcWbyyuE4L3vbhK67QuxQKIB8VK', 'WINDY NUR ANDRIANI', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:18', '2025-10-29 03:59:18'),
(78, '191710301045', '$2y$10$eBE5Cp80s3/y4.XEbNG3N.R0ziD6md.lTGauI6c/l6Jua7p3LmXhK', 'SINDY ROSA DARMANINGRUM', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:18', '2025-10-29 03:59:18'),
(79, '191710301046', '$2y$10$zNiCsOOKxglVDdR2Wt8RTOBayghpMp/FbbE2CiFwGqhdA64j.xJ1S', 'Pramudya Wardhani', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:18', '2025-10-29 03:59:18'),
(80, '191710301047', '$2y$10$hXLOJtB0QUpRVdMQoUoHnuG96GW2zg9Nby8uEl3t5nEHAYf2IkdRS', 'ASTIFA SHIELLY NADHILA PUTRI', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:18', '2025-10-29 03:59:18'),
(81, '191710301048', '$2y$10$HqZC6UQ2WaEnZIe7J.jJ/ewQj1ByeXG4lyKE.3Rto.2jhGzh4TsSS', 'IDZHAR SEBASTIAN SALIHANAFI', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:18', '2025-10-29 03:59:18'),
(82, '191710301049', '$2y$10$lBzQNwNyJ1NFZairZ/cfOOagIX0NfxdiA6LXybKHi5wON36WDVa/W', 'ILHAM AULIA RACHMAN', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:19', '2025-10-29 03:59:19'),
(83, '191710301050', '$2y$10$czUJjpCCLP0lbYexTM0KveCbq0dcXZDoLhjjkHtlNQmjr92xsNsJe', 'FABBY NIDUFIAS DARAJA', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:19', '2025-10-29 03:59:19'),
(84, '191710301051', '$2y$10$D7.tvecY1nB2Ss9McrQ3R.zZrD5eEkOLg4j68H2lVQ3q23cA/gcs.', 'TRIANA OKTAVIANI NURHARDININGSIH', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:19', '2025-10-29 03:59:19'),
(85, '191710301052', '$2y$10$57RtxuOgfJPSCMweq.Pl/.6nIVVP8S20zzFnn/mmYHwYjj5vCu6wy', 'ULFIA NURUL LATIFAH', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:19', '2025-10-29 03:59:19'),
(86, '191710301053', '$2y$10$wc8W3.N2BUf4PGVm9JCUh.zJ1Lli.G3EWoKCWrlkfRFdQ0V.jR3ym', 'NAJA UMI ROSIDAH', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:19', '2025-10-29 03:59:19'),
(87, '191710301054', '$2y$10$zTGzZRePiRXU0k5pJCF0geWPpcQeBWnEoy70IRyRrRc6xpaSe2/2a', 'INGGRI OKTAVIA WULANDARI', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:19', '2025-10-29 03:59:19'),
(88, '191710301055', '$2y$10$EvgBnx1e8X8ij6qYqKID0u1GUaqlxA6S5UIKmK.lPCIFQnm5yaOLC', 'HANIK WIDIANTI', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:19', '2025-10-29 03:59:19'),
(89, '191710301056', '$2y$10$YptsUAcmdrl5nBtNXRDG.eMtOYct9lnuI1ZItiDy1ISsi8.HiDbtS', 'Fatih Al Hakim', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:19', '2025-10-29 03:59:19'),
(90, '191710301057', '$2y$10$Bc.PmRNsSrImAQRZdeC6k.vPh5gM5CwNz4dopxFvrWB5LvUotteYi', 'MUHAMMAD ALIF FIANDRA', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:19', '2025-10-29 03:59:19'),
(91, '191710301058', '$2y$10$W4L3.vRx903lfrqfveUDGu1v31WDjMIJLqIobA68XWayIeC/c/kAC', 'USAMAH ARYA ARROYAH', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:19', '2025-10-29 03:59:19'),
(92, '191710301059', '$2y$10$89lF7uSNyf.SDG19ihliq.CTT2EsyC4.0sNdWyQdTtBtAvYZMC2Qa', 'FIRMAN AGHISTA RAHMAN', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:19', '2025-10-29 03:59:19'),
(93, '191710301060', '$2y$10$u6ulssj8wjQRt1d92GjL.uNqcFXcfZnp0hE5./YzXecuuoy0c4ERm', 'PUTRI NIKITA APRILIA ARUMINGTYAS', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:19', '2025-10-29 03:59:19'),
(94, '191710301061', '$2y$10$KP16uq5C.e8c.QPwrEm1t.6U7yV.J1sTHV1OS2KyBSyFpLL27FpCe', 'AHMAD ASHIDHIQIE PRAMANA', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:19', '2025-10-29 03:59:19'),
(95, '191710301062', '$2y$10$j8y/r4Dw7.6VPERN.vuUNuxvjBZQMA.xCc2jUhPnzvwYKX9tjzcVW', 'ROHMATUL HIDAYAH', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:19', '2025-10-29 03:59:19'),
(96, '191710301063', '$2y$10$iXxoAPqd7wjwnYLR5slZtu7injWiAsi0I1N5C8/93dW4F6u5XxlBe', 'Noris Baihaqi', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:19', '2025-10-29 03:59:19'),
(97, '191710301064', '$2y$10$ylioK4.DGlX8NuQCvE2mcuepUsgkuCUC6pVUMuFNN95hSjrehLkCa', 'DWI INDAH LESTARI', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:19', '2025-10-29 03:59:19'),
(98, '191710301065', '$2y$10$CHpp6mHzHvUGSqf0ZY00keFM4IDNXbb4lEoHAy8zWviEN.8D4XKli', 'KIRANA KHALDA FAHIRA', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:19', '2025-10-29 03:59:19'),
(99, '191710301066', '$2y$10$8DFboQOiikbWYAehvhhmJusev3FBXJMkzk..EXgzMeKNxzQCHLMqq', 'SINDI AYU WARDANI', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:19', '2025-10-29 03:59:19'),
(100, '191710301067', '$2y$10$lUa8x7Fvu5/4MTxY1gJK1OKIwE631ATe14nTab7q1KvA0ZIhMIekm', 'DEVI ASHILA PURNAMASARI', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:19', '2025-10-29 03:59:19'),
(101, '191710301068', '$2y$10$HIzUtkH6Rc61WTtjt9b9Vuj2tvVya3b4JUvpUzwr0J2wQ4ahkDe4W', 'ANISA RIZKI HERAWATI', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:19', '2025-10-29 03:59:19'),
(102, '191710301069', '$2y$10$UwfhtTt4NC.aVx11y91Iu.vAgl3cD0Th3LKHb/YZLt4lMyqY1Vfvi', 'FENRY ARTHOLIN RAMADHAN', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:19', '2025-10-29 03:59:19'),
(103, '191710301070', '$2y$10$asx8WmUVJAfV4wct0up2j.DwZpuUIj2ZxHbS1eJ64L3Tg/AKrUR9i', 'DEDEN FIRMANSYAH', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:19', '2025-10-29 03:59:19'),
(104, '191710301071', '$2y$10$srjQswJWpwUzcW4i.DLkY.UURprMNFbfNxfxu1.9cWqFGn0O/tB/q', 'ACHMAD ZAMRONIE', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:19', '2025-10-29 03:59:19'),
(105, '191710301072', '$2y$10$79/NNJ8ThSXWL847p1mUiuNor23.FT2QFiUL0DSY2ZqAPw4eYxV3C', 'ZAYYAN NISRINA NASYWA', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:19', '2025-10-29 03:59:19'),
(106, '191710301073', '$2y$10$qOtG4qGfyK72VMU.Gq81L.S8Pwm1pj6oKh5MI881H8qZ916vyT7gu', 'HUSNI KASIM RIDHO SOENARDI', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:20', '2025-10-29 03:59:20'),
(107, '191710301074', '$2y$10$W73t5O.bqT4C.6WEnzu9uuOmGOVNzyeuUn1FZG1hIb.jI7no5x.fa', 'RIDATUL WINDA HIDAYAH', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:20', '2025-10-29 03:59:20'),
(108, '191710301075', '$2y$10$jIBkB6vPsD1D.kmbgveskeZ3yM3tCuamISXgOMokfHkM/pqIt7brG', 'RIZQI DHIA RAMADHAN', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:20', '2025-10-29 03:59:20'),
(109, '191710301076', '$2y$10$Ep9Gep1RIcXqgtdHqwlIaORqPlnXwzMO2IWpwcyCOFn17c3iLh2qa', 'ANUGERAH RIZKY RAHMATULLAH', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:20', '2025-10-29 03:59:20'),
(110, '191710301077', '$2y$10$sRjuf/v6n93grfcCKw9eueXTD8HZJL49BtuLmt44v85HEqH7pq/yu', 'BETTY NUR AULIA FEBRIANTI', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:20', '2025-10-29 03:59:20'),
(111, '191710301078', '$2y$10$2At9U5pvruqRXYeCCg9idu7qrzMvtDiS5cB.pmlusqhdSg21Kk00G', 'NANDA SINTYA FITRI SALSABILA', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:20', '2025-10-29 03:59:20'),
(112, '191710301079', '$2y$10$Dnm7Hf5OAfoJR9UXQWa4cuqctlyO4UszyyGVFwUw7NZLrfOecFkae', 'Ardy Kholify Suhud', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:20', '2025-10-29 03:59:20'),
(113, '191710301080', '$2y$10$UR25/PbsZmxa1w5jP.ZxqOyV/tWzKOmyMKZ6D7BOVEasO9KcEZt..', 'MUHAMMAD OVAN RAMDHANO', 'mahasiswa', NULL, NULL, '2025-10-29 03:59:20', '2025-10-29 03:59:20'),
(354, '201710301001', '$2y$10$uyIweN9jsqKm0KtLBHlYg.G5qMqTaDh6MPoLLjx2TjgB/nVDeNXl.', 'FIFI MULYANINGRUM', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:53', '2025-10-29 04:17:53'),
(355, '201710301002', '$2y$10$/9brn1Dpffkx8a9oiwG4zeIfSyMl9uRVU49lj4XHtvVHWaL3ZHgGW', 'LULUK SAPUTRI', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:54', '2025-10-29 04:17:54'),
(356, '201710301003', '$2y$10$/I09HZx42vzs8NSaYCTuWuX1NHiFrWu9UJ5Ls72FIqb6jOJgT8xym', 'DETA RATNA NINGTYAS', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:54', '2025-10-29 04:17:54'),
(357, '201710301004', '$2y$10$OK1v8F05ATDnm0WWYJr8t.uJArc5JuV1Wn.ApYorRXFD14x7aX.4C', 'NUR \'AINI MARDHI UTAMI', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:54', '2025-10-29 04:17:54'),
(358, '201710301005', '$2y$10$0WyKc4TkTm2Q8ULOrieXHeAygXe12hin2LM4EuszlpKJKhEdDlvtu', 'VIA SHAFY ZAHIRA', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:54', '2025-10-29 04:17:54'),
(359, '201710301006', '$2y$10$PygLzRLpoMuvO4aeAeqBIOWuF1Beyz9RiWsmQMbZkNylopWv/9.Si', 'FRANSISKA CANDRA ARISTANTI', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:54', '2025-10-29 04:17:54'),
(360, '201710301007', '$2y$10$svFX/P3wFLaJGyagE8ZKBesZpA0R20YYBMbc8ZnjNxWtz3h5AC0gm', 'MARISATUT DINIYAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:54', '2025-10-29 04:17:54'),
(361, '201710301008', '$2y$10$93bzAWqSH.b/86RgOPwwsOZAIjE8DGYBdpyx6qtueZMTO1U7jrTLC', 'TEGUH PRAYITNO', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:54', '2025-10-29 04:17:54'),
(362, '201710301009', '$2y$10$Kk0ooi0uSOsoBU3tW.jVRO0cUNdBK862kUSTJav6ZP6UCvrwXv..e', 'TIARA DWI KUSUMA PUTRI', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:54', '2025-10-29 04:17:54'),
(363, '201710301010', '$2y$10$V1z1FhNy1MUWhWQYUH6TJuR46NYPNY0YmUtlWR8gVleHy7tdgZRFi', 'ALISSA QOTRUNNADA', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:54', '2025-10-29 04:17:54'),
(364, '201710301011', '$2y$10$n6uOBoBhSB5HM3U.vxTysOQsswe0eqRHKrn.ANfs/00PTh2azxrJW', 'VIVIN TYASTININGSIH', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:54', '2025-10-29 04:17:54'),
(365, '201710301012', '$2y$10$lDxDHqo8WqfL80QcYaV32.MXu4pEeJAFhIba35b58Hn1ZvwccpqwO', 'HIFDZIL ADILA', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:54', '2025-10-29 04:17:54'),
(366, '201710301013', '$2y$10$b//oJxg6C4y2wDJ0e5S5A.Qlh0SNLomRWQ01gT1xIsr6BM2HxNv1G', 'Yusriyyah Vika Rahmadhani', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:54', '2025-10-29 04:17:54'),
(367, '201710301014', '$2y$10$85keq2GleTz8YsllVVCPpeJkxWJMxFWCFheLi/76gnboDy9h2QGVK', 'CINDY PUTERI ANGGRAENI', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:54', '2025-10-29 04:17:54'),
(368, '201710301015', '$2y$10$onejHavOEob/.Ip9psCodOGc4FfCHI8KMlmBMbbVaBC.MVNv12I4W', 'VANIA MELYSSA SAWINDRA', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:54', '2025-10-29 04:17:54'),
(369, '201710301016', '$2y$10$eeBJ7AyZBdKyVEkkoQMCQ.7eOaw9YJw424nn0mkWnBfZNFguSmBuu', 'NUR HAYATI', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:54', '2025-10-29 04:17:54'),
(370, '201710301017', '$2y$10$cjdu24mEEqARDpHuREXtEuizc70W45PFe.wxOueC2TuGEdTYNUppO', 'ANIKA RATNAWATI', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:54', '2025-10-29 04:17:54'),
(371, '201710301018', '$2y$10$qncyQQ4uRoxmgWO53PTj7.IhT0fzZq/JyH/TLf9qR5ulJJDiUcCiK', 'SUPRATIANA RAHAYU', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:54', '2025-10-29 04:17:54'),
(372, '201710301019', '$2y$10$0f65AfRh1MIQo1vxepdlj.Zs7SPh17rBfhzn5.SEwiGy0vdEBCfuy', 'EKA NUR JANNAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:54', '2025-10-29 04:17:54'),
(373, '201710301020', '$2y$10$ulJ8VwWaXyFqQ6QYQRcVu.ddzRHGV4vxRkvbtcM67kvl1GqST5/uC', 'DEWI ARUM PUSPITANIA', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:54', '2025-10-29 04:17:54'),
(374, '201710301021', '$2y$10$al4dMuGXsxzi8IhoMf/bdOyO9hQoFDjxRghDjis0URJeRFEtkd6Bu', 'NOVITRI ALFIANI', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:54', '2025-10-29 04:17:54'),
(375, '201710301022', '$2y$10$Cq9eALSwCQL.PM/RcrIjEOVnx.FxePpBPiZKqraJCWnElT5UL7JkS', 'NI\'MATUL MUFAROHAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:54', '2025-10-29 04:17:54'),
(376, '201710301023', '$2y$10$DRouHnUSsXB.KaHrEiQVruwH9taieYym/2QhsfiShdUsOtMcOnwGa', 'Devy Lailatul Putri', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:54', '2025-10-29 04:17:54'),
(377, '201710301024', '$2y$10$Lx.15alNUFw9pQJA9UQh.urkNkFvdEIkBY6zJCctsUSMpxYIoZkfO', 'IMANY FELASHOF TEOFANI', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:54', '2025-10-29 04:17:54'),
(378, '201710301025', '$2y$10$/2Y/X0/aAJEPFt9epuo6Z.AxVcZR1ffjvgdihjUZjx4PjjZ21c4ey', 'ANIS SETIAWATI', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:55', '2025-10-29 04:17:55'),
(379, '201710301026', '$2y$10$5Pb9Y0hndSt94bBrj3/lPOW2Lb.DhpFm2RrB/a1VArag5U0DI1.Sa', 'MAR\'ATUS SOLIQA MAKRI FATULLOH', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:55', '2025-10-29 04:17:55'),
(380, '201710301027', '$2y$10$YsJlaFgzA6Dfmd37Xoa0XOcehIodyWl0r69Pu7kcXB0eVE08.LVsu', 'SEPTIANING TYAS KUSUMAWARDANI', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:55', '2025-10-29 04:17:55'),
(381, '201710301028', '$2y$10$FWH7h6ZjfEMc4kgOA3ZXiuSxjl7UxxNBmP8.Qc3mXBqnM0HQ8Hiy2', 'FITRA TINNAJIZAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:55', '2025-10-29 04:17:55'),
(382, '201710301029', '$2y$10$Ih0wlI/NRsDsWkiuRW5WreAcxfmapop94FqJjxGQnmONNYV5DNMri', 'LUVITA AGOESTINA RUMOKOY', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:55', '2025-10-29 04:17:55'),
(383, '201710301030', '$2y$10$91CysrBgJ093FRcFWqNW6e1qXUMiWJYJ8RWx7c24t7T97iuWp/omu', 'ADELIANA', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:55', '2025-10-29 04:17:55'),
(384, '201710301031', '$2y$10$Xo4sDM.jubRD6GtyOfsT7OkI117FGPQksCfqV1gbYkqbOkDzq3vb.', 'ANGGUN DWI PRAMUDITA', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:55', '2025-10-29 04:17:55'),
(385, '201710301032', '$2y$10$h0tbX6Z6fQM1v3u.yo.ifOUGpDGq3clJWLtQUQNvha96bRtjGpQjC', 'FIRYAL', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:55', '2025-10-29 04:17:55'),
(386, '201710301033', '$2y$10$0VI9l3Tji40vf1s0ZcNqVulGd6fAzoGJNPlNFsC7LFDM5NOja8SIK', 'PUTRI IZZATUL AZKA', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:55', '2025-10-29 04:17:55'),
(387, '201710301034', '$2y$10$Hq8taOpuWhelGbFufeMbue9I6mSQJL2WtAgu5d6l.MpDmE8kxsicu', 'DIAN RAHMATULLAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:55', '2025-10-29 04:17:55'),
(388, '201710301035', '$2y$10$nhVeM4TyGJEj2lvW0OGzkugeOOqaXpAhdzeVS.N0ZWrFJrZmVJoxK', 'KANAYA SUKMA ARDHA SUWARDANI', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:55', '2025-10-29 04:17:55'),
(389, '201710301036', '$2y$10$8o4p.BC.L75NRtahQb0zSOrYRO.Fy66JbDnmRs.KnA80RSoKHjeEO', 'MOCHAMMAD RIZKY', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:55', '2025-10-29 04:17:55'),
(390, '201710301037', '$2y$10$USh66xqUytaYT28AhvIetOIM9h3SRuZnCxAmXVwAAZnYehoEEsKnK', 'DEA CITRA TAURINE VIVASYA', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:55', '2025-10-29 04:17:55'),
(391, '201710301038', '$2y$10$sEUyQ.nri4mzYVnZgIkQ5e7Kd8G.PHoLGurpPp8S7KYmqR3E6yKwa', 'MARSA SUCI NURMALASARI', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:55', '2025-10-29 04:17:55'),
(392, '201710301039', '$2y$10$kRzIWEvCiAH6v/p5uSXD/eJjYrXJXJFNuOPL2smW7zVpgDb4nxeT.', 'IRVAN MAULANA MALIK IBRAHIM', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:55', '2025-10-29 04:17:55'),
(393, '201710301040', '$2y$10$ctYNExfC5liUwbX7pVGazuL4Kw2qL1.DlfG7bjay3fzIJqYkM3Gbm', 'ENGGAR LANTANG MAHENDRA', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:55', '2025-10-29 04:17:55'),
(394, '201710301041', '$2y$10$AkmUb7hoCH35kUjnaJCCBe2EPbEhTLVViv.iDJDOTKqN9kA5FbBmq', 'VINDHA DHILA WULANDARI', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:55', '2025-10-29 04:17:55'),
(395, '201710301042', '$2y$10$aoOyzWG6H5XXNzuIJarDhu6vD2xq9zOwMqXcy21CmurNqzIS0rYYe', 'M. NAJIH AR ROUF', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:55', '2025-10-29 04:17:55'),
(396, '201710301043', '$2y$10$Wbmx4758lBQyRSTPSCD7VOaqhZtrGoBfF12nvqOiZAY1xQ8UWxP1e', 'QUENY OSCALANI', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:55', '2025-10-29 04:17:55'),
(397, '201710301044', '$2y$10$HJoHk/3PpLrbi3sV14kMXuEzeWLO4Iy1ua0bUfsQzZuY6TgKSLHSq', 'SEMESTA TUALANG CAHAYA', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:55', '2025-10-29 04:17:55'),
(398, '201710301045', '$2y$10$emu1soa66TZtWiRkBjFGt.KupTlw0EmHip/T3D9fjRl.fViiIRikS', 'CHANTIKA PUTRI NUR AZIZAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:55', '2025-10-29 04:17:55'),
(399, '201710301046', '$2y$10$xLyIo2lAlShKHc5TCWSK/.RKEOQtlf/DN8s9rNrx3K8wvS4/aJBMW', 'ELISA FEBRIANA', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:55', '2025-10-29 04:17:55'),
(400, '201710301047', '$2y$10$/FBc2WJ2I8s1j05mtMylsOtOlTtrvUIxx99S2qJeTf/nQxNH2rhwW', 'VINKA OKTAVIA PRAMESTI', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:55', '2025-10-29 04:17:55'),
(401, '201710301048', '$2y$10$olk4YHTN5/YMe.mBLpvNLeMZTU5cYMgzd2e.ex5vBp65M8YJ76rG6', 'MITHA DEVARIS DEWI ANJANI', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:55', '2025-10-29 04:17:55'),
(402, '201710301049', '$2y$10$tsdW4gnhdcIaLyT4FvJ/ge7fu60nGxrxiX/F76FkqHTelqB6CWK/K', 'SALSABILA', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:56', '2025-10-29 04:17:56'),
(403, '201710301050', '$2y$10$5HwrgtFrq6tVIVkPh9q5Yu33NyZZoFM9/MMvKBHoLqZwdc7E7vHLu', 'RITFAN VALENTINO FEBRIANSYAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:56', '2025-10-29 04:17:56'),
(404, '201710301051', '$2y$10$pJryip54hvI.01Lr2TCK5.6pKuFm5b92GKWsfx1kSZUISQStKl.2a', 'SAFRUDIN BACHTIAR CAHYA RAMADHANI', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:56', '2025-10-29 04:17:56'),
(405, '201710301052', '$2y$10$6rctesNyoPcnZ/JWUhg/HOovhbGjel7gJQMYAIDdz.P3I6YOOT7fi', 'LUTFI MAHARANI', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:56', '2025-10-29 04:17:56'),
(406, '201710301053', '$2y$10$tuRkNgr1.iVV3DaNuLDoHeC7HPBuzhk65u.OrPzOa9XmOLW/7CDGW', 'FIRMAN SETIA WIBOWO', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:56', '2025-10-29 04:17:56'),
(407, '201710301054', '$2y$10$9tchE20ITiI6KFQ5mqrcoeyz8ju3AXGYxwAMkUpkwXp3/f2hd8Hby', 'RINA ANGGRAINI', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:56', '2025-10-29 04:17:56'),
(408, '201710301055', '$2y$10$0wcZ6ih9EYAiKbQ8pSW1zuUUheQVTdH.gPzb6K5WatcjKHS6qkxQO', 'RIFDAH NADA NURJANNAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:56', '2025-10-29 04:17:56'),
(409, '201710301056', '$2y$10$S/O7NzOTcoR4srO3XjafjOU37NWc6gNUagQit9vSRIdZ22eYsM7F6', 'YURIS PUTRI SALSABILA', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:56', '2025-10-29 04:17:56'),
(410, '201710301057', '$2y$10$VvEMgFn97KBwMprL7B0iqOn8WKh83AbxhL5s9o.UX1f/uNUiG.h9O', 'MUHAMMAD RIZKY ALAMSYAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:56', '2025-10-29 04:17:56'),
(411, '201710301058', '$2y$10$96Xa/sQQntx48QQiycPQKeauP3FCRk8urB14dzk6/xyOEyOvvHCvC', 'ADILAH DEVIRA FRANSNA', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:56', '2025-10-29 04:17:56'),
(412, '201710301059', '$2y$10$JcaB2cat9224iLz2JaAAl.P/hZe7FCJxJh5RmJER5MhJbY37su2fy', 'OLA RISKA APRILIA INTAN AGHATA', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:56', '2025-10-29 04:17:56'),
(413, '201710301060', '$2y$10$MaSsMgWo1WD9QZsqdNi6HuL.deMC3hJ6Y/x0JztzLeVYzbmTmDErS', 'ANNYSA DELIYANA', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:56', '2025-10-29 04:17:56'),
(414, '201710301061', '$2y$10$YLKQDXWtaXSZ6jcTRgk0uebDchppbUugprShl873koiYM3ZCJIFze', 'SHINTA PRAMUDITA', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:56', '2025-10-29 04:17:56'),
(415, '201710301062', '$2y$10$VabUga0.1XPGI89RR3Ma5OYlv/9VIJAvrh5UqxEx3q.c3PDCZHhJm', 'ANAS FAHRUDDIN', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:56', '2025-10-29 04:17:56'),
(416, '201710301063', '$2y$10$qFktz2FnCuUG.6zahMm6tuPfH8zcapYQZABFaroq6dwU2t/4CCyNG', 'PUTRI SEKAR KINASIH', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:56', '2025-10-29 04:17:56'),
(417, '201710301064', '$2y$10$4nWtswns4f5rRcpFgmkWxeO6fV1GK7it1BtqJ03JDj4oORa7CzxFO', 'ADINE RARA SALSABILA', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:56', '2025-10-29 04:17:56'),
(418, '201710301065', '$2y$10$04GHKRDxJDXDQTtJ8rCQqOhvY2JdR7b9GhMox8wYsJu0FfSwlWuL.', 'PRASETYA AGUSTIAN', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:56', '2025-10-29 04:17:56'),
(419, '201710301066', '$2y$10$EDHtndqJbFsGGV1EtdB8h.3F53lE0LKYqKaHM0IRT2Tv7XKCFCPC6', 'SHOFIA NUR FADHILAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:56', '2025-10-29 04:17:56'),
(420, '201710301067', '$2y$10$2ip1vK/aa8uWbseklLw83e3XVvV0hkMHLAR/QjGCY6P2ndPtwR7NO', 'APRILIA HERAWATI PRAMONO', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:56', '2025-10-29 04:17:56'),
(421, '201710301068', '$2y$10$HkyZYpD9/orvpbZ9uKvhaOvv7FtcjWEQasaxD34SEFnLM4fSEHDcu', 'DWI BUANA PERMATASARI', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:56', '2025-10-29 04:17:56'),
(422, '201710301069', '$2y$10$oDLUty0UIvHCvvD1Z2weo.V84hEZVerJIZjCbSd56OeRzG8iGj4Te', 'INDIRA MAHARANI HIDAYAT', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:56', '2025-10-29 04:17:56'),
(423, '201710301070', '$2y$10$x59tAMDiOP9rKilOWL/q2OfQekM4BTIglAIvSh66uiBm3BIowunES', 'JUNAID MOHAMMAD ZAIN', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:56', '2025-10-29 04:17:56'),
(424, '201710301071', '$2y$10$mtQytXflRqV02JFK3cBB5udqZF04frN5wZuS76c5PYiRRTaKViCzK', 'KRISNA MARCHEN BAYU IRAWAN', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:57', '2025-10-29 04:17:57'),
(425, '201710301072', '$2y$10$X4wNikA1HNZTXr9iYm8jv.JW6DesycqQe9GYSP6aHW5ejcj4Fow6m', 'BIMA ARYA NUGRAHA', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:57', '2025-10-29 04:17:57'),
(426, '201710301073', '$2y$10$7m8usJti1MMGPfk9mwlUCeotsMnXDsskjxBQEV1DT/XeLcdbBCGlW', 'RENALDI AHLI PRAYOGI', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:57', '2025-10-29 04:17:57'),
(427, '201710301074', '$2y$10$s6yZ6zZsk3qF97A5Luqn0uJ7iAx0CufG9EYsOOvwZhWiGWXSrGe4S', 'DWI SETIYAWAN', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:57', '2025-10-29 04:17:57'),
(428, '201710301075', '$2y$10$Tz8yEBcwkLInMLHzLwA7/uiEM0i/b2qgTNKH.7ImFFHWrYhp3hjoq', 'MOCH. KHUSNI MUBAROCH', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:57', '2025-10-29 04:17:57'),
(429, '201710301076', '$2y$10$ydeOpPGiFbwNkYYTleGDBuG4Ux4i.RP6YI6itZU1qRqtOouOwR6y2', 'BALQIIS LUTHFIYYAH MAHARANI', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:57', '2025-10-29 04:17:57'),
(430, '201710301077', '$2y$10$Ye2Mg/nD1jOskJ7rYrLyK.roLznNz0iSZyLMBS82giTvUwpl2a9bW', 'SESARINO PRASETYA PRAMESWARA', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:57', '2025-10-29 04:17:57'),
(431, '201710301078', '$2y$10$rDsz5ciU3LoR9fxVVEvHYuBDYPBtj8yLXlqQ/4rt/U80Ii.bLbVZe', 'SALMA MUFLIH NURIDA', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:57', '2025-10-29 04:17:57'),
(432, '201710301079', '$2y$10$RXIVfxYl79bdz9hADnJBTe2QuDjc2cm4q/6GbzMC.24Wcw37E7gwC', 'AMALIYA PUTRI WAHYUNI', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:57', '2025-10-29 04:17:57'),
(433, '201710301080', '$2y$10$oq6jFVlzawtAcYM9rdwN.OKamWJY.8IQUnfbbT2aEOtD0jdOm3YKC', 'FITRI WULANDARI', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:57', '2025-10-29 04:17:57'),
(434, '201710301081', '$2y$10$CJSJYQTT5clrTfr./qN2ae1LnTnKptuKLmYj6Qu.rEcQkfGTThcza', 'RYANDA IQBALDI', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:57', '2025-10-29 04:17:57'),
(435, '201710301082', '$2y$10$8NZfcFiyW8GBzUSjT18QQu2spF7Z2UNGChuNWnEF2axlJNMFdzX9W', 'MUHAMMAD ALFI MUBAROQ', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:57', '2025-10-29 04:17:57'),
(436, '201710301083', '$2y$10$E03Mgk2fQ4V8SjxRKOiTT.DsThPsZ.8cwm.KgYj2snsj7Lt.OxVZW', 'WILDAN NADZIR KHAIR', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:57', '2025-10-29 04:17:57'),
(437, '201710301084', '$2y$10$2iixc35TZC1.k0ACf7UtYeXyoewDe5FfF45OKp6OMUuPMg1J/fr8O', 'FIRDATUL JANNAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:57', '2025-10-29 04:17:57'),
(438, '201710301085', '$2y$10$9ZCEqOoGQZaOgP4yohQKT.9bMJV.IMqmZdEepG9EeTH9DxPGbAC7C', 'LUTHFIYAH KHAIRUNNISA', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:57', '2025-10-29 04:17:57'),
(439, '201710301086', '$2y$10$yCK/wImJDI.4Sajur.g2k.r.RhafucUD2rXvghBAslH4XS4PtLLVG', 'RIZKI AMINULLAH MILLENIO DAROZAT', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:57', '2025-10-29 04:17:57'),
(440, '201710301087', '$2y$10$6VOWlocIn3pebZl3RyQscu7IT5NX1Cq//r5AmOa1BBle90upx5lXa', 'MUHAMMAD SADAM PUTRA SAPTA', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:57', '2025-10-29 04:17:57'),
(441, '201710301088', '$2y$10$6FWm7CRXZZRverFjNT9iF.RkZH5TyAYHHqiJw31uVwyyqaAdRdZxS', 'FAROUQ ALFIAN SALIM', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:57', '2025-10-29 04:17:57'),
(442, '201710301089', '$2y$10$v6X5rFkIzLNTIUmeFbz1l.iZfO8B6xjD/23ElQxQCG6m/2ENpd3M6', 'ACHMAD SHORFI ALDINI', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:57', '2025-10-29 04:17:57'),
(443, '201710301090', '$2y$10$cydNCcQDBU.ujVicaOm6WuN//6BMF2uqgblM3x6FDJ3/phnEaIZuq', 'AYIHWA LIANJI MEGA', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:57', '2025-10-29 04:17:57'),
(444, '201710301091', '$2y$10$Ae/V4cuI9NwbSTbITkJhz.CeE4C23gCOq/hOVOIWhyw8xXWCbhVGy', 'MUHAMAD ARBY FAUZAN ADRIANI', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:57', '2025-10-29 04:17:57'),
(445, '201710301092', '$2y$10$ieMGhElbraM4L9OHbMhqnONYzvB191vkquGqaRlBKb4shT7wy/On6', 'ABDILLAH FAQIH ARIDA', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:57', '2025-10-29 04:17:57'),
(446, '201710301093', '$2y$10$p/YR2Dg0t21A.JocQveBsOG717Iqb31QGV9Naascy5oZzORkNkSFe', 'RIZKI AMMANDA PERTIWI', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:57', '2025-10-29 04:17:57'),
(447, '201710301094', '$2y$10$luXmrjYec.7nB63ct94AbOj.6g1cJtbnkTU4bokw24g8SBmTTz7cK', 'FAIZI AKMAL RIZQULLAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:57', '2025-10-29 04:17:57'),
(448, '201710301095', '$2y$10$hWiaC9CDTYbBTnoL.54qdOP0TrkYAS5DazRUywfcB03PFyc41V9Qq', 'HAIDAR ALI', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:58', '2025-10-29 04:17:58'),
(449, '201710301096', '$2y$10$fRlAKcjAmAUMhyHOVRIl7.Kxw3OHAiKjF8x3djnTAu0eNqr.Me1Iy', 'AFIDA NURHIDAYATI', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:58', '2025-10-29 04:17:58'),
(450, '201710301097', '$2y$10$06vZIrS2CFWHZgbmfSA0ZeUDtuU3oA8qONoCrHZayPc/HWaYcWpAu', 'MADE NATAJAYA', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:58', '2025-10-29 04:17:58'),
(451, '201710301098', '$2y$10$Fbx7LDKaB0U2iQ036Ni1a.Bv2L9guyhjd7vw3o7FCuKAdxtMCeg6y', 'NAUFAL RIZKY WAHYU PUTRANTO', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:58', '2025-10-29 04:17:58'),
(452, '201710301099', '$2y$10$u66aRo.hyA/WuUAn9cB.Y.trWW3jgXqiPrSt.PcNKtw4mvOdP6yvq', 'MUCHAMMAD FAJAR ASROFI', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:58', '2025-10-29 04:17:58'),
(453, '201710301100', '$2y$10$KEMfjqN6/E4rsj7MlajVhusNHWGN4DSautGQlO2NttlPe7wTI4Sxq', 'OIVA YASA AMANDA', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:58', '2025-10-29 04:17:58'),
(454, '201710301101', '$2y$10$cLGzPUUJA7tt1HkVEt3p1eIzr.fZaAthK4t9a5EDrm4gYWj3nIc5u', 'ESSA TRI HANDAYANI', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:58', '2025-10-29 04:17:58'),
(455, '201710301102', '$2y$10$kkMg2lb803D8DqhxQOCgX.ZGdvIVj.j5C8Mo5sQSndmKQ9U2VrTVm', 'Muhammad Sami Makarim Gena', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:58', '2025-10-29 04:17:58'),
(456, '201710301103', '$2y$10$.bczBvku4Kd5Ns2KwUJ3ROQMZXF.pb0yUbM58sCXCkxJ1FfMEC.5m', 'KHOIRUL UMAM', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:58', '2025-10-29 04:17:58'),
(457, '201710301104', '$2y$10$wubaCRWzRe8kkdLZlS1iKOJ/k1WAIfq6F4W25e2jvYrZs.F7dWjZq', 'QORIAH DELA WASILAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:58', '2025-10-29 04:17:58'),
(458, '201710301105', '$2y$10$J/ax7ONjp8lHi0mVyDBzROPrXRN7bWPawy/2bP2Hobr7iTcMwgtvm', 'ASHON ALROSSY UNERLY', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:58', '2025-10-29 04:17:58'),
(459, '201710301106', '$2y$10$7g2eTtN8xp9d4gseVwz74usw0vpJrnh7XLTSg2B.Y7G658LmbjyTG', 'SIVA PUTRI SETYANINGTIAS', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:58', '2025-10-29 04:17:58'),
(460, '201710301107', '$2y$10$Xd6AqqlESk4STrJ7j0dzzuAN8qLZUx/j8xjbsmHVBYPq06.3aM7qG', 'LIESIA HANAGARI', 'mahasiswa', NULL, NULL, '2025-10-29 04:17:58', '2025-10-29 04:17:58'),
(461, '211710301001', '$2y$10$KiMHJO7BhyD19WM0sfmQxOTFL.BRZyZ8B4y4mhBUQDQ64zTb1qZb6', 'YOVI NUR FIKRI', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:53', '2025-10-29 04:23:53'),
(462, '211710301002', '$2y$10$oH9vMJNlA/1XGtWkUZzxxOTRowWI1tzSh1yQUIkNcAddqACgETvzi', 'MUHAMMAD DWI KURNIAWAN', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:53', '2025-10-29 04:23:53'),
(463, '211710301003', '$2y$10$/Yh3pfNxB4l3SaQW/0C3Uukc.hejFspPq/JnBKXMekrm3oEb1sakO', 'DEVI TARISSA RISMAWATI', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:53', '2025-10-29 04:23:53'),
(464, '211710301004', '$2y$10$rE9DdqfnrP0tNw1.FhbQiOOYfwQ4BjGX3fbgiv.PsfC4LAMdISqWC', 'RESHA DHIAH EKA YULIANASARI', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:53', '2025-10-29 04:23:53'),
(465, '211710301005', '$2y$10$YA4mbqbDp2/OR6Q4f4Ftte3g1/gPndPXl1/7VgtDWmnj.1sYHg8EO', 'YEZA ZANJA BILLAWATI', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:53', '2025-10-29 04:23:53'),
(466, '211710301006', '$2y$10$NLALLIXZQx4fjj14PJ3yxu3uHqavZ4Q/NXqo9UC0UfnEURGHZmDw6', 'M. VIGO AGSELDI UTAMA', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:53', '2025-10-29 04:23:53'),
(467, '211710301007', '$2y$10$VI2Nj25hgURJAD2BPP0w/ODdkkd1whvbk6svEvf9rPq2a6xpUhz5S', 'CANDRIKA NUR VIRGITA', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:53', '2025-10-29 04:23:53'),
(468, '211710301008', '$2y$10$o/W.x0TUMkPF5qlf7gVAg.ezsV.OCfiHJX1kYdj9NJ9wGBG8fZjKO', 'MUTI\'AISYATUR ROFI\'AH', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:53', '2025-10-29 04:23:53'),
(469, '211710301009', '$2y$10$RO2w.4YhFKTihqaQwjHmMeuxeq8mNmEr40DQhwdYYE7drzqDtcvbe', 'YOLANDA MAULANI', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:53', '2025-10-29 04:23:53'),
(470, '211710301010', '$2y$10$Xpbt8o6t9ow0IyjFUq2nG.mR523FmuA9IJ7kH.brByPD60iVpb5Si', 'CINDY ALYA GUSTYA', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:53', '2025-10-29 04:23:53'),
(471, '211710301011', '$2y$10$/gtbFgrA27fkPCWWznU9rO65kUcIZknr0CSes07q3fUl03UetsABu', 'RIMA WARDANI', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:53', '2025-10-29 04:23:53'),
(472, '211710301012', '$2y$10$KtbtozCxhkEz/.WmzvZdzeEHpljgS3LcML0p814.ySQ3c/btRGlmS', 'SALSABILA ATHAYA YASMIN', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:53', '2025-10-29 04:23:53'),
(473, '211710301013', '$2y$10$3T9fPEDY328dqcOt/l/0IuU/39L3Nkc7u7jwn5ayxNqIeQLa1g5gK', 'WENDRA PUTRA PRATAMA', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:53', '2025-10-29 04:23:53'),
(474, '211710301014', '$2y$10$rtz3hpdADF0dMeMcrqZ5OOdo3kkxjuj8Mht.zEejBixP8mW4rffVW', 'DIRA REVITA DAMAYANTI', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:53', '2025-10-29 04:23:53'),
(475, '211710301015', '$2y$10$gKwJkgJ6/mXz3Xaptggni.vrvfcLN/JfwQXGOdHvY7uaQ/HxdHt6S', 'RANDHIAGUS PRAJAMUKTI', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:53', '2025-10-29 04:23:53'),
(476, '211710301016', '$2y$10$YmtDAHKRBW51zK9wUz7OueIFJEC0lzYazltM8XsWt2x6LBVFhS5aq', 'NADILA LAILA AZHARI', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:53', '2025-10-29 04:23:53'),
(477, '211710301017', '$2y$10$LxW/iFxJpbrT64jsiTDW6uW/VadhWs5vocJoW7Ww.dv8yaP/Yl112', 'ARI CANDRA JUNI PAMUNGKAS', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:53', '2025-10-29 04:23:53'),
(478, '211710301018', '$2y$10$bLTVbnyJYCRh9f.kFgsbBO1p74u.iKyXQ1.F9zSe103quYQ11qwfe', 'WISNU WARDANA', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:53', '2025-10-29 04:23:53'),
(479, '211710301019', '$2y$10$L8JYorUKprmH5UJddr3ade6FYnOspkAOMGwQTC6U0JfK4vko4FUe6', 'AKHMAD FAJAR FADLI', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:53', '2025-10-29 04:23:53'),
(480, '211710301020', '$2y$10$PN6cGkVLuoA0F4XfPI7qJerqWcgEKYZOv/bquX4gYexQoZ91.H/pa', 'MADE ARTHA PUTRI AGENG', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:53', '2025-10-29 04:23:53'),
(481, '211710301021', '$2y$10$758uDtttqfOdC088D/.RJ.1/ST.7t9aNWrBfylarFlUqtiy7lz9vm', 'TASYA NATASYA KAMILA MATURIDI', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:53', '2025-10-29 04:23:53'),
(482, '211710301022', '$2y$10$atWIvlrpTTw8d4GnsB.g.OclL576vLC0Cqbz0N1uqIWDZEoUP4uZ6', 'ZYACHBEINA NOVINDA YUDHA SARI', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:54', '2025-10-29 04:23:54'),
(483, '211710301023', '$2y$10$PPhQw3StnnXPnYlt8lt1.OJzs0oVh1rHDSQuGhtRLUS8zvVjrahOG', 'JAYA BAGUS DARMAWAN', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:54', '2025-10-29 04:23:54'),
(484, '211710301024', '$2y$10$/Cq9e2aDp48S.goUjmpdD.GqNKbhNml5MzxB8CytL9MOQbj/SMczK', 'YONATA PONDIA WARDANI', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:54', '2025-10-29 04:23:54'),
(485, '211710301025', '$2y$10$1AyO6DxX4K.FQRygrjahmedzNtyGO5kNxgEgJ1XXgX1TzIc6KDPt2', 'FAHIMATUL ULUMIYAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:54', '2025-10-29 04:23:54'),
(486, '211710301026', '$2y$10$o1srYvaEV7roEulHZ2ODNuLPq4CYYzbKN92MaJQ6e5PkeaTsbrg2K', 'MELCANIA BRIGITA APRIL', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:54', '2025-10-29 04:23:54'),
(487, '211710301027', '$2y$10$pAJztmA2rT9Q7MVK05gr7O.leInm4dI.mbrcQjFdlHnPFxDLi5M6q', 'ANNISA AYU PRATIWI', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:54', '2025-10-29 04:23:54'),
(488, '211710301028', '$2y$10$WhVkdfqAYel9ucIwZngtE.TaH0F8lFyeTJioZ6Lm/CZVn50252z6u', 'MUHAMMAD ZACKY FATHONI', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:54', '2025-10-29 04:23:54'),
(489, '211710301029', '$2y$10$dvzIczYIzyY.1E7RJ.PKT.ET6NEa67BVlSF0j8EYMqPT0CpZnCNqy', 'ALIFA NOVI MUZAYANA', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:54', '2025-10-29 04:23:54'),
(490, '211710301030', '$2y$10$bxErBE6ZXnWsdvs96sWyYe1mHiWiqnAh.iYWpAl9D.B2ga/oAh6R6', 'MAULANA YUSQI SALSABIL', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:54', '2025-10-29 04:23:54'),
(491, '211710301031', '$2y$10$E0RtByhI/GSgNY1RrAAslez1wCl96CiL1sJeTOypsv0E4.xiXVYwe', 'KURNIAWAN TORIK AKBAR GIBRANI', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:54', '2025-10-29 04:23:54'),
(492, '211710301032', '$2y$10$6Fyn6yt1QWmJ2foJea0EbOjfAOhNwZFsbLP.GGnPws.CSsXbhxnQ6', 'DARIS ARUM PUSPO SARI', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:54', '2025-10-29 04:23:54'),
(493, '211710301033', '$2y$10$oMpvZVsHW.qLjxSZK8pwW.emTh4io1KTON8814HYY0zPkPb9E0pFW', 'OCTAFIAN MAULANA QOMARUZZAMAN', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:54', '2025-10-29 04:23:54'),
(494, '211710301034', '$2y$10$61.jJQlQQgGdOsGOh5oPqOSrXjcKN3ePgXpIJtsC9aabXYqBtRoG2', 'SHAFA AZELIA IVADA', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:54', '2025-10-29 04:23:54'),
(495, '211710301035', '$2y$10$/xHMIx60qM/PlsVkt1Mc0ecqsmupEQlnEr.0Ci3IDI0Vg79SlEIMq', 'ANGELINA AZHARA PERMANA', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:54', '2025-10-29 04:23:54'),
(496, '211710301036', '$2y$10$sUXcauC.vlFHjWe8ZHHS0OYuZLjvJdj84pjZjHuJjIiCrVLG6fXbm', 'SOVIA AULIA NABILA', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:54', '2025-10-29 04:23:54'),
(497, '211710301037', '$2y$10$mlw91Yzc1yxLd7Lszf7bR.MGFs76HnstBtz/MFwohCJE0nbJ4XJhy', 'RINDI MAYANG SARI', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:54', '2025-10-29 04:23:54'),
(498, '211710301038', '$2y$10$PGFnDnXBBEdRUQzfPbvwM.Oj0k9l9oMBm/XWjhP70NK18z0Zsn2dW', 'LUSSYANA FAIDATUL LATIFAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:54', '2025-10-29 04:23:54'),
(499, '211710301039', '$2y$10$s.6gliONO64.9Hu1iOENzOWkqKDuM6IFudOxsuJoJJYkwjJE1cnQu', 'AMELVA FIRSTIAN MAULIDA', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:54', '2025-10-29 04:23:54'),
(500, '211710301040', '$2y$10$JJ4nBR.DdusnIOITAAm7Cuf5tq6X7uILU2nzIAUDVTHPkeEPz/dD2', 'ASTRIA SHIELVIONITA NAULIA PUTRI', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:54', '2025-10-29 04:23:54'),
(501, '211710301042', '$2y$10$zdR5smlt4RCkm0joQzkheejrX2NT3C1WvcIaktqg2TV1YUTwQkWJO', 'FERDI AL HIKMAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:54', '2025-10-29 04:23:54'),
(502, '211710301043', '$2y$10$D/SNZkbJUYb4jaKS25NgGuJGb2xxdLLOKULwDFfT6umLPDcVVEHD.', 'TUTUT SULENDRA', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:54', '2025-10-29 04:23:54'),
(503, '211710301044', '$2y$10$mWSyHoCX.cQVrvU6phUGSOuay.6b5kmlhVO/4u0lOJLf/fTds2Zlm', 'ACHMAD ALFIN MAHENDRA', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:55', '2025-10-29 04:23:55'),
(504, '211710301045', '$2y$10$Chr8liHXO0Xv.Tqwj0e6ruV7lcrSFikK2NnV5faXR3KGYRdtuMyIq', 'DONI GUNTORO', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:55', '2025-10-29 04:23:55'),
(505, '211710301046', '$2y$10$hJunocucb28UawhTLskHPuI9nD0syKleLvibMwjYDi2glUuCo/Znu', 'DITA APRILLIA', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:55', '2025-10-29 04:23:55'),
(506, '211710301047', '$2y$10$LQr/z.DUiL9KL./gkow3yOG5DieW2looVxqbI4MdHFJjdxu9ZwkE6', 'ANNISA NURLAILI SALSABILLA', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:55', '2025-10-29 04:23:55'),
(507, '211710301048', '$2y$10$f7NM8do6nSxfozDRV/hWuOcKX0ovit3J7qhvs4lmzY97e/4aCdAX6', 'QORI AFIFAH ANGGRAENI MAHGFIRO', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:55', '2025-10-29 04:23:55'),
(508, '211710301049', '$2y$10$XMJuNKf5EpzD/XD.GUKl..wc/RZGz5BbD6lOrAMGV6dO18tbIEzXi', 'ERIKA DWI SILAWATI', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:55', '2025-10-29 04:23:55'),
(509, '211710301050', '$2y$10$cnreSW.qsfcwonsYZwFd3eWTmbCVUtGPARo8Gmc2k9Ogyn5VEfpWi', 'NURUL AISYAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:55', '2025-10-29 04:23:55'),
(510, '211710301051', '$2y$10$jBd23MFE6QB7JVenlknGeeUiuf7fBshVTs415FOSBpZKGog6FBuJ6', 'RIZQY IBNU SHINNA', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:55', '2025-10-29 04:23:55'),
(511, '211710301052', '$2y$10$0xS82U.yfQeTV6/7YuF8z.y4IF.VBIkB7xFmxMpsPhGbraYg.1bna', 'AFLAKH ZULFAN QORIB', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:55', '2025-10-29 04:23:55'),
(512, '211710301053', '$2y$10$w7JI4BO/wjJ0v3O3law9kefBlg4I9xZCrB/L7UAtI.rnbR1NJ2Qnu', 'AQLIMA SEKAR MAULINA', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:55', '2025-10-29 04:23:55');
INSERT INTO `users` (`id`, `username`, `password`, `name`, `role`, `nip`, `nim`, `created_at`, `updated_at`) VALUES
(513, '211710301054', '$2y$10$W3QpY/Dxpw5TCE56Ri4bYONqZbG5R.ZQ9kGbPDDKS8msOlBj9ItHi', 'MEIDIANA RAHMAWATI', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:55', '2025-10-29 04:23:55'),
(514, '211710301055', '$2y$10$DJKpzrmlHKTCkl.DajnGU.ulauhSZPYxVRUmsrs2qrF6oC4aCTIAe', 'SHILFY ROHMATIKA', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:55', '2025-10-29 04:23:55'),
(515, '211710301056', '$2y$10$J2yDLFKX14TEnWiPVyvTQeVjlL/oBeuZKQnldEZ78FOZnI/daQtyW', 'RISSA AYU WULANDARI', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:55', '2025-10-29 04:23:55'),
(516, '211710301057', '$2y$10$8YDo/PL.cp3zqYR8oe7Um./RF1EAbBzdIKRKvfnYIpNbnKL49l/8y', 'JOSHEP YOSHIO LEEMANS', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:55', '2025-10-29 04:23:55'),
(517, '211710301058', '$2y$10$7EN3bJcfYiwfJ6qzj3YZ9ui.c45FEJHVmSty/9vtxG6EZkTa3CZK.', 'M. SALMAN ELJA', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:55', '2025-10-29 04:23:55'),
(518, '211710301059', '$2y$10$xBy3R.E3O6Izl7SWCKJ6DuUqpOGVj.0/istBU8CLPqAxOOMmYhoXa', 'MAESARANI SALSALINA SITEPU', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:55', '2025-10-29 04:23:55'),
(519, '211710301060', '$2y$10$K9eyxt7hDizr6Z8ISCpIVuJ5s7IxMCwhxx6hPAreknN/PT2Ln0Q0S', 'FILA FARIDATUZ ZAHROK', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:55', '2025-10-29 04:23:55'),
(520, '211710301061', '$2y$10$jeWp6umx0PQSKbz5oeCksO8KPbs8TUcrNyz3vmG0EgL/Hz7TNFQiu', 'GABRIEL DESTINO SITORUS', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:55', '2025-10-29 04:23:55'),
(521, '211710301062', '$2y$10$93xKei1QeRgFpQxxy1Ffe.djy/JMZy4OTEN9iZ8YVXWqWq9LarGmi', 'RAGILYA REGINA ASMARA', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:55', '2025-10-29 04:23:55'),
(522, '211710301063', '$2y$10$733FzTAdWVnZZD3GOimqYOZQC4bGdlRBolQ.XXjyAQZdFsj9hNOIS', 'KEMAS ALMAS MUHAMMAD', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:55', '2025-10-29 04:23:55'),
(523, '211710301064', '$2y$10$xGe9LidFP1YiKo8YHhrQ3eGXDrTjRxNNdO.ngaCBN1v5WjQBCkBiq', 'NUGROHO ADI SURYA', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:55', '2025-10-29 04:23:55'),
(524, '211710301065', '$2y$10$i/qj15Lv11903D4rqG6JseUalLsr7zNhua3t8bWbE88UE417G0PjO', 'HERDITYA RIFQI PRATAMA', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:56', '2025-10-29 04:23:56'),
(525, '211710301066', '$2y$10$tHEEIWnbphsOZEKyNo4xbeqe.DpBMkyqYSWDsQn3ZTx3BeLtcMUve', 'DEVI DWI JULIA RAHMAWATI', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:56', '2025-10-29 04:23:56'),
(526, '211710301067', '$2y$10$kxLZrMGcHXujxPkYuzPar.wQp3/juOfYbiSyhaes0/ZPz0/sX1vqW', 'KRISNOV DIRGA', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:56', '2025-10-29 04:23:56'),
(527, '211710301068', '$2y$10$KsNuOPUhjS.Raep2QtZgNOfXuJwwvGuSK/yOpWjWUeI.1T/8a40zG', 'AHMAD ZULFANI LIANTOMO', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:56', '2025-10-29 04:23:56'),
(528, '211710301069', '$2y$10$JhCf2HWGq5HO0xCralifyeE6HzQ92dsstNwcyWrgb.QrVIqTw4o6q', 'VINA JUANITA SARI', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:56', '2025-10-29 04:23:56'),
(529, '211710301070', '$2y$10$zJWG7yvrC55oXJ7I.6U7Tu373TtUJJkf7auH0BSB3wGT6legv0dM2', 'JILAN HANIFA', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:56', '2025-10-29 04:23:56'),
(530, '211710301071', '$2y$10$YmzUeN9RB0gi6swgvKxznOnvBjahdhPodQqD4d3ApflCmgxySmywu', 'AINA SALSABILLA PUTRI PRASAFI', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:56', '2025-10-29 04:23:56'),
(531, '211710301072', '$2y$10$bIw98epwPvH/dKa//9Tl1eNpybnou4RvrM9VSSexVsvMqgsfVj1EG', 'MOHAMMAD DAFFA ZAHWAN', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:56', '2025-10-29 04:23:56'),
(532, '211710301073', '$2y$10$JXv9cf2bXFj1PRzhNMArMekUr8hpWiHGJau7SUqSlb8y1NkD9ft7y', 'ASYAFA\'ATUL ULYA', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:56', '2025-10-29 04:23:56'),
(533, '211710301074', '$2y$10$wp1Osd5X..4HUnTNki863uADVYm8b/gD0i.Z3AuX6niLvc/8CaSXm', 'MUHAMMAD ADHITIYA FAJAR FEBRIYANTO', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:56', '2025-10-29 04:23:56'),
(534, '211710301075', '$2y$10$/Pc6p8gdSNs24aIdORgWUu4r3NhKZrFzg7pO4CWYGwIG.sAJrEwPa', 'MOH. ZAYID ZIDANE', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:56', '2025-10-29 04:23:56'),
(535, '211710301076', '$2y$10$Vz.BniUoIh6lIfffUkApc.kSCaW7016Ks/UY3mKZhlAnxyhblGAWO', 'NALURITA PUTRI NUR FADZILAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:56', '2025-10-29 04:23:56'),
(536, '211710301077', '$2y$10$/4rkwlQiViXNMlJdbeSNveDUSWQ0GXx8gLwUHIHW/.wTQESNtd/BG', 'RIZKY FIRMANSYACH', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:56', '2025-10-29 04:23:56'),
(537, '211710301078', '$2y$10$Opf3lDt9MSdGQvcw3wCVGu5xdpI4b/4bfxMR263AJl31oqw3Cwo0S', 'ANINDYA DYAH AYU JUNIASTY', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:56', '2025-10-29 04:23:56'),
(538, '211710301079', '$2y$10$s/0G6X9pLQDoI0RGXekph.FX3AfYFSTgbCWthbC7P/HxZQU5nTQcu', 'DIVA PERMATA ALFARISQA', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:56', '2025-10-29 04:23:56'),
(539, '211710301080', '$2y$10$UtPV1XPNCSMyPNYRVGupmOnF/I6dHHJNQLJJh4glNMWZo3vFhF3y.', 'RANI MA\'RUFA', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:56', '2025-10-29 04:23:56'),
(540, '211710301081', '$2y$10$1RQ.CxA96GSuxMtSCj/01.oGqjv39FO0rMqYmxyVtjGsiQMXFuhLi', 'MUHAMMAD LUTHFI NASHIRUDDIN', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:56', '2025-10-29 04:23:56'),
(541, '211710301082', '$2y$10$bvSq3pGlxwUvcT8XRKC52OfG04FhHWdG81PqYZ82Ht4QwNS/DE5VW', 'TABAH AJI PAMUNGKAS', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:56', '2025-10-29 04:23:56'),
(542, '211710301083', '$2y$10$dBE7pJPADmXgq1wVPE6WAOMw7fLmXoSZfKu.JUP.SbUiMaxMY.IrK', 'YOGA AJI PANGESTU', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:56', '2025-10-29 04:23:56'),
(543, '211710301084', '$2y$10$GO9rP43LOMML6ajcDlDfReoO29sfQpb/P/Q4WQ4SD86IuRa1lxGlu', 'MISTY AYU LARASATI', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:56', '2025-10-29 04:23:56'),
(544, '211710301085', '$2y$10$hnSDE3rfwlgNFUyKzWHt9OP/PmJkLz2qjHQov7x2HYnvdsSS/94aq', 'DELIA DEVITA', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:56', '2025-10-29 04:23:56'),
(545, '211710301086', '$2y$10$h8Lec5Wtr8KEhwj9qwA30OxvKCOAmRPcnBe4cuqhJpLIXDLDHec1i', 'ERVINA NUR KHASANAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:56', '2025-10-29 04:23:56'),
(546, '211710301087', '$2y$10$px6BshtLFH2NiqJ9cQ4Ju.Iq6wfwLo5JKgh3kDWlaFuzl0XHsa/Vi', 'YANUAR FAHMI NUR HAPSORO', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:56', '2025-10-29 04:23:56'),
(547, '211710301088', '$2y$10$jTGJptqwjEFoovwi9NcQLOG09VDlxEJ47SRvPiyVFmuixkMyqbGPm', 'SALWATUL AISH SILMIYAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:57', '2025-10-29 04:23:57'),
(548, '211710301089', '$2y$10$p6F.LwJ6u6Ha0okMRsKQLel4hIrHt5rVBT9dkjU7Itgwe4adFOpI.', 'GHALY ARKAN ADIYATMA', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:57', '2025-10-29 04:23:57'),
(549, '211710301090', '$2y$10$yymedpItlOs/NkG1wJoRv.ff4ieF7D78VKjUPCcnCokJl59UmtJ1i', 'WAHYU FAJAR MAULANA', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:57', '2025-10-29 04:23:57'),
(550, '211710301091', '$2y$10$ZPiyEQhTRQ/bbsq.0AYlIOkGeQQ/7vSfZAuVu.PXcDDPiJyU./OAS', 'FANI FAHRURRIJAL AL FARIZI', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:57', '2025-10-29 04:23:57'),
(551, '211710301092', '$2y$10$8vy0V5ePX09kysa7MoTO2uzkI7a9XyULaVVg37AMDD7YwBCFzINDm', 'NAVIS FATWA FADILLAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:57', '2025-10-29 04:23:57'),
(552, '211710301093', '$2y$10$E3O3Bmwleoiqvmp3BboaIuD3wTRsNXOSRZvHuOWFel7B31OJYrdfK', 'WIEKE RAHMA SARI', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:57', '2025-10-29 04:23:57'),
(553, '211710301094', '$2y$10$tLAbySFmoTBlLU.oK4DVle4xRa1D5Dpg1U8UD4PmQPE0Od3GS.U8e', 'ZAKIA NUR FEBRIANTI', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:57', '2025-10-29 04:23:57'),
(554, '211710301095', '$2y$10$4ASmDmZBaGH/lPCqr.0snOnydPhixiqcGy/skNv6Z.YXU4c28TPh.', 'MUHAMMAD ZAIN ASSHODIQ', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:57', '2025-10-29 04:23:57'),
(555, '211710301096', '$2y$10$wTbMU0XdUMqT3aRmYoNoAeUJox/OX6LqKIlnTm0WBbBo/w4s5Mu3K', 'AMELIYA', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:57', '2025-10-29 04:23:57'),
(556, '211710301097', '$2y$10$ylQPrvWaSKsHYYQ0GXlOluKj7hYmUmfAtgzbQuXN.kGtmbzYWEPqS', 'NADILA FEBRIANTI DZULHIJJAH ZAHRO', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:57', '2025-10-29 04:23:57'),
(557, '211710301098', '$2y$10$tdoy0AxylsD/q.L/6vvc/Ow238BtgG6zVbA.FX5r.qkjoZ6XV1612', 'ERGIANT ARLENDA HERDIN', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:57', '2025-10-29 04:23:57'),
(558, '211710301099', '$2y$10$911gl1mSbHI7kHXZMVmrievEjvgrbjF2VTSghDBrA3GFaunlPCJnu', 'WIDANA ADAM RIZALDI', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:57', '2025-10-29 04:23:57'),
(559, '211710301100', '$2y$10$tOivP88IJeqy5Vhsb4IKcupExq4ul1tYTtv.koem8oH9nowfxfv2q', 'NABILAH BALQIS PUTRIANSI', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:57', '2025-10-29 04:23:57'),
(560, '211710301101', '$2y$10$WLaqlZFUulDrUmpCAk5aAOkEzzBXQ1jfu2AmwDvQGL/qJmRtebx0y', 'ALMA TSABITA KAMILAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:57', '2025-10-29 04:23:57'),
(561, '211710301102', '$2y$10$cbJL0tdn71lh00LDqoHgROB21b8mcpwBC46cNAXy3hU7oKeWZ4j2y', 'NINING ARIFAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:57', '2025-10-29 04:23:57'),
(562, '211710301103', '$2y$10$I0gLkq0iTw6TD69JRPMsqez1G7GS1ihnl9u70HiBI.EYFQjhx0Jn.', 'ATSILA RAMADHANI ROSHIFA', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:57', '2025-10-29 04:23:57'),
(563, '211710301104', '$2y$10$S7yHUl9xl8I3RdLKq./USuMVKyfa7o..afKgSjE7sveWHhoWAvF5O', 'MUHAMMAD RAYHAN ALFIAN', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:57', '2025-10-29 04:23:57'),
(564, '211710301105', '$2y$10$lR8Ra4eyAZioC1iKsi5qCOIPaMNNUsjxt.7P1duyAzSJ7MYYSrHoC', 'AWALIYA FARADIBA', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:57', '2025-10-29 04:23:57'),
(565, '211710301106', '$2y$10$BcRtZgZsLhOoz6FDU7imBelHV53ED3ER5dZMqVJ2iImxfNea/uMee', 'SHOFIYAN TITO ABADI', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:57', '2025-10-29 04:23:57'),
(566, '211710301107', '$2y$10$uVOxIfuRDKTsy/HGqKhZ/uOfXe1v85.dZ4le8ux44SiS5ZdT24M6a', 'FAHMI DEWANTARA', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:57', '2025-10-29 04:23:57'),
(567, '211710301108', '$2y$10$UnapGdjcxtl5dr0EYdx5Qe91xf.fr.VuHRXjcD2JcncbX26dEFVIu', 'RIZAL SAPUTRA', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:57', '2025-10-29 04:23:57'),
(568, '211710301109', '$2y$10$7ijZl79M4IQO875UwF76eeZILfa7vdfjImD/DSg8lDjdXcey9I4qC', 'VRISKA AZIZAH AMALIA', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:57', '2025-10-29 04:23:57'),
(569, '211710301110', '$2y$10$hyo3Pymw16SQpL2zy8f2pen4fVJZL/BmEzDTDqMZRGR50cQmk0Cv.', 'NI PUTU INDRA LESTARI', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:57', '2025-10-29 04:23:57'),
(570, '211710301111', '$2y$10$wLyhFCE1pASbe.Q2ncu6tuD/aS8B31AGw78gTCgxRoTkCRRhg90aW', 'SIGIT ARYA PUTRA', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:58', '2025-10-29 04:23:58'),
(571, '211710301112', '$2y$10$dCBlIXCbz4aKeWUTn/ezZ.9k/3RCO1E58QadNjLTAm1QYcYSvB3xG', 'ALIFASHA RAHMANDRYA ADI', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:58', '2025-10-29 04:23:58'),
(572, '211710301113', '$2y$10$kEQF4ZylOk/7CXY7JesVvOjJJFLOYSSQocBUx3YB9rUvgRhMdgqgO', 'ANNISA AZZAHRAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:58', '2025-10-29 04:23:58'),
(573, '211710301114', '$2y$10$Bjteso4jAlMPOZv9zyo8Yuc0hiugUgdYEB6V0VFJ7BQecrg6FYEDS', 'FAJARIKA PUSPITASARI', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:58', '2025-10-29 04:23:58'),
(574, '211710301115', '$2y$10$M5d.QKS845/ljmMOIOlzsO9HD1eXOU6DGa/ofA3CjdbH2XGR3A8TC', 'AWWALLIYYAN FITRATIN NISWA', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:58', '2025-10-29 04:23:58'),
(575, '211710301116', '$2y$10$dhw0PrGxlqh47eOApyRO6ulhgbR0hIMV/BqriNOuHI.jLQIuDSOIq', 'DIMAS WALIYUL A\'LA', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:58', '2025-10-29 04:23:58'),
(576, '211710301117', '$2y$10$H3kjpmOKGFbmB.TrR1mH2.xMu745DLphIgb/Nm7gcPcvKfl.YbQZG', 'ILVI NURSINDIA', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:58', '2025-10-29 04:23:58'),
(577, '211710301118', '$2y$10$0osOYckQwU4zd3f7H1udYON7GMIx52I5VzBQ31ZBg3aRzsJABnf2.', 'BAHRUL ULUMUDIN', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:58', '2025-10-29 04:23:58'),
(578, '211710301119', '$2y$10$5gjnNBS8zVD8FyFeIFdEfu1teSl6zrWJOVLS/V3cgmIZ5uWsq4lNS', 'RIANA FITRIA GOZALI', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:58', '2025-10-29 04:23:58'),
(579, '211710301124', '$2y$10$xjWA1BfcPhv4rbPdHZE.6OXPkOQbwjGUb2LGZ9YUBC4UfL0w43qjW', 'RISTA DEA WULANDARI', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:58', '2025-10-29 04:23:58'),
(580, '211710301125', '$2y$10$BBNKhDYZv/5Ckit/YjoZR.YpJrDcV32wmpnskdkeBycC25ke/Zjru', 'DEV DATUL HELMI', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:58', '2025-10-29 04:23:58'),
(581, '211710301126', '$2y$10$fKRApq4pW3YXnlJcjKs4qulIRokWY8Ztj4tgdGe8KmjWa.rTf9Cbm', 'MUHAMMAD SYAWALAH AMMAR KHADAFI', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:58', '2025-10-29 04:23:58'),
(582, '211710301127', '$2y$10$YssSGNQkMM9F.gIs28JhReR7xGmHZYGPINzY32X91qAev7f653Afu', 'MILAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:23:58', '2025-10-29 04:23:58'),
(583, '221710301001', '$2y$10$AxqCix40yW9el5xBeGV7MODsibfTjTZaIpVN/aDFy0wVh/EXHPdcO', 'ABDULLAHIL MUBAROK', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:20', '2025-10-29 04:27:20'),
(584, '221710301002', '$2y$10$1ZN1KkgI/bklsD6RjcuYdebxEIoXrqEGemSTl.spjYRp8fh78eqkG', 'AINI WAFIROH', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:20', '2025-10-29 04:27:20'),
(585, '221710301003', '$2y$10$nsuDbjaZ.WbeOMCfGUTNYO3.rrHgClDx3TxMVYSlhNdSXYfJ2H0da', 'SHINTA AYU SWASTI', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:20', '2025-10-29 04:27:20'),
(586, '221710301004', '$2y$10$iwwapVLJse7Tzi9KA/4rj.kzzASVRntHjnO4Uu30V0mwfHU9MGHmW', 'MAGISTA SAKINAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:20', '2025-10-29 04:27:20'),
(587, '221710301005', '$2y$10$VRkQObBGNmSIDtvESYi.R.g6NVvQWl/cABjkQCoMIkNvK6tzObH56', 'SARANDA FIONNOLA', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:20', '2025-10-29 04:27:20'),
(588, '221710301006', '$2y$10$EAwjlgFhCwmx0iCkp9dCdupkM/3UB95EPHcxdxkIyPjiQ2adiecVu', 'NASYA RAHMAWATI', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:20', '2025-10-29 04:27:20'),
(589, '221710301007', '$2y$10$YRTQj0S6L37/0nvSpV4sfOqia5rpCmI/2NCjaCMBwQOhLN77ac/JS', 'AZZAHRA NUR FADHILAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:20', '2025-10-29 04:27:20'),
(590, '221710301008', '$2y$10$q26bn5rZtScwnUqKL92IGuLQDZ/OpIYwmwlzPojP3ZhlzrCoX1ssm', 'ILHAM ARIEF SAPUTRA', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:20', '2025-10-29 04:27:20'),
(591, '221710301009', '$2y$10$lDWf.6ACG0qx0s9SOrThfOU2ItYInLd.9nLi9IuveDcgZKwUb.dxm', 'AFIA RIZKY AMALIA', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:20', '2025-10-29 04:27:20'),
(592, '221710301010', '$2y$10$QUfGpXpcRV27O8/3ryOSZ.xYQ7ewF6gy0IY1eBg7FuyblS81dluOy', 'MUHAMMAD ILHAM FARHAN', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:20', '2025-10-29 04:27:20'),
(593, '221710301011', '$2y$10$z/tfRpKsYYoMValIbMdFzOjstnMobf6AHo38K3/OWZ2eneW8B1Bs.', 'FARHAN AKBAR MAULANA', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:20', '2025-10-29 04:27:20'),
(594, '221710301012', '$2y$10$lHwanGPp6502KdtptTHhpu//lf2xiC1Ayh7Lea1RJjbmTo2eON0qy', 'DINDA RAHMA ANGGRAENI', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:21', '2025-10-29 04:27:21'),
(595, '221710301013', '$2y$10$HixfN43q6Y7/a.2hOpswmuhHkQ6rmZjza6K/HiGR3PDif5jxyBK6e', 'MARWAH MUTHIAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:21', '2025-10-29 04:27:21'),
(596, '221710301014', '$2y$10$ODUVwHxfcfjcF1hCsGwTNuzGlTEMAVV81FsGre1Wf5wORY/4YoTfy', 'SITI KHOLIFATUL HASANAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:21', '2025-10-29 04:27:21'),
(597, '221710301015', '$2y$10$PngLpvDqr7F58wjYmYSWDuiBxUx8daCL6F9/C8FeH5oXubzMSuPqC', 'MUHAMMAD ALI MUBAROK', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:21', '2025-10-29 04:27:21'),
(598, '221710301016', '$2y$10$TRuEqQBNyFoMNhmX.CEvLOPQfZfFfkKO0Zm3xxZascUFVmRLfgG9u', 'FARIS MUHAMMAD NUR FAIZ', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:21', '2025-10-29 04:27:21'),
(599, '221710301017', '$2y$10$OTGfpR31qVSEm8iuCKkTp.liyGXIkUDWDHTAsEvUcLULOAyuinleW', 'NAJWA KHANSA NADIA', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:21', '2025-10-29 04:27:21'),
(600, '221710301018', '$2y$10$.2.8WwnITNzZJ5YZ/zTlD.7cqnVyXLuESHZxcEtvvln8nqHCJYfGK', 'RAHMA AZZAHRA', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:21', '2025-10-29 04:27:21'),
(601, '221710301019', '$2y$10$M6Qibw66HXOWfudd/nKxfeTKKUAS6kxMquiDonfGHEaBajfNIJtU6', 'INDIRA APRILIA PUTRI', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:21', '2025-10-29 04:27:21'),
(602, '221710301020', '$2y$10$3fOjinir/eIdpJ5Ia4orQuXH0c/eBip73Uk49kB0dR5yiwLc0ejP2', 'ALFAN MUHAMMAD FAUZAN', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:21', '2025-10-29 04:27:21'),
(603, '221710301021', '$2y$10$VIy1/zMViDGceNopL4GvCO.rdLs6YSkhaIDAstV3arwxCIgZmysPq', 'FITRIANI', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:21', '2025-10-29 04:27:21'),
(604, '221710301022', '$2y$10$pvkqo.Vayuw7fj3A6eSQN.ta.TtH4GFc/8yHUZXiLqYztdbpGrxOq', 'DITA AULIA RAHMAN', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:21', '2025-10-29 04:27:21'),
(605, '221710301023', '$2y$10$QqyoQcYviDq4YUNfUu9XD.WIiCY0Av4fTOV/cyOWLXdl9mo9YRPCm', 'SALWA NURUL AINI', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:21', '2025-10-29 04:27:21'),
(606, '221710301024', '$2y$10$G/Sk3hmxHNG/m5V2FnR64.gVa6rlUAXkKylDOqRZbP.os1HWlke/y', 'DIVA MAHARANI', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:21', '2025-10-29 04:27:21'),
(607, '221710301025', '$2y$10$3fSScmxpcJhhv3Lz33WJw.xcdIsohRWGUqjqYhRuOBGBOAFRLbHE.', 'RIZKI NURUL HAYATI', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:21', '2025-10-29 04:27:21'),
(608, '221710301026', '$2y$10$Wc/9uti/KCinxwv1dT6IVOVVKzhE1jteiIqF1Thkq6JBAIKqkZsNW', 'M. RIZKY NUR PRATAMA', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:21', '2025-10-29 04:27:21'),
(609, '221710301027', '$2y$10$JdIl2NibO9QH.o073Qm0oOYzWA35HfJBQ8UavyzfoggpWH0diNuM2', 'NAILA PUTRI AZZAHRA', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:21', '2025-10-29 04:27:21'),
(610, '221710301028', '$2y$10$DLqxYNPdivig5eLEWbCac.Xhd2STvJUdtBQort14aaBBqJQr3Dtry', 'SYIFA NURUL AINI', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:21', '2025-10-29 04:27:21'),
(611, '221710301029', '$2y$10$DeRNDhQt25v/l.J6rEWQX.H/6GTbR3lwWqfybwu0DBJbQT3hN5qyC', 'M. RASYID RIDHO', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:21', '2025-10-29 04:27:21'),
(612, '221710301030', '$2y$10$WPd3hOoartB2RRVqk.EPGeHRRY3NqZJgDFfHcpyf8EUPRFDixJQ.O', 'DIANA PUTRI LESTARI', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:21', '2025-10-29 04:27:21'),
(613, '221710301031', '$2y$10$bnHz59wAp5LJcx8n4pHGv./pydqRqVI0zb8ZTlZOMkC7hl/PLciJ6', 'FADHILAH NUR KHAIRUNNISA', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:21', '2025-10-29 04:27:21'),
(614, '221710301032', '$2y$10$lPlA89xowpH2n0fsMtxlMOdZMA7ODi0cH68OHASWFlJ2GS9ot9fN6', 'FARIDAH AMALIA', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:21', '2025-10-29 04:27:21'),
(615, '221710301033', '$2y$10$my7.JYk.o/vAHXLYXQwBeumcAqNciWmrDm4bn1jMhM8cImxXMl7yO', 'NAJWA ULFA', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:21', '2025-10-29 04:27:21'),
(616, '221710301034', '$2y$10$OtWFrhqYRCpZnIStAyhJNe/VaNtA1UaQRKd8axdTtxxUocSI.gSfW', 'RATNA AMALIA', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:22', '2025-10-29 04:27:22'),
(617, '221710301035', '$2y$10$ag5hNL5ZvAn3L9rU0IOtfeQB4jTPIFzi015GpHztfEOMfCexjRQZi', 'ALFIYAH NURUL HIDAYAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:22', '2025-10-29 04:27:22'),
(618, '221710301036', '$2y$10$6p2pG7x7FKqnH2cqEHG/9.buSYR3D/J7.8IZlDZfS78fr6lEp4NES', 'DEVI ANGGRAENI', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:22', '2025-10-29 04:27:22'),
(619, '221710301037', '$2y$10$RnFqAnuC1c7C4OtZcvmtyOG3utCkM2EW/ldpUho25gKWghUwGvr1a', 'RIFKY NUR FAJAR', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:22', '2025-10-29 04:27:22'),
(620, '221710301038', '$2y$10$2M/gVe7r0ssNvfDczQOhI.C71K/1xTaysgjhd2lYYZ7yGE8eYdCgW', 'PUTRI MELATI', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:22', '2025-10-29 04:27:22'),
(621, '221710301039', '$2y$10$MLVrMHFjDQBdkOoaEHAsJ.FvEBddLVi4hVyurhuvgVy92o1jtn0Cu', 'ARISKA DWI PUTRI', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:22', '2025-10-29 04:27:22'),
(622, '221710301040', '$2y$10$rZA6FcjAf/gMK6IvOwdz0uf8HvjhlwAac.IvBZE7vgOlKMSVN2BQC', 'LUQMAN HAKIM', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:22', '2025-10-29 04:27:22'),
(623, '221710301041', '$2y$10$LdIRShiNq88iiIM9NRVUpOx1pnVe8bg9GyK4QknMaRqQmXqSS9wZO', 'MUHAMMAD ARDIANSYAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:22', '2025-10-29 04:27:22'),
(624, '221710301042', '$2y$10$GFODxkmnUdly.BeB9LK9Uu5nVsiKa5C8OD.Q2HT1EL.UMceIJOv9a', 'SAFRINA DEWI KURNIA', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:22', '2025-10-29 04:27:22'),
(625, '221710301043', '$2y$10$mmrt3C6QwWnN071EvJf1ceijSnOAQwcGFW9FK1eaGYLgOwZJZF9Ma', 'FAJAR DWI SAPUTRA', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:22', '2025-10-29 04:27:22'),
(626, '221710301044', '$2y$10$VDXF4.MltBfc/.4Y/Ii7netLPDPUUOWIfokPpC.I1emhrUdqDjiAa', 'NUR AZIZAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:22', '2025-10-29 04:27:22'),
(627, '221710301045', '$2y$10$CnCSogFxYp1F3h3zpdoYkeMd8wSaWDiqlqaaFsHxO1SKFKfWWbiZO', 'SITI NUR HALIMAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:22', '2025-10-29 04:27:22'),
(628, '221710301046', '$2y$10$AMgJclUisF6uOut21lJOTOuCbuHmyiwMozOlTKgl6kwB0oveQ0gay', 'RIZKY SEPTIAN', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:22', '2025-10-29 04:27:22'),
(629, '221710301047', '$2y$10$COmnlcZTdcAdVPlR5nNmkei6sQFMIPWzcIE0sR0e9p5gTS5uw9TPK', 'AZIZAH MAULIDA', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:22', '2025-10-29 04:27:22'),
(630, '221710301048', '$2y$10$i1i7y/IJhqRNH7l6BF1q6e9HqmXBffOzqpjFYlyP5qh4zAjLgALCi', 'RINDI AMALIA', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:22', '2025-10-29 04:27:22'),
(631, '221710301049', '$2y$10$3H4B7REyEzpRSXQibWMyZe.XnhDN5SQntQmDdPKRVMONRk0tZ4mCW', 'NAUFAL RAHMAN', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:22', '2025-10-29 04:27:22'),
(632, '221710301050', '$2y$10$9WJPm/YtGR1fnm0WtWssgeCNyuNl5xqUbmkxI6FjE9s3n7Q8IGC4y', 'ELISA NURUL FITRI', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:22', '2025-10-29 04:27:22'),
(633, '221710301051', '$2y$10$1WRzwVmJKZNXNmH1td3WNODCYwrTeQRmoaNQ.XZMXik1eo7twMXmi', 'RISKA NURUL MAULIDA', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:22', '2025-10-29 04:27:22'),
(634, '221710301052', '$2y$10$O5UaI/30OdThWLVSl9ktpuIR3ySflDItj9BzFSphiutRGwpNmDwoO', 'MAYA PUTRI', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:22', '2025-10-29 04:27:22'),
(635, '221710301053', '$2y$10$V8fVnm51AIx/jZ.Id7FMm.898QMySp2ygZ2qqA9LLLU3VbAHSOllS', 'SALMA PUTRI ANANDA', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:22', '2025-10-29 04:27:22'),
(636, '221710301054', '$2y$10$qbw2LAE580w1IZftyMvqFOBp6aiwdUxhwbboA0ZXZ2ir0aUwIySuC', 'YUDA SAPUTRA', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:22', '2025-10-29 04:27:22'),
(637, '221710301055', '$2y$10$dfeU1wrNQOaMT.iZ5NRRa.dxSY4co9Viap9GyT.k0S0fz4X8h0l5W', 'RIO ADITYA', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:22', '2025-10-29 04:27:22'),
(638, '221710301056', '$2y$10$GItPLG.JNahV97sgPTfQheQroLStt5q6Axp928fhtUvZ2Ef/H2MoG', 'SALSABILA RAHMA', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:22', '2025-10-29 04:27:22'),
(639, '221710301057', '$2y$10$D92VTEmNDo8b/bSSFkh7/uRtSwrdLv/Q0tHqZX5fUOflsWmRUai..', 'NOVITA RIZKI', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:23', '2025-10-29 04:27:23'),
(640, '221710301058', '$2y$10$Uhz5lCIGHe5GcQvfGCGTIe1rywpxmX1LJ1o25sNGb.7OzqbBsmbfu', 'PUTRI NUR FAUZIAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:23', '2025-10-29 04:27:23'),
(641, '221710301059', '$2y$10$szNp4bQ2t5k9Gyih9FbhS.ZD6M1xcCAMSRGWN3r0AxFLjyJQEq8/2', 'ILHAM NUR RAHMAN', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:23', '2025-10-29 04:27:23'),
(642, '221710301060', '$2y$10$PLzoFt/Yah7NOUIhUFSc..YfyyoxwvU6M/Gr90wVTURSmL41RXIb.', 'DEWI LESTARI', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:23', '2025-10-29 04:27:23'),
(643, '221710301061', '$2y$10$gYXn8WMXzGz.ID2FkunlT.O6S3zVX5iIak8No2yVlZwOkRd53dVXS', 'KHARISMA PUTRI', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:23', '2025-10-29 04:27:23'),
(644, '221710301062', '$2y$10$EedO.hikQf6wsljJthy/0.AJQuRj9DvpspumglfRDTOJaeLbpWD6K', 'RINA MAULIDA', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:23', '2025-10-29 04:27:23'),
(645, '221710301063', '$2y$10$Ixi1712eD09s.4nLKLxtfO/8psBObRvXuRj2IfbGf3uBRIDIpuzqu', 'MEILANI FITRIANI', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:23', '2025-10-29 04:27:23'),
(646, '221710301064', '$2y$10$YwrAcWgVSV8LLJygqKUavOtoIuxNz8XLkLwWHlTSY7GJbaxaUFOGG', 'ADINDA RAHMA', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:23', '2025-10-29 04:27:23'),
(647, '221710301065', '$2y$10$XlpwYH2TG.W2YCuseRhgXuSwCuGkKr8qOFT//8L0.XeSsjtZQBu26', 'SALWA NUR KHASANAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:23', '2025-10-29 04:27:23'),
(648, '221710301066', '$2y$10$W.LhxJfTbKxb0KhYfVP2B.okBSqZeeOrlT.zph3.wAsW/8QSiyBui', 'DIAN RAHMAWATI', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:23', '2025-10-29 04:27:23'),
(649, '221710301067', '$2y$10$RhhV0ZP0EN83J5r0kXnReOmsvvKGhDr3/7IQwkYqdWMxT.qZdYQrC', 'NAILA FITRIA', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:23', '2025-10-29 04:27:23'),
(650, '221710301068', '$2y$10$XI14O7qcPBsoRbU2yu5pEuR3vXQul02YIUq15ljWyE5xgG6p0YhI6', 'RISKI APRILIA', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:23', '2025-10-29 04:27:23'),
(651, '221710301069', '$2y$10$anE359avPpGifvirEsbbee0GEwerBFaOLChq0jCSWRCvemqmOj3LK', 'DWI AMELIA', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:23', '2025-10-29 04:27:23'),
(652, '221710301070', '$2y$10$6sjQC59NZSHmoYoTonc7GehcFCD6BmOZ/KFhvobegEnIJ.aLmCLpG', 'ANANDA PUTRA', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:23', '2025-10-29 04:27:23'),
(653, '221710301071', '$2y$10$c5C90EFdN9AdsAB9uz/WZ.OMtfQr2/lHw0lNzi3dAAaJaRcI8b9Gi', 'RISMA ANGGRAINI', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:23', '2025-10-29 04:27:23'),
(654, '221710301072', '$2y$10$ptvS1egHaewMBvoO7ZD2V.FqGhFrgpjQYKiUPqj37W.n.j3HeWMtK', 'NOVI RAHAYU', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:23', '2025-10-29 04:27:23'),
(655, '221710301073', '$2y$10$gzahnmluEEtvMLEeEIXLieseNuuXBzq8crGkbHF0u1Tkwc9QZqKAe', 'MELISA ARUM', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:23', '2025-10-29 04:27:23'),
(656, '221710301074', '$2y$10$w67bz5AXuX3L4a9r6VjKvudtdyEg/JoKYQ.OA0FyOrQd.8pJJlS7m', 'FIRMAN RAHMAN', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:23', '2025-10-29 04:27:23'),
(657, '221710301075', '$2y$10$xH4ntBqd9Im19fh4HI9pbOBxj8G5LYoUOQ616ZZhTLKIyYeoU8PE2', 'NADIA RAHMA', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:23', '2025-10-29 04:27:23'),
(658, '221710301076', '$2y$10$46h/B19rx69KEQ9Iafcqg.zuitR8M7DYVsaMCzI.GNUz3fIcsGWsm', 'MUHAMMAD RIZKY', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:23', '2025-10-29 04:27:23'),
(659, '221710301077', '$2y$10$TRlQ9UW5rf8aD.DDhcdQceDjipv5HuMFqAXE/j3wJB6qwlhMHbR5.', 'RINA PUSPITA', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:23', '2025-10-29 04:27:23'),
(660, '221710301078', '$2y$10$lYFgW4bDw3iXacWYUU0XBu4ICr3MWYEeqw92dEbZsKJMIHWz8X7Ei', 'FAJAR SEPTIAN', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:23', '2025-10-29 04:27:23'),
(661, '221710301079', '$2y$10$F8V0qFfIYGIAhjesmJpl9.HKajOQIE4q2KK3tE0rUORqY3Js.Mk0C', 'PUTRI NURUL', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:23', '2025-10-29 04:27:23'),
(662, '221710301080', '$2y$10$.MLO1Va5giQoIdhwacYTwOCg89kW.4uLCsYbl5UGfI7TOl6Pgam6C', 'ADI SAPUTRA', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:24', '2025-10-29 04:27:24'),
(663, '221710301081', '$2y$10$Gw6ToEcpIaK5n99F4T7xbuJQBOoKc//Kmu1PgpY9cJfZnDMhVaDT.', 'DEDI IRFAN', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:24', '2025-10-29 04:27:24'),
(664, '221710301082', '$2y$10$yUDv5coIu8WVtWVPtrrhT.vA/JoJQfEw3dW2JtqJ9w3tE86ZrRd72', 'RISKI WAHYUDI', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:24', '2025-10-29 04:27:24'),
(665, '221710301083', '$2y$10$PRujWPBvqUCjwcttKG8LIutfzC3Tfs35zuUHq/xknXkjrKNmV.Rbq', 'DIANA SAPUTRI', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:24', '2025-10-29 04:27:24'),
(666, '221710301084', '$2y$10$yJjXT9bmCU.9RJAedFCq9.PJhZcQOF./V2T8MBxdWiV6OYIKtvP8W', 'ANISA RAHMAN', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:24', '2025-10-29 04:27:24'),
(667, '221710301085', '$2y$10$aNt6aR5rxz4Eq1iN56TkGe3OnoQ3UODlUsLpnGoEePPl71uaT5gFi', 'AGUS WAHYUDI', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:24', '2025-10-29 04:27:24'),
(668, '221710301086', '$2y$10$cK//5oFhEGvysE2qY9pijuPi6rr4XlHjTnUnKPfCtJxeYPLEBjTBC', 'RINA SEPTIA', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:24', '2025-10-29 04:27:24'),
(669, '221710301087', '$2y$10$6PSR6EYu4LFS2G/0reNAtu/swmOuMRngpR4dzdCt24x0ZBh6JK4dK', 'SALMA NUR HIDAYAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:24', '2025-10-29 04:27:24'),
(670, '221710301088', '$2y$10$sc6TjvgscQfIFZMNC5H58ONudK3COmfCCztPo44z9TJDCgbG.WN6q', 'FITRIA AYU', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:24', '2025-10-29 04:27:24'),
(671, '221710301089', '$2y$10$9a1EKfaQ2w0J9SmeYqb1ju6MPievAwaIKKJ.r9rkViUIVviQmpcuy', 'NUR RAHMAN', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:24', '2025-10-29 04:27:24'),
(672, '221710301090', '$2y$10$N0fDv96GpaH6k1UpC706ueTl9oInZ8/tzMjNarKccucN3mKgh0Ny2', 'RAHMA DWI', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:24', '2025-10-29 04:27:24'),
(673, '221710301091', '$2y$10$YifhKBwaqsXKxLTy2XDa2eavYFmMqsKntZXPl9ipOrCHQx90imuY.', 'INTAN MAULIDA', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:24', '2025-10-29 04:27:24'),
(674, '221710301092', '$2y$10$r/0S108bUbUTWLkIQ6KINuk9.sjb19Mx0hjbSvOFiBkSoZ.rplw56', 'PUTRI LESTARI', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:24', '2025-10-29 04:27:24'),
(675, '221710301093', '$2y$10$bAPkEOu71iGc6QKqAsklQOKJdkIyZ2UC5uYPm2sC4rMXvxVSE0AUO', 'RIZKY MAULANA', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:24', '2025-10-29 04:27:24'),
(676, '221710301094', '$2y$10$XQYdqDesyFQgO2Rc37BX1ue38iA1N7ua3xpNC5VALCRn0IlMbhUdS', 'DIAN RAHMAN', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:24', '2025-10-29 04:27:24'),
(677, '221710301095', '$2y$10$pcAH0m.PChAyeyKDWY6ATO6Z6m1uOINrZmfkGMTzD5VOQUmKXSnBy', 'SALWA LESTARI', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:24', '2025-10-29 04:27:24'),
(678, '221710301096', '$2y$10$0afMbWkQdzUTG0U0A9TD0OeEyQmS5F/lTihPPzlgS/PheccZznAA2', 'NUR RAHAYU', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:24', '2025-10-29 04:27:24'),
(679, '221710301097', '$2y$10$ngGLDYqmAWSA8w2TCX5qceXc9ELOmjZeGMdYUBWUWct5FaGvYDlwW', 'PUTRI ANDINI', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:24', '2025-10-29 04:27:24'),
(680, '221710301098', '$2y$10$U7TL2Zs.YskVzp5EN2C9ausyrREB8JTBFyi2X/TxJ3PQHSH4qU9m6', 'IRMA NUR FITRI', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:24', '2025-10-29 04:27:24'),
(681, '221710301099', '$2y$10$O6MuQ3hkT960lY9/2/CiteQBcpDc6E9TQbJhIb3OuKbgL34/psq3y', 'RAHMA ANDINI', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:24', '2025-10-29 04:27:24'),
(682, '221710301100', '$2y$10$NuCZePQ2GFjzNPBfH7QRFurl1ZwLHLr/kAv3JOJFZNxAO4qvNwwd.', 'INTAN NURUL HIDAYAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:24', '2025-10-29 04:27:24'),
(683, '221710301101', '$2y$10$9YgIIjQ.CMuzH5z.oRoDv.YhN8.1CbknAbEMLGDhE21hAcb9fLC.2', 'MUHAMMAD FADHIL', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:24', '2025-10-29 04:27:24'),
(684, '221710301102', '$2y$10$8I4/MrQjCZEL3qX7htGHsezgdQ2zQ3Lfs0r.0svUKB0pmMLVk5CUG', 'RIZAL ADITYA', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:24', '2025-10-29 04:27:24'),
(685, '221710301103', '$2y$10$mhh2LYqpwJdW7QJ/UbBfTuDlONsVSNtAPAwyQ/hiO1fCz/quDTVgq', 'SALMA LESTARI', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:25', '2025-10-29 04:27:25'),
(686, '221710301104', '$2y$10$DkhVAild7jqaQE3dMy4oneFYCC454sUQKN58I9acYoaY0oLwQTu/m', 'ANISA NURUL HIDAYAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:25', '2025-10-29 04:27:25'),
(687, '221710301105', '$2y$10$Jt88QyOP/OFsxVS/r.XifuqCY4moh.M2or/NTwqFGanOGlOhGtKVG', 'RAHMA PUTRI', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:25', '2025-10-29 04:27:25'),
(688, '221710301106', '$2y$10$5TT/j4jhHKCtjpiEQtPwU.wqDVcTaa5H8GqCMwU1ytE1o2Itel5Xi', 'DEWI MAULIDA', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:25', '2025-10-29 04:27:25'),
(689, '221710301107', '$2y$10$L1xRZUfAxd5mBro9nuEAIOSX2WpbPJt711rTHX52XqAIBkXnMb6Vq', 'ANDIKA RAHMAN', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:25', '2025-10-29 04:27:25'),
(690, '221710301108', '$2y$10$9cwJh77GmDPKBDuo.rxWRevmJ9q2zs4S16pGtUVGUojaJHEY4VK2i', 'FITRI AYU NURUL', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:25', '2025-10-29 04:27:25'),
(691, '221710301109', '$2y$10$1wqBIj6Fmwm8.hZkiAmBY.qgRUlcbjBlizSYUbe1DBU55.PvYaYd2', 'RISMA LESTARI', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:25', '2025-10-29 04:27:25'),
(692, '221710301110', '$2y$10$cPf7u204Ba14k0z631grUeuO92NKgjMR2WP/d66JVXeCcGgWoVzaK', 'NURUL AMALIA', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:25', '2025-10-29 04:27:25'),
(693, '221710301111', '$2y$10$FGwqDsOrAj5Kj2trXHhySuTkmFz9gMqVKGF1aXh4xdES/pg09r8xu', 'SALMA PUTRI NURUL', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:25', '2025-10-29 04:27:25'),
(694, '221710301112', '$2y$10$8ifugPi/4oxZdXfVY60XDucZxzWSBNn6D11aqnHUsNFmrnqBUwynG', 'RIO NUR RAHMAN', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:25', '2025-10-29 04:27:25'),
(695, '221710301113', '$2y$10$pAJNmaZZayEvkuhkbsV0N.GryXcQYsmxMpnzetzDsUPK9DjXqq0jm', 'INDIRA RAHAYU', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:25', '2025-10-29 04:27:25'),
(696, '221710301114', '$2y$10$HVeB4nnIx09ousCpVuWFBuePH3rlgOoJuX0.IOOllqqgmFj.5MeaC', 'RINA AMALIA', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:25', '2025-10-29 04:27:25'),
(697, '221710301115', '$2y$10$.okhQEPp4GSuyTjJfYX0HeDVnwUr/l4cYpjP3C2HFwwRe7/j7ByEi', 'ILHAM PRATAMA', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:25', '2025-10-29 04:27:25'),
(698, '221710301116', '$2y$10$v41LoMijtQpkOejAHW6In.521NL1mFEV0vtexr88UO01xPJ9cMQpe', 'DIAN SEPTIAN', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:25', '2025-10-29 04:27:25'),
(699, '221710301117', '$2y$10$OXxz22cRc.iVQ.K6mjTdguvqVFhb24RMgenPtWfdLkjBxAIgU2FCG', 'NURUL HIDAYAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:25', '2025-10-29 04:27:25'),
(700, '221710301118', '$2y$10$sQkEQqJY9tC4q0e3KS/oyOEJHGt3w2FiWGJZD.ZlB2m3fNeUgVyU6', 'RINA DEWI', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:25', '2025-10-29 04:27:25'),
(701, '221710301119', '$2y$10$93PhgrtLQwwIkQsnRriwTuV/Yv0shYJ6kiv9RpXgToBrRdbNj/hA.', 'SALWA AYU', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:25', '2025-10-29 04:27:25'),
(702, '221710301120', '$2y$10$Y9FrnOxecGg7vzMzNrNe7OCdcC1YnKVIqdurxf1lkyjdmzRq4H0pW', 'PUTRI DIANA', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:25', '2025-10-29 04:27:25'),
(703, '221710301121', '$2y$10$m2mS3kdVB964euXw9DvoMe7WVsdQDZoa60Q6Wiyrjq9zICzNwfXC6', 'REZA MAULANA', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:25', '2025-10-29 04:27:25'),
(704, '221710301122', '$2y$10$EuKThQwnx5n4lJulKmlRJeK2FqESCYigVxD803uabRt3GrvfedKAy', 'SITI NURKHALISA', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:25', '2025-10-29 04:27:25'),
(705, '221710301123', '$2y$10$9Inb8IdpbyQkeSC6DXlrM.DUpivQoF9kgGgyTBkRzhThZwPniDMCC', 'ADILLA HIDAYAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:25', '2025-10-29 04:27:25'),
(706, '221710301124', '$2y$10$GoPrRRhRE56Xp.Wq8/cZROSt01RfnfKWYd5cAbKUYiLb/8BacuvNC', 'WILIAM WISNU', 'mahasiswa', NULL, NULL, '2025-10-29 04:27:25', '2025-10-29 04:27:25'),
(707, '231710301001', '$2y$10$a1xSAgCNZiBDyDPvNPmVPui.4VW459NLqsf42Xf1iPVY6GfV6Jdoi', 'LELIANA JESIKA SUSANTI', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:17', '2025-10-29 04:31:17'),
(708, '231710301002', '$2y$10$eNcyHi0Uzl4.QUsFHLrvmet0e6LUkyhjbyxHLCJhYGAB74d1ObiZ6', 'CRISTINE MARGARETHA SIANIPAR', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:17', '2025-10-29 04:31:17'),
(709, '231710301003', '$2y$10$ZECg12C84nDcOURNQxO10uPvm4OxWYU4esURq3XOhQj9b37DFyCoq', 'NILEN LOUISA', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:17', '2025-10-29 04:31:17'),
(710, '231710301004', '$2y$10$kekzDHiPIKmZsjTMe8V6e.CVdfpwP1T7ej7BM2nr4D.1vWJm5I8Mm', 'KARINA ABRILLIA', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:17', '2025-10-29 04:31:17'),
(711, '231710301005', '$2y$10$v5j.TAxhuQhfLy3ezufGleeR39xsbiK7UeLqXFbyNZ4mP7zFHCeQa', 'EKA DINDA MAR\'ATUS SOLEKHAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:17', '2025-10-29 04:31:17'),
(712, '231710301006', '$2y$10$b/Dv87.EAld7Xs3Y2IYh3uqCDzl/au4uorAR1N3dc/97CfIAys/5y', 'NABILA TRI OKTAVIA', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:17', '2025-10-29 04:31:17'),
(713, '231710301007', '$2y$10$FyNeIHk4jp6gdGQEu7E24O.lTET4UkSXXchgHjL0M2mXtqGtJ4f8a', 'DAMAR RIZKY PRAYOGA', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:17', '2025-10-29 04:31:17'),
(714, '231710301008', '$2y$10$NwOaVRV4uCZMepPy/SnumOXzRqKRFja5dENJkfo45bw038H8pleV.', 'SUCI RAMADANI', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:17', '2025-10-29 04:31:17'),
(715, '231710301009', '$2y$10$uWqH2OQxOCNVJNirfOVXbe4.8IzroVIlWfwhnxtCfuGw5z5SL8Ave', 'ROFIDAH RAHAYU WILUJENG', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:18', '2025-10-29 04:31:18'),
(716, '231710301010', '$2y$10$Kgp7TRTzItRYLYjmWLIFCONHePWAgEm1/9eR0QLY0QnoJP.qpzoJy', 'SEIFIN AMELIA PUTRI AFANDI', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:18', '2025-10-29 04:31:18'),
(717, '231710301011', '$2y$10$w.A5vBBFstdpvXpTwNtQ1uca113Dnw2tszDxrRrCNcSkRvwt91/ey', 'WAHYU NUR AZIZAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:18', '2025-10-29 04:31:18'),
(718, '231710301012', '$2y$10$gNxuHFw9rEiynxibCYAT7ebG6nV3ov5.xmxEv5TxXm6vkISKiqKsG', 'ANNISA NOVITA NURFADILAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:18', '2025-10-29 04:31:18'),
(719, '231710301013', '$2y$10$8BYb9xpdGJfo3Q9N5hJalO1z7T14cfcGT8kiN/Zi4l29NplebkqB6', 'RAHMAWATI ARDHIANA PUTRI', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:18', '2025-10-29 04:31:18'),
(720, '231710301014', '$2y$10$xklMuR2mzIGqZetCv8qFw.mGNjBqjTSNbRBPSRn6yVX/gkliHOdF.', 'NURUL HIKMAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:18', '2025-10-29 04:31:18'),
(721, '231710301015', '$2y$10$lyS/y0V1/gVOLWMWlMSEpemO9w7h/e1F3mT9CVci.RCUOzc1V12pq', 'DESI PUSPITASARI', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:18', '2025-10-29 04:31:18'),
(722, '231710301016', '$2y$10$cSWnBu8cda6d3K0kKFJweunwAVYHduaz4GkNlHUwDCrrXtHss2Lhi', 'DIAN RAMADHANI', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:18', '2025-10-29 04:31:18'),
(723, '231710301017', '$2y$10$0jDbpfO2Rrwcf8hocXxCWedYG/giaf1/QcSyEE4XYRl/MN2g/M6ju', 'SALWA NUR AZIZAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:18', '2025-10-29 04:31:18'),
(724, '231710301018', '$2y$10$RK9IAgUzbOKGU6lt8ucwI.pfbux7UvaCEK3m/jnYMp8eD4BKHy/sm', 'ILHAM NUR FAJAR', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:18', '2025-10-29 04:31:18'),
(725, '231710301019', '$2y$10$kLwJx/Q1Vf1tMZAB9KweX.rCGZzNi/ndr/M7J6AuUjmKUAe.aFP3i', 'ARISKA DWI PUSPITA', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:18', '2025-10-29 04:31:18'),
(726, '231710301020', '$2y$10$m6JUZlQo5HvxGgfUoIwb2e9RO1Bm5p1ks0ma11D/cUN1F4Q.4q9ju', 'ALIFIA NURUL HIDAYAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:18', '2025-10-29 04:31:18'),
(727, '231710301021', '$2y$10$HEmqFWhEYe/Yf8NjtHrK0.gmzY2cj8N0srFs2alJaPbexJoVBmLUq', 'PUTRI MAULIDA', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:18', '2025-10-29 04:31:18'),
(728, '231710301022', '$2y$10$M/B6./Bo7y6/pZWB9dPmdepWLBDabLt5F50EhV8x830sjSwYzOEcC', 'NOVITA DEWI', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:18', '2025-10-29 04:31:18'),
(729, '231710301023', '$2y$10$Ilny9NhoKX.L3NUi1XTzS.mGHl69KIFyaMfGHGPirENDBielzFVX2', 'DEWI ANDINI', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:18', '2025-10-29 04:31:18'),
(730, '231710301024', '$2y$10$IoGLfIP7DWLjK6bvBm0D8eK.RMf3tKJMjFl4Kd0Ys3B4vWucjjB8C', 'AFIFAH RAHMAWATI', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:18', '2025-10-29 04:31:18'),
(731, '231710301025', '$2y$10$saxhT8RuvSlHzXAiiJDQ1eCEFjEvqqhuPujetohYLboOk0yypqQpa', 'SITI KHAIRUNNISA', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:18', '2025-10-29 04:31:18'),
(732, '231710301026', '$2y$10$GkxVcdEwd2fSpudidgETJuF0eEcJOneDBTHdQlSza.z9kf8hfznQq', 'ILHAM RIDWAN', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:18', '2025-10-29 04:31:18'),
(733, '231710301027', '$2y$10$ms4PIRkujrWr9vEYKJnfKeok4upcwPgkNZlQ/CUII.3JzSFVkjzlq', 'MUHAMMAD RIZKY FADHIL', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:18', '2025-10-29 04:31:18'),
(734, '231710301028', '$2y$10$eKu7a9JUbszGBNbQjGUy5.03PWGAzCJ426Yg1iK3jdLt9mwHGYyF.', 'RAHMA AYU', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:18', '2025-10-29 04:31:18'),
(735, '231710301029', '$2y$10$r9FSB123RzusZ.AoK3qi1eUhBRYY.IFwbI1.STXM5Iezz0aRnlnX6', 'RISKA PUTRI', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:18', '2025-10-29 04:31:18'),
(736, '231710301030', '$2y$10$gxS95kVNzZjQ5ivBfIzpj.EMj.1paoqU3fzt61oDsXTebDOlFI9Ay', 'DIANA SARI', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:18', '2025-10-29 04:31:18'),
(737, '231710301031', '$2y$10$1nk1Cth.k37.MIffa8JU/.UaltAbeconFRvYbtQn51jUkvOoqf4/m', 'FIRDA NUR HIDAYAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:18', '2025-10-29 04:31:18'),
(738, '231710301032', '$2y$10$ID1A0cIO.qJR4Fj8rgp8a.Ot63tjufZAw9Us7F0/IVVsoCD1aBKx6', 'NADIA RAHMA', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:19', '2025-10-29 04:31:19'),
(739, '231710301033', '$2y$10$aJb/EjZ.MMiglbanvSf91.mYqoFKvfEcoA3mEF5JXMWgwKU0rs6DW', 'PUTRI SEPTIANA', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:19', '2025-10-29 04:31:19'),
(740, '231710301034', '$2y$10$v3P6KWkVfko6JBBkgvR0XuyQXs8vjZvhCJOZE/Hug4vG0LwC1a70m', 'RINA SARI', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:19', '2025-10-29 04:31:19'),
(741, '231710301035', '$2y$10$zZr.NKURVXYHz7cGbUCGlu84CR8K3A//6RrJpw6sH3yNLpPJuC1dS', 'ANISA RAHAYU', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:19', '2025-10-29 04:31:19'),
(742, '231710301036', '$2y$10$slt4Douov4rnNG7lvC3ASuRqp1Q.QS0HI9g7IEgorE/vreO/219Gi', 'MAYA FITRIANI', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:19', '2025-10-29 04:31:19'),
(743, '231710301037', '$2y$10$MPQvhIhC2LGPPQ2cWkDN0.qU/q8/oxQvzMbkTDY7xnAl6BEIqJtP.', 'SALMA MAULIDA', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:19', '2025-10-29 04:31:19'),
(744, '231710301038', '$2y$10$PGIgg8Q/03K../VwMlmp8OnhObESqXSL8aKvEdLsSJmAxsJOiCqc6', 'NURUL AMALIA', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:19', '2025-10-29 04:31:19'),
(745, '231710301039', '$2y$10$ndwhKyav.cccWc08p.e5JONX9JnmKt6hc0pzPLiw6B90OM09F1xpC', 'DIAN FITRI', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:19', '2025-10-29 04:31:19'),
(746, '231710301040', '$2y$10$5GA398IUR3p/eAFzcE7o2OrdnjvocMP8LU7f62ZiOjXLJrGShPEF2', 'RISMA PUTRI', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:19', '2025-10-29 04:31:19'),
(747, '231710301041', '$2y$10$vEYiIxaOQOwqPIsXVG1Wq.MilPzQCTuJBAOQhcqfkG5nZJPZKZF56', 'FADHIL RAHMAN', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:19', '2025-10-29 04:31:19'),
(748, '231710301042', '$2y$10$7yQ1.MSFvwHK2S9zOymz1.3OKIClJ/w5HE6vpvARW0c9laKaSDnku', 'SITI RAHAYU', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:19', '2025-10-29 04:31:19'),
(749, '231710301043', '$2y$10$IDzYAWjI2QgDHVwumrC4su0j4401wK2QOsrhEywUR5zWu8ent2r5y', 'FAIZAH NURUL HIDAYAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:19', '2025-10-29 04:31:19'),
(750, '231710301044', '$2y$10$3BO9jCyQldir9BzJNAyF9.tjlkJbLSSwDUOcjJ0WdEVIGwcUkgNHy', 'NABILAH RAHMA', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:19', '2025-10-29 04:31:19'),
(751, '231710301045', '$2y$10$YZPn6Pn7vRfROBkGvVL1QODdEAFehH1MkWxX89KxuXABzGBgVcpF.', 'PUTRI ANDINI', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:19', '2025-10-29 04:31:19'),
(752, '231710301046', '$2y$10$QRBQ/bKyUFprUw.PX.63Depx.GMn65JSRrVKGsPzYtvXhBBNDebCC', 'MUHAMMAD RIZKI', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:19', '2025-10-29 04:31:19'),
(753, '231710301047', '$2y$10$IYyaGtx4INPUUSChTWHPn.1OxFgXh82LDMb66a8oxZACtMr7MYENq', 'FIRMAN NUR HIDAYAT', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:19', '2025-10-29 04:31:19'),
(754, '231710301048', '$2y$10$NiZZ9P9rsrwp7McUtx0m2uRUQdkugvfpsino6ZXdG2/pK5DT.EEA6', 'DIANA SEPTIA', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:19', '2025-10-29 04:31:19'),
(755, '231710301049', '$2y$10$EwXJ9Fq9UIDEZpFuJTEbc.v8eGl6bJpZdV683mzmL3L9JmpVurppq', 'NURUL AYU', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:19', '2025-10-29 04:31:19'),
(756, '231710301050', '$2y$10$6r4FoLkUpELCnPqXCH0R.uOqHWbSoB/JK.2SDdHwGihgDGqMeqZaW', 'RISKI NUR HIDAYAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:19', '2025-10-29 04:31:19'),
(757, '231710301051', '$2y$10$9TcNgi6D/0F385iaHVzdPew0C7U/kQMyZ/P/KFRDvpJkb92y8TMw2', 'DEWI SARTIKA', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:19', '2025-10-29 04:31:19'),
(758, '231710301052', '$2y$10$uqZQWRMsleGN5jO8JGtaf./Vb5b1IgrRIqALUPY6dhdZxn5BheCr2', 'SALMA NURUL', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:19', '2025-10-29 04:31:19'),
(759, '231710301053', '$2y$10$QJQcxdlUri6LLfOgZWjyWuWrop.jE3nXFJBqdLnXTjWfdDGDPHMAi', 'RINA NUR HIDAYAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:19', '2025-10-29 04:31:19'),
(760, '231710301054', '$2y$10$muAwVBUiCAp3/R87J4MYxOfmytD4lnxXKCpNlaew1UrHwcO8W.Mbm', 'PUTRA RAHMAN', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:19', '2025-10-29 04:31:19'),
(761, '231710301055', '$2y$10$tn6J578ePsyVaaCCi8O3VuNNFdVCwrzDLHqXPGJRHRftVdA/nZN7G', 'MAULANA FADHIL', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:19', '2025-10-29 04:31:19'),
(762, '231710301056', '$2y$10$5/3KhYWHHnJ3Tg829yOpJuatmM0lwCc25o5zdmioFWKXYiSKmLUHa', 'FITRI NUR HIDAYAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:20', '2025-10-29 04:31:20'),
(763, '231710301057', '$2y$10$gH63TpWEXjYGEL4ZxgteSex3y3jxA93r8RaNhZsGI7XoJqulO4nne', 'INTAN PUTRI', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:20', '2025-10-29 04:31:20'),
(764, '231710301058', '$2y$10$kQvSIo70El130QcEGP8K4et9/TcaL.DCrwftfkT9CWzXz3H6ojGxO', 'RAHMA NURUL HIDAYAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:20', '2025-10-29 04:31:20'),
(765, '231710301059', '$2y$10$Vt37Kp0aWhAuCPuQbT0Cr.4LnBKSKmGssnIMV.XTQymSfkF7pt0PK', 'PUTRI LESTARI', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:20', '2025-10-29 04:31:20'),
(766, '231710301060', '$2y$10$kv/UGIw52YeDb.loWE9Sr.odLh/Qlas1Nf/wGEGoLBZUsVoiAQvoW', 'SALWA MAULIDA', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:20', '2025-10-29 04:31:20'),
(767, '231710301061', '$2y$10$AVInga47ybzgzlZ5h6lzlu20j4xrL55itP2Q2boi6K7gZJvfxkyte', 'ANISA NUR HIDAYAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:20', '2025-10-29 04:31:20'),
(768, '231710301062', '$2y$10$l0J3veMtXR1De.qSV5zGQee4208Ljv8lEm28sVXhTWeGb22cIDf3e', 'RIZKY AMALIA', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:20', '2025-10-29 04:31:20'),
(769, '231710301063', '$2y$10$KXFWhybU8NKU/0HRO8mEIunbURJ8dPspvn8CYy6VwN8nouSngcXwW', 'RINA LESTARI', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:20', '2025-10-29 04:31:20'),
(770, '231710301064', '$2y$10$WGHxxfz8wR8zpJhfzaBc3.x1P2PFK0/fIKU9ej99FR1nY8JgZAupG', 'MUHAMMAD ILHAM', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:20', '2025-10-29 04:31:20'),
(771, '231710301065', '$2y$10$LGTXOKC4whiq3XN9SlYm3.aDwnOGUGpwBHkOMe1.twjYhfzMQlSlu', 'FITRI AMALIA', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:20', '2025-10-29 04:31:20'),
(772, '231710301066', '$2y$10$jR7hqopS4TbpIWfTiknbZesH6ygWEeb9skttgC7qDcMO8mHj26uYi', 'RISMA RAHMA', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:20', '2025-10-29 04:31:20'),
(773, '231710301067', '$2y$10$szpyb3dhrI26uZD0MNugpeebhfqLeWO.FIw4hgGqejRFZCT.NhdsS', 'SALMA NUR HIDAYAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:20', '2025-10-29 04:31:20'),
(774, '231710301068', '$2y$10$odAnNUB4uL0FcmIxfoObeufzkOhAodUyMrAzViv0N7QjPCMMGTjEa', 'ILHAM PUTRA', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:20', '2025-10-29 04:31:20'),
(775, '231710301069', '$2y$10$ac2FPzt8NQZld5oyeb9gou/FSfqoKTggb9EKQ3UVqbLFPtC9xhq/m', 'DEWI MAULIDA', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:20', '2025-10-29 04:31:20'),
(776, '231710301070', '$2y$10$ZZNIVyTDcqjvDVpn.Xzgue8YDoWEXbJoMJqpFdcjqi/7SSW/9ncy2', 'FADHILAH RAHMA', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:20', '2025-10-29 04:31:20'),
(777, '231710301071', '$2y$10$PdvAoNTj..OWvklnj6Jk8e57CoCei.dOsUxyOqS1ah5uZuBkYCB9K', 'RIZAL PRATAMA', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:20', '2025-10-29 04:31:20'),
(778, '231710301072', '$2y$10$VXE3VsXtw4uvf8nipwhW2ehPf2dnHfOEIduDQnKYZFL.gsugd0Y0W', 'PUTRI ANGGRAINI', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:20', '2025-10-29 04:31:20'),
(779, '231710301073', '$2y$10$MbzyXGWuwuoB2.I6xxTYgeVxZJAsPNlV/gMd/hEk7zpX7YtWLP6my', 'NURUL HIKMAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:20', '2025-10-29 04:31:20'),
(780, '231710301074', '$2y$10$LLIGoKWJU9OkXnO8ULOGrebPKHDuNXXdCiK6Xyvq31rlB2tqBhJ46', 'DIAN SAPUTRA', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:20', '2025-10-29 04:31:20'),
(781, '231710301075', '$2y$10$AMLBelZkH/Czx/RiiQkgNu/q0Y6HIZ/91osZypleeyhRWDmWBwkkW', 'RISKI NUR HIDAYAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:20', '2025-10-29 04:31:20'),
(782, '231710301076', '$2y$10$xbPxbNnoceqUJH2q.oiAEOF83PI4BrmBy/0QWKLLhFCM9CQYtkov2', 'FIRMAN MAULANA', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:20', '2025-10-29 04:31:20'),
(783, '231710301077', '$2y$10$i0..BPuWYrB/3ECD4aEjZudxbm3J6i5g2nBGUVteZLqmrOKpcdTHe', 'RAHMA ANDINI', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:20', '2025-10-29 04:31:20'),
(784, '231710301078', '$2y$10$hKrvvxZ0NYEZG24nKmreeOTai9xWUxhXLTvezDZYuFTeNGy0XryUa', 'SITI NUR HIDAYAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:20', '2025-10-29 04:31:20'),
(785, '231710301079', '$2y$10$S/dQOltKLyPgSnJrVnpOC.mphvLE0l4lA9HBZKkc2kDWLRkbMPVv6', 'ANANDA PUTRI', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:20', '2025-10-29 04:31:20'),
(786, '231710301080', '$2y$10$c7umd43OA5Gl5hA6U5HcuuK.o2OWQLtQmK2HVSql6HixX7u0FWgP6', 'MUHAMMAD RAHMAN', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:21', '2025-10-29 04:31:21'),
(787, '231710301081', '$2y$10$ckT1Zpaod3zUcpgHQmLYUuL1WRojWX8IS3107qlm79jViJgnh8fz2', 'DEWI FITRIANI', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:21', '2025-10-29 04:31:21'),
(788, '231710301082', '$2y$10$XJAYXJ5H1IbWpgpaUfc8oOnAY4jGYPowFRC0W0srWmBef5yHRyCne', 'PUTRI NUR HIDAYAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:21', '2025-10-29 04:31:21'),
(789, '231710301083', '$2y$10$0ICaiufIhN5NV8TMagZ/M.wp6kwWnJF5J3QBf2t99QUyXGrg/6sBy', 'FADHIL RAHMAN', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:21', '2025-10-29 04:31:21'),
(790, '231710301084', '$2y$10$jWpucqQRmkY5Y8URnvJy.e4aSAfYnccCNUMUIhApFlsj34jZONtV6', 'INTAN DEWI', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:21', '2025-10-29 04:31:21'),
(791, '231710301085', '$2y$10$NYdu8Bp75vPoaErKBZTRRe2ClvYlksTab4iYzvZt7M9sSV9mVoqT.', 'ANISA PUTRI', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:21', '2025-10-29 04:31:21'),
(792, '231710301086', '$2y$10$JNWjhcuLNfoYx3qkJnutt.0V6oOigI0Iw0L6W0urz8GHdstZeDpL6', 'RAHMA NUR HIDAYAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:21', '2025-10-29 04:31:21'),
(793, '231710301087', '$2y$10$0jpYIoW.K4iTrqPhh1vnluE8OcqB9VhfA0C2hOX/HoM/vZhz2oFO2', 'SALWA PUTRI', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:21', '2025-10-29 04:31:21'),
(794, '231710301088', '$2y$10$FXGczvoGmnq8oIvfcNyQtuhiBA21sNVx5rBp6KfYTSsSeK49gNAaa', 'MUHAMMAD RIDHO', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:21', '2025-10-29 04:31:21'),
(795, '231710301089', '$2y$10$J3IAA7S3SsODOK0uBO9rSuWateIVP2nIuTmf7lRQlySrsp076MBFm', 'DEWI LESTARI', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:21', '2025-10-29 04:31:21');
INSERT INTO `users` (`id`, `username`, `password`, `name`, `role`, `nip`, `nim`, `created_at`, `updated_at`) VALUES
(796, '231710301090', '$2y$10$ltEKV32fKHt6uHnQCoe.VezfJfuJkc1jMQOK7.Iy6wbnJa4C1LNnO', 'RIZKY PUTRA', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:21', '2025-10-29 04:31:21'),
(797, '231710301091', '$2y$10$BqsP4XNCNOTs1XQnB5W2g.0LMY2.UjLShGRTiwkc8yn3HlZ9jkL.S', 'DIAN NUR HIDAYAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:21', '2025-10-29 04:31:21'),
(798, '231710301092', '$2y$10$osA9s/M2OpH5Ogf4t36qXuu3LtGBXRuT4tj73/X18E9NxyWirHpd6', 'SALMA MAULIDA', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:21', '2025-10-29 04:31:21'),
(799, '231710301093', '$2y$10$CRzaA.fFhC4Bz6PiSbNw2uk6v6c28VK6Oz9KXxHI1wqW3OGvbjBnm', 'NURUL RAHAYU', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:21', '2025-10-29 04:31:21'),
(800, '231710301094', '$2y$10$tblO60FVonE6o4EQiZNSDuWvoBH9ZohmmB.6EvaF3XAj.C1sxHoD2', 'PUTRI ANDINI', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:21', '2025-10-29 04:31:21'),
(801, '231710301095', '$2y$10$L4ga7uyOTnXNWAOka7KnzOFWLo1i1V/15p4wZJVbUp9Unskuiq4pG', 'RINA SEPTIA', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:21', '2025-10-29 04:31:21'),
(802, '231710301096', '$2y$10$bE8frdXli7kTcca7aosshu3SumP5rDgjVkou7ZPLt.p9qdQdlf2Cu', 'MUHAMMAD FADHIL', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:21', '2025-10-29 04:31:21'),
(803, '231710301097', '$2y$10$U//P6/tgeJpBN8Gcg4caTec7hfIKPHzjKdG2NXAeu/ec61pfGcfxC', 'FITRI MAULIDA', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:21', '2025-10-29 04:31:21'),
(804, '231710301098', '$2y$10$SNfjnYxk4fGoJI9kSk2y9OVd.2CaPgLpEBG2R6IEXRINc.bXGPy3W', 'SALWA PUTRI', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:21', '2025-10-29 04:31:21'),
(805, '231710301099', '$2y$10$ZxAdV7Z2nWZSe52rbKRvgOX3wijQHwvlNew4CgSX1MCdrNtE8RF.W', 'ILHAM MAULANA', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:21', '2025-10-29 04:31:21'),
(806, '231710301100', '$2y$10$swOPE05cF/7PPsTa1LGFfOp2e89YKS06hG8m0G6hNAR6mI28MS89y', 'RINA MAULIDA', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:21', '2025-10-29 04:31:21'),
(807, '231710301101', '$2y$10$qkZV2YFSgAInqDAy9Wx2o./myhuM2fg8UI2JC6QOA2DGu3.WUs7oW', 'ANISA NUR HIDAYAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:21', '2025-10-29 04:31:21'),
(808, '231710301102', '$2y$10$1mUCY84B630OYGNruPizMOf6WPwUDORpZCqJtwrOE58uo9P2bMGUa', 'FIRMAN SEPTIAN', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:21', '2025-10-29 04:31:21'),
(809, '231710301103', '$2y$10$U87IXK/NnFLa8iKBzjgXg.wvLLevPUKV1Qz7My43eGrf98qN3H03u', 'RISMA ANDINI', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:22', '2025-10-29 04:31:22'),
(810, '231710301104', '$2y$10$QHel3tgzbcUwfgdRkIZyhOJxMauRCJHYnFGBaWbYcCs2HhYHubeFO', 'DIANA RAHMA', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:22', '2025-10-29 04:31:22'),
(811, '231710301105', '$2y$10$V4HSpkWCfRmd/wSpdHZejuvJwZ9ardLTstKwjxiOJ9Q8x6IVNXlde', 'PUTRI NUR HIDAYAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:22', '2025-10-29 04:31:22'),
(812, '231710301106', '$2y$10$N/8bGDIViI9kGYu9P17VKOPEA8nXq9TyZB6T/qMvE7T5BkfLgd3jS', 'RIZKY MAULANA', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:22', '2025-10-29 04:31:22'),
(813, '231710301107', '$2y$10$ggaeCA3Yslo65qv9mIJBsu593Pc/FERMxj7gNuJYQM/zb.06F9gvy', 'FAHRIZAL MEILANA ANGGARA', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:22', '2025-10-29 04:31:22'),
(814, '231710301108', '$2y$10$oyLnyNj2TLpE8J5xblROUuMCofyOdNQ.BHzsQ2Yd7E.sCBHvba7TO', 'MOHAMMAD ABDUL QODIR JAELANI', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:22', '2025-10-29 04:31:22'),
(815, '231710301109', '$2y$10$A0zSS3GCvO1zvv2.qtlNb.Mtv4Ys3AFhBNVIcpfJ1E3blyJjNZ6iO', 'ARDI RAHMAN HIDAYAT', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:22', '2025-10-29 04:31:22'),
(816, '231710301110', '$2y$10$StM0K5rJJgROts5vjxJRfesFFhm4Vl0BjF/gik5lgvODKph55e52i', 'IZZA FUAD DJATMIKA', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:22', '2025-10-29 04:31:22'),
(817, '231710301111', '$2y$10$XcUOfNIKuC53bMhr4jxZV.0dWatl0uOP28IbA/CoWUMriy4nlpQR2', 'MUHAMMAD UKASYAH RAZAN', 'mahasiswa', NULL, NULL, '2025-10-29 04:31:22', '2025-10-29 04:31:22'),
(818, '241710301001', '$2y$10$1s3EGh1cjgskGqXvEjDoV.Un5IabUqa9P7.To5nl8mwFPDPMbQzUW', 'MUHAMMAD IQBAL AL AYUBY', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:07', '2025-10-29 04:36:07'),
(819, '241710301002', '$2y$10$wvH3QJ40JDAxDe1Lw0IsOeP7S.hDIKjf0B4buT.4mNQNyc5z9FGwS', 'DIMAS GHIFARI PUTRA', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:07', '2025-10-29 04:36:07'),
(820, '241710301003', '$2y$10$q1RW6D1SKMXEX8yildhzyOja00W25MXInILB9df71NkwTRS0fa9FC', 'DEWI KUSUMA WARDANI', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:07', '2025-10-29 04:36:07'),
(821, '241710301004', '$2y$10$q3gMyCi9zHXePJweASn9aOliL2MxLUbqE7xG/GNHN/LMQ9vlwLzr6', 'SASTABILA WAHYU MEIRINE', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:07', '2025-10-29 04:36:07'),
(822, '241710301005', '$2y$10$abhOhGKCEuqdmRRW724A6OckmFw2gzNNkd3C/IJc6H7rwbP4OondG', 'KARISMA CAHYA KARTIKA HARJADI', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:07', '2025-10-29 04:36:07'),
(823, '241710301006', '$2y$10$vBo3gc.IoDFQwIZ43Oy5m.rDnXWScNaE19cp4BT1R8U00Yk7HS71y', 'MUHAMMAD RAFLI SAPUTRA', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:07', '2025-10-29 04:36:07'),
(824, '241710301007', '$2y$10$3stxUHc/nHGu41TDEUcbyu8kbn3pwngFthTNf/Gbz./Y2Sb9iOUkq', 'MAULANA FATHUR RAHMAN', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:07', '2025-10-29 04:36:07'),
(825, '241710301008', '$2y$10$.XYvn0Kju2zvvk.rt.IL1OnoyIa4B.f95oi3h4haQ9rvC9Wuf15v2', 'ARTHA PRADANA RAHMAWAN', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:07', '2025-10-29 04:36:07'),
(826, '241710301009', '$2y$10$PVJ6XQqi/c9tOugpTf6Wou0qfB3WpR8uKQB0x.j533H/u1XB5oqA2', 'SALMA KHALIDA PUTRI', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:07', '2025-10-29 04:36:07'),
(827, '241710301010', '$2y$10$4l/4HEv1Rs/obzf2m4g0NuQJkNDV6VZhdTiBj5VrHUJ.OozKhFMK.', 'DEWI ANGGRAINI', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:07', '2025-10-29 04:36:07'),
(828, '241710301011', '$2y$10$vszas6uTTG5tDcoOrmGVC.vDKWirzJ/vBKeDDnUgZb6zlo/9KOEui', 'NURUL MAULIDIA', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:07', '2025-10-29 04:36:07'),
(829, '241710301012', '$2y$10$tDDkNUU0XtAhhChmfd9KBO/q9n7F0BY/2jDSMLLFIi8eJFPPP7eOe', 'AZZAHRA KHANSA SHAFIRA', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:07', '2025-10-29 04:36:07'),
(830, '241710301013', '$2y$10$mUxxg9IcapYKqBI8ilZd8.83aO4eW/4bUxjOeIULvER4D0OdHwWN6', 'PUTRI AYU NURMAWATI', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:07', '2025-10-29 04:36:07'),
(831, '241710301014', '$2y$10$Ls.OPoT28CPemv8Xn1QzpOPzipy4R0HpOQzygZHlcllAApkMNTBwq', 'NABILAH RAMADHANI', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:07', '2025-10-29 04:36:07'),
(832, '241710301015', '$2y$10$3IU1QhlbfZTo21qEHnf3I.lWKPzXrkpO3mCqefxxkzZgJiIvk1e2y', 'RISKI ADITYA SAPUTRA', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:07', '2025-10-29 04:36:07'),
(833, '241710301016', '$2y$10$qGJ5hRiefTmFGEAPZZoUduYNx7elBNKCTSBvPFQ2siUJAxGfkCv8i', 'SITI AULIA NURHAYATI', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:07', '2025-10-29 04:36:07'),
(834, '241710301017', '$2y$10$Sgv3GO2tqIUAQDCtin5SCOEbN.Ns4gLfsGnBhYwJxB4Qeo2Utl.uK', 'DIAN NOVITA ANGGRAENI', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:07', '2025-10-29 04:36:07'),
(835, '241710301018', '$2y$10$TaSRNVlYAcGm6jEjlJJQYeMcxwKt8ehI277cPFNjZoGyRCAi7bY4W', 'PUTRI DWI SEPTIANA', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:08', '2025-10-29 04:36:08'),
(836, '241710301019', '$2y$10$y0bS4gzY76oshHRic2UAiu.0NKMmNNqFO.zNsglFZbMuTKZ.59wtu', 'FIRDA MAULIDA', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:08', '2025-10-29 04:36:08'),
(837, '241710301020', '$2y$10$4u1IETMuW0LzxqLPzSZgIOJynjmZaB9EJ5R2.xGCB155RjhAgAozW', 'ALFIYAH NUR HIDAYAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:08', '2025-10-29 04:36:08'),
(838, '241710301021', '$2y$10$zomFgbI7Io2xU8NNWD7JbOEkUSr3FiNol6TiTmkVcAlyhl6iNgZSq', 'RIZKY FAJAR MAULANA', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:08', '2025-10-29 04:36:08'),
(839, '241710301022', '$2y$10$2OLDY4L8obgoKahGkEsX/OlaiPXahjqgWpK4EStK7wt.kNTL1J0uC', 'RISMA AMALIA', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:08', '2025-10-29 04:36:08'),
(840, '241710301023', '$2y$10$ZpUfYsvYxiTn32.HKrEKqe91pRkHV8g1sfHueMp.MCe5a4C80FtL6', 'ANNISA DWI PUSPITA', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:08', '2025-10-29 04:36:08'),
(841, '241710301024', '$2y$10$PHnMRcCdG2Gu0/TAVhK9O.jjgHIObEGmhFA6mby9FpKdW.X8bD.K6', 'SALWA NUR HIDAYAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:08', '2025-10-29 04:36:08'),
(842, '241710301025', '$2y$10$7BB5cWAXECa13g9MqwCqceHKVVpu7pHcATUFOD0bwQz8wWO5FLkyW', 'DEWI SEPTIA RAHAYU', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:08', '2025-10-29 04:36:08'),
(843, '241710301026', '$2y$10$ZAX4Vs800/u.ZVVjtBRZG.K472JgKoNn75jTcfJ4sUFnn0x71HpIC', 'PUTRI NURUL AZZAHRA', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:08', '2025-10-29 04:36:08'),
(844, '241710301027', '$2y$10$xP7cXa0ApQ9Hyhi0D1ez3.3VS8uGmXC8r/D4n0ktVIIE1JedR5mE2', 'FIRMAN PRASETYO', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:08', '2025-10-29 04:36:08'),
(845, '241710301028', '$2y$10$S/X6VEYx167LAeUzkohrSunc9M3nu5OKzpqL6hxM.0wyATPLpIcTO', 'RINA AMALIA', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:08', '2025-10-29 04:36:08'),
(846, '241710301029', '$2y$10$rc80G4VFBqthZFOy4KyMtOpLHDJWZS4/kjeeoHGq4z5iFWykUJHTC', 'ANDI SAPUTRA', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:08', '2025-10-29 04:36:08'),
(847, '241710301030', '$2y$10$mnoIW8CR3C4DUm.oGeTfFea2ct/wqLY6S1Q6bkxsDFQM0LBSsFf3K', 'RAHMA AYU FITRIANI', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:08', '2025-10-29 04:36:08'),
(848, '241710301031', '$2y$10$TwJxzjtD6IZbyCEfcJLjXult1CBmNPvxU//z/ehsFZtX4ufE0gkFi', 'SALMA ANANDA PUTRI', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:08', '2025-10-29 04:36:08'),
(849, '241710301032', '$2y$10$XllnBoW2YACJQvQ7YAzuree7j6eED72BkddxUC6dyBUdhJHbyolJu', 'NURUL HIDAYAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:08', '2025-10-29 04:36:08'),
(850, '241710301033', '$2y$10$KOFrKpXstkVOEiXo2VSexukSb840jyvDyV/VyRmrcLeZB9HjUG4/6', 'DEWI ANGGRAINI PUTRI', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:08', '2025-10-29 04:36:08'),
(851, '241710301034', '$2y$10$G1UilqcbsvTSyNP1pkp1a.nMrPgIuqUZzr0nQCSljAEEkwRdxdBv2', 'MUHAMMAD FADHIL', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:08', '2025-10-29 04:36:08'),
(852, '241710301035', '$2y$10$NxYsMqo6PRWioBzWa1j8RO7SI4OIj8PhtMGfqhQumamU83GKfBOzi', 'FITRI NUR HIDAYAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:08', '2025-10-29 04:36:08'),
(853, '241710301036', '$2y$10$RJbIOVCqCU9faaNAy5U40ehKTWRcK3qYbEwbkGQqjaMDNQ5k23lWq', 'RAHMA MAULIDA', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:08', '2025-10-29 04:36:08'),
(854, '241710301037', '$2y$10$BDt9sGZhUFmUjmuX7M6MeOUmaygFuvnwg2jBiPej5AzajFD2x.RUy', 'PUTRI AYU ANGGRAINI', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:08', '2025-10-29 04:36:08'),
(855, '241710301038', '$2y$10$RMAMvN81yzX00RMn3lZIPOdGwe2nlaIHBfCrGB7jLs.Xdqyu9lzpK', 'FADHIL RAHMAN', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:08', '2025-10-29 04:36:08'),
(856, '241710301039', '$2y$10$M6cJ4RMYl0fJXdduo.n//egHh7TYzfBz9qredZ0MkQYkVcHU8ttia', 'SALWA PUTRI LESTARI', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:08', '2025-10-29 04:36:08'),
(857, '241710301040', '$2y$10$8cHETtbaJ51Lnbx4coiyGOgv1D8mcmxXQif.2i/LuNd5sAeF1kvbS', 'RINA NUR HIDAYAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:08', '2025-10-29 04:36:08'),
(858, '241710301041', '$2y$10$JtDJfiJHbSa8P1V3m.wHreBtslRe/1BCOIhbsGn0e1cOhaR2NfEpu', 'ILHAM SEPTIAN', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:09', '2025-10-29 04:36:09'),
(859, '241710301042', '$2y$10$7lFGIMQRrtJIwuHzFNMAieZywhohPskl.NnLJvgI7kk9gNUoHn8/a', 'RISKI AMALIA', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:09', '2025-10-29 04:36:09'),
(860, '241710301043', '$2y$10$MtUr9rYQZGWOuEnB60TRt..cQHtm1IRlqPvTMaVLSBKhK30WU19pq', 'DIAN NUR HIDAYAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:09', '2025-10-29 04:36:09'),
(861, '241710301044', '$2y$10$xablwWMVfcejewRCjXPcyO537C4j1BcG2iwDB61TqfbkTBMzw6oDG', 'PUTRA MAULANA', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:09', '2025-10-29 04:36:09'),
(862, '241710301045', '$2y$10$GdcpiyeFyK3aFaxGTOUgJeExRI6vgbOPvnXiSceexlR0qnpbCeXIq', 'MUHAMMAD RIZKY', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:09', '2025-10-29 04:36:09'),
(863, '241710301046', '$2y$10$xZdeYgdcnX/U6vVObllvhukMUyC/FdXnkSZG1304UQEX4ghg3Dvka', 'DEWI FITRIANI', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:09', '2025-10-29 04:36:09'),
(864, '241710301047', '$2y$10$2gdaXL/ojDiiv5E6rfa1V.R1SC46TWQnlQxHfXF7NLdodi.l2zHI2', 'RAHMA LESTARI', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:09', '2025-10-29 04:36:09'),
(865, '241710301048', '$2y$10$grDII8OB16jcGV.Tpx/SO.ZVd1vqJ7n6E3M3TY2ah8JpiKoGycQl.', 'PUTRI SEPTIANA', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:09', '2025-10-29 04:36:09'),
(866, '241710301049', '$2y$10$.AtPdPfrof6ravV4T99rQ.zNbBnIrwSBzk7..0bkIJOjUORn9Og3e', 'NURUL AMALIA', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:09', '2025-10-29 04:36:09'),
(867, '241710301050', '$2y$10$BbkcBTfif1DTdy4GpAMlk.ZkFXBnQUcUtkUSFF3Vf.2ag/OZ2IyOm', 'FIRMAN HIDAYAT', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:09', '2025-10-29 04:36:09'),
(868, '241710301051', '$2y$10$VK264ucFYoQuFToRt0d5Z.pwvnRC8mxMUxYM1RJrq0qe.lnumU8i2', 'RISMA PUTRI', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:09', '2025-10-29 04:36:09'),
(869, '241710301052', '$2y$10$5s5WVIIVIELOKTRoxRUQI.lSMCcbWWT0N4UWq51zdLZv4F9LsQk8O', 'ANDIKA RAHMAN', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:09', '2025-10-29 04:36:09'),
(870, '241710301053', '$2y$10$7Ahti1n6ewf/SVzzqtnCDucP4GNbAcSEdD9XOQWrwJ7PWXVAh0o5W', 'SALWA MAULIDA', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:09', '2025-10-29 04:36:09'),
(871, '241710301054', '$2y$10$/hcBH/z21lZ8PHIh1aft..Rq2sBn4QtPEvmJqlh.JWuFTKiIttzhu', 'DEWI NUR HIDAYAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:09', '2025-10-29 04:36:09'),
(872, '241710301055', '$2y$10$WuQFbBUrHeeVGQckng3rk.2MNar1jrq0qvsPkQSb7t9HUHeT2ttAG', 'PUTRI ANGGRAINI', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:09', '2025-10-29 04:36:09'),
(873, '241710301056', '$2y$10$1IWDADR1NGtdX4OCbb5XUOnDcu2eNYhmH/Uqw.Oy8MIghJbWTxhle', 'RIZKY MAULANA', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:09', '2025-10-29 04:36:09'),
(874, '241710301057', '$2y$10$EpcnshbbrBTey3.R/AtYxetfdey32iBpcfexEngaXAuocCkYAkRpi', 'RAHMA PUTRI', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:09', '2025-10-29 04:36:09'),
(875, '241710301058', '$2y$10$26dJxmjowoZnBosjVAiTUOPkg8XobQY94Fwbo.QHdnM441g8nIbWS', 'FIRDA AMALIA', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:09', '2025-10-29 04:36:09'),
(876, '241710301059', '$2y$10$82EE2XQbJvBtp2KS5JkqxOcjIKOSRXYjjRSVkKd3PGBPPDR.gVU42', 'NABILAH HIDAYAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:09', '2025-10-29 04:36:09'),
(877, '241710301060', '$2y$10$qhswf00qkiKDuEze7yk3CestvIcem6vyLXW386Ttfzk9L/1bW72ae', 'SALWA PUTRI', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:09', '2025-10-29 04:36:09'),
(878, '241710301061', '$2y$10$D9hbX7D76wmXvBkUj227OebaRSHL.7FKW1zQFFRGWrJNXZFuukdtC', 'MUHAMMAD ILHAM', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:09', '2025-10-29 04:36:09'),
(879, '241710301062', '$2y$10$hX21KySILYQvBXxVUy3aeuAc6awrGjeuEk3sfSb6yMQF95ZGL5Jsy', 'RINA NUR HIDAYAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:09', '2025-10-29 04:36:09'),
(880, '241710301063', '$2y$10$C5Nv9VhQ8BG3uuzA9DAAZ.tb4Bpv5i6gU6f8TD9tSGvfWx4T.EFcm', 'DEWI SEPTIA', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:10', '2025-10-29 04:36:10'),
(881, '241710301064', '$2y$10$6Rhn3qQRfiRVN0KsMCoZ3OKsPWGcTl5usowopg6KiWy.D8w6s96gS', 'PUTRI ANANDA', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:10', '2025-10-29 04:36:10'),
(882, '241710301065', '$2y$10$3xlEChR/dVqKeW1z/nM1JeqA14NU2ABJgIcmEzfbSbA5Jh.SK0be6', 'RAHMA LESTARI', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:10', '2025-10-29 04:36:10'),
(883, '241710301066', '$2y$10$Fzl4Ra4hftuHQ4RB502rqOj6tolZZhnZh7tKuhtJx2MUmjhaVWlbK', 'SALWA AYU', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:10', '2025-10-29 04:36:10'),
(884, '241710301067', '$2y$10$lSqS2cLt3ilaXXstEWWzLuTiab3i.Iw/Sw32rwE9QNOf/psGFtbja', 'RISKI HIDAYAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:10', '2025-10-29 04:36:10'),
(885, '241710301068', '$2y$10$fbPlXBHRHlpXuMON4.EipO2VLwlI2iIgC5f4uRzCpWuS8h/CqnsSC', 'DEWI LESTARI', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:10', '2025-10-29 04:36:10'),
(886, '241710301069', '$2y$10$LCWdMrnCL9kMmlJelzAwlOiGI/T3B2Orf7h7vK93nC0Nqt3gqRohC', 'MUHAMMAD FAJAR', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:10', '2025-10-29 04:36:10'),
(887, '241710301070', '$2y$10$vhqaiiZEDkoEo2cd2YmXYuFxXkbK1q.36mACV1Z1vtvKWb.0Ko39q', 'FITRI HIDAYAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:10', '2025-10-29 04:36:10'),
(888, '241710301071', '$2y$10$8ovjvoyXDxjivRdZTpl/6urylrkEjKV6OfOKokBcUkwV/q95JDHfu', 'SALMA NUR HIDAYAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:10', '2025-10-29 04:36:10'),
(889, '241710301072', '$2y$10$uaOUwhWuXNTef08CvdZKRu7JvY0yRCutjemsjpD2y78/9KS.uFMyW', 'RIZKY PUTRA', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:10', '2025-10-29 04:36:10'),
(890, '241710301073', '$2y$10$mMSCwQAQOYBSuDipFz8RDuPkpfQGaZnY8fC7veT/iW8C6EHQHTg5a', 'RAHMA AMALIA', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:10', '2025-10-29 04:36:10'),
(891, '241710301074', '$2y$10$1GHYmU7UpcWHlSoBb.VNUetuzEY6/IMnAyYwM/x8kprgy.odgMvYK', 'PUTRI NUR HIDAYAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:10', '2025-10-29 04:36:10'),
(892, '241710301075', '$2y$10$W4/KAKNKpq.8lQXXUoApvubkv2k/VoQitAxB6l.FgCD.dkLBlgYFW', 'MUHAMMAD RIDHO', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:10', '2025-10-29 04:36:10'),
(893, '241710301076', '$2y$10$MUGs9RwZCrjUMOtrRLXGcelLAAKNy0AvnxWaVlCQCBrnJWMH8c3qK', 'DEWI MAULIDA', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:10', '2025-10-29 04:36:10'),
(894, '241710301077', '$2y$10$/33.8onWOvDzkWyMA7nmZuqgWfx9T9lDw54CLUVvtwKabkkH0dAzC', 'RINA HIDAYAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:10', '2025-10-29 04:36:10'),
(895, '241710301078', '$2y$10$cMnejVOvgHVtT9.itUUThu4viXTtJB71mF3Q5MkX76WkVbaIh8f8C', 'ILHAM RAHMAN', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:10', '2025-10-29 04:36:10'),
(896, '241710301079', '$2y$10$mc9UhRGBvCQ8/pyJwaJ65.s0WiKXHS28GmozIPi5PUvOZFC1nx4Da', 'SALWA LESTARI', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:10', '2025-10-29 04:36:10'),
(897, '241710301080', '$2y$10$T9vMaB4JSFZXDPvvncXCSeBcFWCKlm.vCokkdQjggsUOXOGoX3s7q', 'PUTRI NUR HIDAYAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:10', '2025-10-29 04:36:10'),
(898, '249919990391', '$2y$10$ZtXUTAiIt2JI5muyuaYQH./8Jx8VCKXzhDwHVcYthOupc7uSlBB7C', 'AFI DWI RAMADHANI', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:10', '2025-10-29 04:36:10'),
(899, '249919990396', '$2y$10$JfWikyQOpHEpf1n47S8o/O.Zy9P/YeDP98QjWHB7ADE9A81V8ckTi', 'MUHAMMAD YUSRON FEBRIAN', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:10', '2025-10-29 04:36:10'),
(900, '249919990397', '$2y$10$HfmlTCoB/ZGn2sSHd8NmeOignP98.ujHoL3xyGrItKN3UkUuD1Az2', 'DIVIA JUWITA ZAHRA', 'mahasiswa', NULL, NULL, '2025-10-29 04:36:10', '2025-10-29 04:36:10'),
(901, '251710301001', '$2y$10$KOb7LNlTr4kXXK94xuAJceMRF3.sdFTL2MG5HL.iJhRuFLKpZiPHq', 'GIONY SOFIA BALQIS AZZAHRA', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:38', '2025-10-29 04:41:38'),
(902, '251710301002', '$2y$10$eUmfR8U2KniEdYcnXpewDeNClhfuMZim/kuU/hRnKrB5Go0M9Uarq', 'RIDWAN DWI SETIAWAN', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:38', '2025-10-29 04:41:38'),
(903, '251710301003', '$2y$10$O8FzpeS6lEtlk9GKpSFmsed7IkJUvePYSwCxp5Rgiob1BLfFRSDKi', 'TASYA IKA OCTAFIA DEVI', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:38', '2025-10-29 04:41:38'),
(904, '251710301004', '$2y$10$n0KVqocIXQR9t.o2cOmG3.2Qeu8tc2uDbc0SRVZb/JsXD25T6Ui5m', 'REFIANA AURELIA SAPHIRA', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:38', '2025-10-29 04:41:38'),
(905, '251710301005', '$2y$10$PrUrktDrcxau6XLJlZF7B.S7yI1FG/cq3PjTLYKvknHvPJYyJLLeO', 'NASYWA KIRANA', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:38', '2025-10-29 04:41:38'),
(906, '251710301006', '$2y$10$d65JDpvIIu7VHGmOR/FiZ.fvIvSyHS3yCmhrLxq6.4YXTTFS0xZ8G', 'NIA AMELIA', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:38', '2025-10-29 04:41:38'),
(907, '251710301007', '$2y$10$2dcIFUng60xPz0IcTdWEdOyYfw0xJLTY.x99RKp2pDWMsMTbLCv9m', 'NOVA YUNI LESTARI', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:38', '2025-10-29 04:41:38'),
(908, '251710301008', '$2y$10$QrN/wBo0sRtPFJMpEQrOrOi1lEzdZbTw.9pjR7jVfSrauWB7xH8Vi', 'SETYA NIKITA APRILIA ARUMINGTYAS', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:38', '2025-10-29 04:41:38'),
(909, '251710301009', '$2y$10$Xf3heWbw1/O5QnJ8oTm29e1jlchqXQFuKRhLiqRv1cE9CBXIFVv5W', 'VIKI DWI LESTARI', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:38', '2025-10-29 04:41:38'),
(910, '251710301010', '$2y$10$1vQO2FW8l1tjoFWwwiC5HO8SM9Xy4qqcgbxGZPVrna4YbqMOXrw8G', 'MOHAMAD NOUVAL ZAMZAMI', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:38', '2025-10-29 04:41:38'),
(911, '251710301011', '$2y$10$E7tdFBo9b8nohsKE4ZMZ/ucStDd/XNmV.Jd0NZqW3fGLq4tH/ZIoW', 'RAHMA YULIANTI', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:38', '2025-10-29 04:41:38'),
(912, '251710301012', '$2y$10$yEUosouQOJ1vUnkSUmllqOt7lGVIlsBpAqdsnhCUePvOsPNxJea3O', 'PUTRI INTAN AMALIA', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:38', '2025-10-29 04:41:38'),
(913, '251710301013', '$2y$10$bAp0H98Goqz1YMvJ9X.1VOCq4mCH79ALDQF10xF2A/MXFXpTcT43y', 'REVINA EKA AYU PRATIWI', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:39', '2025-10-29 04:41:39'),
(914, '251710301014', '$2y$10$hEI.d.yleHYpCkJeOrg.deOOw3REhlUKj39F6hdm6l3WBIKzFYJ8i', 'RESTI DEWI KUSUMAWATI', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:39', '2025-10-29 04:41:39'),
(915, '251710301015', '$2y$10$NHWU5371aR1Py3BOtXGOeeXkIYM0NQrkHwN49x271QcNkxQ978gAa', 'SALMA NURUL HIDAYAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:39', '2025-10-29 04:41:39'),
(916, '251710301016', '$2y$10$up3hA.E8LNK92ZZPlmaO7uSfUDCmuXBXPT4C8BNctH/7pBNfATt/S', 'MUHAMMAD FARHAN', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:39', '2025-10-29 04:41:39'),
(917, '251710301017', '$2y$10$6RDWMm4hUbgp2mATSnK0Q.ozmKYEQTQ7lcP6a74AtC6lNzygmYdaC', 'RISKA AMALIA', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:39', '2025-10-29 04:41:39'),
(918, '251710301018', '$2y$10$wg6ESs5FRPqVYx1pA68nWugNyvCFe5dX05eS40y8A2rTqgPqXaBhS', 'RAHMA PUTRI SEPTIANI', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:39', '2025-10-29 04:41:39'),
(919, '251710301019', '$2y$10$RD6fCVbJdy4Kn1deOy8xLOssXGW3FS1nKecc5wWUJKXxmM6409wzq', 'DEWI NUR HIDAYAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:39', '2025-10-29 04:41:39'),
(920, '251710301020', '$2y$10$Lolh5FMBJFlMs8mTL1WLeuOtOyK7FK9n0ytJWfmCFN/nBAEALhCWa', 'PUTRI ANANDA LESTARI', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:39', '2025-10-29 04:41:39'),
(921, '251710301021', '$2y$10$9lXOTmIwYgvL6vg5BnhAyeWf9HOcdPKeeyEVvrijrvv7R7GWAzPH.', 'RISKI ADITYA', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:39', '2025-10-29 04:41:39'),
(922, '251710301022', '$2y$10$Cd7Hx7C2STxQxlFg7pnG1.P1ffG8DDEzROwr4oQVs9DnO/o/WkXL6', 'MUHAMMAD RIZKY', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:39', '2025-10-29 04:41:39'),
(923, '251710301023', '$2y$10$mJ8TmOXnl9cNChXQyLW1U.PHY6kqDvR306AwEO1ecW1129exgSY3S', 'FITRI AMALIA', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:39', '2025-10-29 04:41:39'),
(924, '251710301024', '$2y$10$RrVSJZ.4vsDw8qKk8ttDjOVWnMd9qHVU59iq1Te3HBvOz2gX/YkF6', 'SALWA RAHAYU', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:39', '2025-10-29 04:41:39'),
(925, '251710301025', '$2y$10$/EYlz6plj5WMQI.q23IJBetOuftcL9dFQ4dimBcdpC5kmc3eGtY36', 'NURUL HIDAYAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:39', '2025-10-29 04:41:39'),
(926, '251710301026', '$2y$10$XJgR8JDYBY95b.gBvrZ.1unt3WTDElRrWuPn7K35.0wUitIFOgn92', 'RINA PUTRI MAULIDA', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:39', '2025-10-29 04:41:39'),
(927, '251710301027', '$2y$10$k26OfIBOyOwWBVzUeP0xtum7/o4/Kj9N5lHk9sZa1VpZnz/IqEgHq', 'DIANA NUR HIDAYAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:39', '2025-10-29 04:41:39'),
(928, '251710301028', '$2y$10$cKRtqvh8mO8k.djKlg25Ye.6YJqBmWpoeLhNExMYCKIlUSbfPsXWG', 'ANDIKA RAHMAN', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:39', '2025-10-29 04:41:39'),
(929, '251710301029', '$2y$10$KDNzfKu8PTKNewrWJ4ZkXuxvQFeBa/3is87S6.PGJnWh6qxGPzI4.', 'FIRMAN SEPTIAN', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:39', '2025-10-29 04:41:39'),
(930, '251710301030', '$2y$10$pFIEcBcVO9EIXHTZ4VTFjesackCQznhj/Ll5WnrE6tfS1zHbtaHwy', 'PUTRI MAULIDA', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:39', '2025-10-29 04:41:39'),
(931, '251710301031', '$2y$10$v/LWRDVqdI3y3osTlYMMSOFF01geb5Is64BWdFjX.oObuZPy5eU2.', 'DEWI SEPTIA', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:39', '2025-10-29 04:41:39'),
(932, '251710301032', '$2y$10$NUn0s7SpVAzPfabBOcnWKuOjwYE.NEiH/.HfDPkCU4/j5fO19w1VO', 'SALWA MAULIDA', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:39', '2025-10-29 04:41:39'),
(933, '251710301033', '$2y$10$ElpVy3rXIvM0Bo1enWIyNe39cC2ukkf5bYX8vMJsIh90Zz0bGvIES', 'RINA HIDAYAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:39', '2025-10-29 04:41:39'),
(934, '251710301034', '$2y$10$FvxU9o0FoN3rGRltmYwckuIvcaRSOorPRMcIVcfMoAQ2O0Ns9jkVO', 'NABILAH AMALIA', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:40', '2025-10-29 04:41:40'),
(935, '251710301035', '$2y$10$THZJ4nwFFUVhcCZvtDZdzOmh8SEH2BqYTy.sIzgGfOO9sswLccziy', 'MUHAMMAD RIDHO', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:40', '2025-10-29 04:41:40'),
(936, '251710301036', '$2y$10$5lRpn/Rt6JcC74u4r/fz2uHeZHewjjn6V50uo07lOOynQIg58n5mq', 'RAHMA NUR HIDAYAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:40', '2025-10-29 04:41:40'),
(937, '251710301037', '$2y$10$cdLCvN0yXUIkhmj7hu8Ziu1rXp7k0xFnbhK4TWTiChlFpMFsQs1uq', 'PUTRI LESTARI', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:40', '2025-10-29 04:41:40'),
(938, '251710301038', '$2y$10$gfJo1wWOzZ9z.A1rpAxX5u/R999WN2Cfw1wvZdvU4.eozt/1VIPaa', 'FITRI AMALIA', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:40', '2025-10-29 04:41:40'),
(939, '251710301039', '$2y$10$kK4PjnkBW59NODdr9OjPC.4HUKbedIu7HOmp29C.X72PWb3KdbYhC', 'DEWI MAULIDA', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:40', '2025-10-29 04:41:40'),
(940, '251710301040', '$2y$10$arDgNJTsjTjduRpREGJl0.1X9Nq3HMzoxpje5pBxzabE6UaBs/MtO', 'RINA SEPTIA', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:40', '2025-10-29 04:41:40'),
(941, '251710301041', '$2y$10$x3BvmQUlrn/LhWQX0hJ3XeebUsS0KPB/SMaWhViY5wfkoXw/EzE1G', 'MUHAMMAD ILHAM', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:40', '2025-10-29 04:41:40'),
(942, '251710301042', '$2y$10$ZCSpflkx1V/BpSEkEt0jYuiSR4/ZkiGGm71FlJ4nIbqJ2el7Niese', 'SALWA ANANDA PUTRI', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:40', '2025-10-29 04:41:40'),
(943, '251710301043', '$2y$10$fp7SCWWCyCsTFwe6T7bEZu07ZsWtfNPTOY1yAscg4CXhzYN0yI.9i', 'FADHIL RAHMAN', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:40', '2025-10-29 04:41:40'),
(944, '251710301044', '$2y$10$TV.Vx4xpjvKZn9oedGmshee2KHT4OZxHUY/goN/ldVQ3aWHapDj9m', 'RISKI HIDAYAT', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:40', '2025-10-29 04:41:40'),
(945, '251710301045', '$2y$10$L7mkJgh7s9PuhCQdxQV2ieX09Taghtn2qIE3k8HphRALY5pgEe1vy', 'DEWI LESTARI', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:40', '2025-10-29 04:41:40'),
(946, '251710301046', '$2y$10$TLo0ROv7Sreo.ijnomK2FOpphihd2Mo386b2Yr11vDqCpUwn3EoI.', 'RINA NUR HIDAYAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:40', '2025-10-29 04:41:40'),
(947, '251710301047', '$2y$10$zhCqAJ1N.fA.TwsfG2kCc.Ml51zf8r1xmpanAKi4XKJ7/35Eipg6q', 'PUTRI AMALIA', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:40', '2025-10-29 04:41:40'),
(948, '251710301048', '$2y$10$dyvk8i4/EZSkJbiDofyY4uQLC3kpgFZMlxqTpt2X3o5CVgZlujf1m', 'RAHMA PUTRI', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:40', '2025-10-29 04:41:40'),
(949, '251710301049', '$2y$10$oTDKZi7NY2REYnjFh2Teu..iNcEZnE2RxrlTBgN9TPmfI7CSbEGO.', 'ILHAM MAULANA', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:40', '2025-10-29 04:41:40'),
(950, '251710301050', '$2y$10$wlLYPG7oLz7Sg0dYGryYS.KEKNsrJuoZ/rMYFNEhaC2hrbH1HkG0K', 'SALWA NUR HIDAYAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:40', '2025-10-29 04:41:40'),
(951, '251710301051', '$2y$10$V6HmLuD3O7FpraXb8F7fjef5ekoPsTCirBnMQQubE80n5Wbqo69Im', 'DEWI MAULIDA', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:40', '2025-10-29 04:41:40'),
(952, '251710301052', '$2y$10$m/FV7.90wlUVXELt9f5E.ORxhMvNKox1yliqjMn7oug.wP7yqlu42', 'PUTRI SEPTIANA', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:40', '2025-10-29 04:41:40'),
(953, '251710301053', '$2y$10$YgQCwhDJJp9YeJU6B8.0E.iyB9aYDJFWEKjBtRRfRs/befYicv/WK', 'RINA AMALIA', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:40', '2025-10-29 04:41:40'),
(954, '251710301054', '$2y$10$xsOJbOHtjDa8tyODmufX6eChImQRhWUfbev.LXN1f1XRpgYESNDPu', 'FITRI HIDAYAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:40', '2025-10-29 04:41:40'),
(955, '251710301055', '$2y$10$h2vPeBfR3kT.4LYRxWSmbOM1ha9kIkdw1Nbzts2xST2T84Pr4GXSy', 'SALWA RAHMA', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:40', '2025-10-29 04:41:40'),
(956, '251710301056', '$2y$10$M728Z1l7T8uT2YCfyegDuuqfnhF3Rre8rZ3eboiAmvR39QPxJXJp.', 'RAHMA DEWI', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:41', '2025-10-29 04:41:41'),
(957, '251710301057', '$2y$10$y4xsCatl9dvpcGJ/M43vZOgKi80TdmA/lEEtuLE95XmzyVd4s00N2', 'MUHAMMAD FADHIL', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:41', '2025-10-29 04:41:41'),
(958, '251710301058', '$2y$10$xagOw8URPh.nV/mnXgopNuTV4poUILJkdN5z.BuwiNPj8tds32SMC', 'RISMA PUTRI', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:41', '2025-10-29 04:41:41'),
(959, '251710301059', '$2y$10$8XSrRBNJ9Cnjko7AppGtI.Srm0tuNVLEbtD6M1gN3B0mfYr.hnYKC', 'DEWI NUR HIDAYAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:41', '2025-10-29 04:41:41'),
(960, '251710301060', '$2y$10$BjV/vVClK8m2vfS7g/TO.OjS/YOqCgRSrkwO9WMC06qCJtZxnP/9K', 'PUTRI MAULIDA', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:41', '2025-10-29 04:41:41'),
(961, '251710301061', '$2y$10$WYuniLAjZr8xzfX9IQeH/ek2tfEhBNihGnB/vYWYxjJES4st2BFkC', 'RINA SEPTIANI', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:41', '2025-10-29 04:41:41'),
(962, '251710301062', '$2y$10$G5VGOwU9oKBb/mUirIeIqufpOzyuralUqkVZAs4q0U8zWklHlahUq', 'RAHMA PUTRI', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:41', '2025-10-29 04:41:41'),
(963, '251710301063', '$2y$10$hHW2Nm3/aN1Ng6tYrvdsxeu0mYoa57P4k.TABGixuy4YKfQ1sTs62', 'SALWA MAULIDA', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:41', '2025-10-29 04:41:41'),
(964, '251710301064', '$2y$10$Agy6FISTZguEqWOvOtY/4em1uBE/PfFFyZcd/Hn9A0OBdXLRb41Eq', 'ILHAM RAHMAN', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:41', '2025-10-29 04:41:41'),
(965, '251710301065', '$2y$10$0CfatYQ9Jb1YR8flDg.5WumnQZn1lbovX0PxbCctvKXbSi2MYEiIO', 'DEWI AMALIA', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:41', '2025-10-29 04:41:41'),
(966, '251710301066', '$2y$10$iF/pNeExnAzO7zSZxaLxbuDTAp0VO9NK7vp6NvBBusNi/AhJ6DrrC', 'PUTRI SEPTIA', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:41', '2025-10-29 04:41:41'),
(967, '251710301067', '$2y$10$zfHPvkWbVXEx33uvkg9pgej28EvIBW0./LfQAuGfDSmUfW60gsXAC', 'RAHMA HIDAYAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:41', '2025-10-29 04:41:41'),
(968, '251710301068', '$2y$10$7jWsiIxmrgXXse04tPOTUOKN5xvtUga6wN/T1FwnoNwWmR.enKFVq', 'FITRI AMALIA', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:41', '2025-10-29 04:41:41'),
(969, '251710301069', '$2y$10$aYkjV4VPqNrI791LRCyhJ.xH/X5y5f4j/EMfzS..R.2lBUhP4uSqC', 'RINA NUR HIDAYAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:41', '2025-10-29 04:41:41'),
(970, '251710301070', '$2y$10$u/W55c879nJZd8Kx368nqeORojISJmdSXT9rL2tyTGARnYpgg6R5i', 'DEWI LESTARI', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:41', '2025-10-29 04:41:41'),
(971, '251710301071', '$2y$10$o21n2rpN1R6QW3oMX/3bFucuTy3eg1DlqfG6kGRG1H7l3xCKobJ86', 'PUTRI MAULIDA', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:41', '2025-10-29 04:41:41'),
(972, '251710301072', '$2y$10$e3s9X477p5PVYvuKgqLNfe68LwfNx8GSvAKlXuywWLTbyjxh6J1ta', 'RAHMA PUTRI', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:41', '2025-10-29 04:41:41'),
(973, '251710301073', '$2y$10$MTtNKYDsufbVvDd1i7hETuec9WUpIFyCVHIGLHtfkvVvqZc/zGYt.', 'FIRMAN HIDAYAT', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:41', '2025-10-29 04:41:41'),
(974, '251710301074', '$2y$10$TMKhFqCuLQS40Ow2.LM6s.FwIsMRe3PNPX9d.KyO5V7iBi1b9faFu', 'ILHAM MAULANA', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:41', '2025-10-29 04:41:41'),
(975, '251710301075', '$2y$10$MW5GfAQIvdLa1q5wDu7aP.3RsgQMboFaDvYfj7R.VrK0Hs1Iykq7W', 'SALWA NUR HIDAYAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:41', '2025-10-29 04:41:41'),
(976, '251710301076', '$2y$10$WvCRb6Y7o6Je97Yhhx4Iy.pRLNWuVPzgGi6GalOn7wfpKzkaJxDLe', 'DEWI LESTARI', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:41', '2025-10-29 04:41:41'),
(977, '251710301077', '$2y$10$snMoaoTKUPNMQHrhaKN79.zxxROGU1PmJNl5weNrnCOXalZ5Y4rJK', 'PUTRI ANANDA', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:41', '2025-10-29 04:41:41'),
(978, '251710301078', '$2y$10$XuJZA3f//bly6QlQ/LUtYOF9pIHSdLcsOEi1Edj8nPT7qql0h5L/O', 'RAHMA MAULIDA', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:41', '2025-10-29 04:41:41'),
(979, '251710301079', '$2y$10$fzw3b1r0dRZaGz0QDl3tyuyJxz3nYOdt1dGnbL7cqRITPxBMJhEiS', 'RISKI SEPTIAN', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:42', '2025-10-29 04:41:42'),
(980, '251710301080', '$2y$10$ZQ2YcZcIyv5iVhOxnHdq.uqP04TtnfgfpFoSWzooMEOw03YQ6VAV2', 'FIRMAN AMALIA', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:42', '2025-10-29 04:41:42'),
(981, '251710301081', '$2y$10$FHIMaiMmTt/PoeqGbTEyru3YjF8vq1qlb5jey4kmLq2BvGVUHgxGq', 'DEWI SEPTIANA', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:42', '2025-10-29 04:41:42'),
(982, '251710301082', '$2y$10$p/N778GFIez8Gs7iT/g7fuDp/Cqo2haODnA0FG1Pz1nWkzCf8OtFm', 'PUTRI LESTARI', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:42', '2025-10-29 04:41:42'),
(983, '251710301083', '$2y$10$BMkacXq4UUM3ERBtwNQ0Me9QXbimHfzOhzgykcG1yDXyYiqX.F/Hm', 'RINA NUR HIDAYAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:42', '2025-10-29 04:41:42'),
(984, '251710301084', '$2y$10$aP6xlz6wNZT02acayAyrZ.dg6s9Kuvtyqdm8jVNMW5UPnzwOVBTX2', 'SALWA PUTRI', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:42', '2025-10-29 04:41:42'),
(985, '251710301085', '$2y$10$2zi/qW1eld2cdDw8pg15.egr6VVb0xrz95/mWTkv7BMb3DVZWEX3.', 'RAHMA HIDAYAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:42', '2025-10-29 04:41:42'),
(986, '251710301086', '$2y$10$BFM4ez/30s.xvXvldbXtnuIHoDNiONNUfKXKKJ..R60roUxdF4MF2', 'FITRI AMALIA', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:42', '2025-10-29 04:41:42'),
(987, '251710301087', '$2y$10$YpyLAkoMDGfHsa9VEvuEm.SvJjRgA8r4yHUXtPb2HNh1i656t2S6e', 'MUHAMMAD ILHAM', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:42', '2025-10-29 04:41:42'),
(988, '251710301088', '$2y$10$pUMSicAMYi9/ZPh4.lLVSOYTAK2Ob43nC0szZy65JPdjsvtQO.PwC', 'DEWI NUR HIDAYAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:42', '2025-10-29 04:41:42'),
(989, '251710301089', '$2y$10$4//D7uj1qXNTA4SW6XZcfOMJdQJZepblYcv3SjZHaoCwCO3SuFsg2', 'PUTRI MAULIDA', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:42', '2025-10-29 04:41:42'),
(990, '251710301090', '$2y$10$HkBKYae7BnNCHr6AIdHbeuhfl.DaxcBz35Wf4CfVC/xukAeZVnZbe', 'RINA SEPTIANI', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:42', '2025-10-29 04:41:42'),
(991, '251710301091', '$2y$10$Xma0LoiKSOfgk3I9/SvSteq/i1zj3hpVjUDveh/dHq4nzmS9MK5Y2', 'RAHMA AMALIA', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:42', '2025-10-29 04:41:42'),
(992, '251710301092', '$2y$10$IfcuFiYXc5A6mJXw6P6b5.hqdvXlyH5SzQBI8IWKamg./uH./cUZm', 'FITRI HIDAYAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:42', '2025-10-29 04:41:42'),
(993, '251710301093', '$2y$10$jF6.pvZQllbunyLOOCl5ROGXSZG5rvgZ/b4c5qq2W/6pNaeWIHREm', 'SALWA RAHAYU', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:42', '2025-10-29 04:41:42'),
(994, '251710301094', '$2y$10$xCQi.QPuPeO4k.PCimsgee4GaFiGRxdSXW2RW7M265a.ic.3AarRK', 'DEWI MAULIDA', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:42', '2025-10-29 04:41:42'),
(995, '251710301095', '$2y$10$kKzGfm/rnPdjGVP1yglcVuYfUxrS1uGmW3qfLVF3UlXr69Oncnejm', 'PUTRI SEPTIANI', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:42', '2025-10-29 04:41:42'),
(996, '251710301096', '$2y$10$B0eRTc2gf1P92HCiozF5DuGFD7zrV6EQV2UREtQ071tHgFQKT1rve', 'MUHAMMAD FADHIL', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:42', '2025-10-29 04:41:42'),
(997, '251710301097', '$2y$10$lg7tKrjbSJGXE32ggSzizeTYzCZKA74YEz0KVF4W7XI4ZXfaHReTu', 'RISKI AMALIA', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:42', '2025-10-29 04:41:42'),
(998, '251710301098', '$2y$10$ZuFwKhFllIl5CvLAx1fMye.vio26CllD24zQoUON9s15/rp5irJXa', 'RAHMA HIDAYAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:42', '2025-10-29 04:41:42'),
(999, '251710301099', '$2y$10$mEqSRVYkorCHS6.HO32YR.UAcBDTIgr3SfsrJIEYrrhBooOGNNNnq', 'FITRI AMALIA', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:42', '2025-10-29 04:41:42'),
(1000, '251710301100', '$2y$10$7aA0gKJPISkmPl73fQpgvuddZvMPTC0lt7x6gn.uBOAGDhdAobghK', 'DEWI SEPTIA', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:42', '2025-10-29 04:41:42'),
(1001, '251710301101', '$2y$10$k8fG3Hobj5XjyG784z9CHeM..PzQzuT4AR4ZmVdV7tbqpqqrfm70u', 'PUTRI MAULIDA', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:42', '2025-10-29 04:41:42'),
(1002, '251710301102', '$2y$10$r74lEUdVgDQMtLO1pcwL4e0J6duiNHRJ6SB.iVji4U5ExCCRm2WnK', 'RINA NUR HIDAYAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:43', '2025-10-29 04:41:43'),
(1003, '251710301103', '$2y$10$PFelwupI21rgbFDPmhfUgOHK2syHwP0d3xYRl00GukdcXMkd9xkb6', 'SALWA ANANDA', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:43', '2025-10-29 04:41:43'),
(1004, '251710301104', '$2y$10$aCc3yAuYO1pkds5YWAmYpeJp5QuBU4lLwJsmIFGCSJDvsGZ5fBkG6', 'RAHMA PUTRI', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:43', '2025-10-29 04:41:43'),
(1005, '251710301105', '$2y$10$yi8nsepxkNLP9FS2kS09fujraHcQuURHKIbUHbB6lTVhoXRsPjzzi', 'FITRI HIDAYAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:43', '2025-10-29 04:41:43'),
(1006, '251710301106', '$2y$10$7zKCrEIvkt1Sbt8PZm9o.O.OdAq7OrlIvWPDwfeofLwy0fdd/QaMq', 'DEWI LESTARI', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:43', '2025-10-29 04:41:43'),
(1007, '251710301107', '$2y$10$prxxMc2JN8dWRaphDDhF4OlEoTA7W6I5Kl1qj7xviCswbDixuStte', 'RISKI HIDAYAT', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:43', '2025-10-29 04:41:43'),
(1008, '251710301108', '$2y$10$fjSlgqmIa6rGxqcxsv133OHSqrRQeUbhtfJeMQm.ktsBXSRs1sXy.', 'MUHAMMAD RIDHO', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:43', '2025-10-29 04:41:43'),
(1009, '251710301109', '$2y$10$RNcjrgyRme1CL4pug7xco.r9wRmL0Rr6gg63AQEDkkbsQ74YH6nku', 'DEWI AMALIA', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:43', '2025-10-29 04:41:43'),
(1010, '251710301110', '$2y$10$RAY9Wz5eb/A/2sRwPvxavek8RLvefcrucNT/X2RD2I9Q6tm22IasC', 'PUTRI SEPTIANA', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:43', '2025-10-29 04:41:43'),
(1011, '251710301111', '$2y$10$vFNI2imHMYcDOIK8Lr6u7euDRF9dEotek.8EO6dBV96eLbUVeRZ3G', 'RAHMA HIDAYAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:43', '2025-10-29 04:41:43'),
(1012, '251710301112', '$2y$10$J1A4Vjk8ehgePa2IzZ.HmuwQIvpo5vshRNGnnoJ/AcL0TNJxICnmy', 'FIRMAN RAHMAN', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:43', '2025-10-29 04:41:43'),
(1013, '251710301113', '$2y$10$4FxLYWAmY3brK.tG8feDweLcCY9BzuErBwGCqQAgkXDgOb3.DVFQ6', 'DEWI MAULIDA', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:43', '2025-10-29 04:41:43'),
(1014, '251710301114', '$2y$10$yO2a.PYx1Pvv2cnR3v1PbeSTcZ12Z9oBf44qKonUjRItAD97uJaPC', 'PUTRI NUR HIDAYAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:43', '2025-10-29 04:41:43'),
(1015, '251710301115', '$2y$10$NSrPfoJkLWczu8.cl3MePOhurtLw6RtE5kRExy/9qxOWPAKxcXJcG', 'RAHMA AMALIA', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:43', '2025-10-29 04:41:43'),
(1016, '251710301116', '$2y$10$RWmYVz5jj5iwnMmolVx5oOAvSRQOFfQe/f5PmHSmevV7ZiBnvpml.', 'SALWA MAULIDA', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:43', '2025-10-29 04:41:43'),
(1017, '251710301117', '$2y$10$Iy.fMDcTqk4gNZZUMk7GHuz0BZi1w/NFNtaAcgwswkteqzuUdLnbW', 'RISKI SEPTIA', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:43', '2025-10-29 04:41:43'),
(1018, '251710301118', '$2y$10$jL7DuX7CJYSueslpOqd6VeaV0RzDeVuACp53fxvJ08U4KmYtCUkQe', 'MUHAMMAD ILHAM', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:43', '2025-10-29 04:41:43'),
(1019, '251710301119', '$2y$10$3jYlCs7OjLBLKpIPv595XePqlYwztHWxm4AUn8mZ47slFkErJOjJq', 'DEWI HIDAYAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:43', '2025-10-29 04:41:43'),
(1020, '251710301120', '$2y$10$a8eTWUUYXkyjAs49kYKefOWav/wXLwEc1MiQqL8WtJjfoj64VvCP2', 'PUTRI SEPTIANA', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:43', '2025-10-29 04:41:43'),
(1021, '251710301121', '$2y$10$MIsNfmayLUT.WTB6B5qzgeIdrFsimI2GAr4tqwFs18BUycKydzy4K', 'RINA HIDAYAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:43', '2025-10-29 04:41:43'),
(1022, '251710301122', '$2y$10$us/lgbEik2iYgEFuverS8e4O8FYnqXVXalc4XMv1oCOFUu0QnsMQy', 'SALWA NUR HIDAYAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:43', '2025-10-29 04:41:43'),
(1023, '251710301123', '$2y$10$AtGLvUGq1lEjHF8nVbJOIuusKEVScwEsWqKPTlagMH7mKA18FxIQO', 'RAHMA MAULIDA', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:43', '2025-10-29 04:41:43'),
(1024, '251710301124', '$2y$10$9Y.E6OV/.0FN9lwOu2nvPOUfTFOXvr6Yax.U/uX5tOOFcSu4JcT6q', 'FITRI AMALIA', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:43', '2025-10-29 04:41:43'),
(1025, '251710301125', '$2y$10$TvYyxP145gVSTxEuthkTS.ASqdheHpB5rt3iSD82E1GBjZa96BoW6', 'DEWI SEPTIA', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:44', '2025-10-29 04:41:44'),
(1026, '251710301126', '$2y$10$oGosm2iq9MJJByQaY7CXgeFVJOb5A8mKeBXJULDPq12MAHXlQlEhq', 'RISKI MAULANA', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:44', '2025-10-29 04:41:44'),
(1027, '251710301127', '$2y$10$nffNu1CtL1zWi2.j7v2GtOpdszCpkS/SG9XwLEqNb5S5SrJt1uKtC', 'MUHAMMAD RIZKY', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:44', '2025-10-29 04:41:44'),
(1028, '251710301128', '$2y$10$MTbSViavT7C2Kxgpixtx9eubeN.zcdjjWXjWr0h3s5l4/2npSuXyK', 'PUTRI ANANDA', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:44', '2025-10-29 04:41:44'),
(1029, '251710301129', '$2y$10$zjYj1PV7wGsuCeVf1CYmGewKyyc6XbKBd0wfCDQ303b0mbq9czv6S', 'RAHMA AMALIA', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:44', '2025-10-29 04:41:44'),
(1030, '251710301130', '$2y$10$1z2osnBtDBmLR8osJvkvGeA/7oi8oQ8F4hB2Oay4m3vTcIboOAz1e', 'SALWA LESTARI', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:44', '2025-10-29 04:41:44'),
(1031, '251710301131', '$2y$10$fNKyBGQxg8c2Nr.jhcx6t.Qf6Sq.eb9OX5oNx80Jgl.d4DvpBcY1y', 'DEWI HIDAYAH', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:44', '2025-10-29 04:41:44'),
(1032, '251710301132', '$2y$10$uJsIoh6s5a.TtoaQJ03RYuaqG/ikXrGJt5MBamc9lSM42n1F4DTVK', 'PUTRI SEPTIANI', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:44', '2025-10-29 04:41:44'),
(1033, '251710301133', '$2y$10$6bGljk3BJdJp6/S.gRCeYORd3WrpXYKz6aBq9cnAuaJsi6Rak6DtG', 'RINA MAULIDA', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:44', '2025-10-29 04:41:44'),
(1034, '251710301134', '$2y$10$MhDEBypkPVuu.JS2LXtE6eqiasft/sO/R1.lBffG1nm95qKwUcxwi', 'MUHAMMAD RAFIF BAHTIAR', 'mahasiswa', NULL, NULL, '2025-10-29 04:41:44', '2025-10-29 04:41:44'),
(1035, '123123', '$2y$10$S4dQnbX6DiYCJFP2wg2Pc.tH/vI9RuOdpe.U88XZFZqYBUGhJcXfG', 'Mahasiswa Baru', 'mahasiswa', NULL, '123123', '2025-10-29 14:03:35', '2025-10-29 14:03:35'),
(1036, '123456', '$2y$10$Z1PU3azL.mlZSMbnwGp07e.dNo29wDCENs4g1.Tl1Y1illVnSmuZ2', 'Dosen Luar', 'penguji_eksternal', '123456', NULL, '2025-10-29 15:07:38', '2025-10-29 15:07:38'),
(1046, '234234', '$2y$10$lCOKRVwEYsrP6XQtuI9USOrjKYQ4B3cYPoTnB23nzhPwNDp9OPRoy', 'Mahasiswa Percobaan', 'mahasiswa', NULL, '234234', '2025-11-02 04:37:30', '2025-11-02 04:37:30');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `assignments`
--
ALTER TABLE `assignments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_assignment` (`student_id`,`role`),
  ADD KEY `lecturer_id` (`lecturer_id`),
  ADD KEY `assigned_by` (`assigned_by`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `cuti_records`
--
ALTER TABLE `cuti_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `evaluations`
--
ALTER TABLE `evaluations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_evaluation` (`student_id`,`evaluator_id`,`stage`,`mode`),
  ADD KEY `evaluator_id` (`evaluator_id`);

--
-- Indexes for table `evaluation_components`
--
ALTER TABLE `evaluation_components`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `evaluation_scores`
--
ALTER TABLE `evaluation_scores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_eval_component` (`evaluation_id`,`component_id`),
  ADD KEY `component_id` (`component_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `external_tokens`
--
ALTER TABLE `external_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `lecturers`
--
ALTER TABLE `lecturers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nip` (`nip`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `proposal_evaluations`
--
ALTER TABLE `proposal_evaluations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_proposal_eval` (`student_id`,`evaluator_id`),
  ADD KEY `evaluator_id` (`evaluator_id`);

--
-- Indexes for table `scores`
--
ALTER TABLE `scores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_student_event_scorer` (`student_id`,`event_type`,`scorer_id`),
  ADD KEY `scorer_id` (`scorer_id`),
  ADD KEY `bypassed_by` (`bypassed_by`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `key_name` (`key_name`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nim` (`nim`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `titles`
--
ALTER TABLE `titles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `verified_by` (`verified_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `assignments`
--
ALTER TABLE `assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `cuti_records`
--
ALTER TABLE `cuti_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `evaluations`
--
ALTER TABLE `evaluations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `evaluation_components`
--
ALTER TABLE `evaluation_components`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `evaluation_scores`
--
ALTER TABLE `evaluation_scores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `external_tokens`
--
ALTER TABLE `external_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `lecturers`
--
ALTER TABLE `lecturers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `proposal_evaluations`
--
ALTER TABLE `proposal_evaluations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `scores`
--
ALTER TABLE `scores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1203;

--
-- AUTO_INCREMENT for table `titles`
--
ALTER TABLE `titles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1047;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `assignments`
--
ALTER TABLE `assignments`
  ADD CONSTRAINT `assignments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assignments_ibfk_2` FOREIGN KEY (`lecturer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assignments_ibfk_3` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cuti_records`
--
ALTER TABLE `cuti_records`
  ADD CONSTRAINT `cuti_records_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `evaluations`
--
ALTER TABLE `evaluations`
  ADD CONSTRAINT `evaluations_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `evaluations_ibfk_2` FOREIGN KEY (`evaluator_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `evaluation_scores`
--
ALTER TABLE `evaluation_scores`
  ADD CONSTRAINT `evaluation_scores_ibfk_1` FOREIGN KEY (`evaluation_id`) REFERENCES `evaluations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `evaluation_scores_ibfk_2` FOREIGN KEY (`component_id`) REFERENCES `evaluation_components` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `external_tokens`
--
ALTER TABLE `external_tokens`
  ADD CONSTRAINT `external_tokens_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lecturers`
--
ALTER TABLE `lecturers`
  ADD CONSTRAINT `lecturers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `proposal_evaluations`
--
ALTER TABLE `proposal_evaluations`
  ADD CONSTRAINT `proposal_evaluations_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `proposal_evaluations_ibfk_2` FOREIGN KEY (`evaluator_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `scores`
--
ALTER TABLE `scores`
  ADD CONSTRAINT `scores_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `scores_ibfk_2` FOREIGN KEY (`scorer_id`) REFERENCES `lecturers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `scores_ibfk_3` FOREIGN KEY (`bypassed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `titles`
--
ALTER TABLE `titles`
  ADD CONSTRAINT `titles_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `titles_ibfk_2` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
