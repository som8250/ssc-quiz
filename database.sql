-- Railway & SSC Quiz App Database Schema
-- Run this SQL in your MySQL database

CREATE DATABASE IF NOT EXISTS ssc_quiz_db;
USE ssc_quiz_db;

-- Drop existing tables if needed (uncomment to reset)
-- DROP TABLE IF EXISTS questions;
-- DROP TABLE IF EXISTS quiz_results;
-- DROP TABLE IF EXISTS bookmarks;
-- DROP TABLE IF EXISTS user_quiz_history;
-- DROP TABLE IF EXISTS users;

-- Questions table - Updated with Railway exams
CREATE TABLE IF NOT EXISTS questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    exam_type VARCHAR(20) NOT NULL,
    section VARCHAR(20) DEFAULT 'SSC',
    topic VARCHAR(100) NOT NULL,
    question_text TEXT NOT NULL,
    option_a VARCHAR(255) NOT NULL,
    option_b VARCHAR(255) NOT NULL,
    option_c VARCHAR(255) NOT NULL,
    option_d VARCHAR(255) NOT NULL,
    correct_answer CHAR(1) NOT NULL,
    explanation TEXT,
    difficulty ENUM('easy', 'medium', 'hard') DEFAULT 'medium',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Quiz results table
CREATE TABLE IF NOT EXISTS quiz_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_name VARCHAR(100),
    user_id INT DEFAULT NULL,
    session_id VARCHAR(100) DEFAULT NULL,
    exam_type VARCHAR(20) NOT NULL,
    section VARCHAR(20) DEFAULT 'SSC',
    topic VARCHAR(100) NOT NULL,
    total_questions INT NOT NULL,
    correct_answers INT NOT NULL,
    wrong_answers INT NOT NULL,
    time_taken INT NOT NULL,
    score DECIMAL(5,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sample questions for SSC Exams
INSERT INTO questions (exam_type, section, topic, question_text, option_a, option_b, option_c, option_d, correct_answer, explanation, difficulty) VALUES
-- SSC CGL - Number System
('CGL', 'SSC', 'Number System', 'What is the place value of 7 in the number 87,654?', '7', '70', '700', '7000', 'B', 'In 87,654, the 7 is in the tens place, so its value is 70.', 'easy'),
('CGL', 'SSC', 'Number System', 'Which of the following is the smallest prime number?', '0', '1', '2', '3', 'C', '2 is the smallest and only even prime number.', 'easy'),
('CGL', 'SSC', 'Number System', 'Find the unit digit of 7^25.', '1', '3', '7', '9', 'C', 'The unit digit of powers of 7 follows pattern 7,9,3,1. 25 mod 4 = 1, so unit digit is 7.', 'medium'),

-- SSC CHSL - Number System
('CHSL', 'SSC', 'Number System', 'What is 1234 + 5678?', '6912', '6902', '6812', '6912', 'A', 'Simple addition: 1234 + 5678 = 6912', 'easy'),
('CHSL', 'SSC', 'Number System', 'Count the numbers from 1 to 100. How many times does digit 9 appear?', '10', '11', '20', '19', 'C', '9, 19, 29, 39, 49, 59, 69, 79, 89, 90, 91, 92, 93, 94, 95, 96, 97, 98, 99 (20 times)', 'medium'),

-- SSC MTS - Number System
('MTS', 'SSC', 'Number System', 'What is 999 + 99 + 9?', '1107', '1097', '1087', '1117', 'A', '999 + 99 + 9 = 1107', 'easy'),

-- SSC CPO - Number System
('CPO', 'SSC', 'Number System', 'If a number is divisible by both 2 and 3, it is also divisible by:', '5', '6', '8', '12', 'B', 'LCM of 2 and 3 is 6, so divisible by 6.', 'easy'),

-- SSC GD - Number System
('GD', 'SSC', 'Number System', 'What is 25% of 80?', '15', '20', '25', '30', 'B', '25% of 80 = 25/100 × 80 = 20', 'easy'),

-- SSC CGL - Percentage
('CGL', 'SSC', 'Percentage', 'If 20% of x is 50, then find x.', '200', '250', '300', '350', 'B', '20% of x = 50, so x = 50 × 100/20 = 250', 'easy'),
('CGL', 'SSC', 'Percentage', 'If salary increases from Rs. 10,000 to Rs. 12,000, what is the percentage increase?', '10%', '15%', '20%', '25%', 'C', 'Increase = 2000, Percentage = (2000/10000) × 100 = 20%', 'medium'),

-- SSC CHSL - Percentage
('CHSL', 'SSC', 'Percentage', 'What is 15% of 200?', '25', '30', '35', '40', 'B', '15% of 200 = 15/100 × 200 = 30', 'easy'),
('CHSL', 'SSC', 'Percentage', 'A shopkeeper gives 10% discount on Rs. 500. What is the selling price?', '450', '500', '550', '400', 'A', 'Selling Price = 500 - 10% of 500 = 500 - 50 = 450', 'easy'),

-- SSC MTS - Percentage
('MTS', 'SSC', 'Percentage', 'What is 50% of 100?', '25', '50', '75', '100', 'B', '50% of 100 = 50', 'easy'),

-- SSC CGL - Profit & Loss
('CGL', 'SSC', 'Profit & Loss', 'A man buys an article for Rs. 100 and sells for Rs. 120. What is the profit percent?', '10%', '15%', '20%', '25%', 'C', 'Profit = 20, Profit% = (20/100) × 100 = 20%', 'easy'),

-- SSC CHSL - Profit & Loss
('CHSL', 'SSC', 'Profit & Loss', 'A shopkeeper sells a shirt for Rs. 450 at a loss of 10%. What was the cost price?', '400', '500', '450', '550', 'B', 'CP = SP × 100/(100 - loss%) = 450 × 100/90 = 500', 'medium'),

-- SSC CGL - Ratio & Proportion
('CGL', 'SSC', 'Ratio & Proportion', 'The ratio of 5 to 4 is equal to:', '10:8', '15:12', '20:16', 'All of these', 'D', 'All ratios simplify to 5:4', 'easy'),

-- SSC CHSL - Ratio & Proportion
('CHSL', 'SSC', 'Ratio & Proportion', 'If A:B = 2:3 and B:C = 3:4, then A:B:C is:', '2:3:4', '4:3:4', '2:3:5', '8:12:16', 'A', 'A:B = 2:3, B:C = 3:4, so A:B:C = 2:3:4', 'medium'),

-- SSC CGL - Average
('CGL', 'SSC', 'Average', 'The average of 10, 20, 30, 40, 50 is:', '25', '30', '35', '40', 'B', 'Sum = 150, Average = 150/5 = 30', 'easy'),

-- SSC CHSL - Average
('CHSL', 'SSC', 'Average', 'The average of 7 numbers is 20. If one number is excluded, the average becomes 18. The excluded number is:', '36', '38', '40', '42', 'A', 'Total = 140, New total = 18×6 = 108, Excluded = 140-108 = 36', 'medium'),

-- SSC CGL - Time & Work
('CGL', 'SSC', 'Time & Work', 'A can do a work in 10 days and B can do it in 20 days. In how many days can they do it together?', '6.67 days', '7 days', '8 days', '15 days', 'A', 'A''s work = 1/10, B''s work = 1/20, Combined = 3/20 = 6.67 days', 'medium'),

-- SSC CHSL - Time & Work
('CHSL', 'SSC', 'Time & Work', 'If 5 workers can build a wall in 20 days, how many days will 10 workers take?', '5 days', '10 days', '15 days', '20 days', 'B', 'Work is inversely proportional to workers. 5×20 = 10×x, x=10 days', 'easy'),

-- SSC CGL - Time & Distance
('CGL', 'SSC', 'Time & Distance', 'A car covers 300 km in 5 hours. What is its speed?', '50 km/h', '55 km/h', '60 km/h', '65 km/h', 'C', 'Speed = Distance/Time = 300/5 = 60 km/h', 'easy'),

-- SSC CHSL - Time & Distance
('CHSL', 'SSC', 'Time & Distance', 'A train 100m long passes a pole in 10 seconds. Find its speed in km/h.', '10 km/h', '36 km/h', '30 km/h', '45 km/h', 'B', 'Speed = 100/10 = 10 m/s = 10×18/5 = 36 km/h', 'medium'),

-- SSC CGL - Simple Interest
('CGL', 'SSC', 'Simple Interest', 'Find SI on Rs. 5000 at 10% per annum for 2 years.', '800', '1000', '1200', '1500', 'B', 'SI = (P×R×T)/100 = (5000×10×2)/100 = 1000', 'easy'),

-- SSC CHSL - Simple Interest
('CHSL', 'SSC', 'Simple Interest', 'At what rate will Rs. 2000 become Rs. 2600 in 3 years?', '8%', '10%', '12%', '15%', 'B', 'SI = 600, Rate = (SI×100)/(P×T) = (600×100)/(2000×3) = 10%', 'medium'),

-- SSC CGL - Geometry
('CGL', 'SSC', 'Geometry', 'The sum of angles in a triangle is:', '90°', '180°', '270°', '360°', 'B', 'Sum of angles in a triangle = 180°', 'easy'),
('CGL', 'SSC', 'Geometry', 'The area of a circle with radius 7 cm is (use π = 22/7):', '154 cm²', '144 cm²', '164 cm²', '174 cm²', 'A', 'Area = πr² = 22/7 × 7² = 154 cm²', 'easy'),

-- SSC CHSL - Geometry
('CHSL', 'SSC', 'Geometry', 'Find the perimeter of a rectangle with length 8 cm and breadth 5 cm.', '26 cm', '40 cm', '13 cm', '24 cm', 'A', 'Perimeter = 2(l+b) = 2(8+5) = 26 cm', 'easy'),

-- SSC MTS - Geometry
('MTS', 'SSC', 'Geometry', 'How many sides does a hexagon have?', '5', '6', '7', '8', 'B', 'A hexagon has 6 sides', 'easy'),

-- SSC CGL - Trigonometry
('CGL', 'SSC', 'Trigonometry', 'sin 90° = ?', '0', '1', '-1', '∞', 'B', 'sin 90° = 1', 'easy'),
('CGL', 'SSC', 'Trigonometry', 'cos 0° = ?', '0', '1', '-1', '2', 'B', 'cos 0° = 1', 'easy'),

-- SSC CHSL - Trigonometry
('CHSL', 'SSC', 'Trigonometry', 'tan 45° = ?', '0', '1', '√3', '1/√3', 'B', 'tan 45° = 1', 'easy'),

-- SSC CGL - Mensuration
('CGL', 'SSC', 'Mensuration', 'Volume of a cube with side 3 cm is:', '9 cm³', '18 cm³', '27 cm³', '36 cm³', 'C', 'Volume = side³ = 3³ = 27 cm³', 'easy'),

-- SSC CHSL - Mensuration
('CHSL', 'SSC', 'Mensuration', 'The area of a triangle with base 10 cm and height 8 cm is:', '40 cm²', '80 cm²', '18 cm²', '45 cm²', 'A', 'Area = 1/2 × base × height = 1/2 × 10 × 8 = 40 cm²', 'easy'),

-- Railway NTPC Graduate - Number System
('NTPC_Graduate', 'Railway', 'Number System', 'What is the smallest number that is divisible by 12, 15, and 20?', '60', '120', '180', '240', 'B', 'LCM of 12,15,20 = 2×2×3×5 = 60, but 60×2 = 120', 'medium'),
('NTPC_Graduate', 'Railway', 'Number System', 'The sum of first 50 natural numbers is:', '1250', '1275', '1225', '1300', 'B', 'Sum = n(n+1)/2 = 50×51/2 = 1275', 'easy'),

-- Railway NTPC UG - Number System
('NTPC_UG', 'Railway', 'Number System', 'What is 123 × 456?', '56088', '55088', '54088', '53088', 'A', '123 × 456 = 56088', 'medium'),
('NTPC_UG', 'Railway', 'Number System', 'Which is the largest 4-digit number?', '9999', '9000', '1000', '9990', 'A', '9999 is the largest 4-digit number', 'easy'),

-- Railway Group D - Number System
('Group_D', 'Railway', 'Number System', 'What is 25 + 75?', '90', '100', '110', '120', 'B', '25 + 75 = 100', 'easy'),
('Group_D', 'Railway', 'Number System', 'What is 100 - 37?', '53', '63', '73', '83', 'B', '100 - 37 = 63', 'easy'),

-- Railway NTPC - Percentage
('NTPC_Graduate', 'Railway', 'Percentage', 'If 10% of a number is 45, what is the number?', '350', '400', '450', '500', 'C', 'Number = 45 × 10 = 450', 'easy'),
('NTPC_UG', 'Railway', 'Percentage', 'What is 20% of 150?', '25', '30', '35', '40', 'B', '20% of 150 = 30', 'easy'),

-- Railway Group D - Percentage
('Group_D', 'Railway', 'Percentage', 'Half of 100 is what percent of 50?', '50%', '100%', '150%', '200%', 'B', 'Half of 100 = 50, 50 is 100% of 50', 'medium'),

-- Railway NTPC - Profit & Loss
('NTPC_Graduate', 'Railway', 'Profit & Loss', 'A shopkeeper buys an item for Rs. 200 and sells for Rs. 250. Find profit percent.', '20%', '25%', '30%', '35%', 'B', 'Profit = 50, Profit% = 50/200×100 = 25%', 'easy'),
('NTPC_UG', 'Railway', 'Profit & Loss', 'If cost price is Rs. 80 and selling price is Rs. 100, profit is:', 'Rs. 10', 'Rs. 15', 'Rs. 20', 'Rs. 25', 'C', 'Profit = 100 - 80 = 20', 'easy'),

-- Railway NTPC - Average
('NTPC_Graduate', 'Railway', 'Average', 'Average of 5, 10, 15, 20, 25 is:', '14', '15', '16', '17', 'B', 'Sum = 75, Average = 75/5 = 15', 'easy'),

-- Railway NTPC - Time & Distance
('NTPC_Graduate', 'Railway', 'Time & Distance', 'A train runs at 60 km/h. How far will it go in 2.5 hours?', '120 km', '140 km', '150 km', '160 km', 'C', 'Distance = Speed × Time = 60 × 2.5 = 150 km', 'easy'),

-- Railway NTPC - Geometry
('NTPC_Graduate', 'Railway', 'Geometry', 'How many angles does a quadrilateral have?', '3', '4', '5', '6', 'B', 'A quadrilateral has 4 angles', 'easy'),

-- Railway NTPC - Reasoning
('NTPC_Graduate', 'Railway', 'Reasoning', 'Find the odd one out: Dog, Cat, Lion, Cow', 'Dog', 'Cat', 'Lion', 'Cow', 'C', 'Lion is a wild animal, others are domestic', 'easy'),
('NTPC_UG', 'Railway', 'Reasoning', 'What comes next: 2, 4, 8, 16, ?', '24', '28', '32', '36', 'C', 'Each number is doubled: 16×2 = 32', 'easy');

-- Update existing questions to add section
UPDATE questions SET section = 'SSC' WHERE section IS NULL OR section = '';

-- Verify questions inserted
SELECT COUNT(*) as total_questions FROM questions;
SELECT exam_type, section, topic, COUNT(*) as count FROM questions GROUP BY exam_type, section, topic;

-- =============================================
-- NEW: User System Tables (for quiz takers)
-- =============================================

-- Users table for quiz takers
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    is_active BOOLEAN DEFAULT TRUE,
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Bookmarks/Favorites table
CREATE TABLE IF NOT EXISTS bookmarks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    question_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
    UNIQUE KEY unique_bookmark (user_id, question_id)
);

-- User quiz history (for analytics) - Updated with section field
CREATE TABLE IF NOT EXISTS user_quiz_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    session_id VARCHAR(100),
    exam_type VARCHAR(20),
    section VARCHAR(20) DEFAULT 'SSC',
    topic VARCHAR(100),
    total_questions INT,
    correct_answers INT,
    wrong_answers INT,
    skipped_answers INT,
    time_taken INT,
    score DECIMAL(5,2),
    practice_mode BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Create index for better query performance
CREATE INDEX idx_quiz_results_user ON quiz_results(user_name);
CREATE INDEX idx_user_quiz_history_user ON user_quiz_history(user_id);
CREATE INDEX idx_user_quiz_history_section ON user_quiz_history(section);
CREATE INDEX idx_bookmarks_user ON bookmarks(user_id);

-- Sample user (username: user, password: user123)
-- Password hash for 'user123'
INSERT INTO users (username, email, password_hash, full_name) VALUES
('user', 'user@sscquiz.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Test User');

-- Verify tables created
SHOW TABLES;
