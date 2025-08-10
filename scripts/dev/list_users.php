<?php

require_once __DIR__ . '/../../app/Models/User.php';

echo "--- Fetching All Users from Database ---\n\n";

$users = User::findAll();

if (empty($users)) {
    echo "No users found in the database.\n";
    echo "Please register an Admin and a Customer account through the web interface.\n";
    exit;
}

print_r($users);

echo "\n--- User List Complete ---\n";

?>