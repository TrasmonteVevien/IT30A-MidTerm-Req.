<?php
include 'config.php';
session_start();

$max_attempts = 2; // Max login attempts
$lockout_time = 3 * 60; // 3 minutes in seconds
$now = time();

// Initialize failed attempts if not set
if (!isset($_SESSION['failed_attempts'])) {
    $_SESSION['failed_attempts'] = 0;
}

// Check if user is locked out
if (isset($_SESSION['locked_until']) && $_SESSION['locked_until'] > $now) {
    $remaining_time = $_SESSION['locked_until'] - $now;
    $error_message = "Too many failed attempts. Try again in $remaining_time seconds.";
}

// Check if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: admin_dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($error_message)) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Fetch admin credentials
    $stmt = $pdo->prepare("SELECT * FROM admin WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        if ($admin['role'] !== 'admin') {
            $error_message = "You do not have permission to access this area.";
        } else {
            // Successful login
            session_regenerate_id(true);
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];

            // Reset failed attempts
            $_SESSION['failed_attempts'] = 0;
            unset($_SESSION['locked_until']);

            header("Location: admin_dashboard.php");
            exit();
        }
    } else {
        $_SESSION['failed_attempts']++;

        if ($_SESSION['failed_attempts'] >= $max_attempts) {
            $_SESSION['locked_until'] = $now + $lockout_time;
            $error_message = "Too many failed attempts. Locked out for 3 minutes.";
        } else {
            $remaining_attempts = $max_attempts - $_SESSION['failed_attempts'];
            $error_message = "Invalid username or password. You have $remaining_attempts attempt(s) remaining.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <style>
        body {
            background-color: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
        }
        .login-container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            width: 400px;
            text-align: center;
        }
        h2 {
            margin-bottom: 20px;
        }
        input[type="text"], input[type="password"] {
            width: calc(100% - 20px);
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #cccccc;
            border-radius: 4px;
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #0056b3;
        }
        .error-message {
            color: red;
            margin: 10px 0;
            font-weight: bold;
        }
        .warning {
            color: orange;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Admin Login</h2>
        <?php if (isset($error_message)) : ?>
            <div class="error-message"><?= htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        <form method="post">
            <input type="text" name="username" placeholder="Username" required><br>
            <input type="password" name="password" placeholder="Password" required><br>
            <button type="submit">Login</button>
        </form>
        <?php if ($_SESSION['failed_attempts'] > 0 && $_SESSION['failed_attempts'] < $max_attempts): ?>
            <div class="warning">Warning: <?= $max_attempts - $_SESSION['failed_attempts']; ?> attempt(s) remaining.</div>
        <?php endif; ?>
    </div>
</body>
</html>
