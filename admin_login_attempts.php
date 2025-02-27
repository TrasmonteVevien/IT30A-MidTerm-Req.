<?php
include 'config.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Handle granting or removing access
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['attempt_id'])) {
    $attempt_id = $_POST['attempt_id'];
    $action = ($_POST['action'] == 'grant') ? 'granted' : 'removed';

    $stmt = $pdo->prepare("UPDATE login_attempts SET status = ? WHERE id = ?");
    if ($stmt->execute([$action, $attempt_id])) {
        $_SESSION['success_message'] = "Login attempt ID $attempt_id has been updated to $action.";
    } else {
        $_SESSION['error_message'] = "Failed to update login attempt ID $attempt_id.";
    }
}

// Fetch login attempts using LEFT JOIN for additional details
$stmt = $pdo->prepare("SELECT login_attempts.*, users.username AS user 
                       FROM login_attempts 
                       LEFT JOIN users ON login_attempts.username = users.username 
                       ORDER BY attempt_time DESC");
$stmt->execute();
$attempts = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Login Attempts</title>
    <style>
       body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            text-align: center;
            padding: 20px;
       }
       table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #007bff;
            padding: 10px;
            text-align: left;
        }
        th { background-color: #007bff; color: white; }
        .success { color: green; }
        .failed { color: red; }
        .action-btn { padding: 5px 10px; border: none; cursor: pointer; }
        .grant { background-color: green; color: white; }
        .remove { background-color: red; color: white; }
        .back-btn {
            margin-top: 20px;
            padding: 10px 15px;
            background-color : #007bff;
            color: white;
            border: none;
            cursor: pointer;
            text-decoration: none;
            font-size: 16px;
            display: inline-block;
            border-radius: 4px;
        }
        .back-btn:hover { background-color: #0056b3; }
    </style>
</head>
<body>
    <h2>Login Attempts</h2>

    <?php if (isset($_SESSION['success_message'])): ?>
        <p class="success"><?= htmlspecialchars($_SESSION['success_message']); ?></p>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <p class="failed"><?= htmlspecialchars($_SESSION['error_message']); ?></p>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <table>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>IP Address</th>
            <th>Time</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        <?php foreach ($attempts as $attempt): ?>
        <tr>
            <td><?= htmlspecialchars($attempt['id']); ?></td>
            <td><?= htmlspecialchars($attempt['user'] ?? 'Unknown'); ?></td>
            <td><?= htmlspecialchars($attempt['ip_address']); ?></td>
            <td><?= htmlspecialchars($attempt['attempt_time']); ?></td>
            <td class="<?= $attempt['status'] === 'failed' ? 'failed' : 'success' ?>">
                <?= htmlspecialchars($attempt['status']); ?>
            </td>
            <td>
                <?php if ($attempt['status'] == 'failed'): ?>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="attempt_id" value="<?= $attempt['id']; ?>">
                        <button type="submit" name="action" value="grant" class="action-btn grant">Grant</button>
                    </form>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="attempt_id" value="<?= $attempt['id']; ?>">
                        <button type="submit" name="action" value="remove" class="action-btn remove">Remove</button>
                    </form>
                <?php else: ?>
                    <span>N/A</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <!-- Back Button -->
    <a href="admin_dashboard.php" class="back-btn">← Back </a>
</body>
</html>
