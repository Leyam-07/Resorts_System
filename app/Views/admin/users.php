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
                        <a href="?controller=admin&action=viewUserBookings&id=<?php echo $user['UserID']; ?>" class="btn btn-sm btn-info">View Bookings</a>
                    <?php else: ?>
                        <a href="#" class="btn btn-sm btn-info disabled" aria-disabled="true">View Bookings</a>
                    <?php endif; ?>
                    <a href="?controller=admin&action=editUser&id=<?php echo $user['UserID']; ?>" class="btn btn-sm btn-primary">Edit</a>
                    <?php if (isset($_SESSION['user_id']) && $user['UserID'] == $_SESSION['user_id']): ?>
                        <a href="#" class="btn btn-sm btn-danger disabled" aria-disabled="true">Delete</a>
                    <?php else: ?>
                        <a href="?controller=admin&action=deleteUser&id=<?php echo $user['UserID']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <a href="?controller=admin&action=addUser" class="btn btn-primary">Add New User</a>
    <a href="index.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
    
    <?php require_once __DIR__ . '/../partials/footer.php'; ?>