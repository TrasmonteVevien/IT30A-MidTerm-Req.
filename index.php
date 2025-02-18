<?php
session_start();
include 'config.php'; // Ensure this file contains your database connection

// Set default values
$message = ''; 
$username = '';
$max_login_attempts = 2; // Max login attempts set to 2
$timeout_duration = 300; // Timeout for failed login attempts (5 minutes)

// Check for login attempt count and lockout expiration
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['first_failed_attempt'] = time();
}

// Check if account is locked due to too many failed attempts
if ($_SESSION['login_attempts'] >= $max_login_attempts) {
    $time_since_first_failed_attempt = time() - $_SESSION['first_failed_attempt'];
    if ($time_since_first_failed_attempt < $timeout_duration) {
        $remaining_time = $timeout_duration - $time_since_first_failed_attempt;
        $message = "Too many failed attempts. Try again in $remaining_time seconds.";
    } else {
        // Reset login attempts after lockout period expires
        $_SESSION['login_attempts'] = 0;
        unset($_SESSION['first_failed_attempt']);
    }
}

if (isset($_SESSION['last_username'])) {
    $username = $_SESSION['last_username'];
    unset($_SESSION['last_username']);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_SESSION['login_attempts'] < $max_login_attempts) {
    $username = $_POST['username'];  // Institutional ID
    $password = $_POST['password'];  // Mobile number as password

    // Prepare the SQL statement to get the user details based on institutional ID
    $stmt = $pdo->prepare("SELECT password, username FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    // Check if the user exists and verify password
    if ($user && $password == $user['password']) {
        // Check for any suspicious activity (IP mismatch or session mismatch)
        if ($_SESSION['username'] != $username || $_SESSION['last_ip'] != $_SERVER['REMOTE_ADDR']) {
            $_SESSION['intruder_warning'] = "Suspicious activity detected. Redirecting to security page.";
            header("Location: intruder_warning.php");
            exit();
        }

        // Successful login, request for second layer verification (PIN or OTP)
        $_SESSION['username'] = $username;
        $_SESSION['last_username'] = $username;
        $_SESSION['login_attempts'] = 0; // Reset login attempts on success
        $_SESSION['last_ip'] = $_SERVER['REMOTE_ADDR']; // Store IP address for session tracking

        // Check if user has already verified via PIN/OTP
        if (!isset($_SESSION['verified'])) {
            // Ask for PIN or OTP verification here
            header("Location: otp_verification.php");
            exit();
        }

        // Proceed to dashboard if PIN/OTP is verified
        header("Location: dashboard.php");
        exit();
    } else {
        // Failed login attempt
        $_SESSION['login_attempts']++;
        if ($_SESSION['login_attempts'] == 1) {
            $_SESSION['first_failed_attempt'] = time();
        }
        $message = "Invalid institutional ID or mobile number.";
    }
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
            text-align: center;
        }
        .login-container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            width: 400px;
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
        .message {
            color: red;
            font-size: 14px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        <form method="post">
            <input type="text" name="username" placeholder="Institutional ID (e.g., 20221185)" value="<?php echo htmlspecialchars($username); ?>" required><br>
            <input type="password" name="password" placeholder="Mobile Number (e.g., 09511959950)" required><br>
            <button type="submit">Login</button>
        </form>
        <p class="footer-link">Don't have an account? <a href="register.php">Register here</a></p>
        <?php if ($message) { echo "<p class='message'>$message</p>"; } ?>
    </div>
</body>
</html>
