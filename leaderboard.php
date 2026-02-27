<?php include 'header.php'; ?>

<section id="leaderboard-page" class="page-content">
    <div class="page-header">
        <h2><i class="fas fa-trophy"></i> Leaderboard</h2>
        <p>Top performers in Railway & SSC Quiz.</p>
    </div>

    <div class="leaderboard-tabs">
        <button class="tab-btn active" data-tab="overall">Overall</button>
        <button class="tab-btn" data-tab="ssc">SSC</button>
        <button class="tab-btn" data-tab="railway">Railway</button>
    </div>

    <div class="content-card">
        <div id="leaderboard-content" class="data-list">
            <div class="loading-spinner">
                <i class="fas fa-circle-notch fa-spin"></i> Loading leaderboard...
            </div>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof initLeaderboardPage === 'function') {
            initLeaderboardPage();
        }
    });
</script>

<?php include 'footer.php'; ?>
