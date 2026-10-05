-- Database Schema for KBS Application

-- Create database
CREATE DATABASE IF NOT EXISTS kbs_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE kbs_db;

-- Users table (all users: superadmin, kombi, dosen, mahasiswa)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    role ENUM('superadmin', 'kombi', 'dosen_pembimbing', 'dosen_penguji', 'mahasiswa', 'penguji_eksternal') NOT NULL,
    nip VARCHAR(20) NULL, -- For lecturers
    nim VARCHAR(15) NULL, -- For students
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nim VARCHAR(15) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    angkatan INT NOT NULL,
    semester_masuk INT NOT NULL,
    semester_lulus INT NULL,
    status ENUM('AKTIF', 'CUTI', 'NON-AKTIF', 'MENGULANG', 'LULUS') DEFAULT 'AKTIF',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    user_id INT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE user_roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    role ENUM('superadmin', 'kombi', 'dosen_pembimbing', 'dosen_penguji', 'mahasiswa', 'penguji_eksternal') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY idx_user_role_unique (user_id, role),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Lecturers table
CREATE TABLE lecturers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    nip VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    prodi VARCHAR(100) NULL,
    is_external TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Titles table
CREATE TABLE titles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    title TEXT NOT NULL,
    abstract TEXT NOT NULL,
    keywords TEXT NOT NULL,
    advisor_letter_link TEXT NULL,
    examiner_letter_link TEXT NULL,
    proposed_pembimbing_1_id INT NULL,
    proposed_pembimbing_2_id INT NULL,
    proposed_penguji_1_id INT NULL,
    proposed_penguji_2_id INT NULL,
    proposed_penguji_3_id INT NULL,
    document_path VARCHAR(255) NULL,
    status ENUM('MENUNGGU', 'DITERIMA', 'DITOLAK', 'PERLU_REVISI') DEFAULT 'MENUNGGU',
    notes TEXT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    verified_at TIMESTAMP NULL,
    verified_by INT NULL,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (proposed_pembimbing_1_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (proposed_pembimbing_2_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (proposed_penguji_1_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (proposed_penguji_2_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (proposed_penguji_3_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Assignments table (links students with lecturers/users)
CREATE TABLE assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    lecturer_id INT NOT NULL,
    role ENUM('pembimbing_1', 'pembimbing_2', 'penguji_1', 'penguji_2', 'penguji_3') NOT NULL,
    reason TEXT NULL,
    assigned_by INT NOT NULL,
    effective_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (lecturer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_assignment (student_id, role)
);

-- Cuti records
CREATE TABLE cuti_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    semester INT NOT NULL,
    status ENUM('MENUNGGU', 'DISETUJUI', 'DITOLAK') DEFAULT 'MENUNGGU',
    document_path VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

-- Events table (sempro, semhas, ujian)
CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    type ENUM('SEMPRO', 'SEMHAS', 'PRA_UJIAN', 'UJIAN_SKRIPSI') NOT NULL,
    scheduled_date DATE NOT NULL,
    scheduled_time TIME NOT NULL,
    room VARCHAR(50) NOT NULL,
    status ENUM('MENUNGGU', 'SELESAI', 'BATAL') DEFAULT 'MENUNGGU',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

-- Scores table
CREATE TABLE scores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    event_type ENUM('SEMPRO', 'SEMHAS', 'PRA_UJIAN', 'UJIAN_SKRIPSI') NOT NULL,
    scorer_id INT NOT NULL, -- lecturer who gives score
    score_type ENUM('PEMBIMBING', 'PENGUJI') NOT NULL,
    scoring_method ENUM('BOBOT', 'AKHIR') NOT NULL, -- BOBOT = component-based, AKHIR = final score only
    
    -- Component-based scores (if method is BOBOT)
    component_1_score DECIMAL(5,2) NULL,
    component_1_weight DECIMAL(5,2) NULL,
    component_2_score DECIMAL(5,2) NULL,
    component_2_weight DECIMAL(5,2) NULL,
    component_3_score DECIMAL(5,2) NULL,
    component_3_weight DECIMAL(5,2) NULL,
    
    -- Final score
    final_score DECIMAL(5,2) NULL,
    
    -- Source tracking
    source ENUM('DOSEN', 'BYPASS') DEFAULT 'DOSEN',
    bypass_reason TEXT NULL, -- Required if source is BYPASS
    bypassed_by INT NULL, -- Who did the bypass
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (scorer_id) REFERENCES lecturers(id) ON DELETE CASCADE,
    FOREIGN KEY (bypassed_by) REFERENCES users(id) ON DELETE SET NULL,
    
    UNIQUE KEY unique_student_event_scorer (student_id, event_type, scorer_id)
);

-- Evaluation components table
CREATE TABLE evaluation_components (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    stage VARCHAR(50) NOT NULL,
    weight DECIMAL(6,4) NOT NULL DEFAULT 0.0000,
    description TEXT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Evaluations table
CREATE TABLE evaluations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    evaluator_id INT NOT NULL,
    evaluator_role ENUM('superadmin', 'kombi', 'dosen_pembimbing', 'dosen_penguji', 'penguji_eksternal') NOT NULL,
    stage VARCHAR(50) NOT NULL,
    mode ENUM('components', 'final', 'bypass') NOT NULL DEFAULT 'components',
    final_score DECIMAL(6,2) NULL,
    total_score DECIMAL(6,2) NULL,
    notes TEXT NULL,
    reason TEXT NULL,
    source ENUM('manual', 'bypass') NOT NULL DEFAULT 'manual',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (evaluator_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_evaluation (student_id, evaluator_id, stage, mode)
);

-- Evaluation scores table (component breakdown)
CREATE TABLE evaluation_scores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    evaluation_id INT NOT NULL,
    component_id INT NOT NULL,
    score DECIMAL(6,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (evaluation_id) REFERENCES evaluations(id) ON DELETE CASCADE,
    FOREIGN KEY (component_id) REFERENCES evaluation_components(id) ON DELETE CASCADE,
    UNIQUE KEY unique_eval_component (evaluation_id, component_id)
);

-- Proposal evaluations table
CREATE TABLE proposal_evaluations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    evaluator_id INT NOT NULL,
    evaluator_role ENUM('superadmin', 'kombi', 'dosen_pembimbing', 'dosen_penguji') NOT NULL,
    performance_score_1 DECIMAL(6,2) NULL,
    performance_score_2 DECIMAL(6,2) NULL,
    performance_score_3 DECIMAL(6,2) NULL,
    performance_score_4 DECIMAL(6,2) NULL,
    performance_score_5 DECIMAL(6,2) NULL,
    content_score_1 DECIMAL(6,2) NULL,
    content_score_2 DECIMAL(6,2) NULL,
    content_score_3 DECIMAL(6,2) NULL,
    performance_total DECIMAL(6,2) NULL,
    content_total DECIMAL(6,2) NULL,
    final_score DECIMAL(6,2) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (evaluator_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_proposal_eval (student_id, evaluator_id)
);

-- External examiner tokens
CREATE TABLE external_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nip VARCHAR(20) NOT NULL,
    token VARCHAR(50) NOT NULL,
    event_id INT NOT NULL, -- Link to specific event
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    nda_agreed BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
);

-- Audit logs
CREATE TABLE audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    target_type VARCHAR(50) NOT NULL,
    target_id INT NOT NULL,
    description TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Global settings
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    key_name VARCHAR(100) UNIQUE NOT NULL,
    value TEXT NOT NULL,
    description VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default settings
INSERT INTO settings (key_name, value, description) VALUES
('pembimbing_ratio', '60:40', 'Rasio nilai pembimbing I:II'),
('penguji_method', 'rata-rata', 'Metode agregasi nilai penguji'),
('cuti_threshold', 'before_krs', 'Ambang batas cuti (sebelum akhir KRS)'),
('max_pembimbing', '8', 'Maksimal beban pembimbing per dosen'),
('max_penguji', '10', 'Maksimal beban penguji per dosen');
