<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SSC Quiz Admin - Secure Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary: #4f46e5;
            --secondary: #64748b;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --info: #0ea5e9;
        }
        
        body {
            background: #f3f4f6;
            font-family: 'Segoe UI', system-ui, sans-serif;
        }
        
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .login-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            padding: 48px;
            width: 100%;
            max-width: 420px;
        }
        
        .sidebar {
            min-height: 100vh;
            background: white;
            box-shadow: 2px 0 8px rgba(0,0,0,0.05);
        }
        
        .sidebar .nav-link {
            color: #374151;
            padding: 12px 20px;
            border-radius: 8px;
            margin: 4px 8px;
            font-weight: 500;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: #eef2ff;
            color: var(--primary);
        }
        
        .sidebar .nav-link i {
            margin-right: 12px;
        }
        
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
        
        .table-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .table thead th {
            background: #f9fafb;
            border-bottom: 2px solid #e5e7eb;
            font-weight: 600;
            color: #374151;
            padding: 16px;
        }
        
        .table td {
            padding: 16px;
            vertical-align: middle;
        }
        
        .badge-active {
            background: #d1fae5;
            color: #065f46;
        }
        
        .badge-inactive {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .btn-action {
            padding: 6px 12px;
            font-size: 14px;
        }
        
        .modal-header {
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .modal-footer {
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
        }
        
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }
        
        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-radius: 50%;
            border-top: 3px solid var(--primary);
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .form-label {
            font-weight: 500;
            color: #374151;
            margin-bottom: 6px;
        }
        
        .required::after {
            content: ' *';
            color: var(--danger);
        }
        
        .admin-header {
            background: white;
            border-bottom: 1px solid #e5e7eb;
            padding: 16px 24px;
        }
        
        .dropdown-menu {
            border: none;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            border-radius: 12px;
        }
        
        .dropdown-item {
            padding: 10px 20px;
            border-radius: 8px;
            margin: 2px 8px;
        }
        
        .dropdown-item:hover {
            background: #f3f4f6;
        }
    </style>
</head>
<body>
    <!-- Login Page -->
    <div id="loginPage" class="login-container">
        <div class="login-card">
            <div class="text-center mb-4">
                <i class="bi bi-shield-lock" style="font-size: 48px; color: var(--primary);"></i>
                <h2 class="mt-3">SSC Quiz Admin</h2>
                <p class="text-muted">Sign in to manage your quiz</p>
            </div>
            
            <div id="loginError" class="alert alert-danger d-none"></div>
            
            <form id="loginForm">
                <div class="mb-3">
                    <label class="form-label required">Username or Email</label>
                    <input type="text" class="form-control" id="loginUsername" required placeholder="Enter username or email">
                </div>
                
                <div class="mb-3">
                    <label class="form-label required">Password</label>
                    <input type="password" class="form-control" id="loginPassword" required placeholder="Enter password">
                </div>
                
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="rememberMe">
                    <label class="form-check-label" for="rememberMe">Remember me</label>
                </div>
                
                <button type="submit" class="btn btn-primary w-100 py-2" id="loginBtn">
                    <span id="loginBtnText">Sign In</span>
                    <span id="loginBtnSpinner" class="loading-spinner d-none"></span>
                </button>
            </form>
            
            <div class="text-center mt-4">
                <a href="index.html" class="text-muted text-decoration-none">
                    <i class="bi bi-arrow-left"></i> Back to Quiz
                </a>
            </div>
        </div>
    </div>

    <!-- Admin Dashboard -->
    <div id="adminPage" class="d-none">
        <div class="container-fluid">
            <div class="row">
                <!-- Sidebar -->
                <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse show">
                    <div class="position-sticky pt-3">
                        <div class="px-3 mb-4">
                            <h5 class="text-primary">
                                <i class="bi bi-shield-check"></i> SSC Quiz Admin
                            </h5>
                        </div>
                        
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link active" href="#" data-page="dashboard">
                                    <i class="bi bi-speedometer2"></i> Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#" data-page="subjects">
                                    <i class="bi bi-book"></i> Subjects
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#" data-page="topics">
                                    <i class="bi bi-list-ul"></i> Topics
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#" data-page="questions">
                                    <i class="bi bi-question-circle"></i> Questions
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#" data-page="quiz-sets">
                                    <i class="bi bi-collection"></i> Quiz Sets
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#" data-page="results">
                                    <i class="bi bi-bar-chart"></i> Quiz Results
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#" data-page="import-export">
                                    <i class="bi bi-upload-download"></i> Import/Export
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#" data-page="audit-logs">
                                    <i class="bi bi-clock-history"></i> Audit Logs
                                </a>
                            </li>
                        </ul>
                    </div>
                </nav>

                <!-- Main Content -->
                <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                    <!-- Admin Header -->
                    <div class="admin-header d-flex justify-content-between align-items-center">
                        <h4 class="page-title mb-0">Dashboard</h4>
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> <span id="adminName">Admin</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#"><i class="bi bi-person"></i> Profile</a></li>
                                <li><a class="dropdown-item" href="#"><i class="bi bi-gear"></i> Settings</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="#" id="logoutBtn"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                            </ul>
                        </div>
                    </div>

                    <!-- Dashboard Page -->
                    <div id="dashboardPage" class="page-content">
                        <div class="row g-4 mb-4">
                            <div class="col-md-3">
                                <div class="stat-card">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <p class="text-muted mb-1">Total Questions</p>
                                            <h3 class="mb-0" id="statQuestions">0</h3>
                                        </div>
                                        <div class="stat-icon" style="background: #dbeafe; color: #1d4ed8;">
                                            <i class="bi bi-question-circle"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-card">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <p class="text-muted mb-1">Subjects</p>
                                            <h3 class="mb-0" id="statSubjects">0</h3>
                                        </div>
                                        <div class="stat-icon" style="background: #dcfce7; color: #16a34a;">
                                            <i class="bi bi-book"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-card">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <p class="text-muted mb-1">Quiz Sets</p>
                                            <h3 class="mb-0" id="statQuizSets">0</h3>
                                        </div>
                                        <div class="stat-icon" style="background: #e0e7ff; color: #4338ca;">
                                            <i class="bi bi-collection"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-card">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <p class="text-muted mb-1">Total Attempts</p>
                                            <h3 class="mb-0" id="statAttempts">0</h3>
                                        </div>
                                        <div class="stat-icon" style="background: #fef3c7; color: #d97706;">
                                            <i class="bi bi-people"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="table-card">
                                    <div class="card-header bg-white py-3">
                                        <h5 class="mb-0"><i class="bi bi-graph-up"></i> Recent Quiz Attempts</h5>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0" id="recentResultsTable">
                                            <thead>
                                                <tr>
                                                    <th>User</th>
                                                    <th>Exam</th>
                                                    <th>Score</th>
                                                    <th>Date</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="table-card">
                                    <div class="card-header bg-white py-3">
                                        <h5 class="mb-0"><i class="bi bi-pie-chart"></i> Quick Stats</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between mb-1">
                                                <span>Active Questions</span>
                                                <span id="statActiveQuestions">0</span>
                                            </div>
                                            <div class="progress" style="height: 8px;">
                                                <div class="progress-bar bg-success" id="activeQuestionsBar" style="width: 0%"></div>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between mb-1">
                                                <span>Active Quiz Sets</span>
                                                <span id="statActiveQuizSets">0</span>
                                            </div>
                                            <div class="progress" style="height: 8px;">
                                                <div class="progress-bar bg-primary" id="activeQuizSetsBar" style="width: 0%"></div>
                                            </div>
                                        </div>
                                        <div class="mb-0">
                                            <div class="d-flex justify-content-between mb-1">
                                                <span>24hr Attempts</span>
                                                <span id="statRecentAttempts">0</span>
                                            </div>
                                            <div class="progress" style="height: 8px;">
                                                <div class="progress-bar bg-warning" id="recentAttemptsBar" style="width: 0%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Subjects Page -->
                    <div id="subjectsPage" class="page-content d-none">
                        <div class="table-card">
                            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Subjects Management</h5>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#subjectModal">
                                    <i class="bi bi-plus-lg"></i> Add Subject
                                </button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0" id="subjectsTable">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Icon</th>
                                            <th>Name</th>
                                            <th>Topics</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Topics Page -->
                    <div id="topicsPage" class="page-content d-none">
                        <div class="table-card">
                            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Topics Management</h5>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#topicModal">
                                    <i class="bi bi-plus-lg"></i> Add Topic
                                </button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0" id="topicsTable">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Subject</th>
                                            <th>Name</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Questions Page -->
                    <div id="questionsPage" class="page-content d-none">
                        <div class="table-card">
                            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Questions Management</h5>
                                <div>
                                    <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#questionModal">
                                        <i class="bi bi-plus-lg"></i> Add Question
                                    </button>
                                </div>
                            </div>
                            <div class="p-3 bg-light border-bottom">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <select class="form-select" id="filterExamType">
                                            <option value="">All Exams</option>
                                            <option value="CGL">CGL</option>
                                            <option value="CHSL">CHSL</option>
                                            <option value="MTS">MTS</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <select class="form-select" id="filterDifficulty">
                                            <option value="">All Difficulties</option>
                                            <option value="easy">Easy</option>
                                            <option value="medium">Medium</option>
                                            <option value="hard">Hard</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <button class="btn btn-primary" onclick="loadQuestions()">Apply Filters</button>
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0" id="questionsTable">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Question</th>
                                            <th>Exam</th>
                                            <th>Topic</th>
                                            <th>Difficulty</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                            <div class="card-footer bg-white d-flex justify-content-between align-items-center">
                                <span id="questionsCount">Showing 0 questions</span>
                                <nav>
                                    <ul class="pagination mb-0" id="questionsPagination"></ul>
                                </nav>
                            </div>
                        </div>
                    </div>

                    <!-- Quiz Sets Page -->
                    <div id="quizSetsPage" class="page-content d-none">
                        <div class="table-card">
                            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Quiz Sets Management</h5>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#quizSetModal">
                                    <i class="bi bi-plus-lg"></i> Add Quiz Set
                                </button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0" id="quizSetsTable">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Subject</th>
                                            <th>Exam</th>
                                            <th>Time</th>
                                            <th>Questions</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Results Page -->
                    <div id="resultsPage" class="page-content d-none">
                        <div class="table-card">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0">Quiz Results</h5>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0" id="resultsTable">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>User</th>
                                            <th>Exam</th>
                                            <th>Topic</th>
                                            <th>Score</th>
                                            <th>Time</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Import/Export Page -->
                    <div id="importExportPage" class="page-content d-none">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="table-card">
                                    <div class="card-header bg-white py-3">
                                        <h5 class="mb-0"><i class="bi bi-upload"></i> Import Questions</h5>
                                    </div>
                                    <div class="card-body">
                                        <form id="importForm">
                                            <div class="mb-3">
                                                <label class="form-label">Select File (JSON or CSV)</label>
                                                <input type="file" class="form-control" id="importFile" accept=".json,.csv" required>
                                            </div>
                                            <div class="alert alert-info">
                                                <i class="bi bi-info-circle"></i> 
                                                Required columns: exam_type, topic, question_text, option_a, option_b, option_c, option_d, correct_answer
                                            </div>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bi bi-upload"></i> Import
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="table-card">
                                    <div class="card-header bg-white py-3">
                                        <h5 class="mb-0"><i class="bi bi-download"></i> Export Questions</h5>
                                    </div>
                                    <div class="card-body">
                                        <form id="exportForm">
                                            <div class="mb-3">
                                                <label class="form-label">Export Format</label>
                                                <select class="form-select" id="exportFormat">
                                                    <option value="json">JSON</option>
                                                    <option value="csv">CSV</option>
                                                </select>
                                            </div>
                                            <button type="button" class="btn btn-success" onclick="exportQuestions()">
                                                <i class="bi bi-download"></i> Export
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Audit Logs Page -->
                    <div id="auditLogsPage" class="page-content d-none">
                        <div class="table-card">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0"><i class="bi bi-clock-history"></i> Audit Logs</h5>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0" id="auditLogsTable">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Admin</th>
                                            <th>Action</th>
                                            <th>Table</th>
                                            <th>Details</th>
                                            <th>IP Address</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </div>

    <!-- Subject Modal -->
    <div class="modal fade" id="subjectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="subjectModalTitle">Add Subject</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="subjectForm">
                    <div class="modal-body">
                        <input type="hidden" id="subjectId">
                        <div class="mb-3">
                            <label class="form-label required">Name</label>
                            <input type="text" class="form-control" id="subjectName" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" id="subjectDescription" rows="2"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Icon (Emoji)</label>
                            <input type="text" class="form-control" id="subjectIcon" value="📚" maxlength="10">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Display Order</label>
                            <input type="number" class="form-control" id="subjectOrder" value="0">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Topic Modal -->
    <div class="modal fade" id="topicModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="topicModalTitle">Add Topic</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="topicForm">
                    <div class="modal-body">
                        <input type="hidden" id="topicId">
                        <div class="mb-3">
                            <label class="form-label required">Subject</label>
                            <select class="form-select" id="topicSubject" required></select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">Name</label>
                            <input type="text" class="form-control" id="topicName" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" id="topicDescription" rows="2"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Display Order</label>
                            <input type="number" class="form-control" id="topicOrder" value="0">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Question Modal -->
    <div class="modal fade" id="questionModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="questionModalTitle">Add Question</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="questionForm">
                    <div class="modal-body">
                        <input type="hidden" id="questionId">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Exam Type</label>
                                <select class="form-select" id="questionExamType" required>
                                    <option value="CGL">CGL</option>
                                    <option value="CHSL">CHSL</option>
                                    <option value="MTS">MTS</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Topic</label>
                                <select class="form-select" id="questionTopic" required></select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">Question Text</label>
                            <textarea class="form-control" id="questionText" rows="2" required></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Option A</label>
                                <input type="text" class="form-control" id="questionOptionA" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Option B</label>
                                <input type="text" class="form-control" id="questionOptionB" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Option C</label>
                                <input type="text" class="form-control" id="questionOptionC" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Option D</label>
                                <input type="text" class="form-control" id="questionOptionD" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label required">Correct Answer</label>
                                <select class="form-select" id="questionCorrectAnswer" required>
                                    <option value="A">A</option>
                                    <option value="B">B</option>
                                    <option value="C">C</option>
                                    <option value="D">D</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label required">Difficulty</label>
                                <select class="form-select" id="questionDifficulty" required>
                                    <option value="easy">Easy</option>
                                    <option value="medium" selected>Medium</option>
                                    <option value="hard">Hard</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Marks</label>
                                <input type="number" class="form-control" id="questionMarks" value="1" step="0.5">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Explanation</label>
                            <textarea class="form-control" id="questionExplanation" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Quiz Set Modal -->
    <div class="modal fade" id="quizSetModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="quizSetModalTitle">Add Quiz Set</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="quizSetForm">
                    <div class="modal-body">
                        <input type="hidden" id="quizSetId">
                        <div class="mb-3">
                            <label class="form-label required">Name</label>
                            <input type="text" class="form-control" id="quizSetName" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" id="quizSetDescription" rows="2"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Subject</label>
                                <select class="form-select" id="quizSetSubject"></select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Exam Type</label>
                                <select class="form-select" id="quizSetExamType" required>
                                    <option value="ALL">All Exams</option>
                                    <option value="CGL">CGL</option>
                                    <option value="CHSL">CHSL</option>
                                    <option value="MTS">MTS</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label required">Time (minutes)</label>
                                <input type="number" class="form-control" id="quizSetTime" value="20" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label required">Questions</label>
                                <input type="number" class="form-control" id="quizSetQuestions" value="20" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Negative Marking</label>
                                <input type="number" class="form-control" id="quizSetNegative" value="0.25" step="0.05">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="admin_frontend.js"></script>
</body>
</html>
