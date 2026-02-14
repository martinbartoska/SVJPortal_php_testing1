<?php
// Load dependencies
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/src/models/Database.php';
require_once __DIR__ . '/src/models/User.php';
require_once __DIR__ . '/src/models/Survey.php';
require_once __DIR__ . '/src/models/Quiz.php';
require_once __DIR__ . '/src/helpers/Auth.php';

// Require authentication
Auth::requireLogin();

// Get current user and data
$auth = Auth::getInstance();
$user = $auth->getUser();

// Get statistics
$db = new Database();
$activeSurveys = $db->fetchAll('SELECT * FROM surveys WHERE status = ? ORDER BY created_at DESC LIMIT 5', ['active']);
$activeQuizzes = $db->fetchAll('SELECT * FROM quizzes WHERE status = ? ORDER BY created_at DESC LIMIT 5', ['active']);
$totalUsers = $db->count('users');
$totalResponses = $db->count('survey_responses');

// Update response counts
foreach ($activeSurveys as &$survey) {
    $survey['response_count'] = $db->count('survey_responses', 'survey_id = ?', [$survey['id']]);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SVJ Portal</title>
    <link rel="stylesheet" href="/public/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-brand">SVJ Portal</div>
        <ul class="nav-links">
            <li><a href="/index.php">Dashboard</a></li>
            <li><a href="/surveys.html">Surveys</a></li>
            <li><a href="/quizzes.html">Quizzes</a></li>
            <li><a href="/maintenance.html">Maintenance</a></li>
            <li><a href="/profile.html">Profile</a></li>
            <li><a href="#" @click="logout()">Logout (<?php echo htmlspecialchars($user['name']); ?>)</a></li>
        </ul>
    </nav>

    <main class="container">
        <div class="mb-3">
            <h1>Dashboard</h1>
            <p class="text-muted">Welcome, <?php echo htmlspecialchars($user['name']); ?>! (<?php echo ucfirst($user['role']); ?>)</p>
        </div>

        <!-- Statistics -->
        <div class="grid grid-3 mb-3">
            <div class="stat-card">
                <div class="stat-value"><?php echo count($activeSurveys); ?></div>
                <div class="stat-label">Active Surveys</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo count($activeQuizzes); ?></div>
                <div class="stat-label">Active Quizzes</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $totalUsers; ?></div>
                <div class="stat-label">Total Users</div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="grid grid-2">
            <!-- Recent Surveys -->
            <div class="card">
                <div class="card-header">Active Surveys</div>
                <div class="card-body">
                    <?php if (!empty($activeSurveys)): ?>
                        <ul style="list-style: none;">
                            <?php foreach ($activeSurveys as $survey): ?>
                                <li style="padding: 0.75rem 0; border-bottom: 1px solid #e2e8f0;">
                                    <strong><?php echo htmlspecialchars($survey['title']); ?></strong>
                                    <br>
                                    <small class="text-muted"><?php echo $survey['response_count']; ?> responses</small>
                                    <br>
                                    <a href="/surveys.html" class="btn btn-primary btn-small mt-1">View</a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted">No active surveys</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Quizzes -->
            <div class="card">
                <div class="card-header">Active Quizzes</div>
                <div class="card-body">
                    <?php if (!empty($activeQuizzes)): ?>
                        <ul style="list-style: none;">
                            <?php foreach ($activeQuizzes as $quiz): ?>
                                <li style="padding: 0.75rem 0; border-bottom: 1px solid #e2e8f0;">
                                    <strong><?php echo htmlspecialchars($quiz['title']); ?></strong>
                                    <br>
                                    <small class="text-muted">Passing Score: <?php echo $quiz['passing_score']; ?>%</small>
                                    <br>
                                    <a href="/quizzes.html" class="btn btn-primary btn-small mt-1">Start</a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted">No active quizzes</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">Quick Actions</div>
                <div class="card-body">
                    <a href="/surveys.html" class="btn btn-primary" style="display: block; margin-bottom: 0.5rem;">Take a Survey</a>
                    <a href="/quizzes.html" class="btn btn-primary" style="display: block; margin-bottom: 0.5rem;">Take a Quiz</a>
                    <a href="/maintenance.html" class="btn btn-primary" style="display: block;">Request Maintenance</a>
                </div>
            </div>

            <!-- Account Info -->
            <div class="card">
                <div class="card-header">Your Account</div>
                <div class="card-body">
                    <p><strong>Role:</strong> <?php echo ucfirst($user['role']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                    <a href="/profile.html" class="btn btn-secondary">View Profile</a>
                </div>
            </div>
        </div>
    </main>

    <footer style="text-align: center; padding: 2rem; color: #64748b; border-top: 1px solid #e2e8f0; margin-top: 3rem;">
        <p>&copy; 2025 SVJ Portal - Flat Building Management System</p>
    </footer>

    <script src="/public/js/app.js"></script>
    <script>
        async function logout() {
            try {
                await fetch('/api/auth/logout', { method: 'POST' });
                window.location.href = '/login.html';
            } catch (error) {
                console.error('Logout error:', error);
            }
        }
    </script>
</body>
</html>
