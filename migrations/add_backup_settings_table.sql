-- Migration: Tabel Pengaturan Backup
-- Tanggal: 2026-02-11
-- Deskripsi: Menambahkan tabel untuk menyimpan pengaturan backup (SMB/FTP) di database

-- Tabel untuk menyimpan pengaturan backup
CREATE TABLE IF NOT EXISTS backup_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    -- Pengaturan umum
    backup_enabled TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Aktif/nonaktif backup otomatis',
    backup_type ENUM('local', 'smb', 'ftp', 'none') NOT NULL DEFAULT 'local' COMMENT 'Tipe backup: local (hanya lokal), smb (Windows share), ftp (FTP server)',
    retention_days INT NOT NULL DEFAULT 30 COMMENT 'Berapa hari backup disimpan sebelum dihapus',
    
    -- Pengaturan SMB
    smb_enabled TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Aktif/nonaktif sinkronisasi SMB',
    smb_host VARCHAR(255) NULL COMMENT 'Host atau IP server SMB',
    smb_share VARCHAR(255) NULL COMMENT 'Nama share SMB',
    smb_path VARCHAR(255) NULL DEFAULT '/' COMMENT 'Path di dalam share SMB',
    smb_username VARCHAR(100) NULL COMMENT 'Username untuk koneksi SMB',
    smb_password VARCHAR(255) NULL COMMENT 'Password SMB (dienkripsi)',
    smb_workgroup VARCHAR(100) NULL COMMENT 'Workgroup atau domain Windows',
    smb_binary VARCHAR(255) DEFAULT '/usr/bin/smbclient' COMMENT 'Path ke binary smbclient',
    
    -- Pengaturan FTP
    ftp_enabled TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Aktif/nonaktif sinkronisasi FTP',
    ftp_host VARCHAR(255) NULL COMMENT 'Host atau IP server FTP',
    ftp_port INT DEFAULT 21 COMMENT 'Port FTP (default 21)',
    ftp_username VARCHAR(100) NULL COMMENT 'Username untuk koneksi FTP',
    ftp_password VARCHAR(255) NULL COMMENT 'Password FTP (dienkripsi)',
    ftp_path VARCHAR(255) NULL DEFAULT '/' COMMENT 'Path di server FTP',
    ftp_passive TINYINT(1) DEFAULT 1 COMMENT 'Gunakan mode passive FTP',
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Index
    UNIQUE KEY unique_id (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Tabel pengaturan backup untuk sinkronisasi ke SMB/FTP';

-- Insert default settings
INSERT INTO backup_settings (backup_enabled, backup_type, retention_days)
VALUES (1, 'local', 30)
ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP;

-- Catatan migrasi:
-- 1. Password SMB dan FTP dienkripsi menggunakan AES-256-CBC
-- 2. Key enkripsi diambil dari environment variable BACKUP_ENCRYPTION_KEY
-- 3. Untuk migrasi dari .env, jalankan query INSERT manual:
--    INSERT INTO backup_settings (smb_enabled, smb_host, smb_share, ...)
--    VALUES (1, '192.168.100.145', 'backup', ...);
