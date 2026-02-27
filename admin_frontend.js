/**
 * Secure Admin Frontend JavaScript
 * Handles all admin panel functionality
 */

const API_URL = 'secure_admin_api.php';
let currentPage = 'dashboard';
let questionsPage = 1;

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    checkAuth();
    setupEventListeners();
});

// Check authentication
async function checkAuth() {
    try {
        const response = await fetch(`${API_URL}?action=check_auth`);
        const data = await response.json();
        
        if (data.authenticated) {
            showAdminPanel(data.user);
        } else {
            showLoginPanel();
        }
    } catch (error) {
        showLoginPanel();
    }
}

// Setup event listeners
function setupEventListeners() {
    // Login form
    document.getElementById('loginForm').addEventListener('submit', handleLogin);
    
    // Logout
    document.getElementById('logoutBtn').addEventListener('click', handleLogout);
    
    // Navigation
    document.querySelectorAll('.nav-link[data-page]').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            navigateTo(link.dataset.page);
        });
    });
    
    // Forms
    document.getElementById('subjectForm').addEventListener('submit', handleSubjectSubmit);
    document.getElementById('topicForm').addEventListener('submit', handleTopicSubmit);
    document.getElementById('questionForm').addEventListener('submit', handleQuestionSubmit);
    document.getElementById('quizSetForm').addEventListener('submit', handleQuizSetSubmit);
    document.getElementById('importForm').addEventListener('submit', handleImport);
}

// Show login panel
function showLoginPanel() {
    document.getElementById('loginPage').classList.remove('d-none');
    document.getElementById('adminPage').classList.add('d-none');
}

// Show admin panel
function showAdminPanel(user) {
    document.getElementById('loginPage').classList.add('d-none');
    document.getElementById('adminPage').classList.remove('d-none');
    document.getElementById('adminName').textContent = user.full_name || user.username;
    loadDashboard();
}

// Handle login
async function handleLogin(e) {
    e.preventDefault();
    
    const username = document.getElementById('loginUsername').value;
    const password = document.getElementById('loginPassword').value;
    const errorDiv = document.getElementById('loginError');
    
    const loginBtn = document.getElementById('loginBtn');
    const loginBtnText = document.getElementById('loginBtnText');
    const loginBtnSpinner = document.getElementById('loginBtnSpinner');
    
    loginBtn.disabled = true;
    loginBtnText.classList.add('d-none');
    loginBtnSpinner.classList.remove('d-none');
    errorDiv.classList.add('d-none');
    
    try {
        const response = await fetch(`${API_URL}?action=login`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ username, password })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAdminPanel(data.user);
        } else {
            errorDiv.textContent = data.error;
            errorDiv.classList.remove('d-none');
        }
    } catch (error) {
        errorDiv.textContent = 'Connection error. Please try again.';
        errorDiv.classList.remove('d-none');
    }
    
    loginBtn.disabled = false;
    loginBtnText.classList.remove('d-none');
    loginBtnSpinner.classList.add('d-none');
}

// Handle logout
async function handleLogout(e) {
    e.preventDefault();
    
    try {
        await fetch(`${API_URL}?action=logout`);
    } catch (error) {}
    
    showLoginPanel();
    document.getElementById('loginForm').reset();
}

// Navigate to page
async function navigateTo(page) {
    currentPage = page;
    
    // Update nav
    document.querySelectorAll('.nav-link').forEach(link => {
        link.classList.toggle('active', link.dataset.page === page);
    });
    
    // Update page title
    const titles = {
        dashboard: 'Dashboard',
        subjects: 'Subjects Management',
        topics: 'Topics Management',
        questions: 'Questions Management',
        'quiz-sets': 'Quiz Sets Management',
        results: 'Quiz Results',
        'import-export': 'Import / Export',
        'audit-logs': 'Audit Logs'
    };
    document.querySelector('.page-title').textContent = titles[page] || 'Dashboard';
    
    // Show/hide pages
    document.querySelectorAll('.page-content').forEach(p => p.classList.add('d-none'));
    document.getElementById(`${page}Page`).classList.remove('d-none');
    
    // Load data
    switch (page) {
        case 'dashboard':
            loadDashboard();
            break;
        case 'subjects':
            loadSubjects();
            break;
        case 'topics':
            loadTopics();
            loadSubjectsForSelect();
            break;
        case 'questions':
            loadQuestions();
            loadTopicsForSelect();
            break;
        case 'quiz-sets':
            loadQuizSets();
            loadSubjectsForSelect();
            break;
        case 'results':
            loadResults();
            break;
        case 'import-export':
            break;
        case 'audit-logs':
            loadAuditLogs();
            break;
    }
}

// ==================== DASHBOARD ====================

async function loadDashboard() {
    try {
        const response = await fetch(`${API_URL}?action=dashboard_stats`);
        const data = await response.json();
        
        if (data.success) {
            const stats = data.stats;
            
            document.getElementById('statQuestions').textContent = stats.total_questions;
            document.getElementById('statSubjects').textContent = stats.total_subjects;
            document.getElementById('statQuizSets').textContent = stats.total_quiz_sets;
            document.getElementById('statAttempts').textContent = stats.total_attempts;
            
            document.getElementById('statActiveQuestions').textContent = `${stats.active_questions}/${stats.total_questions}`;
            document.getElementById('statActiveQuizSets').textContent = `${stats.active_quiz_sets}/${stats.total_quiz_sets}`;
            document.getElementById('statRecentAttempts').textContent = stats.recent_attempts;
            
            const qPercent = stats.total_questions > 0 ? (stats.active_questions / stats.total_questions * 100) : 0;
            const qsPercent = stats.total_quiz_sets > 0 ? (stats.active_quiz_sets / stats.total_quiz_sets * 100) : 0;
            
            document.getElementById('activeQuestionsBar').style.width = `${qPercent}%`;
            document.getElementById('activeQuizSetsBar').style.width = `${qsPercent}%`;
            document.getElementById('recentAttemptsBar').style.width = '100%';
        }
        
        // Load recent results
        loadRecentResults();
    } catch (error) {
        console.error('Error loading dashboard:', error);
    }
}

async function loadRecentResults() {
    try {
        const response = await fetch(`${API_URL}?action=get_quiz_results&limit=10`);
        const data = await response.json();
        
        if (data.success && data.results) {
            const tbody = document.querySelector('#recentResultsTable tbody');
            tbody.innerHTML = data.results.map(r => `
                <tr>
                    <td>${r.user_name || 'Anonymous'}</td>
                    <td>${r.exam_type}</td>
                    <td><span class="badge ${r.score >= 60 ? 'bg-success' : r.score >= 40 ? 'bg-warning' : 'bg-danger'}">${r.score}%</span></td>
                    <td>${new Date(r.created_at).toLocaleDateString()}</td>
                </tr>
            `).join('');
        }
    } catch (error) {
        console.error('Error loading recent results:', error);
    }
}

// ==================== SUBJECTS ====================

async function loadSubjects() {
    try {
        const response = await fetch(`${API_URL}?action=get_subjects&include_inactive=true`);
        const data = await response.json();
        
        if (data.success && data.subjects) {
            const tbody = document.querySelector('#subjectsTable tbody');
            tbody.innerHTML = data.subjects.map(s => `
                <tr>
                    <td>${s.id}</td>
                    <td>${s.icon || '📚'}</td>
                    <td><strong>${s.name}</strong><br><small class="text-muted">${s.description || ''}</small></td>
                    <td>${s.topic_count || 0}</td>
                    <td>
                        <span class="badge ${s.is_active ? 'badge-active' : 'badge-inactive'}">
                            ${s.is_active ? 'Active' : 'Inactive'}
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary btn-action" onclick="editSubject(${s.id}, '${s.name}', '${s.description || ''}', '${s.icon || ''}', ${s.display_order})">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-success btn-action" onclick="toggleSubject(${s.id})">
                            <i class="bi bi-toggle-on"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger btn-action" onclick="deleteSubject(${s.id})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
        }
    } catch (error) {
        console.error('Error loading subjects:', error);
    }
}

function editSubject(id, name, description, icon, order) {
    document.getElementById('subjectModalTitle').textContent = 'Edit Subject';
    document.getElementById('subjectId').value = id;
    document.getElementById('subjectName').value = name;
    document.getElementById('subjectDescription').value = description;
    document.getElementById('subjectIcon').value = icon;
    document.getElementById('subjectOrder').value = order;
    new bootstrap.Modal(document.getElementById('subjectModal')).show();
}

async function handleSubjectSubmit(e) {
    e.preventDefault();
    
    const id = document.getElementById('subjectId').value;
    const data = {
        name: document.getElementById('subjectName').value,
        description: document.getElementById('subjectDescription').value,
        icon: document.getElementById('subjectIcon').value,
        display_order: parseInt(document.getElementById('subjectOrder').value)
    };
    
    const action = id ? 'update_subject' : 'add_subject';
    if (id) data.id = parseInt(id);
    
    try {
        const response = await fetch(`${API_URL}?action=${action}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('Subject saved successfully', 'success');
            bootstrap.Modal.getInstance(document.getElementById('subjectModal')).hide();
            document.getElementById('subjectForm').reset();
            loadSubjects();
        } else {
            showToast(result.error, 'danger');
        }
    } catch (error) {
        showToast('Error saving subject', 'danger');
    }
}

async function toggleSubject(id) {
    try {
        const response = await fetch(`${API_URL}?action=toggle_subject&id=${id}`);
        const data = await response.json();
        
        if (data.success) {
            showToast(`Subject ${data.is_active ? 'activated' : 'deactivated'}`, 'success');
            loadSubjects();
        }
    } catch (error) {
        showToast('Error toggling subject', 'danger');
    }
}

async function deleteSubject(id) {
    if (!confirm('Are you sure you want to delete this subject? All related topics will also be deleted.')) return;
    
    try {
        const response = await fetch(`${API_URL}?action=delete_subject&id=${id}`);
        const data = await response.json();
        
        if (data.success) {
            showToast('Subject deleted successfully', 'success');
            loadSubjects();
        } else {
            showToast(data.error, 'danger');
        }
    } catch (error) {
        showToast('Error deleting subject', 'danger');
    }
}

// ==================== TOPICS ====================

async function loadTopics() {
    try {
        const response = await fetch(`${API_URL}?action=get_topics&include_inactive=true`);
        const data = await response.json();
        
        if (data.success && data.topics) {
            const tbody = document.querySelector('#topicsTable tbody');
            tbody.innerHTML = data.topics.map(t => `
                <tr>
                    <td>${t.id}</td>
                    <td>${t.subject_name || 'N/A'}</td>
                    <td><strong>${t.name}</strong></td>
                    <td>
                        <span class="badge ${t.is_active ? 'badge-active' : 'badge-inactive'}">
                            ${t.is_active ? 'Active' : 'Inactive'}
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary btn-action" onclick="editTopic(${t.id}, ${t.subject_id}, '${t.name}', '${t.description || ''}', ${t.display_order})">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-success btn-action" onclick="toggleTopic(${t.id})">
                            <i class="bi bi-toggle-on"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger btn-action" onclick="deleteTopic(${t.id})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
        }
    } catch (error) {
        console.error('Error loading topics:', error);
    }
}

async function loadSubjectsForSelect() {
    try {
        const response = await fetch(`${API_URL}?action=get_subjects`);
        const data = await response.json();
        
        if (data.success && data.subjects) {
            const options = data.subjects.map(s => `<option value="${s.id}">${s.name}</option>`).join('');
            
            document.getElementById('topicSubject').innerHTML = options;
            if (document.getElementById('quizSetSubject')) {
                document.getElementById('quizSetSubject').innerHTML = '<option value="">Select Subject</option>' + options;
            }
        }
    } catch (error) {
        console.error('Error loading subjects:', error);
    }
}

function editTopic(id, subjectId, name, description, order) {
    document.getElementById('topicModalTitle').textContent = 'Edit Topic';
    document.getElementById('topicId').value = id;
    document.getElementById('topicSubject').value = subjectId;
    document.getElementById('topicName').value = name;
    document.getElementById('topicDescription').value = description;
    document.getElementById('topicOrder').value = order;
    new bootstrap.Modal(document.getElementById('topicModal')).show();
}

async function handleTopicSubmit(e) {
    e.preventDefault();
    
    const id = document.getElementById('topicId').value;
    const data = {
        subject_id: parseInt(document.getElementById('topicSubject').value),
        name: document.getElementById('topicName').value,
        description: document.getElementById('topicDescription').value,
        display_order: parseInt(document.getElementById('topicOrder').value)
    };
    
    const action = id ? 'update_topic' : 'add_topic';
    if (id) data.id = parseInt(id);
    
    try {
        const response = await fetch(`${API_URL}?action=${action}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('Topic saved successfully', 'success');
            bootstrap.Modal.getInstance(document.getElementById('topicModal')).hide();
            document.getElementById('topicForm').reset();
            loadTopics();
        } else {
            showToast(result.error, 'danger');
        }
    } catch (error) {
        showToast('Error saving topic', 'danger');
    }
}

async function toggleTopic(id) {
    try {
        const response = await fetch(`${API_URL}?action=toggle_topic&id=${id}`);
        const data = await response.json();
        
        if (data.success) {
            showToast(`Topic ${data.is_active ? 'activated' : 'deactivated'}`, 'success');
            loadTopics();
        }
    } catch (error) {
        showToast('Error toggling topic', 'danger');
    }
}

async function deleteTopic(id) {
    if (!confirm('Are you sure you want to delete this topic?')) return;
    
    try {
        const response = await fetch(`${API_URL}?action=delete_topic&id=${id}`);
        const data = await response.json();
        
        if (data.success) {
            showToast('Topic deleted successfully', 'success');
            loadTopics();
        } else {
            showToast(data.error, 'danger');
        }
    } catch (error) {
        showToast('Error deleting topic', 'danger');
    }
}

// ==================== QUESTIONS ====================

async function loadQuestions() {
    const examType = document.getElementById('filterExamType').value;
    const difficulty = document.getElementById('filterDifficulty').value;
    
    let url = `${API_URL}?action=get_questions&limit=20&offset=${(questionsPage - 1) * 20}&include_inactive=true`;
    if (examType) url += `&exam_type=${examType}`;
    if (difficulty) url += `&difficulty=${difficulty}`;
    
    try {
        const response = await fetch(url);
        const data = await response.json();
        
        if (data.success && data.questions) {
            const tbody = document.querySelector('#questionsTable tbody');
            tbody.innerHTML = data.questions.map(q => `
                <tr>
                    <td>${q.id}</td>
                    <td>${q.question_text.substring(0, 50)}${q.question_text.length > 50 ? '...' : ''}</td>
                    <td>${q.exam_type}</td>
                    <td>${q.topic}</td>
                    <td><span class="badge bg-${q.difficulty === 'easy' ? 'success' : q.difficulty === 'medium' ? 'warning' : 'danger'}">${q.difficulty}</span></td>
                    <td>
                        <span class="badge ${q.is_active ? 'badge-active' : 'badge-inactive'}">
                            ${q.is_active ? 'Active' : 'Inactive'}
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary btn-action" onclick="editQuestion(${q.id}, '${q.exam_type}', '${q.topic}', '${q.question_text.replace(/'/g, "\\'")}', '${q.option_a.replace(/'/g, "\\'")}', '${q.option_b.replace(/'/g, "\\'")}', '${q.option_c.replace(/'/g, "\\'")}', '${q.option_d.replace(/'/g, "\\'")}', '${q.correct_answer}', '${q.explanation || ''}', '${q.difficulty}', ${q.marks})">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-success btn-action" onclick="toggleQuestion(${q.id})">
                            <i class="bi bi-toggle-on"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger btn-action" onclick="deleteQuestion(${q.id})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
            
            document.getElementById('questionsCount').textContent = `Showing ${data.questions.length} of ${data.total} questions`;
        }
    } catch (error) {
        console.error('Error loading questions:', error);
    }
}

async function loadTopicsForSelect() {
    try {
        const response = await fetch(`${API_URL}?action=get_topics`);
        const data = await response.json();
        
        if (data.success && data.topics) {
            const options = data.topics.map(t => `<option value="${t.name}">${t.name}</option>`).join('');
            document.getElementById('questionTopic').innerHTML = options;
        }
    } catch (error) {
        console.error('Error loading topics:', error);
    }
}

function editQuestion(id, examType, topic, question, optA, optB, optC, optD, correct, explanation, difficulty, marks) {
    document.getElementById('questionModalTitle').textContent = 'Edit Question';
    document.getElementById('questionId').value = id;
    document.getElementById('questionExamType').value = examType;
    document.getElementById('questionTopic').value = topic;
    document.getElementById('questionText').value = question;
    document.getElementById('questionOptionA').value = optA;
    document.getElementById('questionOptionB').value = optB;
    document.getElementById('questionOptionC').value = optC;
    document.getElementById('questionOptionD').value = optD;
    document.getElementById('questionCorrectAnswer').value = correct;
    document.getElementById('questionExplanation').value = explanation || '';
    document.getElementById('questionDifficulty').value = difficulty;
    document.getElementById('questionMarks').value = marks;
    new bootstrap.Modal(document.getElementById('questionModal')).show();
}

async function handleQuestionSubmit(e) {
    e.preventDefault();
    
    const id = document.getElementById('questionId').value;
    const data = {
        exam_type: document.getElementById('questionExamType').value,
        topic: document.getElementById('questionTopic').value,
        question_text: document.getElementById('questionText').value,
        option_a: document.getElementById('questionOptionA').value,
        option_b: document.getElementById('questionOptionB').value,
        option_c: document.getElementById('questionOptionC').value,
        option_d: document.getElementById('questionOptionD').value,
        correct_answer: document.getElementById('questionCorrectAnswer').value,
        explanation: document.getElementById('questionExplanation').value,
        difficulty: document.getElementById('questionDifficulty').value,
        marks: parseFloat(document.getElementById('questionMarks').value)
    };
    
    const action = id ? 'update_question' : 'add_question';
    if (id) data.id = parseInt(id);
    
    try {
        const response = await fetch(`${API_URL}?action=${action}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('Question saved successfully', 'success');
            bootstrap.Modal.getInstance(document.getElementById('questionModal')).hide();
            document.getElementById('questionForm').reset();
            loadQuestions();
        } else {
            showToast(result.error, 'danger');
        }
    } catch (error) {
        showToast('Error saving question', 'danger');
    }
}

async function toggleQuestion(id) {
    try {
        const response = await fetch(`${API_URL}?action=toggle_question&id=${id}`);
        const data = await response.json();
        
        if (data.success) {
            showToast(`Question ${data.is_active ? 'activated' : 'deactivated'}`, 'success');
            loadQuestions();
        }
    } catch (error) {
        showToast('Error toggling question', 'danger');
    }
}

async function deleteQuestion(id) {
    if (!confirm('Are you sure you want to delete this question?')) return;
    
    try {
        const response = await fetch(`${API_URL}?action=delete_question&id=${id}`);
        const data = await response.json();
        
        if (data.success) {
            showToast('Question deleted successfully', 'success');
            loadQuestions();
        } else {
            showToast(data.error, 'danger');
        }
    } catch (error) {
        showToast('Error deleting question', 'danger');
    }
}

// ==================== QUIZ SETS ====================

async function loadQuizSets() {
    try {
        const response = await fetch(`${API_URL}?action=get_quiz_sets&include_inactive=true`);
        const data = await response.json();
        
        if (data.success && data.quiz_sets) {
            const tbody = document.querySelector('#quizSetsTable tbody');
            tbody.innerHTML = data.quiz_sets.map(q => `
                <tr>
                    <td>${q.id}</td>
                    <td><strong>${q.name}</strong><br><small class="text-muted">${q.description || ''}</small></td>
                    <td>${q.subject_name || 'N/A'}</td>
                    <td>${q.exam_type}</td>
                    <td>${q.time_limit} min</td>
                    <td>${q.total_questions}</td>
                    <td>
                        <span class="badge ${q.is_active ? 'badge-active' : 'badge-inactive'}">
                            ${q.is_active ? 'Active' : 'Inactive'}
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary btn-action" onclick="editQuizSet(${q.id}, '${q.name}', '${q.description || ''}', ${q.subject_id || ''}, '${q.exam_type}', ${q.time_limit}, ${q.total_questions}, ${q.negative_marking})">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-success btn-action" onclick="toggleQuizSet(${q.id})">
                            <i class="bi bi-toggle-on"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger btn-action" onclick="deleteQuizSet(${q.id})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
        }
    } catch (error) {
        console.error('Error loading quiz sets:', error);
    }
}

function editQuizSet(id, name, description, subjectId, examType, time, questions, negative) {
    document.getElementById('quizSetModalTitle').textContent = 'Edit Quiz Set';
    document.getElementById('quizSetId').value = id;
    document.getElementById('quizSetName').value = name;
    document.getElementById('quizSetDescription').value = description;
    document.getElementById('quizSetSubject').value = subjectId || '';
    document.getElementById('quizSetExamType').value = examType;
    document.getElementById('quizSetTime').value = time;
    document.getElementById('quizSetQuestions').value = questions;
    document.getElementById('quizSetNegative').value = negative;
    new bootstrap.Modal(document.getElementById('quizSetModal')).show();
}

async function handleQuizSetSubmit(e) {
    e.preventDefault();
    
    const id = document.getElementById('quizSetId').value;
    const data = {
        name: document.getElementById('quizSetName').value,
        description: document.getElementById('quizSetDescription').value,
        subject_id: parseInt(document.getElementById('quizSetSubject').value) || null,
        exam_type: document.getElementById('quizSetExamType').value,
        time_limit: parseInt(document.getElementById('quizSetTime').value),
        total_questions: parseInt(document.getElementById('quizSetQuestions').value),
        negative_marking: parseFloat(document.getElementById('quizSetNegative').value)
    };
    
    const action = id ? 'update_quiz_set' : 'add_quiz_set';
    if (id) data.id = parseInt(id);
    
    try {
        const response = await fetch(`${API_URL}?action=${action}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('Quiz set saved successfully', 'success');
            bootstrap.Modal.getInstance(document.getElementById('quizSetModal')).hide();
            document.getElementById('quizSetForm').reset();
            loadQuizSets();
        } else {
            showToast(result.error, 'danger');
        }
    } catch (error) {
        showToast('Error saving quiz set', 'danger');
    }
}

async function toggleQuizSet(id) {
    try {
        const response = await fetch(`${API_URL}?action=toggle_quiz_set&id=${id}`);
        const data = await response.json();
        
        if (data.success) {
            showToast(`Quiz set ${data.is_active ? 'activated' : 'deactivated'}`, 'success');
            loadQuizSets();
        }
    } catch (error) {
        showToast('Error toggling quiz set', 'danger');
    }
}

async function deleteQuizSet(id) {
    if (!confirm('Are you sure you want to delete this quiz set?')) return;
    
    try {
        const response = await fetch(`${API_URL}?action=delete_quiz_set&id=${id}`);
        const data = await response.json();
        
        if (data.success) {
            showToast('Quiz set deleted successfully', 'success');
            loadQuizSets();
        } else {
            showToast(data.error, 'danger');
        }
    } catch (error) {
        showToast('Error deleting quiz set', 'danger');
    }
}

// ==================== RESULTS ====================

async function loadResults() {
    try {
        const response = await fetch(`${API_URL}?action=get_quiz_results&limit=50`);
        const data = await response.json();
        
        if (data.success && data.results) {
            const tbody = document.querySelector('#resultsTable tbody');
            tbody.innerHTML = data.results.map(r => `
                <tr>
                    <td>${r.id}</td>
                    <td>${r.user_name || 'Anonymous'}</td>
                    <td>${r.exam_type}</td>
                    <td>${r.topic}</td>
                    <td><span class="badge ${r.score >= 60 ? 'bg-success' : r.score >= 40 ? 'bg-warning' : 'bg-danger'}">${r.score}%</span></td>
                    <td>${formatTime(r.time_taken)}</td>
                    <td>${new Date(r.created_at).toLocaleString()}</td>
                </tr>
            `).join('');
        }
    } catch (error) {
        console.error('Error loading results:', error);
    }
}

// ==================== IMPORT/EXPORT ====================

async function handleImport(e) {
    e.preventDefault();
    
    const fileInput = document.getElementById('importFile');
    const file = fileInput.files[0];
    
    if (!file) {
        showToast('Please select a file', 'warning');
        return;
    }
    
    const formData = new FormData();
    formData.append('file', file);
    
    try {
        const response = await fetch(`${API_URL}?action=import_questions`, {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast(`Imported ${data.imported} questions successfully`, 'success');
            fileInput.value = '';
        } else {
            showToast(data.error, 'danger');
        }
    } catch (error) {
        showToast('Error importing questions', 'danger');
    }
}

function exportQuestions() {
    const format = document.getElementById('exportFormat').value;
    window.location.href = `${API_URL}?action=export_questions&format=${format}`;
}

// ==================== AUDIT LOGS ====================

async function loadAuditLogs() {
    try {
        const response = await fetch(`${API_URL}?action=get_audit_logs&limit=50`);
        const data = await response.json();
        
        if (data.success && data.logs) {
            const tbody = document.querySelector('#auditLogsTable tbody');
            tbody.innerHTML = data.logs.map(l => `
                <tr>
                    <td>${l.id}</td>
                    <td>${l.admin_username || 'System'}</td>
                    <td>${l.action}</td>
                    <td>${l.table_name}</td>
                    <td><small>${l.old_value || ''} → ${l.new_value || ''}</small></td>
                    <td><small>${l.ip_address}</small></td>
                    <td>${new Date(l.created_at).toLocaleString()}</td>
                </tr>
            `).join('');
        }
    } catch (error) {
        console.error('Error loading audit logs:', error);
    }
}

// ==================== UTILITIES ====================

function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type} border-0`;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    document.querySelector('.toast-container').appendChild(toast);
    new bootstrap.Toast(toast).show();
    
    setTimeout(() => toast.remove(), 5000);
}

function formatTime(seconds) {
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return mins > 0 ? `${mins}m ${secs}s` : `${secs}s`;
}

// Reset form on modal close
document.querySelectorAll('.modal').forEach(modal => {
    modal.addEventListener('hidden.bs.modal', () => {
        const form = modal.querySelector('form');
        if (form) {
            form.reset();
            form.querySelectorAll('input[type="hidden"]').forEach(input => input.value = '');
        }
    });
});
