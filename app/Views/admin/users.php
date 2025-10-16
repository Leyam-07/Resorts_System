<?php
// Prevent direct access
if (!defined('APP_LOADED')) {
    http_response_code(403);
    include __DIR__ . '/../errors/403.php';
    exit();
}

$pageTitle = "Manage Users";
require_once __DIR__ . '/../partials/header.php';
?>

<h2><?= htmlspecialchars($pageTitle) ?></h2>

<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger">
        <?php
        if ($_GET['error'] === 'cannot_delete_self') {
            echo "You cannot delete your own account.";
        } elseif ($_GET['error'] === 'cannot_delete_admin') {
            echo "You cannot delete another Admin user.";
        } else {
            echo "An unknown error occurred.";
        }
        ?>
    </div>
<?php endif; ?>

    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Phone</th>
                <th>Notes</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
            <tr>
                <td><?= htmlspecialchars($user['UserID']) ?></td>
                <td><?= htmlspecialchars($user['Username']) ?></td>
                <td><?= htmlspecialchars($user['Email']) ?></td>
                <td><?= htmlspecialchars($user['Role']) ?></td>
                <td><?= htmlspecialchars($user['FirstName']) ?></td>
                <td><?= htmlspecialchars($user['LastName']) ?></td>
                <td><?= htmlspecialchars($user['PhoneNumber']) ?></td>
                <td><?= htmlspecialchars($user['Notes']) ?></td>
                <td>
                    <?php if ($user['Role'] === 'Customer'): ?>
                        <button type="button" class="btn btn-sm btn-info view-bookings-btn" data-bs-toggle="modal" data-bs-target="#viewUserBookingsModal" data-user-id="<?php echo $user['UserID']; ?>">View Bookings</button>
                    <?php else: ?>
                        <button type="button" class="btn btn-sm btn-info disabled" aria-disabled="true">View Bookings</button>
                    <?php endif; ?>
                    <button type="button" class="btn btn-sm btn-primary edit-user-btn" data-bs-toggle="modal" data-bs-target="#editUserModal" data-user-id="<?php echo $user['UserID']; ?>">Edit</button>
                    <?php if (isset($_SESSION['user_id']) && $user['UserID'] == $_SESSION['user_id']): ?>
                        <button type="button" class="btn btn-sm btn-danger disabled" aria-disabled="true">Delete</button>
                    <?php else: ?>
                         <button type="button" class="btn btn-sm btn-danger delete-user-btn" data-bs-toggle="modal" data-bs-target="#deleteUserModal" data-user-id="<?php echo $user['UserID']; ?>">Delete</button>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
        Add New User
    </button>
    <a href="index.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>

    <?php require_once __DIR__ . '/user_modals.php'; ?>
    
    <?php require_once __DIR__ . '/../partials/footer.php'; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Edit User Modal - Data Population
        var editUserModal = document.getElementById('editUserModal');
        editUserModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var userId = button.getAttribute('data-user-id');
            
            var form = editUserModal.querySelector('#editUserForm');
            form.action = '?controller=admin&action=editUser&id=' + userId;

            // Fetch user data and populate the form
            fetch('?controller=admin&action=getUserJson&id=' + userId)
                .then(response => response.json())
                .then(user => {
                    if (user.error) {
                        alert(user.error);
                        return;
                    }
                    form.querySelector('#edit-username').value = user.Username;
                    form.querySelector('#edit-email').value = user.Email;
                    form.querySelector('#edit-firstName').value = user.FirstName;
                    form.querySelector('#edit-lastName').value = user.LastName;
                    form.querySelector('#edit-phoneNumber').value = user.PhoneNumber;
                    form.querySelector('#edit-notes').value = user.Notes;
                });
        });

        // Delete User Modal
        var deleteUserModal = document.getElementById('deleteUserModal');
        deleteUserModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var userId = button.getAttribute('data-user-id');
            var deleteBtn = deleteUserModal.querySelector('#deleteUserConfirmBtn');
            deleteBtn.href = '?controller=admin&action=deleteUser&id=' + userId;
        });

        // View User Bookings Modal
        var viewUserBookingsModal = document.getElementById('viewUserBookingsModal');
        viewUserBookingsModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var userId = button.getAttribute('data-user-id');
            var modalBody = viewUserBookingsModal.querySelector('.modal-body');
            modalBody.innerHTML = 'Loading...';
            fetch('?controller=admin&action=getUserBookings&id=' + userId)
                .then(response => response.text())
                .then(html => {
                    modalBody.innerHTML = html;
                });
        });
    });
    </script>