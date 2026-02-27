<?php
/**
 * Secure Admin Authentication System
 * Handles login, logout, session management, and route protection
 */

// Configuration (must be before session_start)
define('SESSION_NAME', 'SSC_ADMIN_SESSION');
define('TOKEN_EXPIRY', 3600); // 1 hour
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_TIME', 900); // 15 minutes

// Only set session name and start session if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}

// Check if user is logged in
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && 
           isset($_SESSION['admin_token']) && 
           isset($_SESSION['admin_role']);
}

// Get current admin ID
function getCurrentAdminId() {
    return $_SESSION['admin_id'] ?? null;
}

// Get current admin role
function getCurrentAdminRole() {
    return $_SESSION['admin_role'] ?? null;
}

// Generate secure token
function generateToken($userId) {
    return bin2hex(random_bytes(32));
}

// Hash password using bcrypt
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

// Verify password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Require admin authentication
function requireAuth() {
    if (!isAdminLoggedIn()) {
        http_response_code(401);
        echo json_encode(['error' => 'Authentication required', 'code' => 'AUTH_REQUIRED']);
        exit;
    }
}

// Require specific role
function requireRole($roles) {
    requireAuth();
    $currentRole = getCurrentAdminRole();
    if (!in_array($currentRole, (array)$roles)) {
        http_response_code(403);
        echo json_encode(['error' => 'Insufficient permissions', 'code' => 'FORBIDDEN']);
        exit;
    }
}

// Login function
function adminLogin($username, $password) {
    global $conn;
    
    // Check for locked out IP
    $ip = $_SERVER['REMOTE_ADDR'];
    $lockoutKey = "login_lockout_$ip";
    
    if (isset($_SESSION[$lockoutKey]) && $_SESSION[$lockoutKey]['attempts'] >= MAX_LOGIN_ATTEMPTS) {
        $lockoutEnd = $_SESSION[$lockoutKey]['time'] + LOCKOUT_TIME;
        if (time() < $lockoutEnd) {
            $remaining = $lockoutEnd - time();
            return [
                'success' => false, 
                'error' => 'Too many login attempts. Try again in ' . ceil($remaining/60) . ' minutes.',
                'code' => 'LOCKED_OUT'
            ];
        } else {
            unset($_SESSION[$lockoutKey]);
        }
    }
    
    // Sanitize input
    $username = trim($username);
    $username = $conn->real_escape_string($username);
    
    // Get user from database
    $stmt = $conn->prepare("SELECT id, username, email, password_hash, full_name, role, is_active, last_login 
                           FROM admin_users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        // Log failed attempt
        logAuthEvent(null, 'LOGIN_FAILED', 'User not found: ' . $username);
        return ['success' => false, 'error' => 'Invalid credentials', 'code' => 'INVALID'];
    }
    
    $user = $result->fetch_assoc();
    $stmt->close();
    
    // Check if account is active
    if (!$user['is_active']) {
        logAuthEvent($user['id'], 'LOGIN_FAILED', 'Account disabled');
        return ['success' => false, 'error' => 'Account is disabled', 'code' => 'DISABLED'];
    }
    
    // Verify password
    if (!verifyPassword($password, $user['password_hash'])) {
        // Log failed attempt
        logAuthEvent($user['id'], 'LOGIN_FAILED', 'Wrong password');
        
        // Track failed attempts
        if (!isset($_SESSION[$lockoutKey])) {
            $_SESSION[$lockoutKey] = ['attempts' => 0, 'time' => time()];
        }
        $_SESSION[$lockoutKey]['attempts']++;
        $_SESSION[$lockoutKey]['time'] = time();
        
        return ['success' => false, 'error' => 'Invalid credentials', 'code' => 'INVALID'];
    }
    
    // Check if password needs rehashing
    if (password_needs_rehash($user['password_hash'], PASSWORD_BCRYPT, ['cost' => 12])) {
        $newHash = hashPassword($password);
        $stmt = $conn->prepare("UPDATE admin_users SET password_hash = ? WHERE id = ?");
        $stmt->bind_param("si", $newHash, $user['id']);
        $stmt->execute();
        $stmt->close();
    }
    
    // Generate session token
    $token = generateToken($user['id']);
    
    // Update last login
    $stmt = $conn->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
    $stmt->bind_param("i", $user['id']);
    $stmt->execute();
    $stmt->close();
    
    // Set session
    $_SESSION['admin_id'] = $user['id'];
    $_SESSION['admin_username'] = $user['username'];
    $_SESSION['admin_email'] = $user['email'];
    $_SESSION['admin_full_name'] = $user['full_name'];
    $_SESSION['admin_role'] = $user['role'];
    $_SESSION['admin_token'] = $token;
    $_SESSION['login_time'] = time();
    
    // Clear lockout
    unset($_SESSION[$lockoutKey]);
    
    // Log successful login
    logAuthEvent($user['id'], 'LOGIN_SUCCESS', 'User logged in successfully');
    
    return [
        'success' => true,
        'user' => [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'full_name' => $user['full_name'],
            'role' => $user['role']
        ],
        'token' => $token
    ];
}

// Logout function
function adminLogout() {
    $adminId = getCurrentAdminId();
    logAuthEvent($adminId, 'LOGOUT', 'User logged out');
    
    // Destroy session
    session_unset();
    session_destroy();
    
    return ['success' => true];
}

// Log authentication events
function logAuthEvent($userId, $action, $details) {
    global $conn;
    
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    
    $stmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, old_value, ip_address, user_agent) 
                           VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $userId, $action, $details, $ip, $agent);
    $stmt->execute();
    $stmt->close();
}

// Check session timeout
function checkSessionTimeout() {
    if (isset($_SESSION['login_time'])) {
        if (time() - $_SESSION['login_time'] > TOKEN_EXPIRY) {
            adminLogout();
            return false;
        }
        // Extend session on activity
        $_SESSION['login_time'] = time();
    }
    return true;
}

// Get dashboard statistics
function getDashboardStats() {
    global $conn;
    
    $stats = [];
    
    // Total questions
    $result = $conn->query("SELECT COUNT(*) as count FROM questions");
    $stats['total_questions'] = $result->fetch_assoc()['count'];
    
    // Active questions
    $result = $conn->query("SELECT COUNT(*) as count FROM questions WHERE is_active = 1");
    $stats['active_questions'] = $result->fetch_assoc()['count'];
    
    // Total subjects
    $result = $conn->query("SELECT COUNT(*) as count FROM subjects");
    $stats['total_subjects'] = $result->fetch_assoc()['count'];
    
    // Active subjects
    $result = $conn->query("SELECT COUNT(*) as count FROM subjects WHERE is_active = 1");
    $stats['active_subjects'] = $result->fetch_assoc()['count'];
    
    // Total topics
    $result = $conn->query("SELECT COUNT(*) as count FROM topics");
    $stats['total_topics'] = $result->fetch_assoc()['count'];
    
    // Total quiz sets
    $result = $conn->query("SELECT COUNT(*) as count FROM quiz_sets");
    $stats['total_quiz_sets'] = $result->fetch_assoc()['count'];
    
    // Active quiz sets
    $result = $conn->query("SELECT COUNT(*) as count FROM quiz_sets WHERE is_active = 1");
    $stats['active_quiz_sets'] = $result->fetch_assoc()['count'];
    
    // Total quiz attempts
    $result = $conn->query("SELECT COUNT(*) as count FROM quiz_results");
    $stats['total_attempts'] = $result->fetch_assoc()['count'];
    
    // Recent activity
    $result = $conn->query("SELECT COUNT(*) as count FROM quiz_results WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)");
    $stats['recent_attempts'] = $result->fetch_assoc()['count'];
    
    return $stats;
}
