<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Registration - Resort Management</title>
    <!-- Bootstrap CSS -->
    <link href="<?= BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet">
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
                        $errorMsg = '';
                        $oldInput = isset($_SESSION['old_input']) ? $_SESSION['old_input'] : [];
                        unset($_SESSION['old_input']);

                        if (isset($_SESSION['error_message']) && !empty($_SESSION['error_message'])) {
                            $errorMsg = $_SESSION['error_message'];
                            unset($_SESSION['error_message']);
                        } elseif (isset($_GET['error'])) {
                            switch ($_GET['error']) {
                                case 'username_exists':
                                    $errorMsg = 'Username already taken. Please choose another.';
                                    break;
                                case 'email_exists':
                                    $errorMsg = 'An account with this email already exists.';
                                    break;
                                case 'password_mismatch':
                                    $errorMsg = 'Passwords do not match. Please try again.';
                                    break;
                                default:
                                    $errorMsg = 'Registration failed. Please try again.';
                                    break;
                            }
                        }
                        if (!empty($errorMsg)) {
                            echo '<div class="alert alert-danger" role="alert">' . $errorMsg . '</div>';
                        }
                    ?>
                    <!-- Registration Form -->
                    <form action="/ResortsSystem/public/index.php?action=registerAdmin" method="POST">
                        <input type="hidden" name="role" value="Admin">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($oldInput['username'] ?? ''); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($oldInput['email'] ?? ''); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="firstName" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="firstName" name="firstName" value="<?php echo htmlspecialchars($oldInput['firstName'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="lastName" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="lastName" name="lastName" value="<?php echo htmlspecialchars($oldInput['lastName'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="phoneNumber" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phoneNumber" name="phoneNumber" value="<?php echo htmlspecialchars($oldInput['phoneNumber'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="socials" class="form-label">Socials</label>
                            <textarea class="form-control" id="socials" name="socials" rows="3" required><?php echo htmlspecialchars($oldInput['socials'] ?? ''); ?></textarea>
                            <div class="form-text">Enter social media links, one per line.</div>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Register Admin</button>
                        </div>
                    </form>
                    <div class="mt-3 text-center">
                        <p>Go to regular login? <a href="/ResortsSystem/public/index.php?action=login">Login here</a></p>
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
