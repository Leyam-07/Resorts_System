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
        } elseif ($_GET['error'] === 'only_customers_can_be_deactivated') {
            echo "Only customer accounts can be activated or deactivated.";
        } else {
            echo "An unknown error occurred.";
        }
        ?>
    </div>
<?php endif; ?>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th style="width: 50px;">ID</th>
                    <th>User</th>
                    <th>Contact Info</th>
                    <th>Role & Status</th>
                    <th>Socials</th>
                    <th>Notes</th>
                    <th>Assigned Resorts</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><small class="text-muted">#<?= htmlspecialchars($user['UserID']) ?></small></td>
                    <td>
                        <strong><?= htmlspecialchars($user['Username']) ?></strong>
                        <div class="small text-muted"><?= htmlspecialchars($user['FirstName'] . ' ' . $user['LastName']) ?></div>
                    </td>
                    <td>
                        <div><i class="fas fa-envelope text-muted me-1" style="width: 16px;"></i> <?= htmlspecialchars($user['Email']) ?></div>
                        <?php if (!empty($user['PhoneNumber'])): ?>
                            <div class="small text-muted"><i class="fas fa-phone text-muted me-1" style="width: 16px;"></i> <?= htmlspecialchars($user['PhoneNumber']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="d-flex flex-column gap-1">
                            <span>
                                <span class="badge bg-secondary"><?= htmlspecialchars($user['Role']) ?></span>
                                <?php if ($user['Role'] === 'Admin' && $user['UserID'] == $_SESSION['user_id']): ?>
                                    <span class="badge bg-warning text-dark">You</span>
                                <?php endif; ?>
                            </span>
                            
                            <?php if ($user['AdminType']): ?>
                                <span>
                                    <span class="badge bg-info text-dark">
                                        <?= htmlspecialchars(User::getAdminTypeDisplay($user['AdminType'])) ?>
                                    </span>
                                </span>
                            <?php endif; ?>

                            <?php if ($user['Role'] === 'Customer'): ?>
                                <span>
                                    <span class="badge bg-<?= $user['IsActive'] ? 'success' : 'danger' ?>">
                                        <?= $user['IsActive'] ? 'Active' : 'Deactivated' ?>
                                    </span>
                                </span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <?php if (!empty($user['Socials'])): ?>
                            <div class="d-flex gap-2 flex-wrap" style="max-width: 100px;">
                                <?php
                                $socialsUrls = preg_split("/\r\n|\n|\r/", $user['Socials']);
                                foreach ($socialsUrls as $socialsUrl) {
                                    if (empty(trim($socialsUrl))) continue;
                                    $socialsUrlForHref = trim($socialsUrl);
                                    if (strpos($socialsUrlForHref, 'http') !== 0) {
                                        $socialsUrlForHref = 'https://' . $socialsUrlForHref;
                                    }
                                    
                                    // Detect Icon
                                    $iconClass = 'fas fa-link'; // Default
                                    if (strpos($socialsUrlForHref, 'facebook.com') !== false || strpos($socialsUrlForHref, 'fb.com') !== false) $iconClass = 'fab fa-facebook text-primary';
                                    elseif (strpos($socialsUrlForHref, 'twitter.com') !== false || strpos($socialsUrlForHref, 'x.com') !== false) $iconClass = 'fab fa-twitter text-info';
                                    elseif (strpos($socialsUrlForHref, 'instagram.com') !== false) $iconClass = 'fab fa-instagram text-danger';
                                    elseif (strpos($socialsUrlForHref, 'linkedin.com') !== false) $iconClass = 'fab fa-linkedin text-primary';
                                    elseif (strpos($socialsUrlForHref, 'youtube.com') !== false) $iconClass = 'fab fa-youtube text-danger';
                                    elseif (strpos($socialsUrlForHref, 'tiktok.com') !== false) $iconClass = 'fab fa-tiktok text-dark';
                                    ?>
                                    <a href="<?= htmlspecialchars($socialsUrlForHref) ?>" target="_blank" class="text-decoration-none" title="<?= htmlspecialchars($socialsUrlForHref) ?>">
                                        <i class="<?= $iconClass ?> fa-lg"></i>
                                    </a>
                                <?php } ?>
                            </div>
                        <?php else: ?>
                            <span class="text-muted small">-</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (!empty($user['Notes'])): ?>
                            <div class="text-truncate" style="max-width: 150px;" title="<?= htmlspecialchars($user['Notes']) ?>">
                                <?= htmlspecialchars($user['Notes']) ?>
                            </div>
                        <?php else: ?>
                            <span class="text-muted small">-</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php
                        if ($user['Role'] === 'Staff' && !empty($user['AssignedResorts'])) {
                            $assignedCount = count(explode(', ', $user['AssignedResorts']));
                            $totalResorts = count($resorts);
                            if ($assignedCount === $totalResorts) {
                                echo '<span class="badge bg-primary">All Resorts</span>';
                            } else {
                                echo '<div class="text-truncate" style="max-width: 150px;" title="' . htmlspecialchars($user['AssignedResorts']) . '">';
                                $resortList = explode(', ', $user['AssignedResorts']);
                                foreach($resortList as $resortName) {
                                    echo '<span class="badge bg-secondary me-1">' . htmlspecialchars($resortName) . '</span>';
                                }
                                echo '</div>';
                            }
                        } else {
                            echo '<span class="text-muted small">-</span>';
                        }
                        ?>
                    </td>
                    <td>
                    <div class="btn-group" role="group">
                        <?php if ($user['Role'] === 'Customer'): ?>
                            <button type="button" class="btn btn-sm btn-info view-bookings-btn" data-bs-toggle="modal" data-bs-target="#viewUserBookingsModal" data-user-id="<?= $user['UserID'] ?>">View Bookings</button>
                        <?php elseif ($user['Role'] === 'Staff'): ?>
                            <button type="button" class="btn btn-sm btn-success assign-resorts-btn" data-bs-toggle="modal" data-bs-target="#assignedResortsModal" data-user-id="<?= $user['UserID'] ?>" data-username="<?= htmlspecialchars($user['Username']) ?>">Assigned Resorts</button>
                        <?php else: ?>
                            <button type="button" class="btn btn-sm btn-info disabled" aria-disabled="true">View Bookings</button>
                        <?php endif; ?>
                        <?php
                        $isMainAdmin = User::isMainAdmin($_SESSION['user_id']);
                        // Allow editing if: 1) it's the current user, OR 2) the current user is Main Admin AND the target user is NOT a Customer.
                        $canEditThisUser = ($user['UserID'] == $_SESSION['user_id']) || ($isMainAdmin && $user['Role'] !== 'Customer');
                        ?>
                        <?php if ($canEditThisUser): ?>
                            <button type="button" class="btn btn-sm btn-primary edit-user-btn d-flex align-items-center justify-content-center" data-bs-toggle="modal" data-bs-target="#editUserModal" data-user-id="<?php echo $user['UserID']; ?>" style="min-width: 60px;">Edit</button>
                        <?php else: ?>
                            <button type="button" class="btn btn-sm btn-primary d-flex align-items-center justify-content-center" disabled style="min-width: 60px;">Edit</button>
                        <?php endif; ?>

                        <?php if ($user['Role'] === 'Customer'): ?>
                            <a href="?controller=admin&action=toggleUserActivation&id=<?= $user['UserID'] ?>" class="btn btn-sm btn-<?= $user['IsActive'] ? 'warning' : 'success' ?> d-flex align-items-center justify-content-center" style="min-width: 85px;">
                                <?= $user['IsActive'] ? 'Deactivate' : 'Activate' ?>
                            </a>
                        <?php else:
                            $canDeleteThisUser = ($user['UserID'] != $_SESSION['user_id']) && ($isMainAdmin || $user['Role'] !== 'Admin');
                            if ($canDeleteThisUser): ?>
                                <button type="button" class="btn btn-sm btn-danger delete-user-btn d-flex align-items-center justify-content-center" data-bs-toggle="modal" data-bs-target="#deleteUserModal" data-user-id="<?php echo $user['UserID']; ?>" style="min-width: 85px;">Delete</button>
                            <?php else: ?>
                                <button type="button" class="btn btn-sm btn-danger disabled d-flex align-items-center justify-content-center" aria-disabled="true" style="min-width: 85px;">Delete</button>
                            <?php endif; ?>
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

        // Assigned Resorts Modal
        var assignedResortsModal = document.getElementById('assignedResortsModal');
        assignedResortsModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var userId = button.getAttribute('data-user-id');
            var username = button.getAttribute('data-username');
            
            var modalTitle = assignedResortsModal.querySelector('.modal-title');
            var form = assignedResortsModal.querySelector('#assignResortsForm');
            var resortsContainer = assignedResortsModal.querySelector('#resorts-checkbox-container');
            
            modalTitle.textContent = 'Assign Resorts for ' + username;
            form.action = '?controller=admin&action=updateStaffResortAssignments';
            form.querySelector('input[name="userId"]').value = userId;
            resortsContainer.innerHTML = 'Loading...';

            // Fetch assigned resorts for the user
            fetch('?controller=admin&action=getStaffResortAssignmentsJson&id=' + userId)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        resortsContainer.innerHTML = '<div class="alert alert-danger">' + data.error + '</div>';
                        return;
                    }
                    
                    const assignedIds = data.assigned || [];
                    const allResorts = <?php echo json_encode($resorts); ?>;
                    
                    let checkboxesHtml = '';
                    allResorts.forEach(resort => {
                        const isChecked = assignedIds.includes(parseInt(resort.resortId));
                        checkboxesHtml += `
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="resort_ids[]" value="${resort.resortId}" id="resort-${resort.resortId}" ${isChecked ? 'checked' : ''}>
                                <label class="form-check-label" for="resort-${resort.resortId}">
                                    ${resort.name}
                                </label>
                            </div>
                        `;
                    });
                    resortsContainer.innerHTML = checkboxesHtml;
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
