CREATE DATABASE IF NOT EXISTS qrcam CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE qrcam;

CREATE TABLE IF NOT EXISTS students (
    id CHAR(36) PRIMARY KEY,
    student_no VARCHAR(80) NOT NULL UNIQUE,
    full_name VARCHAR(200) NOT NULL,
    class_section VARCHAR(120) NOT NULL DEFAULT '',
    gender ENUM('male', 'female', 'unknown') NOT NULL DEFAULT 'unknown',
    qr_token CHAR(64) NOT NULL UNIQUE,
    created_at DATETIME NOT NULL
) ENGINE=InnoDB;

SET @gender_column_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'students' AND COLUMN_NAME = 'gender'
);
SET @gender_migration = IF(
    @gender_column_exists = 0,
    'ALTER TABLE students ADD COLUMN gender ENUM(''male'', ''female'', ''unknown'') NOT NULL DEFAULT ''unknown'' AFTER class_section',
    'SELECT ''students.gender already exists'''
);
PREPARE gender_statement FROM @gender_migration;
EXECUTE gender_statement;
DEALLOCATE PREPARE gender_statement;

CREATE TABLE IF NOT EXISTS teacher_accounts (
    id CHAR(36) PRIMARY KEY,
    username VARCHAR(120) NOT NULL UNIQUE,
    full_name VARCHAR(160) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    KEY teacher_accounts_name (full_name)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS attendance (
    id CHAR(36) PRIMARY KEY,
    student_id CHAR(36) NOT NULL,
    attendance_date DATE NOT NULL,
    attended_at DATETIME NOT NULL,
    status VARCHAR(16) NOT NULL DEFAULT 'present',
    note VARCHAR(500) NOT NULL DEFAULT '',
    UNIQUE KEY attendance_once_per_day (student_id, attendance_date),
    CONSTRAINT attendance_student_fk FOREIGN KEY (student_id) REFERENCES students(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS audit_logs (
    id CHAR(36) PRIMARY KEY,
    actor VARCHAR(120) NOT NULL,
    action VARCHAR(40) NOT NULL,
    record_id CHAR(36) NOT NULL,
    before_data JSON NULL,
    after_data JSON NOT NULL,
    created_at DATETIME NOT NULL,
    KEY audit_record_created (record_id, created_at)
) ENGINE=InnoDB;
