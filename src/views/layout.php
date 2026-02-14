<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'SVJ Portal'; ?></title>
    <link rel="stylesheet" href="/public/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="navbar-brand">SVJ Portal</div>
        <ul class="nav-links">
            <li><a href="/index.php">Dashboard</a></li>
            <li><a href="/surveys.php">Surveys</a></li>
            <li><a href="/quizzes.php">Quizzes</a></li>
            <li><a href="/maintenance.php">Maintenance</a></li>
            <li><a href="/logout.php">Logout</a></li>
        </ul>
    </nav>

    <!-- Main Content -->
    <main>
        <?php echo $content ?? ''; ?>
    </main>

    <footer style="text-align: center; padding: 2rem; color: #64748b; border-top: 1px solid #e2e8f0; margin-top: 3rem;">
        <p>&copy; 2025 SVJ Portal - Flat Building Management System</p>
    </footer>

    <script src="/public/js/app.js"></script>
</body>
</html>
