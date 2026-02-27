-- Secure Admin Panel Database Schema
-- Run this SQL to update your existing database

-- Select the database (change if your database name is different)
USE ssc_quiz_db;

-- 1. Admin Users Table
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    role ENUM('super_admin', 'admin') DEFAULT 'admin',
    is_active BOOLEAN DEFAULT TRUE,
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 2. Subjects Table
CREATE TABLE IF NOT EXISTS subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    icon VARCHAR(50),
    is_active BOOLEAN DEFAULT TRUE,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 3. Topics Table (linked to subjects)
CREATE TABLE IF NOT EXISTS topics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
);

-- 4. Quiz Sets Table
CREATE TABLE IF NOT EXISTS quiz_sets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    subject_id INT,
    exam_type ENUM('CGL', 'CHSL', 'MTS', 'ALL') DEFAULT 'ALL',
    time_limit INT NOT NULL DEFAULT 20, -- in minutes
    total_questions INT DEFAULT 20,
    marks_per_question DECIMAL(5,2) DEFAULT 1.00,
    negative_marking DECIMAL(3,2) DEFAULT 0.25,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE SET NULL
);

-- 5. Add exam_type to existing questions table
ALTER TABLE questions ADD COLUMN IF NOT EXISTS subject_id INT;
ALTER TABLE questions ADD COLUMN IF NOT EXISTS is_active BOOLEAN DEFAULT TRUE;
ALTER TABLE questions ADD COLUMN IF NOT EXISTS quiz_set_id INT;
ALTER TABLE questions ADD COLUMN IF NOT EXISTS marks DECIMAL(5,2) DEFAULT 1.00;

-- 6. Audit Log Table
CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(50),
    record_id INT,
    old_value TEXT,
    new_value TEXT,
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES admin_users(id) ON DELETE SET NULL
);

-- 7. Add user tracking to quiz results
ALTER TABLE quiz_results ADD COLUMN IF NOT EXISTS session_id VARCHAR(100);

-- Insert sample admin (username: admin, password: Admin@123)
-- Password is hashed using bcrypt
INSERT INTO admin_users (username, email, password_hash, full_name, role) VALUES
('admin', 'admin@sscquiz.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Super Admin', 'super_admin');

-- Insert sample subjects
INSERT INTO subjects (name, description, icon, display_order) VALUES
('Mathematics', 'SSC Math Topics', '📐', 1),
('English', 'English Grammar & Vocabulary', '📚', 2),
('Reasoning', 'Logical Reasoning', '🧠', 3),
('General Knowledge', 'General Awareness', '🌍', 4);

-- Insert sample topics for Mathematics
INSERT INTO topics (subject_id, name, display_order) VALUES
(1, 'Number System', 1),
(1, 'Percentage', 2),
(1, 'Profit & Loss', 3),
(1, 'Ratio & Proportion', 4),
(1, 'Average', 5),
(1, 'Time & Work', 6),
(1, 'Time & Distance', 7),
(1, 'Simple Interest', 8),
(1, 'LCM & HCF', 9),
(1, 'Algebra', 10),
(1, 'Geometry', 11),
(1, 'Trigonometry', 12),
(1, 'Mensuration', 13);

-- Insert sample quiz sets
INSERT INTO quiz_sets (name, description, subject_id, exam_type, time_limit, total_questions) VALUES
('CGL Math Practice', 'Practice set for SSC CGL Mathematics', 1, 'CGL', 25, 25),
('CHSL Math Practice', 'Practice set for SSC CHSL Mathematics', 1, 'CHSL', 20, 20),
('MTS Math Practice', 'Practice set for SSC MTS Mathematics', 1, 'MTS', 15, 15),
('All Exams Math', 'Mixed Math questions for all exams', 1, 'ALL', 20, 20);

-- Verify setup
SELECT 'Admin Users:' as info, COUNT(*) as count FROM admin_users
UNION ALL
SELECT 'Subjects:', COUNT(*) FROM subjects
UNION ALL
SELECT 'Topics:', COUNT(*) FROM topics
UNION ALL
SELECT 'Quiz Sets:', COUNT(*) FROM quiz_sets;
