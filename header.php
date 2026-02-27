<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Railway & SSC Quiz App</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📚</text></svg>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="loading">
    <div class="container">
        <header class="main-header">
            <nav class="navbar">
                <div class="logo">
                    <a href="index.php">
                        <span class="logo-icon">📚</span>
                        <h1>Railway & SSC Quiz</h1>
                    </a>
                </div>
                
                <div class="header-actions">
                    
                    <div id="user-section" class="user-section">
                        <a style="text-decoration: none !important;" href="login.html" id="login-btn" class="secondary-btn small">Login</a>
                        
                        <div id="user-dropdown" class="user-dropdown hidden">
                            <div class="user-profile-toggle">
                                <i class="fas fa-user-circle" style="font-size: 1.5rem;"></i>
                                <span id="user-name-display"></span>
                                <i class="fas fa-chevron-down" style="font-size: 0.8rem;"></i>
                            </div>
                            <div class="dropdown-menu">
                                <div class="dropdown-header">
                                    <span id="user-name-header"></span>
                                </div>
                                <div style="border-top: 1px solid var(--border-color); margin: 4px 0;"></div>
                                
                                <a href="index.php"><i class="fas fa-home"></i> Home</a>
                                <a href="leaderboard.php"><i class="fas fa-trophy"></i> Leaderboard</a>
                                <a href="analytics.php"><i class="fas fa-chart-line"></i> Analytics</a>
                                <a href="bookmarks.php"><i class="fas fa-bookmark"></i> Bookmarks</a>
                                <a href="history.php"><i class="fas fa-history"></i> History</a>
                                <a href="progress.php"><i class="fas fa-tasks"></i> Progress</a>
                                
                                <div style="border-top: 1px solid var(--border-color); margin: 4px 0;"></div>
                                
                                <button id="logout-btn" class="logout-link"><i class="fas fa-sign-out-alt"></i> Logout</button>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>
        </header>