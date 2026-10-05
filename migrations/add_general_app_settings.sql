-- Migration: Add General App Settings
-- Date: 2025-02-07
-- Description: Add configurable application settings for multi-tenant support

-- Insert default general app settings
INSERT INTO settings (key_name, value, description) VALUES
-- Application Settings
('app_name', 'KomBi-TIP Apps', 'Nama aplikasi (singkat)'),
('app_full_name', 'Sistem Komisi Bimbingan Skripsi', 'Nama lengkap aplikasi'),
('app_short_name', 'KomBi-TIP', 'Nama pendek aplikasi'),
('app_version', '1.0.0', 'Versi aplikasi'),

-- Organization Settings
('org_prodi', 'Program Studi Teknologi Industri Pertanian', 'Nama program studi'),
('org_fakultas', 'Fakultas Teknologi Pertanian', 'Nama fakultas'),
('org_universitas', 'Universitas Jember', 'Nama universitas'),
('org_location', 'Jember', 'Kota lokasi'),

-- Contact/Footer Settings
('app_footer_text', 'KomBi-TIP Apps', 'Teks footer aplikasi'),
('app_developer_name', 'BS', 'Nama developer'),
('app_developer_url', 'https://www.suryadharma.work/', 'URL website developer'),
('app_admin_contact', 'admin@kombi.unej.ac.id', 'Kontak admin')

ON DUPLICATE KEY UPDATE description = VALUES(description);
