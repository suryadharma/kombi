-- Migration: Add Email Feature for Lecturer Notifications
-- Date: 2025-02-07
-- Description: Add email columns to lecturers and users tables, create email_queue and email_logs tables

-- ============================================
-- 1. Add email column to lecturers table
-- ============================================
ALTER TABLE lecturers 
ADD COLUMN email VARCHAR(150) NULL AFTER prodi,
ADD INDEX idx_email (email);

-- ============================================
-- 2. Add email column to users table (for future notifications)
-- ============================================
ALTER TABLE users 
ADD COLUMN email VARCHAR(150) NULL AFTER name,
ADD INDEX idx_email (email);

-- ============================================
-- 3. Create email_logs table for tracking sent emails
-- ============================================
CREATE TABLE IF NOT EXISTS email_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recipient_email VARCHAR(150) NOT NULL,
    recipient_name VARCHAR(100) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    event_type ENUM('SEMPRO', 'SEMHAS', 'PRA_UJIAN', 'UJIAN_SKRIPSI') NOT NULL,
    student_id INT NOT NULL,
    student_name VARCHAR(100) NOT NULL,
    pdf_path VARCHAR(255) NULL,
    status ENUM('PENDING', 'SENT', 'FAILED', 'RETRY') DEFAULT 'PENDING',
    error_message TEXT NULL,
    attempts INT DEFAULT 0,
    sent_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_event_type (event_type),
    INDEX idx_student_id (student_id),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 4. Create email_queue table for async email sending
-- ============================================
CREATE TABLE IF NOT EXISTS email_queue (
    id INT AUTO_INCREMENT PRIMARY KEY,
    to_email VARCHAR(150) NOT NULL,
    to_name VARCHAR(100) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    attachment_path VARCHAR(255) NULL,
    event_type ENUM('SEMPRO', 'SEMHAS', 'PRA_UJIAN', 'UJIAN_SKRIPSI') NOT NULL,
    student_id INT NOT NULL,
    priority INT DEFAULT 0 COMMENT '0=normal, 1=high, 2=urgent',
    max_attempts INT DEFAULT 3,
    attempts INT DEFAULT 0,
    status ENUM('PENDING', 'PROCESSING', 'SENT', 'FAILED') DEFAULT 'PENDING',
    error_message TEXT NULL,
    scheduled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    sent_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_status_priority (status, priority),
    INDEX idx_scheduled (scheduled_at),
    INDEX idx_status (status),
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 5. Insert email configuration settings
-- ============================================
INSERT INTO settings (key_name, value, description) VALUES
('email_enabled', '0', 'Aktifkan fitur pengiriman email otomatis ke dosen'),
('email_host', 'smtp.gmail.com', 'Host SMTP untuk pengiriman email'),
('email_port', '587', 'Port SMTP (587 untuk TLS, 465 untuk SSL)'),
('email_username', '', 'Email pengirim (contoh: nama@gmail.com)'),
('email_password', '', 'App password Google (bukan password akun)'),
('email_from_name', 'Sistem Kombi - Fakultas', 'Nama pengirim yang tampil di email'),
('email_encryption', 'tls', 'Enkripsi yang digunakan (tls atau ssl)'),
('email_batch_size', '10', 'Jumlah email yang dikirim per batch (untuk menghindari rate limit)'),
('email_retry_delay', '5', 'Delay antar retry dalam menit'),
('email_max_queue_age', '24', 'Maksimal umur queue sebelum dihapus (jam)'),
('email_delay_minutes', '5', 'Delay pengiriman email setelah nilai disimpan (menit)')
ON DUPLICATE KEY UPDATE description = VALUES(description);

-- ============================================
-- 6. Create view for email queue monitoring
-- ============================================
CREATE OR REPLACE VIEW v_email_queue_stats AS
SELECT 
    status,
    COUNT(*) as count,
    MIN(created_at) as oldest,
    MAX(created_at) as newest
FROM email_queue
GROUP BY status;

-- ============================================
-- 7. Add trigger to clean up old email logs
-- ============================================
DELIMITER $$

CREATE PROCEDURE CleanOldEmailLogs()
BEGIN
    DELETE FROM email_logs 
    WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY)
    AND status IN ('SENT', 'FAILED');
    
    DELETE FROM email_queue 
    WHERE created_at < DATE_SUB(NOW(), INTERVAL (SELECT value FROM settings WHERE key_name = 'email_max_queue_age') HOUR)
    AND status IN ('SENT', 'FAILED');
END$$

DELIMITER ;

-- ============================================
-- Usage Notes:
-- ============================================
-- 1. Setelah menjalankan migration ini, update data dosen dengan email:
--    UPDATE lecturers SET email = 'nama@dosen.ac.id' WHERE nip = '...';
--
-- 2. Untuk mengaktifkan email, update setting:
--    UPDATE settings SET value = '1' WHERE key_name = 'email_enabled';
--
-- 3. Setup cron job untuk memproses email queue (setiap 5 menit):
--    */5 * * * * php /path/to/kombi/src/cron/process_email_queue.php
--
-- 4. Untuk membuat App Password Google:
--    - Buka https://myaccount.google.com/security
--    - Aktifkan 2-Step Verification
--    - Go to App Passwords
--    - Create app password untuk "Mail"
--    - Copy password dan paste di setting email_password
