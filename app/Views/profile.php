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
        <form id="profileForm" action="?controller=user&action=profile" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
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
                        <div class="invalid-feedback">Please enter a username.</div>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['Email']); ?>" required>
                        <div class="invalid-feedback">Please enter a valid email address.</div>
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
                    <div class="invalid-feedback">Password must be at least 8 characters long.</div>
                </div>
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                    <div class="invalid-feedback">Passwords do not match.</div>
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    const profileForm = document.getElementById('profileForm');
    if (profileForm) {
        const username = document.getElementById('username');
        const email = document.getElementById('email');
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        const currentUserId = <?php echo json_encode($_SESSION['user_id'] ?? null); ?>;

        const checkIfExists = async (field, value) => {
            try {
                const response = await fetch('?controller=validation&action=checkUserExists', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ [field.name]: value, excludeUserId: currentUserId })
                });
                const data = await response.json();
                return data.exists;
            } catch (error) {
                console.error('Validation check failed:', error);
                return false; // Fail safe
            }
        };

        const validateField = async (field, validationFn, asyncValidationFn = null) => {
            const value = field.value;
            const feedback = field.nextElementSibling;
            let isValid = validationFn(value);

            if (isValid && asyncValidationFn) {
                const exists = await asyncValidationFn(field, value);
                if (exists) {
                    isValid = false;
                    feedback.textContent = `${field.name.charAt(0).toUpperCase() + field.name.slice(1)} already exists.`;
                }
            }

            if (isValid) {
                field.classList.add('is-valid');
                field.classList.remove('is-invalid');
            } else {
                field.classList.add('is-invalid');
                field.classList.remove('is-valid');
                if (feedback.textContent === '') {
                    // Restore default message if it was cleared
                    feedback.textContent = field.id === 'email' ? 'Please enter a valid email address.' : 'Please enter a username.';
                }
            }
            return isValid;
        };
        
        const validatePasswords = () => {
            const passValue = password.value;
            const confirmValue = confirmPassword.value;
            const feedback = confirmPassword.nextElementSibling;

            // Only validate if a new password is being entered
            if (passValue.length === 0 && confirmValue.length === 0) {
                password.classList.remove('is-invalid', 'is-valid');
                confirmPassword.classList.remove('is-invalid', 'is-valid');
                return true;
            }
            
            let isPassValid = passValue.length >= 8;
            if (isPassValid) {
                password.classList.add('is-valid');
                password.classList.remove('is-invalid');
            } else {
                password.classList.add('is-invalid');
                password.classList.remove('is-valid');
            }

            let isConfirmValid = passValue === confirmValue;
            if (isConfirmValid) {
                confirmPassword.classList.add('is-valid');
                confirmPassword.classList.remove('is-invalid');
            } else {
                confirmPassword.classList.add('is-invalid');
                confirmPassword.classList.remove('is-valid');
            }

            return isPassValid && isConfirmValid;
        };

        username.addEventListener('input', () => validateField(username, value => value.trim().length > 0, checkIfExists));
        email.addEventListener('input', () => validateField(email, value => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value), checkIfExists));
        password.addEventListener('input', validatePasswords);
        confirmPassword.addEventListener('input', validatePasswords);

        profileForm.addEventListener('submit', function (event) {
            let isFormValid = true;
            if (!validateField(username, value => value.trim().length > 0)) isFormValid = false;
            if (!validateField(email, value => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value))) isFormValid = false;
            if (!validatePasswords()) isFormValid = false;
            
            if (!isFormValid || !profileForm.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            profileForm.classList.add('was-validated');
        }, false);
    }
});
</script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
