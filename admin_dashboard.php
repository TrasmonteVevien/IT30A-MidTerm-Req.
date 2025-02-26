<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Show login notifier message
if (isset($_SESSION['login_notifier'])) {
    echo "<p style='color: red; font-weight: bold;'>".$_SESSION['login_notifier']."</p>";
    unset($_SESSION['login_notifier']); // Clear the message after displaying it
}
?>

<h2>Welcome, Admin!</h2>
<p><a href="admin_login_attempts.php">View Login Attempts</a></p>
