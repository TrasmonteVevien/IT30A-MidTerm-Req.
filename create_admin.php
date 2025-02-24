<?php
include 'config.php';

$admin_username = 'admin';
$admin_password = password_hash('20221185', PASSWORD_DEFAULT); // Change 'admin123' to a secure password

$stmt = $pdo->prepare("INSERT INTO admin (username, password) VALUES (?, ?)");
$stmt->execute([$admin_username, $admin_password]);

echo "Admin user created successfully.";
?>
