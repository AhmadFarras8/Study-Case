<?php
// seed_admin.php — run ONCE, then delete this file
include 'config.php';

$name = 'Shop Admin';
$email = 'admin@coffeeshop';
$password = password_hash('admin123', PASSWORD_DEFAULT);
$role = 'admin';

$stmt = mysqli_prepare($conn, "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $password, $role);
mysqli_stmt_execute($stmt);

echo "Admin created. Email: admin@coffeeshop, Password: admin123. Delete this file now!";
?>
