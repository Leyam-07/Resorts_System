<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Registration - Resort Management</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card my-5">
                <div class="card-body">
                    <h3 class="card-title text-center">Create an Admin Account</h3>
                    <hr>
                    <?php
                        if (isset($_GET['error'])) {
                            $errorMsg = '';
                            switch ($_GET['error']) {
                                case 'username_exists':
                                    $errorMsg = 'Username already taken. Please choose another.';
                                    break;
                                case 'email_exists':
                                    $errorMsg = 'An account with this email already exists.';
                                    break;
                                default:
                                    $errorMsg = 'Registration failed. Please try again.';
                                    break;
                            }
                            echo '<div class="alert alert-danger" role="alert">' . $errorMsg . '</div>';
                        }
                    ?>
                    <!-- Registration Form -->
                    <form action="../Controllers/UserController.php?action=registerAdmin" method="POST">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="firstName" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="firstName" name="firstName">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="lastName" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="lastName" name="lastName">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="phoneNumber" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phoneNumber" name="phoneNumber">
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Register Admin</button>
                        </div>
                    </form>
                    <div class="mt-3 text-center">
                        <p>Go to regular login? <a href="login.php">Login here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>