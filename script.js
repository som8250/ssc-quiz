// Quiz App JavaScript

// Exam configurations
const EXAM_CONFIG = {
    SSC: {
        negativeMarking: 0.25,
        exams: ['CGL', 'CHSL', 'CPO', 'GD', 'MTS'],
        fullNames: {
            'CGL': 'SSC CGL',
            'CHSL': 'SSC CHSL',
            'CPO': 'SSC CPO',
            'GD': 'SSC GD',
            'MTS': 'SSC MTS'
        }
    },
    Railway: {
        negativeMarking: 0.33,
        exams: ['NTPC_Graduate', 'NTPC_UG', 'Group_D'],
        fullNames: {
            'NTPC_Graduate': 'NTPC Graduate',
            'NTPC_UG': 'NTPC UG',
            'Group_D': 'Group D'
        }
    }
};

// State Management
const state = {
    currentScreen: 'welcome',
    questions: [],
    currentQuestionIndex: 0,
    userAnswers: {},
    startTime: null,
    timerInterval: null,
    timeRemaining: 0,
    userName: '',
    section: 'SSC', // 'SSC' or 'Railway'
    topic: '',
    quizMode: 'quiz',
    isLoggedIn: false,
    user: null,
    currentQuestionId: null,
    isBookmarked: false,
    negativeMarking: 0.25
};

// Chart instances
let progressChart = null;
let topicChart = null;

// DOM Elements
const screens = {
    welcome: document.getElementById('welcome-screen'),
    quiz: document.getElementById('quiz-screen'),
    result: document.getElementById('result-screen')
};

const elements = {
    userNameInput: document.getElementById('user-name'),
    topicSelect: document.getElementById('topic-select'),
    startQuizBtn: document.getElementById('start-quiz-btn'),
    sectionButtons: document.querySelectorAll('.section-btn'),
    modeButtons: document.querySelectorAll('.mode-btn'),
    modeDescription: document.getElementById('mode-description'),
    negativeMarkingDisplay: document.getElementById('negative-marking'),
    questionCounter: document.getElementById('question-counter'),
    progressFill: document.getElementById('progress-fill'),
    timer: document.getElementById('timer'),
    quizTimer: document.getElementById('quiz-timer'),
    questionTopic: document.getElementById('question-topic'),
    questionDifficulty: document.getElementById('question-difficulty'),
    questionText: document.getElementById('question-text'),
    optionsContainer: document.querySelector('.options-container'),
    nextBtn: document.getElementById('next-btn'),
    skipBtn: document.getElementById('skip-btn'),
    answerIndicators: document.getElementById('answer-indicators'),
    resultsList: document.getElementById('results-list'),
    reviewSection: document.getElementById('review-section'),
    reviewQuestions: document.getElementById('review-questions'),
    bookmarkBtn: document.getElementById('bookmark-btn'),
    loginBtn: document.getElementById('login-btn'),
    userDropdown: document.getElementById('user-dropdown'),
    dropdownToggle: document.getElementById('dropdown-toggle'), // Added for dropdown fix
    dropdownMenu: document.getElementById('dropdown-menu'),     // Added for dropdown fix
    userNameDisplay: document.getElementById('user-name-display'),
    bookmarksBtn: document.getElementById('bookmarks-btn'),
    historyBtn: document.getElementById('history-btn'),
    analyticsBtn: document.getElementById('analytics-btn'),
    progressBtn: document.getElementById('progress-btn'),
    leaderboardBtn: document.getElementById('leaderboard-btn'),
    logoutBtn: document.getElementById('logout-btn'),
    authModal: document.getElementById('auth-modal'),
    loginForm: document.getElementById('login-form'),
    registerForm: document.getElementById('register-form'),
    bookmarksModal: document.getElementById('bookmarks-modal'),
    bookmarksList: document.getElementById('bookmarks-list'),
    progressModal: document.getElementById('progress-modal'),
    progressTabBtns: document.querySelectorAll('.progress-tab'),
    tabContents: document.querySelectorAll('.tab-content')
};

// Initialize App
function init() {
    if (elements.topicSelect) loadTopics();
    if (elements.resultsList) loadRecentResults();
    setupDropdown(); // Added Dropdown fix
    setupEventListeners();
    checkUserSession();
    updateNegativeMarking();
    initCurrentPage(); // Initialize page-specific data
}

// Check user session
function checkUserSession() {
    const savedUser = localStorage.getItem('quizUser');
    if (savedUser) {
        state.user = JSON.parse(savedUser);
        state.isLoggedIn = true;
        updateUserUI();
    }
}

// Update user UI
function updateUserUI() {
    if (state.isLoggedIn && state.user) {
        // Hide login button when logged in
        if (elements.loginBtn) elements.loginBtn.classList.add('hidden');
        
        // Show user dropdown when logged in
        if (elements.userDropdown) elements.userDropdown.classList.remove('hidden');
        if (elements.userNameDisplay) elements.userNameDisplay.textContent = state.user.username;

        // Update name input if empty
        if (elements.userNameInput && !elements.userNameInput.value) {
            elements.userNameInput.value = state.user.full_name || state.user.username;
        }
    } else {
        // Show login button when not logged in
        if (elements.loginBtn) elements.loginBtn.classList.remove('hidden');
        
        // Hide user dropdown when not logged in
        if (elements.userDropdown) elements.userDropdown.classList.add('hidden');
    }
}

// Fix: Native Dropdown Logic
function setupDropdown() {
    if (elements.dropdownToggle && elements.dropdownMenu) {
        elements.dropdownToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            elements.dropdownMenu.classList.toggle('show');
        });

        // Close dropdown when clicking anywhere else on the page
        document.addEventListener('click', (e) => {
            if (!elements.dropdownToggle.contains(e.target) && !elements.dropdownMenu.contains(e.target)) {
                elements.dropdownMenu.classList.remove('show');
            }
        });
    }
}

// Update negative marking based on section
function updateNegativeMarking() {
    const config = EXAM_CONFIG[state.section];
    state.negativeMarking = config.negativeMarking;

    // Update negative marking display
    if (elements.negativeMarkingDisplay) {
        elements.negativeMarkingDisplay.textContent = `-${config.negativeMarking} Marking`;
    }

    // Update mode description
    updateModeDescription();
}

// Setup Event Listeners
function setupEventListeners() {
    // Login button - safely navigate
    if (elements.loginBtn) {
        elements.loginBtn.addEventListener('click', function (e) {
            window.location.href = 'login.php';
        });
    }

    // Section selection
    if (elements.sectionButtons) {
        elements.sectionButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                elements.sectionButtons.forEach(b => b.classList.remove('selected'));
                btn.classList.add('selected');
                state.section = btn.dataset.section;
                updateNegativeMarking();
                loadTopics();
            });
        });
    }

    // Quiz mode selection
    if (elements.modeButtons) {
        elements.modeButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                elements.modeButtons.forEach(b => b.classList.remove('selected'));
                btn.classList.add('selected');
                state.quizMode = btn.dataset.mode;
                updateModeDescription();
            });
        });
    }

    // Topic selection
    if (elements.topicSelect) {
        elements.topicSelect.addEventListener('change', (e) => {
            state.topic = e.target.value;
        });
    }

    // Start quiz button
    if (elements.startQuizBtn) {
        elements.startQuizBtn.addEventListener('click', startQuiz);
    }

    // Next button
    if (elements.nextBtn) {
        elements.nextBtn.addEventListener('click', () => {
            if (state.currentQuestionIndex < state.questions.length - 1) {
                goToNextQuestion();
            } else {
                finishQuiz();
            }
        });
    }

    // Skip button
    if (elements.skipBtn) {
        elements.skipBtn.addEventListener('click', () => {
            if (!state.userAnswers[state.currentQuestionIndex]) {
                state.userAnswers[state.currentQuestionIndex] = { skipped: true };
                updateAnswerIndicator(state.currentQuestionIndex, 'unanswered');
            }
            if (state.currentQuestionIndex < state.questions.length - 1) {
                goToNextQuestion();
            } else {
                finishQuiz();
            }
        });
    }

    // Result screen buttons
    const retryBtn = document.getElementById('retry-btn');
    if (retryBtn) retryBtn.addEventListener('click', () => showScreen('welcome'));

    const homeBtn = document.getElementById('home-btn');
    if (homeBtn) homeBtn.addEventListener('click', () => showScreen('welcome'));

    const reviewBtn = document.getElementById('review-btn');
    if (reviewBtn) reviewBtn.addEventListener('click', toggleReview);

    // Bookmark button
    if (elements.bookmarkBtn) {
        elements.bookmarkBtn.addEventListener('click', toggleBookmark);
    }

    // Auth forms
    const switchToRegister = document.getElementById('switch-to-register');
    if (switchToRegister) {
        switchToRegister.addEventListener('click', (e) => {
            e.preventDefault();
            switchAuthForm('register');
        });
    }

    const switchToLogin = document.getElementById('switch-to-login');
    if (switchToLogin) {
        switchToLogin.addEventListener('click', (e) => {
            e.preventDefault();
            switchAuthForm('login');
        });
    }

    const closeAuth = document.getElementById('close-auth');
    if (closeAuth) closeAuth.addEventListener('click', () => closeModal('auth-modal'));

    // Login form
    if (elements.loginForm) elements.loginForm.addEventListener('submit', handleLogin);

    // Register form
    if (elements.registerForm) elements.registerForm.addEventListener('submit', handleRegister);

    // Dropdown Actions
    if (elements.logoutBtn) elements.logoutBtn.addEventListener('click', handleLogout);
    
    if (elements.bookmarksBtn) {
        elements.bookmarksBtn.addEventListener('click', () => {
            closeModal('auth-modal');
            openBookmarksModal();
        });
    }
    
    if (elements.historyBtn) {
        elements.historyBtn.addEventListener('click', () => {
            closeModal('auth-modal');
            showAnalyticsModal();
        });
    }
    
    if (elements.progressBtn) {
        elements.progressBtn.addEventListener('click', () => {
            closeModal('auth-modal');
            showProgressModal();
        });
    }
    
    if (elements.analyticsBtn) {
        elements.analyticsBtn.addEventListener('click', () => {
            closeModal('auth-modal');
            showAnalyticsModal();
        });
    }
    
    if (elements.leaderboardBtn) {
        elements.leaderboardBtn.addEventListener('click', () => {
            closeModal('auth-modal');
            showLeaderboardModal();
        });
    }

    const closeBookmarks = document.getElementById('close-bookmarks');
    if (closeBookmarks) closeBookmarks.addEventListener('click', () => closeModal('bookmarks-modal'));
    
    const closeProgress = document.getElementById('close-progress');
    if (closeProgress) closeProgress.addEventListener('click', () => closeModal('progress-modal'));

    // Progress tabs
    if (elements.progressTabBtns) {
        elements.progressTabBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                elements.progressTabBtns.forEach(b => b.classList.remove('active'));
                elements.tabContents.forEach(c => c.classList.remove('active'));
                btn.classList.add('active');
                const tabEl = document.getElementById(`tab-${btn.dataset.tab}`);
                if (tabEl) tabEl.classList.add('active');
            });
        });
    }
}

// Update mode description
function updateModeDescription() {
    if (!elements.modeDescription) return;
    const examName = EXAM_CONFIG[state.section].exams[0]; // Example fallback

    if (state.quizMode === 'practice') {
        elements.modeDescription.textContent = `No timer, see answers instantly, practice at your own pace!`;
        if (elements.quizTimer) elements.quizTimer.classList.add('hidden');
    } else {
        elements.modeDescription.textContent = `20 questions, 20 minutes, ${state.section} pattern with -${state.negativeMarking} negative marking`;
        if (elements.quizTimer) elements.quizTimer.classList.remove('hidden');
    }
}

// Auth functions
function openAuthModal(mode) {
    switchAuthForm(mode);
    if (elements.authModal) elements.authModal.classList.remove('hidden');
}

function switchAuthForm(mode) {
    if (mode === 'register') {
        if (elements.loginForm) elements.loginForm.classList.add('hidden');
        if (elements.registerForm) elements.registerForm.classList.remove('hidden');
        const authTitle = document.getElementById('auth-title');
        if (authTitle) authTitle.textContent = 'Register';
    } else {
        if (elements.registerForm) elements.registerForm.classList.add('hidden');
        if (elements.loginForm) elements.loginForm.classList.remove('hidden');
        const authTitle = document.getElementById('auth-title');
        if (authTitle) authTitle.textContent = 'Login';
    }
}

async function handleLogin(e) {
    e.preventDefault();
    const username = document.getElementById('login-username')?.value;
    const password = document.getElementById('login-password')?.value;

    try {
        const response = await fetch('api.php?action=user_login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ username, password })
        });
        const data = await response.json();

        if (data.success) {
            state.user = data.user;
            state.isLoggedIn = true;
            localStorage.setItem('quizUser', JSON.stringify(data.user));
            updateUserUI();
            closeModal('auth-modal');
            if (elements.loginForm) elements.loginForm.reset();
        } else {
            alert(data.error || 'Login failed');
        }
    } catch (error) {
        console.error('Login error:', error);
        alert('Login failed. Please try again.');
    }
}

async function handleRegister(e) {
    e.preventDefault();
    const username = document.getElementById('register-username')?.value;
    const email = document.getElementById('register-email')?.value;
    const fullName = document.getElementById('register-name')?.value;
    const password = document.getElementById('register-password')?.value;

    try {
        const response = await fetch('api.php?action=register_user', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ username, email, full_name: fullName, password })
        });
        const data = await response.json();

        if (data.success) {
            alert('Registration successful! Please login.');
            switchAuthForm('login');
            if (elements.registerForm) elements.registerForm.reset();
        } else {
            alert(data.error || 'Registration failed');
        }
    } catch (error) {
        console.error('Registration error:', error);
        alert('Registration failed. Please try again.');
    }
}

async function handleLogout() {
    try {
        await fetch('api.php?action=logout', { method: 'POST' });
    } catch (e) {
        console.error('Logout API error:', e);
    }
    state.user = null;
    state.isLoggedIn = false;
    localStorage.removeItem('quizUser');
    updateUserUI();
    window.location.href = 'index.php';
}

// Bookmark functions
async function toggleBookmark() {
    if (!state.currentQuestionId) return;

    if (!state.isLoggedIn) {
        alert('Please login to bookmark questions');
        window.location.href = 'login.php';
        return;
    }

    try {
        const response = await fetch('api.php?action=toggle_bookmark', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ question_id: state.currentQuestionId })
        });
        const data = await response.json();

        if (data.success) {
            state.isBookmarked = data.bookmarked;
            updateBookmarkUI();
        } else if (data.code === 'NOT_LOGGED_IN') {
            alert('Please login to bookmark questions');
            window.location.href = 'login.php';
        }
    } catch (error) {
        console.error('Bookmark error:', error);
    }
}

function updateBookmarkUI() {
    if (!elements.bookmarkBtn) return;
    if (state.isBookmarked) {
        elements.bookmarkBtn.classList.add('bookmarked');
        elements.bookmarkBtn.innerHTML = '<i class="fas fa-bookmark"></i>';
    } else {
        elements.bookmarkBtn.classList.remove('bookmarked');
        elements.bookmarkBtn.innerHTML = '<i class="far fa-bookmark"></i>';
    }
}

async function openBookmarksModal() {
    if (!state.isLoggedIn) {
        alert('Please login to view bookmarks');
        return;
    }

    try {
        const response = await fetch('api.php?action=get_bookmarks');
        const data = await response.json();

        if (data.success) {
            renderBookmarks(data.bookmarks);
            if (elements.bookmarksModal) elements.bookmarksModal.classList.remove('hidden');
        }
    } catch (error) {
        console.error('Error loading bookmarks:', error);
    }
}

function renderBookmarks(bookmarks) {
    if (!elements.bookmarksList) return;
    if (!bookmarks || bookmarks.length === 0) {
        elements.bookmarksList.innerHTML = '<p class="no-results">No bookmarks yet</p>';
        return;
    }

    elements.bookmarksList.innerHTML = bookmarks.map(b => `
        <div class="bookmark-item">
            <h4>${b.question_text.substring(0, 100)}...</h4>
            <div class="meta">
                <span>${b.topic}</span>
                <span>${b.exam_type}</span>
                <span>${b.difficulty}</span>
            </div>
        </div>
    `).join('');
}

// Modal functions
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) modal.classList.add('hidden');
}

async function loadTopics() {
    if (!elements.topicSelect) return;
    try {
        let url = 'api.php?action=get_topics';

        const response = await fetch(url);
        const data = await response.json();

        elements.topicSelect.innerHTML = '<option value="">All Topics</option>';

        if (data.topics && data.topics.length > 0) {
            data.topics.forEach(topic => {
                const option = document.createElement('option');
                option.value = topic;
                option.textContent = topic;
                elements.topicSelect.appendChild(option);
            });
        } else {
            const option = document.createElement('option');
            option.textContent = 'No topics available';
            elements.topicSelect.appendChild(option);
        }
    } catch (error) {
        console.error('Error loading topics:', error);
        elements.topicSelect.innerHTML = '<option value="">Error loading topics</option>';
    }
}

// Load Recent Results
async function loadRecentResults() {
    if (!elements.resultsList) return;
    try {
        const response = await fetch('api.php?action=get_results&limit=5');
        const data = await response.json();

        if (data.results && data.results.length > 0) {
            elements.resultsList.innerHTML = data.results.map(result => `
                <div class="result-item">
                    <span class="result-name">${result.user_name || 'Anonymous'}</span>
                    <span class="result-exam">${result.exam_type}</span>
                    <span class="result-score">${result.score}%</span>
                </div>
            `).join('');
        }
    } catch (error) {
        console.error('Error loading results:', error);
    }
}

// Start Quiz
async function startQuiz() {
    if (!elements.userNameInput) return;
    const name = elements.userNameInput.value.trim();
    if (!name) {
        alert('Please enter your name');
        return;
    }
    state.userName = name;

    try {
        // Build URL with filters
        let url = 'api.php?action=get_questions&limit=20';
        if (state.topic) {
            url += `&topic=${encodeURIComponent(state.topic)}`;
        }

        const response = await fetch(url);
        const data = await response.json();

        if (data.questions && data.questions.length > 0) {
            state.questions = data.questions;
            state.currentQuestionIndex = 0;
            state.userAnswers = {};
            state.startTime = Date.now();

            // Set timer based on mode
            if (state.quizMode === 'quiz') {
                state.timeRemaining = 20 * 60; // 20 minutes
                startTimer();
            }

            renderQuestion();
            generateAnswerIndicators();
            showScreen('quiz');
        } else {
            alert('No questions available for the selected criteria');
        }
    } catch (error) {
        console.error('Error loading questions:', error);
        alert('Failed to load questions. Please try again.');
    }
}

// Start Timer
function startTimer() {
    state.timerInterval = setInterval(() => {
        state.timeRemaining--;

        const mins = Math.floor(state.timeRemaining / 60);
        const secs = state.timeRemaining % 60;
        if (elements.timer) {
            elements.timer.textContent = `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
        }

        if (state.timeRemaining <= 0) {
            clearInterval(state.timerInterval);
            finishQuiz();
        }
    }, 1000);
}

// Render Question
function renderQuestion() {
    const question = state.questions[state.currentQuestionIndex];

    if (elements.questionText) elements.questionText.textContent = question.question;
    if (elements.questionTopic) elements.questionTopic.textContent = question.topic;
    
    if (elements.questionDifficulty) {
        elements.questionDifficulty.textContent = question.difficulty;
        elements.questionDifficulty.className = `difficulty-badge ${question.difficulty.toLowerCase()}`;
    }

    // Render options
    const optionsHTML = Object.entries(question.options).map(([key, value]) => `
        <button class="option-btn" data-option="${key}">
            <span class="option-letter">${key}</span>
            <span class="option-text">${value}</span>
        </button>
    `).join('');

    if (elements.optionsContainer) {
        elements.optionsContainer.innerHTML = optionsHTML;

        // Add click handlers
        document.querySelectorAll('.option-btn').forEach(btn => {
            btn.addEventListener('click', () => selectOption(btn.dataset.option));
        });
    }

    // Update counter
    if (elements.questionCounter) {
        elements.questionCounter.textContent = `Question ${state.currentQuestionIndex + 1}/${state.questions.length}`;
    }

    // Update progress bar
    if (elements.progressFill) {
        const progress = ((state.currentQuestionIndex + 1) / state.questions.length) * 100;
        elements.progressFill.style.width = `${progress}%`;
    }

    // Reset selection state
    document.querySelectorAll('.option-btn').forEach(btn => {
        btn.classList.remove('selected');
    });

    // Enable/disable next button based on answer
    if (elements.nextBtn) elements.nextBtn.disabled = true;
    if (elements.skipBtn) elements.skipBtn.textContent = state.currentQuestionIndex < state.questions.length - 1 ? 'Skip' : 'Finish';

    // Update bookmark state
    state.currentQuestionId = question.id;
    state.isBookmarked = false;
    updateBookmarkUI();
}

// Select Option
function selectOption(option) {
    const question = state.questions[state.currentQuestionIndex];

    // Save answer
    state.userAnswers[state.currentQuestionIndex] = {
        selected: option,
        correct: option === question.correctAnswer
    };

    // Update UI
    document.querySelectorAll('.option-btn').forEach(btn => {
        btn.classList.remove('selected');
        if (btn.dataset.option === option) {
            btn.classList.add('selected');
        }
    });

    // Enable next button
    if (elements.nextBtn) elements.nextBtn.disabled = false;

    // Update indicator
    updateAnswerIndicator(state.currentQuestionIndex, 'answered');
}

// Generate Answer Indicators
function generateAnswerIndicators() {
    if (elements.answerIndicators) {
        elements.answerIndicators.innerHTML = state.questions.map((_, index) => `
            <button class="indicator" data-index="${index}" onclick="goToQuestion(${index})">${index + 1}</button>
        `).join('');
    }
}

// Update Answer Indicator
function updateAnswerIndicator(index, status) {
    const indicator = document.querySelector(`.indicator[data-index="${index}"]`);
    if (indicator) {
        indicator.classList.remove('unanswered', 'answered', 'correct', 'wrong');
        indicator.classList.add(status);
    }
}

// Go to Next Question
function goToNextQuestion() {
    if (state.currentQuestionIndex < state.questions.length - 1) {
        state.currentQuestionIndex++;
        renderQuestion();
    }
}

// Go to Specific Question
function goToQuestion(index) {
    state.currentQuestionIndex = index;
    renderQuestion();
}

// Finish Quiz
function finishQuiz() {
    clearInterval(state.timerInterval);

    const timeTaken = Math.floor((Date.now() - state.startTime) / 1000);
    const result = calculateResult(timeTaken);

    // Save result to database
    saveResult(result);

    // Display results
    displayResults(result);
    showScreen('result');
}

// Calculate Result
function calculateResult(timeTaken) {
    let correct = 0;
    let wrong = 0;
    let skipped = 0;

    state.questions.forEach((question, index) => {
        const answer = state.userAnswers[index];

        if (!answer || answer.skipped) {
            skipped++;
        } else if (answer.selected === question.correctAnswer) {
            correct++;
        } else {
            wrong++;
        }
    });

    const total = state.questions.length;

    // Calculate score with negative marking
    const rawScore = correct - (wrong * state.negativeMarking);
    const maxScore = total;
    const score = ((Math.max(0, rawScore) / maxScore) * 100).toFixed(2);

    return {
        correct,
        wrong,
        skipped,
        total,
        rawScore,
        maxScore,
        score,
        negativeMarking: state.negativeMarking,
        timeTaken,
        examType: state.section,
        section: state.section,
        topic: state.topic || 'Mixed'
    };
}

// Display Results
function displayResults(result) {
    const scorePer = document.getElementById('score-percentage');
    if (scorePer) scorePer.textContent = `${result.score}%`;
    
    const corCount = document.getElementById('correct-count');
    if (corCount) corCount.textContent = result.correct;
    
    const wroCount = document.getElementById('wrong-count');
    if (wroCount) wroCount.textContent = result.wrong;
    
    const skpCount = document.getElementById('skipped-count');
    if (skpCount) skpCount.textContent = result.skipped;
    
    const timeTkn = document.getElementById('time-taken');
    if (timeTkn) timeTkn.textContent = formatTime(result.timeTaken);

    // Update negative marking display
    const negVal = document.getElementById('negative-value');
    if (negVal) negVal.textContent = `-${result.negativeMarking}`;

    // Update score breakdown
    const corScore = document.getElementById('correct-score');
    if (corScore) corScore.textContent = `+${result.correct}`;
    
    const wroScore = document.getElementById('wrong-score');
    if (wroScore) wroScore.textContent = `-${(result.wrong * result.negativeMarking).toFixed(2)}`;
    
    const totScore = document.getElementById('total-score');
    if (totScore) totScore.textContent = `${result.rawScore.toFixed(2)}/${result.maxScore}`;

    // Set message based on score
    const message = document.getElementById('result-message');
    if (message) {
        if (result.score >= 80) {
            message.textContent = 'Excellent! You\'re well prepared! 🎉';
        } else if (result.score >= 60) {
            message.textContent = 'Good job! Keep practicing! 👍';
        } else if (result.score >= 40) {
            message.textContent = 'Not bad! More practice needed 📚';
        } else {
            message.textContent = 'Keep studying! You can do it! 💪';
        }
    }

    // Generate review
    generateReview();
}

// Generate Review
function generateReview() {
    if (!elements.reviewQuestions) return;
    elements.reviewQuestions.innerHTML = '';

    state.questions.forEach((question, index) => {
        const answer = state.userAnswers[index];
        const isCorrect = answer && answer.selected === question.correctAnswer;
        const isSkipped = !answer || answer.skipped;

        let statusClass = isSkipped ? 'skipped' : (isCorrect ? 'correct' : 'wrong');

        const reviewDiv = document.createElement('div');
        reviewDiv.className = `review-question ${statusClass}`;

        let optionsHTML = '';
        const optionLetters = ['A', 'B', 'C', 'D'];

        Object.entries(question.options).forEach(([key, value], idx) => {
            let optionClass = '';
            if (key === question.correctAnswer) {
                optionClass = 'correct-answer';
            } else if (key === answer?.selected && !isCorrect) {
                optionClass = 'wrong-answer';
            } else if (key === answer?.selected) {
                optionClass = 'user-answer';
            }

            optionsHTML += `<div class="review-option ${optionClass}">
                ${optionLetters[idx] || key}. ${value}
                ${key === question.correctAnswer ? ' ✓' : ''}
                ${key === answer?.selected && !isCorrect ? ' (Your answer)' : ''}
            </div>`;
        });

        reviewDiv.innerHTML = `
            <h4>Q${index + 1}. ${question.question}</h4>
            <div class="review-options">${optionsHTML}</div>
            <div class="review-explanation">
                <strong>Explanation:</strong> ${question.explanation || 'No explanation available'}
            </div>
        `;

        elements.reviewQuestions.appendChild(reviewDiv);
    });
}

// Toggle Review Section
function toggleReview() {
    const reviewSection = document.getElementById('review-section');
    const reviewBtn = document.getElementById('review-btn');
    if (reviewSection && reviewBtn) {
        if (reviewSection.style.display === 'none' || reviewSection.style.display === '') {
            reviewSection.style.display = 'block';
            reviewBtn.textContent = 'Hide Review';
        } else {
            reviewSection.style.display = 'none';
            reviewBtn.textContent = 'Review Answers';
        }
    }
}

// Save Result to Database
async function saveResult(result) {
    try {
        await fetch('api.php?action=save_result', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                user_name: state.userName,
                exam_type: result.examType,
                topic: result.topic,
                total_questions: result.total,
                correct_answers: result.correct,
                wrong_answers: result.wrong,
                time_taken: result.timeTaken,
                score: result.score
            })
        });

        // Also save to user quiz history if logged in
        if (state.isLoggedIn) {
            await saveQuizResult({
                user_name: state.userName,
                exam_type: result.examType,
                section: result.section,
                topic: result.topic,
                total_questions: result.total,
                correct_answers: result.correct,
                wrong_answers: result.wrong,
                skipped_answers: result.skipped,
                time_taken: result.timeTaken,
                score: result.score,
                practice_mode: state.quizMode === 'practice'
            });
        }
    } catch (error) {
        console.error('Error saving result:', error);
    }
}

// Save quiz result to history
async function saveQuizResult(resultData) {
    try {
        const response = await fetch('api.php?action=save_user_result', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                user_name: resultData.user_name || 'Anonymous',
                exam_type: resultData.exam_type,
                section: resultData.section,
                topic: resultData.topic,
                total_questions: resultData.total_questions,
                correct_answers: resultData.correct_answers,
                wrong_answers: resultData.wrong_answers,
                skipped_answers: resultData.skipped_answers,
                time_taken: resultData.time_taken,
                score: resultData.score,
                practice_mode: resultData.practice_mode || false
            })
        });
        const data = await response.json();
        if (data.success) {
            console.log('Quiz result saved:', data.result_id);
        }
        return data;
    } catch (error) {
        console.error('Error saving quiz result:', error);
        return { success: false, error: error.message };
    }
}

// Load performance analytics
async function loadPerformanceAnalytics() {
    try {
        const response = await fetch('api.php?action=get_performance');
        const data = await response.json();

        if (data.success) {
            return data;
        } else if (data.code === 'NOT_LOGGED_IN') {
            return null;
        }
        return null;
    } catch (error) {
        console.error('Error loading analytics:', error);
        return null;
    }
}

// Load user quiz history
async function loadUserHistory(limit = 20) {
    try {
        const response = await fetch(`api.php?action=get_user_history&limit=${limit}`);
        const data = await response.json();

        if (data.success) {
            return data;
        } else if (data.code === 'NOT_LOGGED_IN') {
            return null;
        }
        return null;
    } catch (error) {
        console.error('Error loading history:', error);
        return null;
    }
}

// Show analytics modal
async function showAnalyticsModal() {
    const data = await loadPerformanceAnalytics();

    if (!data) {
        alert('Please login to view your analytics');
        return;
    }

    const modal = document.getElementById('analytics-modal');
    if (!modal) {
        const modalHtml = `
        <div id="analytics-modal" class="modal">
            <div class="modal-content large-modal">
                <span class="close">&times;</span>
                <h2>📈 Performance Analytics</h2>
                <div id="analytics-overall"></div>
                <div id="analytics-weak"></div>
                <div id="analytics-by-topic"></div>
            </div>
        </div>`;
        document.body.insertAdjacentHTML('beforeend', modalHtml);

        document.querySelector('#analytics-modal .close').addEventListener('click', () => {
            document.getElementById('analytics-modal').style.display = 'none';
        });
    }

    const modalEl = document.getElementById('analytics-modal');
    const overall = data.overall || {};
    const weakAreas = data.weak_areas || [];
    const byTopic = data.by_topic || [];

    document.getElementById('analytics-overall').innerHTML = `
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value">${overall.total_quizzes || 0}</div>
                <div class="stat-label">Quizzes Taken</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">${overall.accuracy || 0}%</div>
                <div class="stat-label">Accuracy</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">${Math.round(overall.avg_score || 0)}%</div>
                <div class="stat-label">Avg Score</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">${Math.round(overall.best_score || 0)}%</div>
                <div class="stat-label">Best Score</div>
            </div>
        </div>`;

    document.getElementById('analytics-weak').innerHTML = weakAreas.length > 0 ? `
        <div class="alert alert-warning">
            <strong>⚠️ Areas Needing Improvement:</strong>
            <div class="weak-topics">
                ${weakAreas.map(w => `<span class="topic-badge">${w.topic} (${w.accuracy}%)</span>`).join('')}
            </div>
        </div>` : '';

    if (byTopic.length > 0) {
        document.getElementById('analytics-by-topic').innerHTML = `
            <h3>Performance by Topic</h3>
            <div class="topic-chart">
                ${byTopic.map(t => `
                    <div class="topic-row">
                        <div class="topic-name">${t.topic}</div>
                        <div class="topic-bar-container">
                            <div class="topic-bar" style="width: ${t.accuracy}%; background: ${t.accuracy >= 80 ? 'var(--success-color)' : t.accuracy >= 60 ? 'var(--warning-color)' : 'var(--error-color)'}"></div>
                        </div>
                        <div class="topic-accuracy">${t.accuracy}%</div>
                    </div>
                `).join('')}
            </div>`;
    } else {
        document.getElementById('analytics-by-topic').innerHTML = '<p class="text-center">Complete some quizzes to see your performance analytics!</p>';
    }

    document.getElementById('analytics-modal').style.display = 'block';
}

// Show Progress Modal with charts
async function showProgressModal() {
    if (!state.isLoggedIn) {
        alert('Please login to view your progress');
        return;
    }

    if (elements.progressModal) elements.progressModal.classList.remove('hidden');

    const data = await loadPerformanceAnalytics();
    const historyData = await loadUserHistory(50);

    // Update overview tab
    if (data && data.overall) {
        const tQuizzes = document.getElementById('total-quizzes');
        if (tQuizzes) tQuizzes.textContent = data.overall.total_quizzes || 0;
        
        const aScore = document.getElementById('avg-score');
        if (aScore) aScore.textContent = `${Math.round(data.overall.avg_score || 0)}%`;
        
        const bScore = document.getElementById('best-score');
        if (bScore) bScore.textContent = `${Math.round(data.overall.best_score || 0)}%`;

        // Section performance
        const sscData = data.by_section?.SSC || { avg_score: 0 };
        const railwayData = data.by_section?.Railway || { avg_score: 0 };

        const sBar = document.getElementById('ssc-bar');
        if(sBar) sBar.style.width = `${sscData.avg_score}%`;
        const sPer = document.getElementById('ssc-percent');
        if(sPer) sPer.textContent = `${Math.round(sscData.avg_score)}%`;
        
        const rBar = document.getElementById('railway-bar');
        if(rBar) rBar.style.width = `${railwayData.avg_score}%`;
        const rPer = document.getElementById('railway-percent');
        if(rPer) rPer.textContent = `${Math.round(railwayData.avg_score)}%`;

        // Progress chart
        renderProgressChart(historyData?.history || []);

        // Topic analysis - Strong and weak areas
        renderTopicAnalysis(data.by_topic || []);
    } else {
        const tQuizzes = document.getElementById('total-quizzes');
        if (tQuizzes) tQuizzes.textContent = '0';
        const aScore = document.getElementById('avg-score');
        if (aScore) aScore.textContent = '0%';
        const bScore = document.getElementById('best-score');
        if (bScore) bScore.textContent = '0%';
    }

    // History tab
    renderHistoryList(historyData?.history || []);
}

// Render Progress Chart
function renderProgressChart(history) {
    const ctx = document.getElementById('progress-chart');
    if (!ctx) return;

    // Destroy existing chart
    if (progressChart) {
        progressChart.destroy();
    }

    const labels = history.slice(0, 10).map((_, i) => `Quiz ${i + 1}`).reverse();
    const scores = history.slice(0, 10).map(h => parseFloat(h.score)).reverse();

    progressChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Score %',
                data: scores,
                borderColor: '#4f46e5',
                backgroundColor: 'rgba(79, 70, 229, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });
}

// Render Topic Analysis
function renderTopicAnalysis(topics) {
    const strongTopics = topics.filter(t => t.accuracy >= 70).sort((a, b) => b.accuracy - a.accuracy);
    const weakTopics = topics.filter(t => t.accuracy < 70).sort((a, b) => a.accuracy - b.accuracy);

    const strongContainer = document.getElementById('strong-topics');
    const weakContainer = document.getElementById('weak-topics');

    if (strongContainer) {
        if (strongTopics.length > 0) {
            strongContainer.innerHTML = strongTopics.slice(0, 5).map(t => `
                <div class="topic-item">
                    <span class="topic-name">${t.topic}</span>
                    <span class="topic-accuracy good">${t.accuracy}%</span>
                </div>
            `).join('');
        } else {
            strongContainer.innerHTML = '<p class="no-data">Complete more quizzes to see strong areas</p>';
        }
    }

    if (weakContainer) {
        if (weakTopics.length > 0) {
            weakContainer.innerHTML = weakTopics.slice(0, 5).map(t => `
                <div class="topic-item">
                    <span class="topic-name">${t.topic}</span>
                    <span class="topic-accuracy poor">${t.accuracy}%</span>
                </div>
            `).join('');
        } else {
            weakContainer.innerHTML = '<p class="no-data">No weak areas yet! Keep it up!</p>';
        }
    }

    // Topic bar chart
    const ctx = document.getElementById('topic-chart');
    if (ctx && topics.length > 0) {
        if (topicChart) {
            topicChart.destroy();
        }

        const sortedTopics = [...topics].sort((a, b) => b.accuracy - a.accuracy).slice(0, 8);

        topicChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: sortedTopics.map(t => t.topic),
                datasets: [{
                    label: 'Accuracy %',
                    data: sortedTopics.map(t => t.accuracy),
                    backgroundColor: sortedTopics.map(t =>
                        t.accuracy >= 70 ? '#10b981' : t.accuracy >= 50 ? '#f59e0b' : '#ef4444'
                    )
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100
                    }
                }
            }
        });
    }
}

// Render History List
function renderHistoryList(history) {
    const container = document.getElementById('history-list');
    if (!container) return;

    if (!history || history.length === 0) {
        container.innerHTML = '<p class="no-results">No quiz history yet</p>';
        return;
    }

    container.innerHTML = history.slice(0, 20).map(h => `
        <div class="history-item">
            <div class="history-header">
                <span class="history-exam">${h.exam_type}</span>
                <span class="history-topic">${h.topic || 'General'}</span>
                <span class="history-score ${h.score >= 60 ? 'good' : 'poor'}">${h.score}%</span>
            </div>
            <div class="history-details">
                <span>✅ ${h.correct_answers}/${h.total_questions}</span>
                <span>❌ ${h.wrong_answers}</span>
                <span>⏱️ ${formatTime(h.time_taken)}</span>
                <span class="history-date">${formatDate(h.created_at)}</span>
            </div>
        </div>
    `).join('');
}

// Load leaderboard
async function loadLeaderboard(limit = 10) {
    try {
        const response = await fetch(`api.php?action=get_leaderboard&limit=${limit}`);
        const data = await response.json();
        return data.success ? data : null;
    } catch (error) {
        console.error('Error loading leaderboard:', error);
        return null;
    }
}

// Show leaderboard modal
async function showLeaderboardModal() {
    const data = await loadLeaderboard(10);

    const modal = document.getElementById('leaderboard-modal');
    if (!modal) {
        const modalHtml = `
        <div id="leaderboard-modal" class="modal">
            <div class="modal-content large-modal">
                <span class="close">&times;</span>
                <h2>🏆 Leaderboard</h2>
                <div id="leaderboard-list"></div>
            </div>
        </div>`;
        document.body.insertAdjacentHTML('beforeend', modalHtml);

        document.querySelector('#leaderboard-modal .close').addEventListener('click', () => {
            document.getElementById('leaderboard-modal').style.display = 'none';
        });
    }

    const modalEl = document.getElementById('leaderboard-modal');
    const listEl = document.getElementById('leaderboard-list');

    if (!data) {
        modalEl.style.display = 'block';
        listEl.innerHTML = '<p class="text-center">Error loading leaderboard.</p>';
        return;
    }

    const leaders = data.leaderboard || [];

    if (leaders.length > 0) {
        listEl.innerHTML = leaders.map((leader, index) => `
            <div class="leaderboard-item ${index < 3 ? 'top-' + (index + 1) : ''}">
                <span class="rank">${index + 1}</span>
                <span class="name">${leader.user_name}</span>
                <span class="score">${leader.avg_score}%</span>
                <span class="quizzes">${leader.total_quizzes} quizzes</span>
            </div>
        `).join('');
    } else {
        listEl.innerHTML = '<p class="text-center">No leaderboard data yet.</p>';
    }

    modalEl.style.display = 'block';
}

// Show Screen
function showScreen(screenName) {
    Object.values(screens).forEach(screen => {
        if (screen) screen.classList.remove('active');
    });
    if (screens[screenName]) screens[screenName].classList.add('active');
    state.currentScreen = screenName;
}

// Format time in seconds to MM:SS
function formatTime(seconds) {
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
}

// Format date
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-IN', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Initialize the app when DOM is loaded
document.addEventListener('DOMContentLoaded', init);

// --- NEW PAGE INITIALIZATION FUNCTIONS ---

// Bookmarks Page
async function initBookmarksPage() {
    const listContainer = document.getElementById('bookmarks-list');
    if (!listContainer) return;

    try {
        const response = await fetch('api.php?action=get_bookmarks');
        const data = await response.json();

        if (data.success && data.bookmarks.length > 0) {
            listContainer.innerHTML = data.bookmarks.map(b => `
                <div class="bookmark-item" onclick="showQuestionDetail(${b.id})">
                    <div class="bookmark-info">
                        <h4>${b.question_text.substring(0, 100)}${b.question_text.length > 100 ? '...' : ''}</h4>
                        <div class="meta">
                            <span class="topic">${b.topic}</span>
                            <span class="exam">${b.exam_type}</span>
                            <span class="date">${formatDate(b.created_at)}</span>
                        </div>
                    </div>
                    <i class="fas fa-chevron-right"></i>
                </div>
            `).join('');
        } else {
            listContainer.innerHTML = '<p class="no-results">No bookmarks yet. Save questions during a quiz to see them here.</p>';
        }
    } catch (error) {
        console.error('Error loading bookmarks:', error);
        listContainer.innerHTML = '<p class="error">Error loading bookmarks. Please try again.</p>';
    }
}

// History Page
async function initHistoryPage() {
    const listContainer = document.getElementById('history-list');
    const statsContainer = document.getElementById('history-stats');
    if (!listContainer) return;

    const data = await loadUserHistory(50);
    if (data && data.success) {
        renderHistoryList(data.history);

        if (data.stats && statsContainer) {
            statsContainer.innerHTML = `
                <div class="summary-card">
                    <div class="summary-info">
                        <span class="label">Total Attempts</span>
                        <h3>${data.stats.total_quizzes}</h3>
                    </div>
                </div>
                <div class="summary-card">
                    <div class="summary-info">
                        <span class="label">Avg Score</span>
                        <h3>${Math.round(data.stats.avg_score)}%</h3>
                    </div>
                </div>
                <div class="summary-card">
                    <div class="summary-info">
                        <span class="label">Best Score</span>
                        <h3>${Math.round(data.stats.best_score)}%</h3>
                    </div>
                </div>
            `;
        }
    } else {
        listContainer.innerHTML = '<p class="no-results">No quiz history yet. Complete a quiz to see your results.</p>';
    }
}

// Leaderboard Page
async function initLeaderboardPage() {
    const content = document.getElementById('leaderboard-content');
    if (!content) return;

    const tabs = document.querySelectorAll('.leaderboard-tabs .tab-btn');
    tabs.forEach(tab => {
        tab.addEventListener('click', async () => {
            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            loadLeaderboardData(tab.dataset.tab);
        });
    });

    loadLeaderboardData('overall');
}

async function loadLeaderboardData(type) {
    const content = document.getElementById('leaderboard-content');
    if (!content) return;
    content.innerHTML = '<div class="loading-spinner"><i class="fas fa-circle-notch fa-spin"></i> Loading...</div>';

    const data = await loadLeaderboard(20);
    if (data && data.leaderboard) {
        content.innerHTML = `
            <table class="leaderboard-table">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>User</th>
                        <th>Quizzes</th>
                        <th>Avg Accuracy</th>
                        <th>Best Score</th>
                    </tr>
                </thead>
                <tbody>
                    ${data.leaderboard.map(l => `
                        <tr class="${l.id == state.user?.id ? 'current-user' : ''}">
                            <td><span class="rank-badge rank-${l.rank}">${l.rank}</span></td>
                            <td>${l.full_name || l.username}</td>
                            <td>${l.quiz_count}</td>
                            <td>${Math.round(l.avg_score)}%</td>
                            <td>${Math.round(l.best_score)}%</td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        `;
    } else {
        content.innerHTML = '<p class="no-results">No leaderboard data available.</p>';
    }
}

// Analytics Page
async function initAnalyticsPage() {
    const data = await loadPerformanceAnalytics();
    if (!data) return;

    const historyData = await loadUserHistory(20);
    if (historyData) {
        renderTrendChart(historyData.history, 'analytics-trend-chart');
    }

    renderTopicChartAnalytics(data.by_topic || [], 'analytics-topic-chart');

    const sectionList = document.getElementById('section-performance-list');
    if (sectionList && data.by_section) {
        sectionList.innerHTML = Object.entries(data.by_section).map(([name, info]) => `
            <div class="section-bar-item">
                <span class="section-label">${name}</span>
                <div class="bar-container">
                    <div class="bar-fill" style="width: ${info.avg_score}%"></div>
                </div>
                <span class="section-value">${Math.round(info.avg_score)}%</span>
            </div>
        `).join('');
    }
}

// Progress Page
async function initProgressPage() {
    const data = await loadPerformanceAnalytics();
    if (!data) return;

    const totVal = document.getElementById('total-quizzes-val');
    if (totVal) totVal.textContent = data.overall?.total_quizzes || 0;
    
    const avgVal = document.getElementById('avg-score-val');
    if (avgVal) avgVal.textContent = `${Math.round(data.overall?.avg_score || 0)}%`;
    
    const bstVal = document.getElementById('best-score-val');
    if (bstVal) bstVal.textContent = `${Math.round(data.overall?.best_score || 0)}%`;

    const strongList = document.getElementById('strong-topics-list');
    const weakList = document.getElementById('weak-topics-list');

    if (strongList && data.strong_areas && data.strong_areas.length > 0) {
        strongList.innerHTML = data.strong_areas.map(t => `
            <div class="topic-card strong">
                <i class="fas fa-check-circle"></i>
                <span>${t.topic}</span>
                <span class="percent">${Math.round(t.accuracy)}%</span>
            </div>
        `).join('');
    }

    if (weakList && data.weak_areas && data.weak_areas.length > 0) {
        weakList.innerHTML = data.weak_areas.map(t => `
            <div class="topic-card weak">
                <i class="fas fa-exclamation-triangle"></i>
                <span>${t.topic}</span>
                <span class="percent">${Math.round(t.accuracy)}%</span>
            </div>
        `).join('');
    }
}

// Helper to render trend chart with specific ID
function renderTrendChart(history, elementId) {
    const ctx = document.getElementById(elementId);
    if (!ctx) return;

    const labels = history.slice(0, 10).map((_, i) => `Quiz ${i + 1}`).reverse();
    const scores = history.slice(0, 10).map(h => parseFloat(h.score)).reverse();

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Score %',
                data: scores,
                borderColor: '#4f46e5',
                tension: 0.4,
                fill: true,
                backgroundColor: 'rgba(79, 70, 229, 0.1)'
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });
}

function renderTopicChartAnalytics(topics, elementId) {
    const ctx = document.getElementById(elementId);
    if (!ctx || topics.length === 0) return;

    const sortedTopics = [...topics].sort((a, b) => b.accuracy - a.accuracy).slice(0, 8);

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: sortedTopics.map(t => t.topic),
            datasets: [{
                label: 'Accuracy %',
                data: sortedTopics.map(t => t.accuracy),
                backgroundColor: sortedTopics.map(t =>
                    t.accuracy >= 70 ? '#10b981' : t.accuracy >= 50 ? '#f59e0b' : '#ef4444'
                )
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });
}

// Initialize individual pages based on URL or logic
function initCurrentPage() {
    const path = window.location.pathname;
    const page = path.split("/").pop() || 'index.php';

    // Set active link in navbar
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href === page || (page === '' && href === 'index.php')) {
            link.classList.add('active');
        } else {
            link.classList.remove('active');
        }
    });

    if (page.includes('bookmarks.php')) initBookmarksPage();
    if (page.includes('history.php')) initHistoryPage();
    if (page.includes('leaderboard.php')) initLeaderboardPage();
    if (page.includes('analytics.php')) initAnalyticsPage();
    if (page.includes('progress.php')) initProgressPage();
}