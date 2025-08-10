<?php
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
                <td>
                    <a href="?controller=admin&action=editUser&id=<?php echo $user['UserID']; ?>" class="btn btn-sm btn-primary">Edit</a>
                    <a href="?controller=admin&action=deleteUser&id=<?php echo $user['UserID']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <a href="?controller=admin&action=addUser" class="btn btn-primary">Add New User</a>
    <a href="index.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
    
    <?php require_once __DIR__ . '/../partials/footer.php'; ?>