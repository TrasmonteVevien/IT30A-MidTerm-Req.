<?php
session_start();
include 'config.php'; // Ensure this file contains your database connection

// Set default values
$message = ''; 
$username = '';

// Check if there was a previously successful login and set the username field
if (isset($_SESSION['last_username'])) {
    $username = $_SESSION['last_username'];
    unset($_SESSION['last_username']); // Clear the stored username after showing it once
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username']; // Capture the entered username
    $password = $_POST['password'];

    // Prepare the SQL statement
    $stmt = $pdo->prepare("SELECT password FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Password is correct, start the session
        $_SESSION['username'] = $username; // Store the username in session
        $_SESSION['last_username'] = $username; // Store last successful login
        header("Location: dashboard.php"); // Redirect to a protected page
        exit();
    }
    // No need to set error message as user experience should remain smooth

    // Clear fields for new login attempt
    $username = '';
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <style>
        body {
            background-color: #f0f2f5;
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
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 300px;
        }
        h2 {
            margin-bottom: 20px;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #cccccc;
            border-radius: 4px;
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        <form method="post">
            <input type="text" name="username" placeholder="Username" value="<?php echo htmlspecialchars($username); ?>" required><br>
            <input type="password" name="password" placeholder="Password" required><br>
            <button type="submit">Login</button>
        </form>
        <p>Don't have an account? <a href="register.php">Register here</a></p>
    </div>
</body>
</html>

