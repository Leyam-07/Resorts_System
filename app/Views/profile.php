<?php
$pageTitle = "My Profile";
require_once __DIR__ . '/partials/header.php';
?>

<h2><?= htmlspecialchars($pageTitle) ?></h2>
        <?php if (isset($_GET['status']) && $_GET['status'] === 'updated'): ?>
            <div class="alert alert-success">Profile updated successfully!</div>
        <?php endif; ?>
        <?php if (isset($_GET['error']) && $_GET['error'] === 'password_mismatch'): ?>
            <div class="alert alert-danger">Passwords do not match.</div>
        <?php endif; ?>
        <form action="?controller=user&action=profile" method="POST">
            <div style="max-height: 78vh; overflow-y: auto; padding-right: 15px;">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['Username']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['Email']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="firstName" class="form-label">First Name</label>
                <input type="text" class="form-control" id="firstName" name="firstName" value="<?php echo htmlspecialchars($user['FirstName']); ?>">
            </div>
            <div class="mb-3">
                <label for="lastName" class="form-label">Last Name</label>
                <input type="text" class="form-control" id="lastName" name="lastName" value="<?php echo htmlspecialchars($user['LastName']); ?>">
            </div>
            <div class="mb-3">
                <label for="phoneNumber" class="form-label">Phone Number</label>
                <input type="text" class="form-control" id="phoneNumber" name="phoneNumber" value="<?php echo htmlspecialchars($user['PhoneNumber']); ?>">
            </div>
            <hr>
            <h5>Change Password</h5>
            <div class="mb-3">
                <label for="password" class="form-label">New Password</label>
                <input type="password" class="form-control" id="password" name="password">
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm New Password</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password">
            </div>
            <div class="form-text mb-3">Leave blank if you don't want to change your password.</div>
            </div>
            <button type="submit" class="btn btn-primary">Update Profile</button>
            <a href="?controller=booking&action=showMyBookings" class="btn btn-info">My Bookings</a>
            <a href="index.php" class="btn btn-secondary">Back to Dashboard</a>
        </form>

<?php require_once __DIR__ . '/partials/footer.php'; ?>