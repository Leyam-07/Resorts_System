<?php
// Prevent direct access
if (!defined('APP_LOADED')) {
    http_response_code(403);
    include __DIR__ . '/../errors/403.php';
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Add New User</h2>
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
                    $errorMsg = 'Failed to add user. Please try again.';
                    break;
            }
            echo '<div class="alert alert-danger" role="alert">' . $errorMsg . '</div>';
        }
    ?>
    <form action="?controller=admin&action=addUser" method="POST">
        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" class="form-control" id="username" name="username" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <div class="mb-3">
            <label for="role" class="form-label">Role</label>
            <select class="form-select" id="role" name="role">
                <option value="Customer">Customer</option>
                <option value="Staff">Staff</option>
                <option value="Admin">Admin</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="firstName" class="form-label">First Name</label>
            <input type="text" class="form-control" id="firstName" name="firstName">
        </div>
        <div class="mb-3">
            <label for="lastName" class="form-label">Last Name</label>
            <input type="text" class="form-control" id="lastName" name="lastName">
        </div>
        <div class="mb-3">
            <label for="phoneNumber" class="form-label">Phone Number</label>
            <input type="text" class="form-control" id="phoneNumber" name="phoneNumber">
        </div>
        <div class="mb-3">
            <label for="notes" class="form-label">Notes</label>
            <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Add User</button>
        <a href="?controller=admin&action=users" class="btn btn-secondary">Cancel</a>
    </form>
</div>
</body>
</html>