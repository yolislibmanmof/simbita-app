-- =====================================================================
-- SIMBITA DATABASE - FINAL SQL
-- Sistem Informasi Bimbingan Tugas Akhir
-- Mendukung: Skripsi (S1), Tesis (S2), Disertasi (S3)
-- Database: MySQL 8.x / MariaDB 10.4+
-- =====================================================================

CREATE DATABASE IF NOT EXISTS simbita_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE simbita_db;

SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- 1. USERS
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS activity_logs;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS work_milestones;
DROP TABLE IF EXISTS review_checklist_results;
DROP TABLE IF EXISTS checklist_items;
DROP TABLE IF EXISTS review_comments;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS document_versions;
DROP TABLE IF EXISTS documents;
DROP TABLE IF EXISTS document_types;
DROP TABLE IF EXISTS supervision_assignments;
DROP TABLE IF EXISTS academic_works;
DROP TABLE IF EXISTS work_types;
DROP TABLE IF EXISTS lecturer_profiles;
DROP TABLE IF EXISTS student_profiles;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','lecturer','student') NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  last_login_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_users_role_active (role, is_active)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 2. STUDENT PROFILES
-- ---------------------------------------------------------------------
CREATE TABLE student_profiles (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL UNIQUE,
  student_number VARCHAR(30) NOT NULL UNIQUE,
  degree_level ENUM('s1','s2','s3') NOT NULL,
  study_program VARCHAR(150) NOT NULL,
  faculty VARCHAR(150) NULL,
  enrollment_year SMALLINT UNSIGNED NOT NULL,
  phone VARCHAR(25) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_student_user FOREIGN KEY (user_id)
    REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 3. LECTURER PROFILES
-- ---------------------------------------------------------------------
CREATE TABLE lecturer_profiles (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL UNIQUE,
  lecturer_number VARCHAR(30) NULL,
  academic_degree VARCHAR(50) NULL,
  department VARCHAR(150) NULL,
  expertise VARCHAR(255) NULL,
  phone VARCHAR(25) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_lecturer_user FOREIGN KEY (user_id)
    REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 4. WORK TYPES
-- ---------------------------------------------------------------------
CREATE TABLE work_types (
  id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(20) NOT NULL UNIQUE,
  name VARCHAR(100) NOT NULL,
  degree_level ENUM('s1','s2','s3') NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO work_types (code, name, degree_level) VALUES
('skripsi', 'Skripsi', 's1'),
('tesis', 'Tesis', 's2'),
('disertasi', 'Disertasi', 's3');

-- ---------------------------------------------------------------------
-- 5. ACADEMIC WORKS
-- ---------------------------------------------------------------------
CREATE TABLE academic_works (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_id BIGINT UNSIGNED NOT NULL,
  work_type_id TINYINT UNSIGNED NOT NULL,
  title VARCHAR(300) NOT NULL,
  abstract TEXT NULL,
  keywords VARCHAR(500) NULL,
  status ENUM(
    'draft','proposal','feasibility_test','writing',
    'progress_exam','revision','closed_exam',
    'ready_for_defense','defense','completed','cancelled'
  ) NOT NULL DEFAULT 'draft',
  started_at DATE NULL,
  completed_at DATE NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_work_student_type (student_id, work_type_id),
  CONSTRAINT fk_work_student FOREIGN KEY (student_id)
    REFERENCES student_profiles(id) ON DELETE CASCADE,
  CONSTRAINT fk_work_type FOREIGN KEY (work_type_id)
    REFERENCES work_types(id) ON DELETE RESTRICT,
  INDEX idx_work_status (status)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 6. SUPERVISION ASSIGNMENTS
-- ---------------------------------------------------------------------
CREATE TABLE supervision_assignments (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  academic_work_id BIGINT UNSIGNED NOT NULL,
  lecturer_id BIGINT UNSIGNED NOT NULL,
  assignment_role ENUM(
    'supervisor_1','supervisor_2',
    'promotor','co_promotor','examiner','advisor'
  ) NOT NULL,
  assigned_by BIGINT UNSIGNED NOT NULL,
  assigned_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  UNIQUE KEY uq_assignment (academic_work_id, lecturer_id, assignment_role, is_active),
  CONSTRAINT fk_assignment_work FOREIGN KEY (academic_work_id)
    REFERENCES academic_works(id) ON DELETE CASCADE,
  CONSTRAINT fk_assignment_lecturer FOREIGN KEY (lecturer_id)
    REFERENCES lecturer_profiles(id) ON DELETE CASCADE,
  CONSTRAINT fk_assignment_admin FOREIGN KEY (assigned_by)
    REFERENCES users(id) ON DELETE RESTRICT,
  INDEX idx_assignment_work (academic_work_id, is_active)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 7. DOCUMENT TYPES
-- ---------------------------------------------------------------------
CREATE TABLE document_types (
  id SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  work_type_id TINYINT UNSIGNED NULL,
  code VARCHAR(50) NOT NULL,
  name VARCHAR(150) NOT NULL,
  sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_document_type_code (work_type_id, code),
  CONSTRAINT fk_document_type_work FOREIGN KEY (work_type_id)
    REFERENCES work_types(id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO document_types
  (work_type_id, code, name, sort_order)
VALUES
  (NULL, 'proposal', 'Proposal Penelitian', 1),
  (NULL, 'chapter_1', 'Bab I Pendahuluan', 2),
  (NULL, 'chapter_2', 'Bab II Tinjauan Pustaka', 3),
  (NULL, 'chapter_3', 'Bab III Metode Penelitian', 4),
  (NULL, 'chapter_4', 'Bab IV Hasil dan Pembahasan', 5),
  (NULL, 'chapter_5', 'Bab V Penutup', 6),
  (NULL, 'full_document', 'Naskah Lengkap', 7),
  (NULL, 'revision', 'Dokumen Revisi', 8),
  (NULL, 'other', 'Dokumen Lainnya', 9),
  (3, 'publication_manuscript', 'Naskah Publikasi', 10),
  (3, 'progress_report', 'Laporan Kemajuan Penelitian', 11);

-- ---------------------------------------------------------------------
-- 8. DOCUMENTS
-- ---------------------------------------------------------------------
CREATE TABLE documents (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  academic_work_id BIGINT UNSIGNED NOT NULL,
  document_type_id SMALLINT UNSIGNED NOT NULL,
  title VARCHAR(255) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_document_work_type (academic_work_id, document_type_id),
  CONSTRAINT fk_document_work FOREIGN KEY (academic_work_id)
    REFERENCES academic_works(id) ON DELETE CASCADE,
  CONSTRAINT fk_document_type FOREIGN KEY (document_type_id)
    REFERENCES document_types(id) ON DELETE RESTRICT,
  INDEX idx_document_work (academic_work_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 9. DOCUMENT VERSIONS
-- ---------------------------------------------------------------------
CREATE TABLE document_versions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  document_id BIGINT UNSIGNED NOT NULL,
  version_number INT UNSIGNED NOT NULL DEFAULT 1,
  original_filename VARCHAR(255) NOT NULL,
  stored_filename VARCHAR(255) NOT NULL,
  file_path VARCHAR(500) NOT NULL,
  file_mime VARCHAR(120) NOT NULL,
  file_size_bytes BIGINT UNSIGNED NOT NULL,
  page_count INT UNSIGNED NULL,
  student_note TEXT NULL,
  uploaded_by BIGINT UNSIGNED NOT NULL,
  uploaded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  is_current TINYINT(1) NOT NULL DEFAULT 1,
  UNIQUE KEY uq_document_version (document_id, version_number),
  CONSTRAINT fk_version_document FOREIGN KEY (document_id)
    REFERENCES documents(id) ON DELETE CASCADE,
  CONSTRAINT fk_version_uploader FOREIGN KEY (uploaded_by)
    REFERENCES users(id) ON DELETE RESTRICT,
  INDEX idx_version_current (document_id, is_current)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 10. REVIEWS
-- ---------------------------------------------------------------------
CREATE TABLE reviews (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  document_version_id BIGINT UNSIGNED NOT NULL,
  reviewer_id BIGINT UNSIGNED NOT NULL,
  review_status ENUM(
    'pending','in_review','needs_revision','approved','rejected'
  ) NOT NULL DEFAULT 'pending',
  general_note TEXT NULL,
  reviewed_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_review_version FOREIGN KEY (document_version_id)
    REFERENCES document_versions(id) ON DELETE CASCADE,
  CONSTRAINT fk_review_reviewer FOREIGN KEY (reviewer_id)
    REFERENCES users(id) ON DELETE RESTRICT,
  INDEX idx_review_status (review_status),
  INDEX idx_review_reviewer (reviewer_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 11. REVIEW COMMENTS
-- ---------------------------------------------------------------------
CREATE TABLE review_comments (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  review_id BIGINT UNSIGNED NOT NULL,
  page_number INT UNSIGNED NOT NULL,
  position_x DECIMAL(5,2) NOT NULL,
  position_y DECIMAL(5,2) NOT NULL,
  comment_text TEXT NOT NULL,
  priority ENUM('minor','important','mandatory') NOT NULL DEFAULT 'important',
  is_resolved TINYINT(1) NOT NULL DEFAULT 0,
  resolved_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_comment_review FOREIGN KEY (review_id)
    REFERENCES reviews(id) ON DELETE CASCADE,
  INDEX idx_comment_page (review_id, page_number),
  INDEX idx_comment_priority (priority),
  INDEX idx_comment_unresolved (review_id, is_resolved)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 12. CHECKLIST ITEMS
-- ---------------------------------------------------------------------
CREATE TABLE checklist_items (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  work_type_id TINYINT UNSIGNED NULL,
  document_type_id SMALLINT UNSIGNED NULL,
  item_text VARCHAR(255) NOT NULL,
  item_order SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_checklist_scope (work_type_id, document_type_id, item_order),
  CONSTRAINT fk_checklist_work_type FOREIGN KEY (work_type_id)
    REFERENCES work_types(id) ON DELETE CASCADE,
  CONSTRAINT fk_checklist_document_type FOREIGN KEY (document_type_id)
    REFERENCES document_types(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 13. REVIEW CHECKLIST RESULTS
-- ---------------------------------------------------------------------
CREATE TABLE review_checklist_results (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  review_id BIGINT UNSIGNED NOT NULL,
  checklist_item_id BIGINT UNSIGNED NOT NULL,
  result ENUM('pending','complete','incomplete','not_applicable')
    NOT NULL DEFAULT 'pending',
  note VARCHAR(500) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_review_checklist (review_id, checklist_item_id),
  CONSTRAINT fk_result_review FOREIGN KEY (review_id)
    REFERENCES reviews(id) ON DELETE CASCADE,
  CONSTRAINT fk_result_item FOREIGN KEY (checklist_item_id)
    REFERENCES checklist_items(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 14. WORK MILESTONES
-- ---------------------------------------------------------------------
CREATE TABLE work_milestones (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  academic_work_id BIGINT UNSIGNED NOT NULL,
  milestone_type ENUM(
    'proposal','feasibility_test','writing','progress_exam',
    'revision','closed_exam','defense_preparation','defense',
    'final_revision','publication'
  ) NOT NULL,
  title VARCHAR(255) NOT NULL,
  description TEXT NULL,
  due_date DATE NOT NULL,
  completed_at DATETIME NULL,
  created_by BIGINT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_milestone_work FOREIGN KEY (academic_work_id)
    REFERENCES academic_works(id) ON DELETE CASCADE,
  CONSTRAINT fk_milestone_admin FOREIGN KEY (created_by)
    REFERENCES users(id) ON DELETE RESTRICT,
  INDEX idx_milestone_due (academic_work_id, due_date)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 15. NOTIFICATIONS
-- ---------------------------------------------------------------------
CREATE TABLE notifications (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  title VARCHAR(200) NOT NULL,
  message TEXT NOT NULL,
  type ENUM('comment','review','approval','deadline','system')
    NOT NULL DEFAULT 'system',
  related_table VARCHAR(64) NULL,
  related_id BIGINT UNSIGNED NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  read_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_notification_user FOREIGN KEY (user_id)
    REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_notification_unread (user_id, is_read, created_at)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 16. ACTIVITY LOGS
-- ---------------------------------------------------------------------
CREATE TABLE activity_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NULL,
  action VARCHAR(100) NOT NULL,
  related_table VARCHAR(64) NULL,
  related_id BIGINT UNSIGNED NULL,
  description TEXT NULL,
  ip_address VARCHAR(45) NULL,
  user_agent VARCHAR(255) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_log_user FOREIGN KEY (user_id)
    REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_log_user_time (user_id, created_at),
  INDEX idx_log_action (action)
) ENGINE=InnoDB;

-- =====================================================================
-- DEFAULT CHECKLIST
-- =====================================================================
INSERT INTO checklist_items
  (work_type_id, document_type_id, item_text, item_order)
VALUES
  (NULL, 1, 'Judul jelas, spesifik, dan sesuai bidang ilmu', 1),
  (NULL, 1, 'Latar belakang masalah tersusun logis', 2),
  (NULL, 1, 'Rumusan masalah jelas dan terukur', 3),
  (NULL, 1, 'Tujuan penelitian sesuai rumusan masalah', 4),
  (NULL, 1, 'Metode penelitian dijelaskan secara memadai', 5),
  (NULL, 1, 'Daftar pustaka memadai dan sitasi konsisten', 6),
  (NULL, 2, 'Latar belakang sesuai topik penelitian', 1),
  (NULL, 2, 'Rumusan masalah konsisten dengan judul', 2),
  (NULL, 2, 'Tujuan dan manfaat penelitian jelas', 3),
  (NULL, 2, 'Batasan masalah dijelaskan', 4),
  (NULL, 3, 'Teori relevan dan mutakhir', 1),
  (NULL, 3, 'Kerangka pemikiran jelas', 2),
  (NULL, 3, 'Hipotesis atau proposisi ditulis sesuai kebutuhan', 3),
  (NULL, 4, 'Desain dan metode penelitian sesuai tujuan', 1),
  (NULL, 4, 'Populasi, sampel, atau sumber data jelas', 2),
  (NULL, 4, 'Teknik pengumpulan dan analisis data dijelaskan', 3),
  (NULL, 5, 'Hasil disajikan sistematis', 1),
  (NULL, 5, 'Pembahasan menjawab rumusan masalah', 2),
  (NULL, 5, 'Kesimpulan sesuai tujuan penelitian', 3),
  (NULL, 6, 'Kesimpulan merangkum hasil utama', 1),
  (NULL, 6, 'Saran relevan dan dapat ditindaklanjuti', 2);

-- =====================================================================
-- DEFAULT ADMIN ACCOUNT
-- Password: Admin@12345
-- Ganti password ini segera setelah instalasi.
-- =====================================================================
INSERT INTO users (name, email, password_hash, role, is_active)
VALUES (
  'Administrator SIMBITA',
  'admin@simbita.local',
  '$2y$10$e0NRz2jZKXq0Q7Z6j9W1De9Yv0Y6q4J8u8W3F6s8R7G9N2K4L5M6O',
  'admin',
  1
);
