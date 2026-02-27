<?php include 'header.php'; ?>

<section id="analytics-page" class="page-content">
    <div class="page-header">
        <h2><i class="fas fa-chart-bar"></i> Performance Analytics</h2>
        <p>In-depth analysis of your quiz attempts and subject mastery.</p>
    </div>

    <div class="analytics-grid">
        <div class="content-card wide">
            <h4><i class="fas fa-chart-line"></i> Performance Trend</h4>
            <div class="chart-container">
                <canvas id="analytics-trend-chart"></canvas>
            </div>
        </div>

        <div class="content-card">
            <h4><i class="fas fa-bullseye"></i> Topic-wise Accuracy</h4>
            <div class="chart-container">
                <canvas id="analytics-topic-chart"></canvas>
            </div>
        </div>

        <div class="content-card">
            <h4><i class="fas fa-adjust"></i> Section Performance</h4>
            <div id="section-performance-list" class="data-list">
                <!-- Section bars will be injected here -->
            </div>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof initAnalyticsPage === 'function') {
            initAnalyticsPage();
        }
    });
</script>

<?php include 'footer.php'; ?>
