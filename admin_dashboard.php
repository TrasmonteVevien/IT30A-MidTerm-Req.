<?php
include 'config.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Fetch failed login attempts
$stmt = $pdo->query("SELECT * FROM login_attempts ORDER BY attempt_time DESC");
$failed_attempts = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            padding: 20px;
        }
        .dashboard-container {
            max-width: 900px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
        }
        h2 {
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        .delete-btn {
            color: red;
            text-decoration: none;
            cursor: pointer;
        }
        .logout-btn {
            display: inline-block;
            padding: 10px 15px;
            background-color: #dc3545;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 10px;
        }
        .logout-btn:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <h2>Admin Dashboard</h2>
        <p>Welcome, <strong><?= htmlspecialchars($_SESSION['admin_username']); ?></strong>!</p>
        <a href="admin_logout.php" class="logout-btn">Logout</a>
        
        <h3>Failed Login Attempts</h3>
        <table>
            <tr>
                <th>Username</th>
                <th>IP Address</th>
                <th>Attempt Time</th>
                <th>Action</th>
            </tr>
            <?php foreach ($failed_attempts as $attempt): ?>
            <tr>
                <td><?= htmlspecialchars($attempt['username']); ?></td>
                <td><?= htmlspecialchars($attempt['ip_address']); ?></td>
                <td><?= htmlspecialchars($attempt['attempt_time']); ?></td>
                <td><a href="remove_attempt.php?id=<?= $attempt['id']; ?>" class="delete-btn">Remove</a></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>
