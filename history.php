<?php include 'header.php'; ?>

<section id="history-page" class="page-content">
    <div class="page-header">
        <h2><i class="fas fa-history"></i> Quiz History</h2>
        <p>Review your past performances and quiz attempts.</p>
    </div>

    <div class="content-card">
        <div class="stats-overview" id="history-stats">
            <!-- Stats cards will be injected here -->
        </div>
        <div id="history-list" class="data-list">
            <div class="loading-spinner">
                <i class="fas fa-circle-notch fa-spin"></i> Loading history...
            </div>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof initHistoryPage === 'function') {
            initHistoryPage();
        }
    });
</script>

<?php include 'footer.php'; ?>
