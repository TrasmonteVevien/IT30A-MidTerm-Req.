<?php
include 'config.php';
session_start();

// Set default values
$username = isset($_SESSION['last_username']) ? $_SESSION['last_username'] : '';

// Initialize failed login attempts and session timeout
if (!isset($_SESSION['failed_attempts'])) {
    $_SESSION['failed_attempts'] = 0;
}
if (!isset($_SESSION['last_login_time'])) {
    $_SESSION['last_login_time'] = time();
}

$max_attempts = 2; // Maximum login attempts before lockout
$lockout_time = 60 * 5; // 5 minutes lockout period

// Check if user is locked out
if (isset($_SESSION['lockout_time']) && time() < $_SESSION['lockout_time']) {
    $remaining_time = ($_SESSION['lockout_time'] - time()) / 60;
    $error_message = "Too many failed attempts. Try again in " . ceil($remaining_time) . " minutes.";
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $ip_address = $_SERVER['REMOTE_ADDR'];

    // Prepare the SQL statement to check user credentials
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Reset failed attempts
        $_SESSION['failed_attempts'] = 0;
        unset($_SESSION['lockout_time']);

        // Set session user id and store last successful username
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['last_username'] = $username;
        $_SESSION['last_login_time'] = time();
        $_SESSION['success_message'] = "Login successful!";

        // Redirect to dashboard
        header("Location: dashboard.php");
        exit();
    } else {
        // Log failed attempt
        $stmt = $pdo->prepare("INSERT INTO login_attempts (username, ip_address, attempt_time) VALUES (?, ?, NOW())");
        $stmt->execute([$username, $ip_address]);

        // Incorrect credentials - increment failed attempts
        $_SESSION['failed_attempts']++;

        if ($_SESSION['failed_attempts'] >= $max_attempts) {
            $_SESSION['lockout_time'] = time() + $lockout_time;
            $error_message = "Too many failed attempts. Try again in 5 minutes.";
        } else {
            $error_message = "Invalid username or password. Attempt " . $_SESSION['failed_attempts'] . " of $max_attempts.";
        }
    }

    // Clear username input after failed login
    $username = '';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
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
        .footer-link {
            margin-top: 15px;
            font-size: 14px;
        }
        .footer-link a {
            color: #007bff;
            text-decoration: none;
        }
        .footer-link a:hover {
            text-decoration: underline;
        }
        .error-message {
            color: red;
            margin: 10px 0;
        }
        .success-message {
            color: green;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        <?php if (isset($error_message)) : ?>
            <div class="error-message"><?= htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['success_message'])) : ?>
            <div class="success-message"><?= htmlspecialchars($_SESSION['success_message']); ?></div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        <form method="post">
            <input type="text" name="username" placeholder="Username" value="<?php echo htmlspecialchars($username); ?>" required><br>
            <input type="password" name="password" placeholder="Password" required><br>
            <button type="submit">Login</button>
        </form>
        <div class="footer-link">
            <p>Don't have an account? <a href="register.php">Register here</a></p>
        </div>
    </div>
</body>
</html>
