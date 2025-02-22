<?php
include 'config.php';
session_start();

// Set default values for username and clear password input
$username = isset($_SESSION['last_username']) ? $_SESSION['last_username'] : '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepare the SQL statement to check user credentials
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Set session user id and store last successful username
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['last_username'] = $username;

        // Redirect to the dashboard page after successful login
        header("Location: dashboard.php"); // Change this to your intended page
        exit();
    } else {
        // Incorrect username/password
        $error_message = "Invalid username or password.";
    }

    // Clear fields for new login attempt
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
            background-color: #e9ecef; /* Updated background color */
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
        }
        .login-container {
            background-color: #ffffff;
            padding: 30px; /* Increased padding for better spacing */
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2); /* More pronounced shadow */
            width: 400px; /* Increased width for better usability */
            text-align: center;
        }
        h2 {
            margin-bottom: 20px;
        }
        input[type="text"], input[type="password"] {
            width: calc(100% - 20px); /* Full width with padding adjustment */
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #cccccc;
            border-radius: 4px;
        }
        button {
            width: 100%; /* Full width button */
            padding: 10px;
            background-color: #007bff; /* Button color */
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px; /* Increased font size for better readability */
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #0056b3; /* Darker shade on hover */
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
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        <?php if (isset($error_message)) : ?>
            <div class="error-message"><?= htmlspecialchars($error_message); ?></div>
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

