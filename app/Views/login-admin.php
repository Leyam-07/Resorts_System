<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Resort Management</title>
    <!-- Bootstrap CSS -->
    <link href="<?= BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card my-5">
                <div class="card-body">
                    <h3 class="card-title text-center">Admin Login</h3>
                    <hr>
                    <?php
                        if (isset($_GET['error']) && $_GET['error'] == 'invalid_credentials') {
                            echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">Invalid username or password.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                        }
                        if (isset($_GET['registration']) && $_GET['registration'] == 'success') {
                            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">Registration successful! You can now log in.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                        }
                        if (isset($_GET['logout']) && $_GET['logout'] == 'success') {
                            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">You have been logged out successfully.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                        }
                    ?>
                    <!-- Login Form -->
                    <form action="index.php?action=loginAdmin" method="POST">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Login</button>
                        </div>
                    </form>
                    <div class="mt-3 text-center">
                        <p>Don't have an account? <a href="?action=showAdminRegisterForm">Register here</a></p>
                        <p class="mt-2">Or, <a href="?">continue as a guest</a> to preview the site.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="<?= BASE_URL ?>/assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
