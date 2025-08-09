<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>User Management</h2>
    <a href="?controller=admin&action=addUser" class="btn btn-primary mb-3">Add New User</a>
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
                    <!-- Actions like edit/delete can be added here -->
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>