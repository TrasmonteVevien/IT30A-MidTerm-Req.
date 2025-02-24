<?php
include 'config.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Delete the failed attempt
    $stmt = $pdo->prepare("DELETE FROM login_attempts WHERE id = ?");
    $stmt->execute([$id]);
    
    // Redirect back
    header("Location: admin_dashboard.php");
    exit();
}
?>
