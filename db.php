<?php
/**
 * Database Connection Module
 * 
 * Handles database connections with environment-based configuration.
 * Includes proper error logging and connection management.
 */

// Load environment variables from .env file if available
function loadEnv($filePath) {
    if (!file_exists($filePath)) {
        return false;
    }
    
    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Parse key=value pairs
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Remove quotes if present
            if (preg_match('/^["\'](.*)["\']\s*$/', $value, $matches)) {
                $value = $matches[1];
            }
            
            // Set environment variable
            putenv("$key=$value");
        }
    }
    return true;
}

// Load .env file
$envFile = __DIR__ . '/.env';
loadEnv($envFile);

// Get database configuration from environment variables with fallbacks
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'ssc_quiz_db');

// Application settings from environment
define('APP_ENV', getenv('APP_ENV') ?: 'production');
define('DEBUG_MODE', getenv('DEBUG_MODE') === 'true');
define('SESSION_LIFETIME', (int)(getenv('SESSION_LIFETIME') ?: 3600));

// Log file path
define('LOG_FILE', __DIR__ . '/logs/app_' . date('Y-m-d') . '.log');

/**
 * Custom error handler to log errors
 */
function logError($message, $context = []) {
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? ' | Context: ' . json_encode($context) : '';
    $logMessage = "[$timestamp] ERROR: $message$contextStr\n";
    
    // Ensure logs directory exists
    $logDir = __DIR__ . '/logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    // Write to log file
    error_log($logMessage, 3, LOG_FILE);
    
    // Also use PHP's error_log for system logging
    error_log($message);
}

/**
 * Get database connection
 * Returns mysqli connection object or null on failure
 */
function getDbConnection() {
    static $conn = null;
    
    if ($conn !== null) {
        return $conn;
    }
    
    // Create connection
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Check connection
    if ($conn->connect_error) {
        logError('Database connection failed', [
            'host' => DB_HOST,
            'user' => DB_USER,
            'database' => DB_NAME,
            'error' => $conn->connect_error
        ]);
        
        $GLOBALS['db_error'] = $conn->connect_error;
        return null;
    }
    
    // Set charset
    $conn->set_charset('utf8mb4');
    
    // Clear any previous errors
    $GLOBALS['db_error'] = null;
    
    logError('Database connection established successfully', [
        'host' => DB_HOST,
        'database' => DB_NAME
    ]);
    
    return $conn;
}

/**
 * Execute a query with error logging
 */
function executeQuery($sql, $params = [], $types = '') {
    $conn = getDbConnection();
    
    if (!$conn) {
        logError('Failed to execute query - no database connection', ['sql' => $sql]);
        return false;
    }
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        logError('Query preparation failed', [
            'sql' => $sql,
            'error' => $conn->error
        ]);
        return false;
    }
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    if (!$stmt->execute()) {
        logError('Query execution failed', [
            'sql' => $sql,
            'error' => $stmt->error
        ]);
        $stmt->close();
        return false;
    }
    
    return $stmt;
}

/**
 * Get last database error
 */
function getDbError() {
    return $GLOBALS['db_error'] ?? null;
}

/**
 * Close database connection
 */
function closeDbConnection() {
    global $conn;
    
    if ($conn) {
        $conn->close();
        $conn = null;
        logError('Database connection closed');
    }
}

// Register shutdown function to log fatal errors
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR])) {
        logError('Fatal error', [
            'message' => $error['message'],
            'file' => $error['file'],
            'line' => $error['line']
        ]);
    }
});
