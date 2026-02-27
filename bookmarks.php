<?php include 'header.php'; ?>

<section id="bookmarks-page" class="page-content">
    <div class="page-header">
        <h2><i class="fas fa-bookmark"></i> My Bookmarks</h2>
        <p>Your saved questions for quick review.</p>
    </div>

    <div class="content-card">
        <div id="bookmarks-list" class="data-list">
            <div class="loading-spinner">
                <i class="fas fa-circle-notch fa-spin"></i> Loading bookmarks...
            </div>
        </div>
    </div>
</section>

<!-- Question Preview Modal -->
<div id="question-modal" class="modal hidden">
    <div class="modal-content modal-large">
        <div class="modal-header">
            <h3 id="modal-question-title">Question Detail</h3>
            <button class="close-modal" id="close-question-modal"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body" id="question-detail-body">
            <!-- Question content will be loaded here -->
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof initBookmarksPage === 'function') {
            initBookmarksPage();
        } else {
            // Fallback if script.js isn't updated yet
            console.log('initBookmarksPage not found, waiting for script.js to load');
        }
    });
</script>

<?php include 'footer.php'; ?>
