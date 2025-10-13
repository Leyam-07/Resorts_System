<?php
$pageTitle = "My Profile";
require_once __DIR__ . '/partials/guest_header.php';
?>

<h2><?= htmlspecialchars($pageTitle) ?></h2>

<div class="alert alert-info">
    Please <a href="?action=login">login</a> or <a href="?action=showRegisterForm">register</a> to view and manage your profile.
</div>

<form>
    <fieldset disabled>
        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" class="form-control" id="username" name="username" placeholder="Your Username" >
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" placeholder="your.email@example.com">
        </div>
        <div class="mb-3">
            <label for="firstName" class="form-label">First Name</label>
            <input type="text" class="form-control" id="firstName" name="firstName" placeholder="First Name">
        </div>
        <div class="mb-3">
            <label for="lastName" class="form-label">Last Name</label>
            <input type="text" class="form-control" id="lastName" name="lastName" placeholder="Last Name">
        </div>
        <div class="mb-3">
            <label for="phoneNumber" class="form-label">Phone Number</label>
            <input type="text" class="form-control" id="phoneNumber" name="phoneNumber" placeholder="Phone Number">
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
    </fieldset>
</form>

<?php require_once __DIR__ . '/partials/footer.php'; ?>