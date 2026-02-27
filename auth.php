<?php
/**
 * Centralized Authentication Module
 * 
 * This file provides session-based authentication checking.
 * Include this file at the top of any PHP file that needs auth verification.
 * Now includes CSRF protection and secure session configuration.
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    // Set secure session parameters
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_path', '/');
    ini_set('session.cookie_domain', '');
    
    // Only set secure flag if using HTTPS
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        ini_set('session.cookie_secure', 1);
    }
    
    // Use strict mode to prevent session fixation
    ini_set('session.use_strict_mode', 1);
    
    session_start();
}

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/**
 * Validate CSRF token
 * @param string $token The token to validate
 * @return bool True if valid, false otherwise
 */
function validateCsrfToken($token) {
    if (!isset($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get CSRF token for forms
 * @return string The CSRF token
 */
function getCsrfToken() {
    return $_SESSION['csrf_token'] ?? '';
}

/**
 * Generate a new CSRF token (for form regeneration after validation)
 */
function regenerateCsrfToken() {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/**
 * Sanitize input for output
 * @param mixed $data The data to sanitize
 * @return mixed Sanitized data
 */
function sanitizeOutput($data) {
    if (is_array($data)) {
        return array_map('sanitizeOutput', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Check if user is logged in
 * Returns true if user_id is set in session
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current logged in user ID
 * Returns user_id or null if not logged in
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current logged in username
 * Returns username or null if not logged in
 */
function getCurrentUsername() {
    return $_SESSION['user_username'] ?? null;
}

/**
 * Get current user role
 * Returns role or null if not logged in
 */
function getCurrentUserRole() {
    return $_SESSION['user_role'] ?? null;
}

/**
 * Check if current user is admin
 */
function isAdmin() {
    return getCurrentUserRole() === 'admin';
}

/**
 * Require login - redirects to login page if not logged in
 * Should be called at the top of restricted pages
 */
function requireLogin() {
    if (!isLoggedIn()) {
        // Store the intended URL for redirect after login
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? 'index.php';
        
        header('Location: login.php');
        exit;
    }
}

/**
 * Require admin role - redirects to index if not admin
 */
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: index.php');
        exit;
    }
}

/**
 * Get the redirect URL after successful login
 * Returns the stored URL or defaults to index.php
 */
function getRedirectAfterLogin() {
    $redirect = $_SESSION['redirect_after_login'] ?? 'index.php';
    unset($_SESSION['redirect_after_login']);
    return $redirect;
}

/**
 * API authentication check
 * Used for AJAX/API endpoints
 * Returns JSON error and exits if not logged in
 */
function requireApiLogin() {
    if (!isLoggedIn()) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false, 
            'error' => 'Please login to continue', 
            'code' => 'NOT_LOGGED_IN'
        ]);
        exit;
    }
}

/**
 * API admin check
 * Used for AJAX/API admin endpoints
 * Returns JSON error and exits if not admin
 */
function requireApiAdmin() {
    requireApiLogin();
    if (!isAdmin()) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false, 
            'error' => 'Admin access required', 
            'code' => 'ADMIN_REQUIRED'
        ]);
        exit;
    }
}

/**
 * Rate limiting check for login attempts
 * @param string $identifier User identifier (IP or username)
 * @param int $maxAttempts Maximum allowed attempts
 * @param int $timeWindow Time window in seconds
 * @return bool True if within limit, false if exceeded
 */
function checkRateLimit($identifier, $maxAttempts = 5, $timeWindow = 300) {
    $rateLimitFile = __DIR__ . '/logs/rate_limit_' . md5($identifier) . '.json';
    $now = time();
    $attempts = [];
    
    // Load existing attempts
    if (file_exists($rateLimitFile)) {
        $data = json_decode(file_get_contents($rateLimitFile), true);
        if ($data && is_array($data)) {
            // Filter to only recent attempts within time window
            foreach ($data as $timestamp) {
                if ($now - $timestamp < $timeWindow) {
                    $attempts[] = $timestamp;
                }
            }
        }
    }
    
    // Check if exceeded
    if (count($attempts) >= $maxAttempts) {
        return false;
    }
    
    // Record this attempt
    $attempts[] = $now;
    file_put_contents($rateLimitFile, json_encode($attempts));
    
    return true;
}

/**
 * Record failed login attempt
 * @param string $identifier User identifier (IP or username)
 */
function recordFailedLogin($identifier) {
    $rateLimitFile = __DIR__ . '/logs/rate_limit_' . md5($identifier) . '.json';
    $now = time();
    $attempts = [];
    
    if (file_exists($rateLimitFile)) {
        $data = json_decode(file_get_contents($rateLimitFile), true);
        if ($data && is_array($data)) {
            foreach ($data as $timestamp) {
                if ($now - $timestamp < 300) { // 5 minutes
                    $attempts[] = $timestamp;
                }
            }
        }
    }
    
    $attempts[] = $now;
    file_put_contents($rateLimitFile, json_encode($attempts));
    
    // Log the failed attempt
    $logFile = __DIR__ . '/logs/login_attempts.log';
    $logDir = __DIR__ . '/logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    $logMessage = "[" . date('Y-m-d H:i:s') . "] Failed login attempt for: $identifier\n";
    error_log($logMessage, 3, $logFile);
}

/**
 * Clear rate limit after successful login
 * @param string $identifier User identifier
 */
function clearRateLimit($identifier) {
    $rateLimitFile = __DIR__ . '/logs/rate_limit_' . md5($identifier) . '.json';
    if (file_exists($rateLimitFile)) {
        unlink($rateLimitFile);
    }
}
