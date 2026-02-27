<?php include 'header.php'; ?>

<section id="progress-page" class="page-content">
    <div class="page-header">
        <h2><i class="fas fa-tasks"></i> My Progress</h2>
        <p>Track your learning journey and exam readiness.</p>
    </div>

    <div class="progress-summary-grid">
        <div class="summary-card">
            <div class="icon-circle primary">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="summary-info">
                <span class="label">Total Quizzes</span>
                <h3 id="total-quizzes-val">0</h3>
            </div>
        </div>
        <div class="summary-card">
            <div class="icon-circle success">
                <i class="fas fa-star"></i>
            </div>
            <div class="summary-info">
                <span class="label">Average Score</span>
                <h3 id="avg-score-val">0%</h3>
            </div>
        </div>
        <div class="summary-card">
            <div class="icon-circle warning">
                <i class="fas fa-trophy"></i>
            </div>
            <div class="summary-info">
                <span class="label">Best Score</span>
                <h3 id="best-score-val">0%</h3>
            </div>
        </div>
    </div>

    <div class="progress-detailed-grid">
        <div class="content-card">
            <h4><i class="fas fa-thumbs-up"></i> Strong Areas</h4>
            <div id="strong-topics-list" class="topic-grid">
                <p class="no-data">Take more quizzes to see analysis</p>
            </div>
        </div>
        <div class="content-card">
            <h4><i class="fas fa-exclamation-triangle"></i> Areas to Improve</h4>
            <div id="weak-topics-list" class="topic-grid">
                <p class="no-data">Take more quizzes to see analysis</p>
            </div>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof initProgressPage === 'function') {
            initProgressPage();
        }
    });
</script>

<?php include 'footer.php'; ?>
