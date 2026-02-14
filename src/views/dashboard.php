<?php ob_start(); ?>

<div class="container">
    <div class="mb-3">
        <h1>Dashboard</h1>
        <p class="text-muted">Welcome, <?php echo htmlspecialchars($user['name'] ?? 'User'); ?>!</p>
    </div>

    <!-- Statistics -->
    <div class="grid grid-3 mb-3">
        <div class="stat-card">
            <div class="stat-value"><?php echo $stats['active_surveys'] ?? 0; ?></div>
            <div class="stat-label">Active Surveys</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?php echo $stats['active_quizzes'] ?? 0; ?></div>
            <div class="stat-label">Active Quizzes</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?php echo $stats['total_users'] ?? 0; ?></div>
            <div class="stat-label">Total Users</div>
        </div>
    </div>

    <div class="grid grid-2">
        <!-- Recent Surveys -->
        <div class="card">
            <div class="card-header">Active Surveys</div>
            <div class="card-body">
                <?php if (!empty($recent_surveys)): ?>
                    <ul style="list-style: none;">
                        <?php foreach ($recent_surveys as $survey): ?>
                            <li style="padding: 0.75rem 0; border-bottom: 1px solid #e2e8f0;">
                                <strong><?php echo htmlspecialchars($survey['title']); ?></strong>
                                <br>
                                <small class="text-muted"><?php echo $survey['response_count'] ?? 0; ?> responses</small>
                                <br>
                                <a href="/survey.php?id=<?php echo $survey['id']; ?>" class="btn btn-primary btn-small mt-1">View</a>
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
                <?php if (!empty($recent_quizzes)): ?>
                    <ul style="list-style: none;">
                        <?php foreach ($recent_quizzes as $quiz): ?>
                            <li style="padding: 0.75rem 0; border-bottom: 1px solid #e2e8f0;">
                                <strong><?php echo htmlspecialchars($quiz['title']); ?></strong>
                                <br>
                                <small class="text-muted">Passing Score: <?php echo $quiz['passing_score']; ?>%</small>
                                <br>
                                <a href="/quiz.php?id=<?php echo $quiz['id']; ?>" class="btn btn-primary btn-small mt-1">Start</a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-muted">No active quizzes</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require 'layout.php';
?>
