<?php include 'header.php'; ?>

<!-- Welcome Screen -->
<section id="welcome-screen" class="screen active">
    <div class="welcome-card">
        <h2>Welcome to Quiz App</h2>
        <p class="subtitle">Practice for Railway & SSC Exams</p>
        
        <div class="user-info">
            <label for="user-name">Enter Your Name:</label>
            <input type="text" id="user-name" placeholder="Your Name" maxlength="50">
        </div>

        <!-- Section Selection (Railway/SSC) -->
        <div class="selection-group">
            <label>Select Section:</label>
            <div class="section-buttons">
                <button class="section-btn selected" data-section="SSC">
                    <i class="fas fa-graduation-cap"></i> SSC
                </button>
                <button style="color: gray!important" class="section-btn" data-section="Railway">
                    <i class="fas fa-train"></i> Railway
                </button>
            </div>
        </div>


        <div class="selection-group">
            <label>Select Topic:</label>
            <select id="topic-select">
                <option value="">Loading topics...</option>
            </select>
        </div>

        <div class="selection-group mode-selection">
            <label>Quiz Mode:</label>
            <div class="mode-buttons">
                <button class="mode-btn selected" data-mode="quiz">
                    <i class="fas fa-stopwatch"></i> Timed Mock Test
                </button>
            </div>
            <p class="mode-description" id="mode-description">20 questions, 20 minutes with negative marking</p>
        </div>

        <div class="quiz-info">
            <div class="info-item">
                <span class="info-icon">❓</span>
                <span>20 Questions</span>
            </div>
            <div class="info-item">
                <span class="info-icon">⏱️</span>
                <span>20 Minutes</span>
            </div>
            <div class="info-item">
                <span class="info-icon">⚠️</span>
                <span id="negative-marking">-0.25 Marking</span>
            </div>
            <div class="info-item">
                <span class="info-icon">📊</span>
                <span>Instant Results</span>
            </div>
        </div>

        <button id="start-quiz-btn" class="primary-btn">
            <i class="fas fa-play-circle"></i> Start Quiz <span class="btn-icon">→</span>
        </button>
    </div>

    <!-- Recent Results -->
    <div class="recent-results">
        <h3>Recent Results</h3>
        <div id="results-list">
            <p class="no-results">No results yet</p>
        </div>
    </div>
</section>

<!-- Quiz Screen -->
<section id="quiz-screen" class="screen">
    <div class="quiz-header">
        <div class="quiz-progress">
            <span id="question-counter">Question 1/20</span>
            <div class="progress-bar">
                <div id="progress-fill"></div>
            </div>
        </div>
        <div class="quiz-timer" id="quiz-timer">
            <span class="timer-icon">⏱️</span>
            <span id="timer">20:00</span>
        </div>
    </div>

    <div class="quiz-content">
        <div class="question-meta">
            <span id="question-topic" class="topic-badge">Number System</span>
            <span id="question-difficulty" class="difficulty-badge easy">Easy</span>
            <button id="bookmark-btn" class="bookmark-btn" title="Bookmark this question">
                <i class="far fa-bookmark"></i>
            </button>
        </div>

        <div class="question-container">
            <h2 id="question-text">Question text will appear here?</h2>
        </div>

        <div class="options-container">
            <button class="option-btn" data-option="A">
                <span class="option-letter">A</span>
                <span class="option-text">Option A</span>
            </button>
            <button class="option-btn" data-option="B">
                <span class="option-letter">B</span>
                <span class="option-text">Option B</span>
            </button>
            <button class="option-btn" data-option="C">
                <span class="option-letter">C</span>
                <span class="option-text">Option C</span>
            </button>
            <button class="option-btn" data-option="D">
                <span class="option-letter">D</span>
                <span class="option-text">Option D</span>
            </button>
        </div>

        <div class="quiz-navigation">
            <button id="skip-btn" class="secondary-btn">Skip</button>
            <button id="next-btn" class="primary-btn" disabled>Next →</button>
        </div>
    </div>

    <div class="quiz-footer">
        <div class="answer-indicator" id="answer-indicators">
            <!-- Indicators will be generated here -->
        </div>
    </div>
</section>

<!-- Result Screen -->
<section id="result-screen" class="screen">
    <div class="result-card">
        <div class="result-header">
            <h2>Quiz Complete!</h2>
            <p id="result-message">Great job!</p>
        </div>

        <div class="score-display">
            <div class="score-circle">
                <span id="score-percentage">0%</span>
            </div>
        </div>

        <div class="result-stats">
            <div class="stat-item correct">
                <span class="stat-icon">✓</span>
                <span class="stat-value" id="correct-count">0</span>
                <span class="stat-label">Correct</span>
            </div>
            <div class="stat-item wrong">
                <span class="stat-icon">✗</span>
                <span class="stat-value" id="wrong-count">0</span>
                <span class="stat-label">Wrong</span>
            </div>
            <div class="stat-item skipped">
                <span class="stat-icon">⊘</span>
                <span class="stat-value" id="skipped-count">0</span>
                <span class="stat-label">Skipped</span>
            </div>
            <div class="stat-item time">
                <span class="stat-icon">⏱️</span>
                <span class="stat-value" id="time-taken">0s</span>
                <span class="stat-label">Time</span>
            </div>
        </div>

        <div class="score-breakdown">
            <h4>Score Breakdown</h4>
            <div class="breakdown-item">
                <span>Correct (+1)</span>
                <span id="correct-score">+0</span>
            </div>
            <div class="breakdown-item negative">
                <span>Wrong (<span id="negative-value">-0.25</span>)</span>
                <span id="wrong-score">-0</span>
            </div>
            <div class="breakdown-item total">
                <span>Total Score</span>
                <span id="total-score">0</span>
            </div>
        </div>

        <div class="result-actions">
            <button id="review-btn" class="secondary-btn">Review Answers</button>
            <button id="retry-btn" class="primary-btn">Try Again</button>
            <button id="home-btn" class="secondary-btn">Home</button>
        </div>
    </div>

    <!-- Review Section -->
    <div id="review-section" class="review-container">
        <h3>Answer Review</h3>
        <div id="review-questions"></div>
    </div>
</section>

<!-- Modals are now on separate pages or kept for overlay if needed -->
<!-- Login/Register Modal is still useful for quick login -->
<div id="auth-modal" class="modal hidden">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="auth-title">Login</h3>
            <button class="close-modal" id="close-auth"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <!-- Login Form -->
            <form id="login-form" class="auth-form">
                <div class="form-group">
                    <label for="login-username">Username or Email</label>
                    <input type="text" id="login-username" required>
                </div>
                <div class="form-group">
                    <label for="login-password">Password</label>
                    <input type="password" id="login-password" required>
                </div>
                <button type="submit" class="primary-btn full-width">Login</button>
                <p class="form-footer">
                    Don't have an account? <a href="#" id="switch-to-register">Register</a>
                </p>
            </form>
            <!-- Register Form -->
            <form id="register-form" class="auth-form hidden">
                <div class="form-group">
                    <label for="register-username">Username</label>
                    <input type="text" id="register-username" required minlength="3">
                </div>
                <div class="form-group">
                    <label for="register-email">Email</label>
                    <input type="email" id="register-email" required>
                </div>
                <div class="form-group">
                    <label for="register-name">Full Name (Optional)</label>
                    <input type="text" id="register-name">
                </div>
                <div class="form-group">
                    <label for="register-password">Password</label>
                    <input type="password" id="register-password" required minlength="6">
                </div>
                <button type="submit" class="primary-btn full-width">Register</button>
                <p class="form-footer">
                    Already have an account? <a href="#" id="switch-to-login">Login</a>
                </p>
            </form>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
