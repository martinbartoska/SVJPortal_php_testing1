<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SVJ Portal - Dashboard</title>
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
            <li><a href="/login.html">Login</a></li>
        </ul>
    </nav>

    <main class="container" x-data="dashboardApp()">
        <div class="mb-3">
            <h1>Dashboard</h1>
            <p class="text-muted">Welcome to SVJ Portal - Flat Building Management System</p>
        </div>

        <!-- Statistics -->
        <div class="grid grid-3 mb-3">
            <div class="stat-card">
                <div class="stat-value" x-text="stats.active_surveys">0</div>
                <div class="stat-label">Active Surveys</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" x-text="stats.active_quizzes">0</div>
                <div class="stat-label">Active Quizzes</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" x-text="stats.total_users">0</div>
                <div class="stat-label">Total Users</div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="grid grid-2">
            <div class="card">
                <div class="card-header">Active Surveys</div>
                <div class="card-body">
                    <p class="text-muted">Participate in community surveys to share your feedback.</p>
                    <a href="/surveys.html" class="btn btn-primary mt-2">View Surveys</a>
                </div>
            </div>

            <div class="card">
                <div class="card-header">Active Quizzes</div>
                <div class="card-body">
                    <p class="text-muted">Test your knowledge with our interactive quizzes.</p>
                    <a href="/quizzes.html" class="btn btn-primary mt-2">View Quizzes</a>
                </div>
            </div>

            <div class="card">
                <div class="card-header">Maintenance Requests</div>
                <div class="card-body">
                    <p class="text-muted">Report maintenance issues in your flat.</p>
                    <a href="/maintenance.html" class="btn btn-primary mt-2">Submit Request</a>
                </div>
            </div>

            <div class="card">
                <div class="card-header">Building Information</div>
                <div class="card-body">
                    <p class="text-muted">Find important notices and announcements.</p>
                    <a href="/announcements.html" class="btn btn-primary mt-2">View Announcements</a>
                </div>
            </div>
        </div>
    </main>

    <footer style="text-align: center; padding: 2rem; color: #64748b; border-top: 1px solid #e2e8f0; margin-top: 3rem;">
        <p>&copy; 2025 SVJ Portal - Flat Building Management System</p>
    </footer>

    <script src="/public/js/app.js"></script>
    <script>
        function dashboardApp() {
            return {
                stats: {
                    active_surveys: 0,
                    active_quizzes: 0,
                    total_users: 0
                },
                init() {
                    this.loadStats();
                },
                async loadStats() {
                    try {
                        // In production, these would fetch from API
                        this.stats.active_surveys = 3;
                        this.stats.active_quizzes = 2;
                        this.stats.total_users = 45;
                    } catch (error) {
                        console.error('Failed to load stats:', error);
                    }
                }
            }
        }
    </script>
</body>
</html>
