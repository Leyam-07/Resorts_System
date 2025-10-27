<?php
$pageTitle = "My Profile";
require_once __DIR__ . '/partials/header.php';
?>

<h2><?= htmlspecialchars($pageTitle) ?></h2>
        <?php if (isset($_GET['status']) && $_GET['status'] === 'updated'): ?>
            <div class="alert alert-success alert-dismissible fade show">Profile updated successfully!<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>
        <?php endif; ?>
        <?php if (isset($_GET['error']) && $_GET['error'] === 'password_mismatch'): ?>
            <div class="alert alert-danger alert-dismissible fade show">Passwords do not match.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin'): ?>
        <div class="text-center mb-4">
            <img src="<?php echo !empty($user['ProfileImageURL']) ? BASE_URL . '/' . htmlspecialchars($user['ProfileImageURL']) : 'https://via.placeholder.com/150'; ?>" alt="Profile Image" class="img-thumbnail rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
        </div>
        <?php endif; ?>
        <form action="?controller=user&action=profile" method="POST" enctype="multipart/form-data">
            <div style="max-height: 78vh; overflow-y: auto; padding-right: 15px;">
                <fieldset id="profile-fieldset" disabled>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin'): ?>
                    <div class="mb-3">
                        <label for="profileImage" class="form-label">Update Profile Image</label>
                        <input class="form-control" type="file" id="profileImage" name="profileImage" accept="image/*">
                    </div>
                    <?php endif; ?>
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
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin'): ?>
                <div class="mb-3">
                    <label for="socials" class="form-label">Socials</label>
                    <textarea class="form-control" id="socials" name="socials" rows="3"><?php echo htmlspecialchars($user['Socials'] ?? ''); ?></textarea>
                    <div class="form-text">Enter social media links, one per line.</div>
                </div>
                <?php endif; ?>
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
            </div>
            <button type="button" id="edit-button" class="btn btn-primary">Edit Profile</button>
            <button type="submit" id="save-button" class="btn btn-primary" style="display: none;">Save Changes</button>
            <button type="button" id="cancel-button" class="btn btn-secondary" style="display: none;">Cancel</button>
            <a href="index.php" id="back-button" class="btn btn-secondary">Back to Dashboard</a>
        </form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const fieldset = document.getElementById('profile-fieldset');
    const editButton = document.getElementById('edit-button');
    const saveButton = document.getElementById('save-button');
    const cancelButton = document.getElementById('cancel-button');
    const backButton = document.getElementById('back-button');

    editButton.addEventListener('click', function () {
        fieldset.disabled = false;
        editButton.style.display = 'none';
        saveButton.style.display = 'inline-block';
        cancelButton.style.display = 'inline-block';
        backButton.style.display = 'none'; // This will hide the back button as well
    });

    cancelButton.addEventListener('click', function () {
        // A simple way to reset is to reload the page.
        window.location.reload();
    });
});
</script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
