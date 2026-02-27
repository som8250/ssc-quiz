<?php
/**
 * Secure Admin API
 * All endpoints require authentication unless marked as public
 */

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

require_once 'config.php';
require_once 'admin_auth.php';

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Get request method and action
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// Check database connection
if ($GLOBALS['db_error']) {
    echo json_encode(['error' => 'Database connection failed. Please check your configuration.', 'code' => 'DB_ERROR', 'details' => $GLOBALS['db_error']]);
    exit;
}

// Public endpoints (no auth required)
$publicEndpoints = ['login', 'logout', 'check_auth', 'dashboard_stats'];

if (!in_array($action, $publicEndpoints)) {
    // Check session timeout
    if (!checkSessionTimeout()) {
        echo json_encode(['error' => 'Session expired', 'code' => 'SESSION_EXPIRED']);
        exit;
    }
    
    // Require authentication for protected endpoints
    requireAuth();
}

// Route requests
switch ($action) {
    // Authentication
    case 'login':
        handleLogin();
        break;
    case 'logout':
        handleLogout();
        break;
    case 'check_auth':
        checkAuth();
        break;
    
    // Dashboard
    case 'dashboard_stats':
        handleDashboardStats();
        break;
    
    // Subjects CRUD
    case 'get_subjects':
        handleGetSubjects();
        break;
    case 'add_subject':
        requireRole(['super_admin', 'admin']);
        handleAddSubject();
        break;
    case 'update_subject':
        requireRole(['super_admin', 'admin']);
        handleUpdateSubject();
        break;
    case 'delete_subject':
        requireRole(['super_admin']);
        handleDeleteSubject();
        break;
    case 'toggle_subject':
        requireRole(['super_admin', 'admin']);
        handleToggleSubject();
        break;
    
    // Topics CRUD
    case 'get_topics':
        handleGetTopics();
        break;
    case 'add_topic':
        requireRole(['super_admin', 'admin']);
        handleAddTopic();
        break;
    case 'update_topic':
        requireRole(['super_admin', 'admin']);
        handleUpdateTopic();
        break;
    case 'delete_topic':
        requireRole(['super_admin']);
        handleDeleteTopic();
        break;
    case 'toggle_topic':
        requireRole(['super_admin', 'admin']);
        handleToggleTopic();
        break;
    
    // Quiz Sets CRUD
    case 'get_quiz_sets':
        handleGetQuizSets();
        break;
    case 'add_quiz_set':
        requireRole(['super_admin', 'admin']);
        handleAddQuizSet();
        break;
    case 'update_quiz_set':
        requireRole(['super_admin', 'admin']);
        handleUpdateQuizSet();
        break;
    case 'delete_quiz_set':
        requireRole(['super_admin']);
        handleDeleteQuizSet();
        break;
    case 'toggle_quiz_set':
        requireRole(['super_admin', 'admin']);
        handleToggleQuizSet();
        break;
    
    // Questions CRUD
    case 'get_questions':
        handleGetQuestions();
        break;
    case 'add_question':
        requireRole(['super_admin', 'admin']);
        handleAddQuestion();
        break;
    case 'update_question':
        requireRole(['super_admin', 'admin']);
        handleUpdateQuestion();
        break;
    case 'delete_question':
        requireRole(['super_admin']);
        handleDeleteQuestion();
        break;
    case 'toggle_question':
        requireRole(['super_admin', 'admin']);
        handleToggleQuestion();
        break;
    
    // Import/Export
    case 'export_questions':
        requireRole(['super_admin', 'admin']);
        handleExportQuestions();
        break;
    case 'import_questions':
        requireRole(['super_admin', 'admin']);
        handleImportQuestions();
        break;
    
    // Audit Logs
    case 'get_audit_logs':
        requireRole(['super_admin']);
        handleGetAuditLogs();
        break;
    
    // Quiz Results
    case 'get_quiz_results':
        handleGetQuizResults();
        break;
    
    default:
        echo json_encode(['error' => 'Invalid action', 'code' => 'INVALID_ACTION']);
}

// ================== AUTHENTICATION HANDLERS ==================

function handleLogin() {
    global $conn;
    
    // Support both JSON and form data
    $data = json_decode(file_get_contents('php://input'), true);
    if (!empty($data)) {
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';
    } else {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
    }
    
    if (empty($username) || empty($password)) {
        echo json_encode(['error' => 'Username and password required', 'code' => 'MISSING_CREDENTIALS']);
        return;
    }
    
    $result = adminLogin($username, $password);
    echo json_encode($result);
}

function handleLogout() {
    $result = adminLogout();
    echo json_encode($result);
}

function checkAuth() {
    if (isAdminLoggedIn()) {
        echo json_encode([
            'authenticated' => true,
            'user' => [
                'id' => $_SESSION['admin_id'],
                'username' => $_SESSION['admin_username'],
                'full_name' => $_SESSION['admin_full_name'],
                'role' => $_SESSION['admin_role']
            ]
        ]);
    } else {
        echo json_encode(['authenticated' => false]);
    }
}

// ================== DASHBOARD HANDLERS ==================

function handleDashboardStats() {
    global $conn;
    
    $stats = [];
    
    // Check if questions table exists
    $result = $conn->query("SHOW TABLES LIKE 'questions'");
    if ($result->num_rows === 0) {
        echo json_encode(['success' => true, 'stats' => [
            'total_questions' => 0,
            'active_questions' => 0,
            'total_subjects' => 0,
            'active_subjects' => 0,
            'total_topics' => 0,
            'total_quiz_sets' => 0,
            'active_quiz_sets' => 0,
            'total_attempts' => 0,
            'recent_attempts' => 0
        ]]);
        return;
    }
    
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
    
    echo json_encode(['success' => true, 'stats' => $stats]);
}

// ================== SUBJECTS CRUD ==================

function handleGetSubjects() {
    global $conn;
    
    $includeInactive = $_GET['include_inactive'] ?? false;
    
    // Check if table exists
    $result = $conn->query("SHOW TABLES LIKE 'subjects'");
    if ($result->num_rows === 0) {
        echo json_encode(['success' => true, 'subjects' => []]);
        return;
    }
    
    $sql = "SELECT * FROM subjects";
    if (!$includeInactive) {
        $sql .= " WHERE is_active = 1";
    }
    $sql .= " ORDER BY display_order, name";
    
    $result = $conn->query($sql);
    $subjects = [];
    
    while ($row = $result->fetch_assoc()) {
        $topicsResult = $conn->query("SELECT COUNT(*) as count FROM topics WHERE subject_id = {$row['id']}");
        $row['topic_count'] = $topicsResult->fetch_assoc()['count'];
        $subjects[] = $row;
    }
    
    echo json_encode(['success' => true, 'subjects' => $subjects]);
}

function handleAddSubject() {
    global $conn;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $name = trim($data['name'] ?? '');
    $description = trim($data['description'] ?? '');
    $icon = trim($data['icon'] ?? '📚');
    $displayOrder = intval($data['display_order'] ?? 0);
    
    if (empty($name)) {
        echo json_encode(['error' => 'Subject name is required', 'code' => 'VALIDATION_ERROR']);
        return;
    }
    
    $stmt = $conn->prepare("INSERT INTO subjects (name, description, icon, display_order) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $name, $description, $icon, $displayOrder);
    
    if ($stmt->execute()) {
        $id = $stmt->insert_id;
        logAdminAction('ADD_SUBJECT', 'subjects', $id, null, json_encode($data));
        echo json_encode(['success' => true, 'id' => $id, 'message' => 'Subject added successfully']);
    } else {
        echo json_encode(['error' => 'Failed to add subject', 'code' => 'DB_ERROR']);
    }
    
    $stmt->close();
}

function handleUpdateSubject() {
    global $conn;
    
    $data = json_decode(file_get_contents('php://input'), true);
    $id = intval($data['id'] ?? 0);
    
    if ($id <= 0) {
        echo json_encode(['error' => 'Invalid subject ID', 'code' => 'VALIDATION_ERROR']);
        return;
    }
    
    $oldResult = $conn->query("SELECT * FROM subjects WHERE id = $id");
    $oldValue = $oldResult->fetch_assoc();
    
    $name = trim($data['name'] ?? $oldValue['name']);
    $description = trim($data['description'] ?? $oldValue['description']);
    $icon = trim($data['icon'] ?? $oldValue['icon']);
    $displayOrder = intval($data['display_order'] ?? $oldValue['display_order']);
    
    $stmt = $conn->prepare("UPDATE subjects SET name = ?, description = ?, icon = ?, display_order = ? WHERE id = ?");
    $stmt->bind_param("sssii", $name, $description, $icon, $displayOrder, $id);
    
    if ($stmt->execute()) {
        logAdminAction('UPDATE_SUBJECT', 'subjects', $id, json_encode($oldValue), json_encode($data));
        echo json_encode(['success' => true, 'message' => 'Subject updated successfully']);
    } else {
        echo json_encode(['error' => 'Failed to update subject', 'code' => 'DB_ERROR']);
    }
    
    $stmt->close();
}

function handleDeleteSubject() {
    global $conn;
    
    $id = intval($_GET['id'] ?? 0);
    
    if ($id <= 0) {
        echo json_encode(['error' => 'Invalid subject ID', 'code' => 'VALIDATION_ERROR']);
        return;
    }
    
    $oldResult = $conn->query("SELECT * FROM subjects WHERE id = $id");
    $oldValue = $oldResult->fetch_assoc();
    
    $stmt = $conn->prepare("DELETE FROM subjects WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        logAdminAction('DELETE_SUBJECT', 'subjects', $id, json_encode($oldValue), null);
        echo json_encode(['success' => true, 'message' => 'Subject deleted successfully']);
    } else {
        echo json_encode(['error' => 'Failed to delete subject', 'code' => 'DB_ERROR']);
    }
    
    $stmt->close();
}

function handleToggleSubject() {
    global $conn;
    
    $id = intval($_GET['id'] ?? 0);
    
    if ($id <= 0) {
        echo json_encode(['error' => 'Invalid subject ID', 'code' => 'VALIDATION_ERROR']);
        return;
    }
    
    $stmt = $conn->prepare("UPDATE subjects SET is_active = NOT is_active WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $result = $conn->query("SELECT is_active FROM subjects WHERE id = $id");
        $newState = $result->fetch_assoc()['is_active'];
        
        logAdminAction('TOGGLE_SUBJECT', 'subjects', $id, null, "is_active: $newState");
        echo json_encode(['success' => true, 'is_active' => $newState]);
    } else {
        echo json_encode(['error' => 'Failed to toggle subject', 'code' => 'DB_ERROR']);
    }
    
    $stmt->close();
}

// ================== TOPICS CRUD ==================

function handleGetTopics() {
    global $conn;
    
    $subjectId = intval($_GET['subject_id'] ?? 0);
    $includeInactive = $_GET['include_inactive'] ?? false;
    
    // Check if table exists
    $result = $conn->query("SHOW TABLES LIKE 'topics'");
    if ($result->num_rows === 0) {
        echo json_encode(['success' => true, 'topics' => []]);
        return;
    }
    
    $sql = "SELECT t.*, s.name as subject_name FROM topics t 
            LEFT JOIN subjects s ON t.subject_id = s.id";
    
    if ($subjectId > 0) {
        $sql .= " WHERE t.subject_id = $subjectId";
    }
    
    if (!$includeInactive && $subjectId > 0) {
        $sql .= " AND t.is_active = 1";
    } elseif (!$includeInactive) {
        $sql .= " WHERE t.is_active = 1";
    }
    
    $sql .= " ORDER BY t.display_order, t.name";
    
    $result = $conn->query($sql);
    $topics = [];
    
    while ($row = $result->fetch_assoc()) {
        $topics[] = $row;
    }
    
    echo json_encode(['success' => true, 'topics' => $topics]);
}

function handleAddTopic() {
    global $conn;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $subjectId = intval($data['subject_id'] ?? 0);
    $name = trim($data['name'] ?? '');
    $description = trim($data['description'] ?? '');
    $displayOrder = intval($data['display_order'] ?? 0);
    
    if ($subjectId <= 0 || empty($name)) {
        echo json_encode(['error' => 'Subject and name are required', 'code' => 'VALIDATION_ERROR']);
        return;
    }
    
    $stmt = $conn->prepare("INSERT INTO topics (subject_id, name, description, display_order) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("issi", $subjectId, $name, $description, $displayOrder);
    
    if ($stmt->execute()) {
        $id = $stmt->insert_id;
        logAdminAction('ADD_TOPIC', 'topics', $id, null, json_encode($data));
        echo json_encode(['success' => true, 'id' => $id, 'message' => 'Topic added successfully']);
    } else {
        echo json_encode(['error' => 'Failed to add topic', 'code' => 'DB_ERROR']);
    }
    
    $stmt->close();
}

function handleUpdateTopic() {
    global $conn;
    
    $data = json_decode(file_get_contents('php://input'), true);
    $id = intval($data['id'] ?? 0);
    
    if ($id <= 0) {
        echo json_encode(['error' => 'Invalid topic ID', 'code' => 'VALIDATION_ERROR']);
        return;
    }
    
    $oldResult = $conn->query("SELECT * FROM topics WHERE id = $id");
    $oldValue = $oldResult->fetch_assoc();
    
    $subjectId = intval($data['subject_id'] ?? $oldValue['subject_id']);
    $name = trim($data['name'] ?? $oldValue['name']);
    $description = trim($data['description'] ?? $oldValue['description']);
    $displayOrder = intval($data['display_order'] ?? $oldValue['display_order']);
    
    $stmt = $conn->prepare("UPDATE topics SET subject_id = ?, name = ?, description = ?, display_order = ? WHERE id = ?");
    $stmt->bind_param("issii", $subjectId, $name, $description, $displayOrder, $id);
    
    if ($stmt->execute()) {
        logAdminAction('UPDATE_TOPIC', 'topics', $id, json_encode($oldValue), json_encode($data));
        echo json_encode(['success' => true, 'message' => 'Topic updated successfully']);
    } else {
        echo json_encode(['error' => 'Failed to update topic', 'code' => 'DB_ERROR']);
    }
    
    $stmt->close();
}

function handleDeleteTopic() {
    global $conn;
    
    $id = intval($_GET['id'] ?? 0);
    
    if ($id <= 0) {
        echo json_encode(['error' => 'Invalid topic ID', 'code' => 'VALIDATION_ERROR']);
        return;
    }
    
    $oldResult = $conn->query("SELECT * FROM topics WHERE id = $id");
    $oldValue = $oldResult->fetch_assoc();
    
    $stmt = $conn->prepare("DELETE FROM topics WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        logAdminAction('DELETE_TOPIC', 'topics', $id, json_encode($oldValue), null);
        echo json_encode(['success' => true, 'message' => 'Topic deleted successfully']);
    } else {
        echo json_encode(['error' => 'Failed to delete topic', 'code' => 'DB_ERROR']);
    }
    
    $stmt->close();
}

function handleToggleTopic() {
    global $conn;
    
    $id = intval($_GET['id'] ?? 0);
    
    if ($id <= 0) {
        echo json_encode(['error' => 'Invalid topic ID', 'code' => 'VALIDATION_ERROR']);
        return;
    }
    
    $stmt = $conn->prepare("UPDATE topics SET is_active = NOT is_active WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $result = $conn->query("SELECT is_active FROM topics WHERE id = $id");
        $newState = $result->fetch_assoc()['is_active'];
        
        logAdminAction('TOGGLE_TOPIC', 'topics', $id, null, "is_active: $newState");
        echo json_encode(['success' => true, 'is_active' => $newState]);
    } else {
        echo json_encode(['error' => 'Failed to toggle topic', 'code' => 'DB_ERROR']);
    }
    
    $stmt->close();
}

// ================== QUIZ SETS CRUD ==================

function handleGetQuizSets() {
    global $conn;
    
    $includeInactive = $_GET['include_inactive'] ?? false;
    
    // Check if table exists
    $result = $conn->query("SHOW TABLES LIKE 'quiz_sets'");
    if ($result->num_rows === 0) {
        echo json_encode(['success' => true, 'quiz_sets' => []]);
        return;
    }
    
    $sql = "SELECT q.*, s.name as subject_name FROM quiz_sets q 
            LEFT JOIN subjects s ON q.subject_id = s.id";
    
    if (!$includeInactive) {
        $sql .= " WHERE q.is_active = 1";
    }
    
    $sql .= " ORDER BY q.created_at DESC";
    
    $result = $conn->query($sql);
    $quizSets = [];
    
    while ($row = $result->fetch_assoc()) {
        $quizSets[] = $row;
    }
    
    echo json_encode(['success' => true, 'quiz_sets' => $quizSets]);
}

function handleAddQuizSet() {
    global $conn;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $name = trim($data['name'] ?? '');
    $description = trim($data['description'] ?? '');
    $subjectId = intval($data['subject_id'] ?? null);
    $examType = $data['exam_type'] ?? 'ALL';
    $timeLimit = intval($data['time_limit'] ?? 20);
    $totalQuestions = intval($data['total_questions'] ?? 20);
    $marksPerQuestion = floatval($data['marks_per_question'] ?? 1.00);
    $negativeMarking = floatval($data['negative_marking'] ?? 0.25);
    
    if (empty($name)) {
        echo json_encode(['error' => 'Quiz set name is required', 'code' => 'VALIDATION_ERROR']);
        return;
    }
    
    $subjectId = $subjectId > 0 ? $subjectId : null;
    
    $stmt = $conn->prepare("INSERT INTO quiz_sets 
        (name, description, subject_id, exam_type, time_limit, total_questions, marks_per_question, negative_marking) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    $null = null;
    if ($subjectId === null) {
        $stmt = $conn->prepare("INSERT INTO quiz_sets 
            (name, description, exam_type, time_limit, total_questions, marks_per_question, negative_marking) 
            VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssiidd", $name, $description, $examType, $timeLimit, $totalQuestions, $marksPerQuestion, $negativeMarking);
    } else {
        $stmt->bind_param("issiiidd", $name, $description, $subjectId, $examType, $timeLimit, $totalQuestions, $marksPerQuestion, $negativeMarking);
    }
    
    if ($stmt->execute()) {
        $id = $stmt->insert_id;
        logAdminAction('ADD_QUIZ_SET', 'quiz_sets', $id, null, json_encode($data));
        echo json_encode(['success' => true, 'id' => $id, 'message' => 'Quiz set added successfully']);
    } else {
        echo json_encode(['error' => 'Failed to add quiz set', 'code' => 'DB_ERROR']);
    }
    
    $stmt->close();
}

function handleUpdateQuizSet() {
    global $conn;
    
    $data = json_decode(file_get_contents('php://input'), true);
    $id = intval($data['id'] ?? 0);
    
    if ($id <= 0) {
        echo json_encode(['error' => 'Invalid quiz set ID', 'code' => 'VALIDATION_ERROR']);
        return;
    }
    
    $oldResult = $conn->query("SELECT * FROM quiz_sets WHERE id = $id");
    $oldValue = $oldResult->fetch_assoc();
    
    $name = trim($data['name'] ?? $oldValue['name']);
    $description = trim($data['description'] ?? $oldValue['description']);
    $subjectId = intval($data['subject_id'] ?? $oldValue['subject_id']);
    $examType = $data['exam_type'] ?? $oldValue['exam_type'];
    $timeLimit = intval($data['time_limit'] ?? $oldValue['time_limit']);
    $totalQuestions = intval($data['total_questions'] ?? $oldValue['total_questions']);
    $marksPerQuestion = floatval($data['marks_per_question'] ?? $oldValue['marks_per_question']);
    $negativeMarking = floatval($data['negative_marking'] ?? $oldValue['negative_marking']);
    
    $stmt = $conn->prepare("UPDATE quiz_sets 
        SET name = ?, description = ?, subject_id = ?, exam_type = ?, time_limit = ?, 
        total_questions = ?, marks_per_question = ?, negative_marking = ? 
        WHERE id = ?");
    
    $stmt->bind_param("ssiiidddi", $name, $description, $subjectId, $examType, 
                      $timeLimit, $totalQuestions, $marksPerQuestion, $negativeMarking, $id);
    
    if ($stmt->execute()) {
        logAdminAction('UPDATE_QUIZ_SET', 'quiz_sets', $id, json_encode($oldValue), json_encode($data));
        echo json_encode(['success' => true, 'message' => 'Quiz set updated successfully']);
    } else {
        echo json_encode(['error' => 'Failed to update quiz set', 'code' => 'DB_ERROR']);
    }
    
    $stmt->close();
}

function handleDeleteQuizSet() {
    global $conn;
    
    $id = intval($_GET['id'] ?? 0);
    
    if ($id <= 0) {
        echo json_encode(['error' => 'Invalid quiz set ID', 'code' => 'VALIDATION_ERROR']);
        return;
    }
    
    $oldResult = $conn->query("SELECT * FROM quiz_sets WHERE id = $id");
    $oldValue = $oldResult->fetch_assoc();
    
    $stmt = $conn->prepare("DELETE FROM quiz_sets WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        logAdminAction('DELETE_QUIZ_SET', 'quiz_sets', $id, json_encode($oldValue), null);
        echo json_encode(['success' => true, 'message' => 'Quiz set deleted successfully']);
    } else {
        echo json_encode(['error' => 'Failed to delete quiz set', 'code' => 'DB_ERROR']);
    }
    
    $stmt->close();
}

function handleToggleQuizSet() {
    global $conn;
    
    $id = intval($_GET['id'] ?? 0);
    
    if ($id <= 0) {
        echo json_encode(['error' => 'Invalid quiz set ID', 'code' => 'VALIDATION_ERROR']);
        return;
    }
    
    $stmt = $conn->prepare("UPDATE quiz_sets SET is_active = NOT is_active WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $result = $conn->query("SELECT is_active FROM quiz_sets WHERE id = $id");
        $newState = $result->fetch_assoc()['is_active'];
        
        logAdminAction('TOGGLE_QUIZ_SET', 'quiz_sets', $id, null, "is_active: $newState");
        echo json_encode(['success' => true, 'is_active' => $newState]);
    } else {
        echo json_encode(['error' => 'Failed to toggle quiz set', 'code' => 'DB_ERROR']);
    }
    
    $stmt->close();
}

// ================== QUESTIONS CRUD ==================

function handleGetQuestions() {
    global $conn;
    
    $subjectId = intval($_GET['subject_id'] ?? 0);
    $topicId = intval($_GET['topic_id'] ?? 0);
    $examType = $_GET['exam_type'] ?? '';
    $difficulty = $_GET['difficulty'] ?? '';
    $includeInactive = $_GET['include_inactive'] ?? false;
    $limit = intval($_GET['limit'] ?? 50);
    $offset = intval($_GET['offset'] ?? 0);
    
    // Check if table exists
    $result = $conn->query("SHOW TABLES LIKE 'questions'");
    if ($result->num_rows === 0) {
        echo json_encode(['success' => true, 'questions' => [], 'total' => 0]);
        return;
    }
    
    $sql = "SELECT q.*, s.name as subject_name 
            FROM questions q 
            LEFT JOIN subjects s ON q.subject_id = s.id";
    
    $conditions = [];
    
    if ($subjectId > 0) {
        $conditions[] = "q.subject_id = $subjectId";
    }
    if (!empty($examType)) {
        $conditions[] = "q.exam_type = '$examType'";
    }
    if (!empty($difficulty)) {
        $conditions[] = "q.difficulty = '$difficulty'";
    }
    if (!$includeInactive) {
        $conditions[] = "q.is_active = 1";
    }
    
    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }
    
    $sql .= " ORDER BY q.id DESC LIMIT $limit OFFSET $offset";
    
    $result = $conn->query($sql);
    $questions = [];
    
    while ($row = $result->fetch_assoc()) {
        $questions[] = $row;
    }
    
    // Get total count
    $countSql = str_replace("SELECT q.*, s.name as subject_name", "SELECT COUNT(*) as total", $sql);
    $countSql = preg_replace('/LIMIT \d+ OFFSET \d+/', '', $countSql);
    $total = $conn->query($countSql)->fetch_assoc()['total'];
    
    echo json_encode(['success' => true, 'questions' => $questions, 'total' => $total]);
}

function handleAddQuestion() {
    global $conn;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $examType = $data['exam_type'] ?? 'CGL';
    $topic = trim($data['topic'] ?? '');
    $subjectId = intval($data['subject_id'] ?? 0);
    $questionText = trim($data['question_text'] ?? '');
    $optionA = trim($data['option_a'] ?? '');
    $optionB = trim($data['option_b'] ?? '');
    $optionC = trim($data['option_c'] ?? '');
    $optionD = trim($data['option_d'] ?? '');
    $correctAnswer = strtoupper($data['correct_answer'] ?? 'A');
    $explanation = trim($data['explanation'] ?? '');
    $difficulty = $data['difficulty'] ?? 'medium';
    $marks = floatval($data['marks'] ?? 1.00);
    
    if (empty($questionText) || empty($optionA) || empty($optionB) || empty($optionC) || empty($optionD)) {
        echo json_encode(['error' => 'All fields are required', 'code' => 'VALIDATION_ERROR']);
        return;
    }
    
    if (!in_array($correctAnswer, ['A', 'B', 'C', 'D'])) {
        echo json_encode(['error' => 'Invalid correct answer', 'code' => 'VALIDATION_ERROR']);
        return;
    }
    
    $stmt = $conn->prepare("INSERT INTO questions 
        (exam_type, topic, subject_id, question_text, option_a, option_b, option_c, option_d, correct_answer, explanation, difficulty, marks) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->bind_param("ssissssssssd", 
        $examType, $topic, $subjectId, $questionText, 
        $optionA, $optionB, $optionC, $optionD, 
        $correctAnswer, $explanation, $difficulty, $marks);
    
    if ($stmt->execute()) {
        $id = $stmt->insert_id;
        logAdminAction('ADD_QUESTION', 'questions', $id, null, "Topic: $topic, Exam: $examType");
        echo json_encode(['success' => true, 'id' => $id, 'message' => 'Question added successfully']);
    } else {
        echo json_encode(['error' => 'Failed to add question', 'code' => 'DB_ERROR']);
    }
    
    $stmt->close();
}

function handleUpdateQuestion() {
    global $conn;
    
    $data = json_decode(file_get_contents('php://input'), true);
    $id = intval($data['id'] ?? 0);
    
    if ($id <= 0) {
        echo json_encode(['error' => 'Invalid question ID', 'code' => 'VALIDATION_ERROR']);
        return;
    }
    
    $oldResult = $conn->query("SELECT * FROM questions WHERE id = $id");
    $oldValue = $oldResult->fetch_assoc();
    
    $examType = $data['exam_type'] ?? $oldValue['exam_type'];
    $topic = trim($data['topic'] ?? $oldValue['topic']);
    $subjectId = intval($data['subject_id'] ?? $oldValue['subject_id']);
    $questionText = trim($data['question_text'] ?? $oldValue['question_text']);
    $optionA = trim($data['option_a'] ?? $oldValue['option_a']);
    $optionB = trim($data['option_b'] ?? $oldValue['option_b']);
    $optionC = trim($data['option_c'] ?? $oldValue['option_c']);
    $optionD = trim($data['option_d'] ?? $oldValue['option_d']);
    $correctAnswer = strtoupper($data['correct_answer'] ?? $oldValue['correct_answer']);
    $explanation = trim($data['explanation'] ?? $oldValue['explanation']);
    $difficulty = $data['difficulty'] ?? $oldValue['difficulty'];
    $marks = floatval($data['marks'] ?? $oldValue['marks']);
    
    $stmt = $conn->prepare("UPDATE questions 
        SET exam_type = ?, topic = ?, subject_id = ?, question_text = ?, 
            option_a = ?, option_b = ?, option_c = ?, option_d = ?, 
            correct_answer = ?, explanation = ?, difficulty = ?, marks = ? 
        WHERE id = ?");
    
    $stmt->bind_param("ssissssssssdi", 
        $examType, $topic, $subjectId, $questionText, 
        $optionA, $optionB, $optionC, $optionD, 
        $correctAnswer, $explanation, $difficulty, $marks, $id);
    
    if ($stmt->execute()) {
        logAdminAction('UPDATE_QUESTION', 'questions', $id, json_encode($oldValue), null);
        echo json_encode(['success' => true, 'message' => 'Question updated successfully']);
    } else {
        echo json_encode(['error' => 'Failed to update question', 'code' => 'DB_ERROR']);
    }
    
    $stmt->close();
}

function handleDeleteQuestion() {
    global $conn;
    
    $id = intval($_GET['id'] ?? 0);
    
    if ($id <= 0) {
        echo json_encode(['error' => 'Invalid question ID', 'code' => 'VALIDATION_ERROR']);
        return;
    }
    
    $oldResult = $conn->query("SELECT * FROM questions WHERE id = $id");
    $oldValue = $oldResult->fetch_assoc();
    
    $stmt = $conn->prepare("DELETE FROM questions WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        logAdminAction('DELETE_QUESTION', 'questions', $id, json_encode($oldValue), null);
        echo json_encode(['success' => true, 'message' => 'Question deleted successfully']);
    } else {
        echo json_encode(['error' => 'Failed to delete question', 'code' => 'DB_ERROR']);
    }
    
    $stmt->close();
}

function handleToggleQuestion() {
    global $conn;
    
    $id = intval($_GET['id'] ?? 0);
    
    if ($id <= 0) {
        echo json_encode(['error' => 'Invalid question ID', 'code' => 'VALIDATION_ERROR']);
        return;
    }
    
    $stmt = $conn->prepare("UPDATE questions SET is_active = NOT is_active WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $result = $conn->query("SELECT is_active FROM questions WHERE id = $id");
        $newState = $result->fetch_assoc()['is_active'];
        
        logAdminAction('TOGGLE_QUESTION', 'questions', $id, null, "is_active: $newState");
        echo json_encode(['success' => true, 'is_active' => $newState]);
    } else {
        echo json_encode(['error' => 'Failed to toggle question', 'code' => 'DB_ERROR']);
    }
    
    $stmt->close();
}

// ================== IMPORT/EXPORT ==================

function handleExportQuestions() {
    global $conn;
    
    $format = $_GET['format'] ?? 'json';
    $subjectId = intval($_GET['subject_id'] ?? 0);
    $topic = $_GET['topic'] ?? '';
    
    $sql = "SELECT exam_type, topic, question_text, option_a, option_b, option_c, option_d, 
            correct_answer, explanation, difficulty, marks 
            FROM questions WHERE 1=1";
    
    if ($subjectId > 0) {
        $sql .= " AND subject_id = $subjectId";
    }
    if (!empty($topic)) {
        $sql .= " AND topic = '$topic'";
    }
    
    $result = $conn->query($sql);
    $questions = [];
    
    while ($row = $result->fetch_assoc()) {
        $questions[] = $row;
    }
    
    if ($format === 'csv') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="questions_export_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['exam_type', 'topic', 'question_text', 'option_a', 'option_b', 'option_c', 'option_d', 'correct_answer', 'explanation', 'difficulty', 'marks']);
        
        foreach ($questions as $q) {
            fputcsv($output, $q);
        }
        
        fclose($output);
    } else {
        echo json_encode(['success' => true, 'questions' => $questions, 'count' => count($questions)]);
    }
}

function handleImportQuestions() {
    global $conn;
    
    if (!isset($_FILES['file'])) {
        echo json_encode(['error' => 'No file uploaded', 'code' => 'VALIDATION_ERROR']);
        return;
    }
    
    $file = $_FILES['file'];
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    
    $imported = 0;
    $errors = [];
    
    if ($extension === 'json') {
        $content = file_get_contents($file['tmp_name']);
        $questions = json_decode($content, true);
        
        if (!is_array($questions)) {
            echo json_encode(['error' => 'Invalid JSON format', 'code' => 'VALIDATION_ERROR']);
            return;
        }
        
        foreach ($questions as $q) {
            $examType = $q['exam_type'] ?? 'CGL';
            $topic = $q['topic'] ?? '';
            $questionText = $q['question_text'] ?? '';
            $optionA = $q['option_a'] ?? '';
            $optionB = $q['option_b'] ?? '';
            $optionC = $q['option_c'] ?? '';
            $optionD = $q['option_d'] ?? '';
            $correctAnswer = strtoupper($q['correct_answer'] ?? 'A');
            $explanation = $q['explanation'] ?? '';
            $difficulty = $q['difficulty'] ?? 'medium';
            $marks = floatval($q['marks'] ?? 1.00);
            
            if (empty($questionText)) {
                $errors[] = "Skipped question due to missing question text";
                continue;
            }
            
            $stmt = $conn->prepare("INSERT INTO questions 
                (exam_type, topic, question_text, option_a, option_b, option_c, option_d, correct_answer, explanation, difficulty, marks) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $stmt->bind_param("ssisssssssd", 
                $examType, $topic, $questionText, $optionA, $optionB, $optionC, $optionD, 
                $correctAnswer, $explanation, $difficulty, $marks);
            
            if ($stmt->execute()) {
                $imported++;
            }
            $stmt->close();
        }
    } elseif ($extension === 'csv') {
        if (($handle = fopen($file['tmp_name'], 'r')) !== FALSE) {
            $headers = fgetcsv($handle);
            
            while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                $row = array_combine($headers, $data);
                
                $stmt = $conn->prepare("INSERT INTO questions 
                    (exam_type, topic, question_text, option_a, option_b, option_c, option_d, correct_answer, explanation, difficulty, marks) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
                $stmt->bind_param("ssisssssssd", 
                    $row['exam_type'], $row['topic'], $row['question_text'], 
                    $row['option_a'], $row['option_b'], $row['option_c'], $row['option_d'],
                    strtoupper($row['correct_answer']), $row['explanation'], $row['difficulty'], 
                    floatval($row['marks'] ?? 1.00));
                
                if ($stmt->execute()) {
                    $imported++;
                }
                $stmt->close();
            }
            fclose($handle);
        }
    } else {
        echo json_encode(['error' => 'Unsupported file format. Use JSON or CSV', 'code' => 'VALIDATION_ERROR']);
        return;
    }
    
    logAdminAction('IMPORT_QUESTIONS', 'questions', null, null, "Imported: $imported questions");
    echo json_encode(['success' => true, 'imported' => $imported, 'errors' => $errors]);
}

// ================== AUDIT LOGS ==================

function handleGetAuditLogs() {
    global $conn;
    
    $limit = intval($_GET['limit'] ?? 50);
    $offset = intval($_GET['offset'] ?? 0);
    
    // Check if table exists
    $result = $conn->query("SHOW TABLES LIKE 'audit_logs'");
    if ($result->num_rows === 0) {
        echo json_encode(['success' => true, 'logs' => []]);
        return;
    }
    
    $sql = "SELECT al.*, au.username as admin_username 
            FROM audit_logs al 
            LEFT JOIN admin_users au ON al.user_id = au.id 
            ORDER BY al.created_at DESC 
            LIMIT $limit OFFSET $offset";
    
    $result = $conn->query($sql);
    $logs = [];
    
    while ($row = $result->fetch_assoc()) {
        $logs[] = $row;
    }
    
    echo json_encode(['success' => true, 'logs' => $logs]);
}

// ================== QUIZ RESULTS ==================

function handleGetQuizResults() {
    global $conn;
    
    $limit = intval($_GET['limit'] ?? 50);
    $offset = intval($_GET['offset'] ?? 0);
    
    // Check if table exists
    $result = $conn->query("SHOW TABLES LIKE 'quiz_results'");
    if ($result->num_rows === 0) {
        echo json_encode(['success' => true, 'results' => [], 'total' => 0]);
        return;
    }
    
    $sql = "SELECT * FROM quiz_results ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
    $result = $conn->query($sql);
    $results = [];
    
    while ($row = $result->fetch_assoc()) {
        $results[] = $row;
    }
    
    $total = $conn->query("SELECT COUNT(*) as total FROM quiz_results")->fetch_assoc()['total'];
    
    echo json_encode(['success' => true, 'results' => $results, 'total' => $total]);
}

// Helper function to log admin actions
function logAdminAction($action, $table, $recordId, $oldValue, $newValue) {
    global $conn;
    
    $adminId = getCurrentAdminId();
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    
    // Check if audit_logs table exists
    $result = $conn->query("SHOW TABLES LIKE 'audit_logs'");
    if ($result->num_rows === 0) {
        return; // Table doesn't exist yet
    }
    
    $stmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, table_name, record_id, old_value, new_value, ip_address, user_agent) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ississss", $adminId, $action, $table, $recordId, $oldValue, $newValue, $ip, $agent);
    $stmt->execute();
    $stmt->close();
}

$conn->close();
