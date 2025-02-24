<?php
include 'config.php';
session_start();

// Security: Prevent session fixation
session_regenerate_id(true);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Validate input
    if (!empty($email) && !empty($password)) {
        // Prepare SQL statement to get user details
        $stmt = $pdo->prepare("SELECT id, email, password, department FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Verify user and password
        if ($user && password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['department'] = $user['department'];

            // Redirect to the dashboard
            header("Location: dashboard.php");
            exit();
        } else {
            $_SESSION['error_message'] = "Invalid email or password.";
        }
    } else {
        $_SESSION['error_message'] = "Please enter both email and password.";
    }
}

// Redirect back to login page if authentication fails
header("Location: index.php");
exit();
