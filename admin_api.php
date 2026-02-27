<?php
header('Content-Type: application/json');
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'add_question':
            addQuestion();
            break;
        case 'get_questions':
            getQuestions();
            break;
        case 'get_all_results':
            getAllResults();
            break;
        default:
            echo json_encode(['error' => 'Invalid action']);
    }
} elseif ($method === 'POST') {
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'add_question':
            addQuestion();
            break;
        default:
            echo json_encode(['error' => 'Invalid action']);
    }
}

// Add Question
function addQuestion() {
    global $conn;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $exam_type = $data['exam_type'] ?? '';
    $topic = $data['topic'] ?? '';
    $question_text = $data['question_text'] ?? '';
    $option_a = $data['option_a'] ?? '';
    $option_b = $data['option_b'] ?? '';
    $option_c = $data['option_c'] ?? '';
    $option_d = $data['option_d'] ?? '';
    $correct_answer = $data['correct_answer'] ?? '';
    $difficulty = $data['difficulty'] ?? 'medium';
    $explanation = $data['explanation'] ?? '';
    
    $stmt = $conn->prepare("INSERT INTO questions 
        (exam_type, topic, question_text, option_a, option_b, option_c, option_d, correct_answer, difficulty, explanation) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->bind_param("ssssssisss", 
        $exam_type, $topic, $question_text, $option_a, $option_b, 
        $option_c, $option_d, $correct_answer, $difficulty, $explanation);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'id' => $stmt->insert_id]);
    } else {
        echo json_encode(['error' => $stmt->error]);
    }
    
    $stmt->close();
}

// Get All Questions
function getQuestions() {
    global $conn;
    
    $limit = $_GET['limit'] ?? 50;
    $sql = "SELECT * FROM questions ORDER BY id DESC LIMIT $limit";
    $result = $conn->query($sql);
    $questions = [];
    
    while ($row = $result->fetch_assoc()) {
        $questions[] = $row;
    }
    
    echo json_encode(['questions' => $questions, 'total' => count($questions)]);
}

// Get All Results
function getAllResults() {
    global $conn;
    
    $limit = $_GET['limit'] ?? 100;
    $sql = "SELECT * FROM quiz_results ORDER BY created_at DESC LIMIT $limit";
    $result = $conn->query($sql);
    $results = [];
    
    while ($row = $result->fetch_assoc()) {
        $results[] = $row;
    }
    
    echo json_encode(['results' => $results, 'total' => count($results)]);
}

$conn->close();
