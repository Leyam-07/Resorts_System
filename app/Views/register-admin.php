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
                        // $mainAdminExists is passed from UserController::showAdminRegisterForm()
                        $formDisabled = $mainAdminExists ? 'disabled' : '';
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
                    <?php if ($mainAdminExists): ?>
                        <div class="alert alert-danger text-center" role="alert">
                            <strong>Registration Restricted:</strong> A Main Admin account already exists. Only one Main Admin is allowed for system security and control.
                        </div>
                    <?php endif; ?>
                    <!-- Registration Form -->
                    <form action="/ResortsSystem/public/index.php?action=registerAdmin" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                        <input type="hidden" name="role" value="Admin">
                        <fieldset <?= $formDisabled ?>>
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($oldInput['username'] ?? ''); ?>" required <?= $formDisabled ?>>
                            <div class="invalid-feedback">Please choose a username.</div>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($oldInput['email'] ?? ''); ?>" required <?= $formDisabled ?>>
                            <div class="invalid-feedback">Please provide a valid email.</div>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required <?= $formDisabled ?>>
                            <div class="invalid-feedback">Password must be at least 8 characters long.</div>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required <?= $formDisabled ?>>
                            <div class="invalid-feedback">Passwords do not match.</div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="firstName" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="firstName" name="firstName" value="<?php echo htmlspecialchars($oldInput['firstName'] ?? ''); ?>" <?= $formDisabled ?>>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="lastName" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="lastName" name="lastName" value="<?php echo htmlspecialchars($oldInput['lastName'] ?? ''); ?>" <?= $formDisabled ?>>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="phoneNumber" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phoneNumber" name="phoneNumber" value="<?php echo htmlspecialchars($oldInput['phoneNumber'] ?? ''); ?>" <?= $formDisabled ?>>
                        </div>
                        <div class="mb-3">
                            <label for="socials" class="form-label">Socials</label>
                            <textarea class="form-control" id="socials" name="socials" rows="3" required <?= $formDisabled ?>><?php echo htmlspecialchars($oldInput['socials'] ?? ''); ?></textarea>
                            <div class="form-text">Enter social media links, one per line.</div>
                            <div class="invalid-feedback">Please enter social media links.</div>
                        </div>
                        <div class="mb-3">
                            <label for="profileImage" class="form-label">Profile Image</label>
                            <input class="form-control" type="file" id="profileImage" name="profileImage" accept="image/*" required <?= $formDisabled ?>>
                            <div class="invalid-feedback">Please upload a profile image.</div>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary" <?= $formDisabled ?>>Register Admin</button>
                        </div>
                        </fieldset>
                    </form>
                    <div class="mt-3 text-center">
                        <p>Go to admin login? <a href="?action=loginAdmin">Login here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="<?= BASE_URL ?>/assets/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const username = document.getElementById('username');
        const email = document.getElementById('email');
        const password = document.getElementById('password');
        const confirm_password = document.getElementById('confirm_password');

        async function checkIfExists(field, value) {
            const response = await fetch('?controller=validation&action=checkUserExists', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ [field]: value })
            });
            const data = await response.json();
            return data.exists;
        }

        function validateField(field, validationRule, serverRule = null) {
            if (!validationRule(field.value)) {
                field.classList.add('is-invalid');
                field.classList.remove('is-valid');
                return;
            }

            if (serverRule) {
                serverRule(field.id, field.value).then(exists => {
                    if (exists) {
                        field.classList.add('is-invalid');
                        field.classList.remove('is-valid');
                        field.nextElementSibling.textContent = `${field.id.charAt(0).toUpperCase() + field.id.slice(1)} is already taken.`;
                    } else {
                        field.classList.add('is-valid');
                        field.classList.remove('is-invalid');
                    }
                });
            } else {
                field.classList.add('is-valid');
                field.classList.remove('is-invalid');
            }
        }

        username.addEventListener('input', () => validateField(username, value => value.trim().length > 0, checkIfExists));
        email.addEventListener('input', () => validateField(email, value => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value), checkIfExists));
        
        function validatePasswords() {
            validateField(password, value => value.length >= 8);
            validateField(confirm_password, value => value === password.value && value.length > 0);
        }

        password.addEventListener('input', validatePasswords);
        confirm_password.addEventListener('input', validatePasswords);

        const form = document.querySelector('.needs-validation');
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }

            // Trigger validation feedback on all fields
            validateField(username, value => value.trim().length > 0);
            validateField(email, value => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value));
            validatePasswords();

            form.classList.add('was-validated');
        }, false);
    });
</script>
</body>
</html>
