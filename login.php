<?php
include 'config.php';
session_start();

$max_attempts = 2; // Maximum allowed attempts
$lockout_time = 3 * 60; // Lockout duration in seconds
$now = time();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Fetch user data including failed attempts and lockout time
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user) {
        // Check if the user is locked out
        if ($user['locked_until'] && $user['locked_until'] > $now) {
            $remaining_time = $user['locked_until'] - $now;
            $error_message = "Too many failed attempts. Try again in $remaining_time seconds.";
        } else {
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Successful login
                session_regenerate_id(true);
                $_SESSION['user_logged_in'] = true;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_username'] = $user['username'];

                // Reset failed attempts and unlock user
                $stmt = $pdo->prepare("UPDATE users SET failed_attempts = 0, locked_until = NULL WHERE id = ?");
                $stmt->execute([$user['id']]);

                header("Location: user_dashboard.php");
                exit();
            } else {
                // Increment failed attempts
                $failed_attempts = $user['failed_attempts'] + 1;

                if ($failed_attempts >= $max_attempts) {
                    $lock_until = $now + $lockout_time;
                    $stmt = $pdo->prepare("UPDATE users SET failed_attempts = ?, locked_until = ? WHERE id = ?");
                    $stmt->execute([$failed_attempts, $lock_until, $user['id']]);
                    $error_message = "Too many failed attempts. Locked out for 3 minutes.";
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET failed_attempts = ? WHERE id = ?");
                    $stmt->execute([$failed_attempts, $user['id']]);
                    $remaining_attempts = $max_attempts - $failed_attempts;
                    $error_message = "Invalid username or password. You have $remaining_attempts attempt(s) remaining.";
                }
            }
        }
    } else {
        $error_message = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
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
        <h2>User Login</h2>
        <?php if (isset($error_message)) : ?>
            <div class="error-message"><?= htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        <form method="post">
            <input type="text" name="username" placeholder="Username" required><br>
            <input type="password" name="password" placeholder="Password" required><br>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>
