<?php
// Start session only if not already active
if (session_status() === PHP_SESSION_NONE) {
    // Set secure session parameters
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
    ini_set('session.use_strict_mode', 1);
    session_start();
}

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ssc_quiz_db');

// Connect to database
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    // Store error for API to handle gracefully
    $GLOBALS['db_error'] = $conn->connect_error;
} else {
    $GLOBALS['db_error'] = null;
    // Set charset
    $conn->set_charset('utf8');
}

// Quiz settings
define('QUESTIONS_PER_QUIZ', 20);
define('TIME_PER_QUESTION', 60); // seconds

// Function to get connection
function getDbConnection() {
    global $conn;
    if ($conn->connect_error) {
        return null;
    }
    return $conn;
}
