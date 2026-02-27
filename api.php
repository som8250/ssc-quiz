<?php
header('Content-Type: application/json');
require_once 'config.php';

// Sanitize input function
function sanitizeInput($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'get_topics':
            getTopics();
            break;
        case 'get_questions':
            getQuestions();
            break;
        case 'get_exam_types':
            getExamTypes();
            break;
        case 'get_results':
            getResults();
            break;
        default:
            echo json_encode(['error' => 'Invalid action']);
    }
} elseif ($method === 'POST') {
    // Try to get action from GET, POST, or JSON body
    $action = $_REQUEST['action'] ?? '';
    
    // If no action in request, check JSON body
    if (empty($action)) {
        $jsonData = json_decode(file_get_contents('php://input'), true);
        $action = $jsonData['action'] ?? '';
    }
    
    switch ($action) {
        case 'save_result':
            saveResult();
            break;
        case 'get_results':
            getResults();
            break;
        case 'save_user_result':
            saveUserResult();
            break;
        case 'get_user_history':
            getUserHistory();
            break;
        case 'get_performance':
            getPerformanceAnalytics();
            break;
        case 'get_leaderboard':
            getLeaderboard();
            break;
        case 'register_user':
            registerUser();
            break;
        case 'user_login':
            userLogin();
            break;
        case 'logout':
            userLogout();
            break;
        case 'toggle_bookmark':
            toggleBookmark();
            break;
        case 'get_bookmarks':
            getBookmarks();
            break;
        default:
            echo json_encode(['error' => 'Invalid action']);
    }
}

// Get available topics
function getTopics() {
    global $conn;
    $exam_type = $_GET['exam_type'] ?? '';
    
    // Use prepared statement to prevent SQL injection
    if (!empty($exam_type) && in_array($exam_type, ['CGL', 'CHSL', 'MTS', 'CPO', 'GD', 'NTPC_Graduate', 'NTPC_UG', 'Group_D'])) {
        $stmt = $conn->prepare("SELECT DISTINCT topic FROM questions WHERE exam_type = ? ORDER BY topic");
        $stmt->bind_param("s", $exam_type);
    } else {
        $stmt = $conn->prepare("SELECT DISTINCT topic FROM questions ORDER BY topic");
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $topics = [];
    
    while ($row = $result->fetch_assoc()) {
        $topics[] = $row['topic'];
    }
    
    $stmt->close();
    echo json_encode(['topics' => $topics]);
}

// Get available exam types
function getExamTypes() {
    global $conn;
    
    $sql = "SELECT DISTINCT exam_type FROM questions ORDER BY exam_type";
    $result = $conn->query($sql);
    $exam_types = [];
    
    while ($row = $result->fetch_assoc()) {
        $exam_types[] = $row['exam_type'];
    }
    
    echo json_encode(['exam_types' => $exam_types]);
}

// Get questions for quiz
function getQuestions() {
    global $conn;
    
    $exam_type = $_GET['exam_type'] ?? '';
    $topic = $_GET['topic'] ?? '';
    $limit = QUESTIONS_PER_QUIZ;
    $practice_mode = isset($_GET['practice_mode']) && $_GET['practice_mode'] === 'true';
    
    // Validate and sanitize inputs
    $valid_exam_types = ['CGL', 'CHSL', 'CPO', 'GD', 'MTS', 'NTPC_Graduate', 'NTPC_UG', 'Group_D', 'all'];
    if (!empty($exam_type) && !in_array($exam_type, $valid_exam_types)) {
        $exam_type = '';
    }
    $topic = sanitizeInput($topic);
    
    // Use prepared statement to prevent SQL injection
    $sql = "SELECT id, question_text, option_a, option_b, option_c, option_d, 
            correct_answer, explanation, difficulty, topic, exam_type FROM questions WHERE 1=1";
    
    $params = [];
    $types = "";
    
    if (!empty($exam_type) && $exam_type !== 'all') {
        $sql .= " AND exam_type = ?";
        $params[] = $exam_type;
        $types .= "s";
    }
    
    if (!empty($topic)) {
        $sql .= " AND topic = ?";
        $params[] = $topic;
        $types .= "s";
    }
    
    // In practice mode, get more questions (50) for practice
    if ($practice_mode) {
        $limit = 50;
    }
    
    $sql .= " ORDER BY RAND() LIMIT ?";
    $params[] = $limit;
    $types .= "i";
    
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $questions = [];
    
    while ($row = $result->fetch_assoc()) {
        // Shuffle options for each question
        $options = [
            'A' => $row['option_a'],
            'B' => $row['option_b'],
            'C' => $row['option_c'],
            'D' => $row['option_d']
        ];
        
        $questions[] = [
            'id' => $row['id'],
            'question' => $row['question_text'],
            'options' => $options,
            'correctAnswer' => $practice_mode ? $row['correct_answer'] : '', // Hide answer in quiz mode
            'explanation' => $practice_mode ? $row['explanation'] : '',
            'difficulty' => $row['difficulty'],
            'topic' => $row['topic'],
            'exam_type' => $row['exam_type']
        ];
    }
    
    $stmt->close();
    
    echo json_encode([
        'questions' => $questions,
        'total' => count($questions),
        'time_limit' => $practice_mode ? 0 : (count($questions) * TIME_PER_QUESTION),
        'practice_mode' => $practice_mode
    ]);
}

// Save quiz result
function saveResult() {
    global $conn;
    
    // Try JSON body first, then fall back to form data
    $jsonData = json_decode(file_get_contents('php://input'), true);
    if (!empty($jsonData)) {
        $user_name = sanitizeInput($jsonData['user_name'] ?? 'Anonymous');
        $exam_type = sanitizeInput($jsonData['exam_type'] ?? '');
        $topic = sanitizeInput($jsonData['topic'] ?? '');
        $total_questions = intval($jsonData['total_questions'] ?? 0);
        $correct_answers = intval($jsonData['correct_answers'] ?? 0);
        $wrong_answers = intval($jsonData['wrong_answers'] ?? 0);
        $time_taken = intval($jsonData['time_taken'] ?? 0);
        $score = floatval($jsonData['score'] ?? 0);
    } else {
        $user_name = sanitizeInput($_REQUEST['user_name'] ?? 'Anonymous');
        $exam_type = sanitizeInput($_REQUEST['exam_type'] ?? '');
        $topic = sanitizeInput($_REQUEST['topic'] ?? '');
        $total_questions = intval($_REQUEST['total_questions'] ?? 0);
        $correct_answers = intval($_REQUEST['correct_answers'] ?? 0);
        $wrong_answers = intval($_REQUEST['wrong_answers'] ?? 0);
        $time_taken = intval($_REQUEST['time_taken'] ?? 0);
        $score = floatval($_REQUEST['score'] ?? 0);
    }
    
    $stmt = $conn->prepare("INSERT INTO quiz_results 
        (user_name, exam_type, topic, total_questions, correct_answers, wrong_answers, time_taken, score) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->bind_param("sssiisid", 
        $user_name, $exam_type, $topic, $total_questions, 
        $correct_answers, $wrong_answers, $time_taken, $score);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'result_id' => $stmt->insert_id]);
    } else {
        echo json_encode(['error' => 'Failed to save result']);
    }
    
    $stmt->close();
}

// Get quiz results
function getResults() {
    global $conn;
    
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
    
    // Validate limit
    if ($limit < 1) $limit = 1;
    if ($limit > 100) $limit = 100;
    
    $stmt = $conn->prepare("SELECT * FROM quiz_results ORDER BY created_at DESC LIMIT ?");
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $results = [];
    
    while ($row = $result->fetch_assoc()) {
        $results[] = $row;
    }
    
    $stmt->close();
    echo json_encode(['results' => $results]);
}

// Register new user
function registerUser() {
    global $conn;
    
    // Try JSON body first, then fall back to form data
    $jsonData = json_decode(file_get_contents('php://input'), true);
    if (!empty($jsonData)) {
        $username = sanitizeInput($jsonData['username'] ?? '');
        $email = filter_var($jsonData['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $password = $jsonData['password'] ?? '';
        $full_name = sanitizeInput($jsonData['full_name'] ?? '');
    } else {
        $username = sanitizeInput($_REQUEST['username'] ?? '');
        $email = filter_var($_REQUEST['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $password = $_REQUEST['password'] ?? '';
        $full_name = sanitizeInput($_REQUEST['full_name'] ?? '');
    }
    
    // Validation
    if (empty($username) || strlen($username) < 3) {
        echo json_encode(['success' => false, 'error' => 'Username must be at least 3 characters']);
        return;
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'error' => 'Valid email is required']);
        return;
    }
    
    if (empty($password) || strlen($password) < 6) {
        echo json_encode(['success' => false, 'error' => 'Password must be at least 6 characters']);
        return;
    }
    
    // Check if username or email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $stmt->close();
        echo json_encode(['success' => false, 'error' => 'Username or email already exists']);
        return;
    }
    $stmt->close();
    
    // Hash password
    $password_hash = password_hash($password, PASSWORD_BCRYPT);
    
    // Insert user
    $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash, full_name) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $email, $password_hash, $full_name);
    
    if ($stmt->execute()) {
        $user_id = $stmt->insert_id;
        $stmt->close();
        echo json_encode(['success' => true, 'user_id' => $user_id, 'message' => 'Registration successful!']);
    } else {
        $stmt->close();
        echo json_encode(['success' => false, 'error' => 'Registration failed']);
    }
}

// User login
function userLogin() {
    global $conn;
    
    // Try JSON body first, then fall back to form data
    $jsonData = json_decode(file_get_contents('php://input'), true);
    if (!empty($jsonData)) {
        $username = sanitizeInput($jsonData['username'] ?? '');
        $password = $jsonData['password'] ?? '';
    } else {
        $username = sanitizeInput($_REQUEST['username'] ?? '');
        $password = $_REQUEST['password'] ?? '';
    }
    
    if (empty($username) || empty($password)) {
        echo json_encode(['success' => false, 'error' => 'Username and password are required']);
        return;
    }
    
    $stmt = $conn->prepare("SELECT id, username, email, password_hash, full_name FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        echo json_encode(['success' => false, 'error' => 'Invalid credentials']);
        return;
    }
    
    $user = $result->fetch_assoc();
    $stmt->close();
    
    if (password_verify($password, $user['password_hash'])) {
        // Generate session token
        $token = bin2hex(random_bytes(32));
        
        // Store session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_username'] = $user['username'];
        $_SESSION['user_token'] = $token;
        
        echo json_encode([
            'success' => true, 
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'full_name' => $user['full_name']
            ],
            'token' => $token
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid credentials']);
    }
}

// User logout
function userLogout() {
    session_unset();
    session_destroy();
    echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
}

// Toggle bookmark
function toggleBookmark() {
    global $conn;
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'error' => 'Please login to bookmark questions', 'code' => 'NOT_LOGGED_IN']);
        return;
    }
    
    // Try JSON body first, then fall back to form data
    $jsonData = json_decode(file_get_contents('php://input'), true);
    if (!empty($jsonData)) {
        $question_id = intval($jsonData['question_id'] ?? 0);
    } else {
        $question_id = intval($_REQUEST['question_id'] ?? 0);
    }
    $user_id = $_SESSION['user_id'];
    
    if ($question_id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid question ID']);
        return;
    }
    
    // Check if bookmark exists
    $stmt = $conn->prepare("SELECT id FROM bookmarks WHERE user_id = ? AND question_id = ?");
    $stmt->bind_param("ii", $user_id, $question_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Remove bookmark
        $stmt->close();
        $stmt = $conn->prepare("DELETE FROM bookmarks WHERE user_id = ? AND question_id = ?");
        $stmt->bind_param("ii", $user_id, $question_id);
        $stmt->execute();
        $stmt->close();
        echo json_encode(['success' => true, 'bookmarked' => false, 'message' => 'Bookmark removed']);
    } else {
        // Add bookmark
        $stmt->close();
        $stmt = $conn->prepare("INSERT INTO bookmarks (user_id, question_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $user_id, $question_id);
        $stmt->execute();
        $stmt->close();
        echo json_encode(['success' => true, 'bookmarked' => true, 'message' => 'Question bookmarked']);
    }
}

// Get user bookmarks
function getBookmarks() {
    global $conn;
    
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'error' => 'Please login', 'code' => 'NOT_LOGGED_IN']);
        return;
    }
    
    $user_id = $_SESSION['user_id'];
    
    $stmt = $conn->prepare("SELECT q.id, q.question_text, q.topic, q.exam_type, q.difficulty, b.created_at 
                             FROM bookmarks b 
                             JOIN questions q ON b.question_id = q.id 
                             WHERE b.user_id = ? 
                             ORDER BY b.created_at DESC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $bookmarks = [];
    while ($row = $result->fetch_assoc()) {
        $bookmarks[] = $row;
    }
    
    $stmt->close();
    echo json_encode(['success' => true, 'bookmarks' => $bookmarks]);
}

// Verify answer (for practice mode)
if (isset($_GET['action']) && $_GET['action'] === 'verify_answer') {
    $question_id = intval($_GET['question_id'] ?? 0);
    $answer = sanitizeInput($_GET['answer'] ?? '');
    
    if ($question_id <= 0) {
        echo json_encode(['error' => 'Invalid question ID']);
        exit;
    }
    
    $stmt = $conn->prepare("SELECT correct_answer, explanation FROM questions WHERE id = ?");
    $stmt->bind_param("i", $question_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        echo json_encode([
            'correct' => ($answer === $row['correct_answer']),
            'correct_answer' => $row['correct_answer'],
            'explanation' => $row['explanation']
        ]);
    } else {
        echo json_encode(['error' => 'Question not found']);
    }
    
    $stmt->close();
}

// Save user quiz result (with user_id)
function saveUserResult() {
    global $conn;
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'error' => 'Please login to save your progress', 'code' => 'NOT_LOGGED_IN']);
        return;
    }
    
    // Try JSON body first, then fall back to form data
    $jsonData = json_decode(file_get_contents('php://input'), true);
    if (!empty($jsonData)) {
        $user_name = sanitizeInput($jsonData['user_name'] ?? 'Anonymous');
        $exam_type = sanitizeInput($jsonData['exam_type'] ?? '');
        $section = sanitizeInput($jsonData['section'] ?? 'SSC');
        $topic = sanitizeInput($jsonData['topic'] ?? '');
        $total_questions = intval($jsonData['total_questions'] ?? 0);
        $correct_answers = intval($jsonData['correct_answers'] ?? 0);
        $wrong_answers = intval($jsonData['wrong_answers'] ?? 0);
        $skipped_answers = intval($jsonData['skipped_answers'] ?? 0);
        $time_taken = intval($jsonData['time_taken'] ?? 0);
        $score = floatval($jsonData['score'] ?? 0);
        $practice_mode = isset($jsonData['practice_mode']) && $jsonData['practice_mode'] === true;
    } else {
        $user_name = sanitizeInput($_REQUEST['user_name'] ?? 'Anonymous');
        $exam_type = sanitizeInput($_REQUEST['exam_type'] ?? '');
        $section = sanitizeInput($_REQUEST['section'] ?? 'SSC');
        $topic = sanitizeInput($_REQUEST['topic'] ?? '');
        $total_questions = intval($_REQUEST['total_questions'] ?? 0);
        $correct_answers = intval($_REQUEST['correct_answers'] ?? 0);
        $wrong_answers = intval($_REQUEST['wrong_answers'] ?? 0);
        $skipped_answers = intval($_REQUEST['skipped_answers'] ?? 0);
        $time_taken = intval($_REQUEST['time_taken'] ?? 0);
        $score = floatval($_REQUEST['score'] ?? 0);
        $practice_mode = isset($_REQUEST['practice_mode']) && $_REQUEST['practice_mode'] === 'true';
    }
    
    $user_id = $_SESSION['user_id'];
    $session_id = session_id();
    
    // Save to user_quiz_history
    $stmt = $conn->prepare("INSERT INTO user_quiz_history 
        (user_id, session_id, exam_type, section, topic, total_questions, correct_answers, wrong_answers, skipped_answers, time_taken, score, practice_mode) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->bind_param("isssiiiiiddi", 
        $user_id, $session_id, $exam_type, $section, $topic, $total_questions, 
        $correct_answers, $wrong_answers, $skipped_answers, $time_taken, $score, $practice_mode);
    
    if ($stmt->execute()) {
        $result_id = $stmt->insert_id;
        $stmt->close();
        
        // Also save to general quiz_results for public leaderboard
        $stmt = $conn->prepare("INSERT INTO quiz_results 
            (user_name, exam_type, section, topic, total_questions, correct_answers, wrong_answers, time_taken, score, session_id, user_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssiisidsii", 
            $user_name, $exam_type, $section, $topic, $total_questions, 
            $correct_answers, $wrong_answers, $time_taken, $score, $session_id, $user_id);
        $stmt->execute();
        $stmt->close();
        
        echo json_encode(['success' => true, 'result_id' => $result_id]);
    } else {
        echo json_encode(['error' => 'Failed to save result']);
    }
}

// Get user quiz history
function getUserHistory() {
    global $conn;
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'error' => 'Please login to view history', 'code' => 'NOT_LOGGED_IN']);
        return;
    }
    
    $user_id = $_SESSION['user_id'];
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 20;
    if ($limit < 1) $limit = 1;
    if ($limit > 100) $limit = 100;
    
    $stmt = $conn->prepare("SELECT * FROM user_quiz_history WHERE user_id = ? ORDER BY created_at DESC LIMIT ?");
    $stmt->bind_param("ii", $user_id, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $history = [];
    while ($row = $result->fetch_assoc()) {
        $history[] = $row;
    }
    
    $stmt->close();
    
    // Get statistics
    $stats_stmt = $conn->prepare("SELECT 
        COUNT(*) as total_quizzes,
        SUM(correct_answers) as total_correct,
        SUM(total_questions) as total_questions,
        AVG(score) as avg_score,
        MAX(score) as best_score
        FROM user_quiz_history WHERE user_id = ?");
    $stats_stmt->bind_param("i", $user_id);
    $stats_stmt->execute();
    $stats_result = $stats_stmt->get_result();
    $stats = $stats_result->fetch_assoc();
    $stats_stmt->close();
    
    echo json_encode([
        'success' => true,
        'history' => $history,
        'stats' => $stats
    ]);
}

// Get performance analytics (by topic)
function getPerformanceAnalytics() {
    global $conn;
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'error' => 'Please login to view analytics', 'code' => 'NOT_LOGGED_IN']);
        return;
    }
    
    $user_id = $_SESSION['user_id'];
    
    // Get performance by topic
    $stmt = $conn->prepare("SELECT 
        topic,
        COUNT(*) as quiz_count,
        SUM(correct_answers) as correct,
        SUM(wrong_answers) as wrong,
        SUM(total_questions) as total,
        AVG(score) as avg_score
        FROM user_quiz_history 
        WHERE user_id = ? AND topic IS NOT NULL AND topic != ''
        GROUP BY topic
        ORDER BY avg_score ASC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $by_topic = [];
    while ($row = $result->fetch_assoc()) {
        $row['accuracy'] = $row['total'] > 0 ? round(($row['correct'] / $row['total']) * 100, 1) : 0;
        $by_topic[] = $row;
    }
    $stmt->close();
    
    // Get performance by exam type
    $stmt = $conn->prepare("SELECT 
        exam_type,
        COUNT(*) as quiz_count,
        SUM(correct_answers) as correct,
        SUM(wrong_answers) as wrong,
        SUM(total_questions) as total,
        AVG(score) as avg_score
        FROM user_quiz_history 
        WHERE user_id = ? AND exam_type IS NOT NULL
        GROUP BY exam_type
        ORDER BY avg_score DESC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $by_exam = [];
    while ($row = $result->fetch_assoc()) {
        $row['accuracy'] = $row['total'] > 0 ? round(($row['correct'] / $row['total']) * 100, 1) : 0;
        $by_exam[] = $row;
    }
    $stmt->close();
    
    // Get performance by section (SSC/Railway)
    $section_stmt = $conn->prepare("SELECT 
        section,
        COUNT(*) as quiz_count,
        SUM(correct_answers) as correct,
        SUM(wrong_answers) as wrong,
        SUM(total_questions) as total,
        AVG(score) as avg_score
        FROM user_quiz_history 
        WHERE user_id = ? AND section IS NOT NULL
        GROUP BY section
        ORDER BY avg_score DESC");
    $section_stmt->bind_param("i", $user_id);
    $section_stmt->execute();
    $section_result = $section_stmt->get_result();
    
    $by_section = [];
    while ($row = $section_result->fetch_assoc()) {
        $row['accuracy'] = $row['total'] > 0 ? round(($row['correct'] / $row['total']) * 100, 1) : 0;
        $by_section[$row['section']] = $row;
    }
    $section_stmt->close();
    
    // Get overall stats
    $stats_stmt = $conn->prepare("SELECT 
        COUNT(*) as total_quizzes,
        SUM(correct_answers) as total_correct,
        SUM(total_questions) as total_questions,
        SUM(time_taken) as total_time,
        AVG(score) as avg_score,
        MAX(score) as best_score,
        MIN(score) as lowest_score
        FROM user_quiz_history WHERE user_id = ?");
    $stats_stmt->bind_param("i", $user_id);
    $stats_stmt->execute();
    $stats_result = $stats_stmt->get_result();
    $overall = $stats_result->fetch_assoc();
    $overall['accuracy'] = $overall['total_questions'] > 0 ? round(($overall['total_correct'] / $overall['total_questions']) * 100, 1) : 0;
    $stats_stmt->close();
    
    // Get weak areas (topics with less than 60% accuracy)
    $weak_areas = array_filter($by_topic, function($t) {
        return $t['accuracy'] < 60;
    });
    
    // Get strong areas (topics with more than 80% accuracy)
    $strong_areas = array_filter($by_topic, function($t) {
        return $t['accuracy'] >= 80;
    });
    
    echo json_encode([
        'success' => true,
        'by_topic' => $by_topic,
        'by_exam_type' => $by_exam,
        'by_section' => $by_section,
        'overall' => $overall,
        'weak_areas' => array_values($weak_areas),
        'strong_areas' => array_values($strong_areas)
    ]);
}

// Get leaderboard
function getLeaderboard() {
    global $conn;
    
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
    if ($limit < 1) $limit = 1;
    if ($limit > 50) $limit = 50;
    
    // Get top users by average score (minimum 3 quizzes)
    $stmt = $conn->prepare("SELECT 
        u.id,
        u.username,
        u.full_name,
        COUNT(h.id) as quiz_count,
        AVG(h.score) as avg_score,
        MAX(h.score) as best_score,
        SUM(h.correct_answers) as total_correct,
        SUM(h.total_questions) as total_questions
        FROM users u
        JOIN user_quiz_history h ON u.id = h.user_id
        GROUP BY u.id, u.username, u.full_name
        HAVING COUNT(h.id) >= 3
        ORDER BY avg_score DESC, quiz_count DESC
        LIMIT ?");
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $leaderboard = [];
    $rank = 1;
    while ($row = $result->fetch_assoc()) {
        $row['rank'] = $rank++;
        $row['accuracy'] = $row['total_questions'] > 0 ? round(($row['total_correct'] / $row['total_questions']) * 100, 1) : 0;
        $leaderboard[] = $row;
    }
    $stmt->close();
    
    // Get current user's rank if logged in
    $user_rank = null;
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $user_stmt = $conn->prepare("SELECT 
            u.id,
            u.username,
            COUNT(h.id) as quiz_count,
            AVG(h.score) as avg_score
            FROM users u
            JOIN user_quiz_history h ON u.id = h.user_id
            WHERE u.id = ?
            GROUP BY u.id, u.username");
        $user_stmt->bind_param("i", $user_id);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result();
        
        if ($user_row = $user_result->fetch_assoc()) {
            // Calculate rank
            $rank_stmt = $conn->prepare("SELECT COUNT(*) as rank FROM (
                SELECT u.id, AVG(h.score) as avg_score
                FROM users u
                JOIN user_quiz_history h ON u.id = h.user_id
                GROUP BY u.id
                HAVING COUNT(h.id) >= 3
            ) sub WHERE avg_score > ?");
            $rank_stmt->bind_param("d", $user_row['avg_score']);
            $rank_stmt->execute();
            $rank_result = $rank_stmt->get_result();
            $rank_data = $rank_result->fetch_assoc();
            $user_rank = intval($rank_data['rank']) + 1;
            $rank_stmt->close();
            
            $user_row['rank'] = $user_rank;
            $user_rank = $user_row;
        }
        $user_stmt->close();
    }
    
    echo json_encode([
        'success' => true,
        'leaderboard' => $leaderboard,
        'user_rank' => $user_rank
    ]);
}

$conn->close();
