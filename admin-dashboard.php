<?php
include 'config.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
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
    <title>Admin Dashboard - Failed Logins</title>
    <style>
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
            cursor: pointer;
        }
    </style>
</head>
<body>
    <h2>Failed Login Attempts</h2>
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
</body>
</html>
🔹 Allow Admin to Remove Suspicious Attempts
Create remove_attempt.php to let the admin delete suspicious login attempts.

php
Copy
Edit
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
