<?php
session_start();

// Check if the user is logged in, otherwise redirect to login page
if (!isset($_SESSION['user_id'])) {
    header('Location: ../app/Views/login.php');
    exit();
}

// Get user data from session
$username = $_SESSION['username'];
$role = $_SESSION['role'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Resort Management</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="../app/Controllers/UserController.php?action=logout">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3>Welcome, <?php echo htmlspecialchars($username); ?>!</h3>
                </div>
                <div class="card-body">
                    <?php if ($role === 'Admin'): ?>
                        <h5 class="card-title">Admin Dashboard</h5>
                        <p class="card-text">You have full access to the system.</p>
                        <!-- Admin-specific content goes here -->
                    <?php elseif ($role === 'Staff'): ?>
                        <h5 class="card-title">Staff Dashboard</h5>
                        <p class="card-text">You can view daily tasks and schedules.</p>
                        <!-- Staff-specific content goes here -->
                    <?php else: ?>
                        <h5 class="card-title">Customer Dashboard</h5>
                        <p class="card-text">Welcome to your personal dashboard.</p>
                        <!-- Customer-specific content goes here -->
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>