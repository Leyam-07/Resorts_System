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
        } elseif ($_GET['error'] === 'only_main_admin_can_delete_admins') {
            echo "Only the Main Admin can delete other admin accounts.";
        } elseif ($_GET['error'] === 'only_main_admin_can_create_admins') {
            echo "Only the Main Admin can create other admin accounts.";
        } elseif ($_GET['error'] === 'admin_type_exists') {
            echo "An admin with this sub-admin role already exists. Please choose a different role.";
        } elseif ($_GET['error'] === 'only_main_admin_can_edit_admins') {
            echo "Only the Main Admin can edit other admin accounts.";
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
                <th>Admin Type</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Phone</th>
                <th>Socials</th>
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
                <td>
                    <?= htmlspecialchars($user['Role']) ?>
                    <?php if ($user['Role'] === 'Admin' && $user['UserID'] == $_SESSION['user_id']): ?>
                        <span class="badge bg-warning text-dark">You</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($user['AdminType']): ?>
                        <span class="badge bg-info">
                            <?= htmlspecialchars(User::getAdminTypeDisplay($user['AdminType'])) ?>
                        </span>
                    <?php else: ?>
                        
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($user['FirstName']) ?></td>
                <td><?= htmlspecialchars($user['LastName']) ?></td>
                <td><?= htmlspecialchars($user['PhoneNumber']) ?></td>
                <td>
                    <?php if (!empty($user['Socials'])): ?>
                        <small>
                            <?php
                            $socialsUrls = preg_split("/\r\n|\n|\r/", $user['Socials']);
                            foreach ($socialsUrls as $socialsUrl) {
                                if (empty(trim($socialsUrl))) continue;

                                $socialsUrlForHref = trim($socialsUrl);
                                if (strpos($socialsUrlForHref, 'http') !== 0) {
                                    $socialsUrlForHref = 'https://' . $socialsUrlForHref;
                                }
                                
                                $cleanedUrl = str_replace(['https://www.', 'http://www.', 'https://', 'http://'], '', trim($socialsUrl));
                                
                                $displayUrl = $cleanedUrl;
                                if (strlen($displayUrl) > 25) {
                                    $displayUrl = substr($displayUrl, 0, 25) . '...';
                                }
                                ?>
                                <a href="<?= htmlspecialchars($socialsUrlForHref) ?>" target="_blank" title="<?= htmlspecialchars($cleanedUrl) ?>">
                                    <?= htmlspecialchars($displayUrl) ?>
                                </a><br>
                            <?php } ?>
                        </small>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($user['Notes']) ?></td>
                <td>
                    <div class="btn-group" role="group">
                        <?php if ($user['Role'] === 'Customer'): ?>
                            <button type="button" class="btn btn-sm btn-info view-bookings-btn" data-bs-toggle="modal" data-bs-target="#viewUserBookingsModal" data-user-id="<?php echo $user['UserID']; ?>">View Bookings</button>
                        <?php else: ?>
                            <button type="button" class="btn btn-sm btn-info disabled" aria-disabled="true">View Bookings</button>
                        <?php endif; ?>
                        <?php
                        $isMainAdmin = User::isMainAdmin($_SESSION['user_id']);
                        $canEditThisUser = ($user['UserID'] == $_SESSION['user_id']) || ($isMainAdmin && $user['Role'] !== 'Admin') || ($isMainAdmin && $user['Role'] === 'Admin');
                        $canDeleteThisUser = ($user['UserID'] != $_SESSION['user_id']) && ($isMainAdmin || $user['Role'] !== 'Admin');
                        ?>
                        <?php if ($canEditThisUser): ?>
                           <button type="button" class="btn btn-sm btn-primary edit-user-btn" data-bs-toggle="modal" data-bs-target="#editUserModal" data-user-id="<?php echo $user['UserID']; ?>">Edit</button>
                        <?php else: ?>
                           <button type="button" class="btn btn-sm btn-primary" disabled>Edit</button>
                        <?php endif; ?>
                        <?php if ($canDeleteThisUser): ?>
                            <button type="button" class="btn btn-sm btn-danger delete-user-btn" data-bs-toggle="modal" data-bs-target="#deleteUserModal" data-user-id="<?php echo $user['UserID']; ?>">Delete</button>
                        <?php else: ?>
                            <button type="button" class="btn btn-sm btn-danger disabled" aria-disabled="true">Delete</button>
                        <?php endif; ?>
                    </div>
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
        const currentUserId = <?php echo json_encode($_SESSION['user_id']); ?>;
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
                    form.querySelector('#edit-socials').value = user.Socials;
                    form.querySelector('#edit-notes').value = user.Notes;

                    const imageContainer = form.querySelector('#edit-profile-image-container');
                    const imagePreview = form.querySelector('#edit-profile-image-preview');
                    const uploadContainer = form.querySelector('#profile-image-upload-container');
                    const socialsContainer = form.querySelector('#edit-socials').closest('.mb-3');
                    const notesContainer = form.querySelector('#edit-notes').closest('.mb-3');
                    const baseUrl = "<?php echo BASE_URL; ?>";

                    if (user.Role === 'Admin') {
                        imageContainer.style.display = 'block';
                        uploadContainer.style.display = 'block';
                        socialsContainer.style.display = 'block';

                        if (user.UserID == currentUserId) {
                            notesContainer.style.display = 'none';
                        } else {
                            notesContainer.style.display = 'block';
                        }

                        if (user.ProfileImageURL) {
                            imagePreview.src = baseUrl + '/' + user.ProfileImageURL;
                        } else {
                            imagePreview.src = 'https://via.placeholder.com/150';
                        }
                    } else {
                        imageContainer.style.display = 'none';
                        uploadContainer.style.display = 'none';
                        socialsContainer.style.display = 'none';
                        notesContainer.style.display = 'block';
                    }
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

    // Toggle Admin Type field when creating new user
    function toggleAddAdminTypeField() {
        const roleSelect = document.getElementById('add-role');
        const adminTypeContainer = document.getElementById('add-adminType-container');
        if (roleSelect && adminTypeContainer) {
            if (roleSelect.value === 'Admin') {
                adminTypeContainer.style.display = 'block';
            } else {
                adminTypeContainer.style.display = 'none';
            }
        }
    }
    </script>