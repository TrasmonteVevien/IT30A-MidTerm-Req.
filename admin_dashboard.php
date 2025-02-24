<?php
include 'config.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Fetch all users
$usersStmt = $pdo->query("SELECT id, email, department FROM users");
$users = $usersStmt->fetchAll();

// Fetch login activity
$loginsStmt = $pdo->query("
    SELECT logins.id, users.email, logins.login_time, logins.ip_address
    FROM logins 
    JOIN users ON logins.user_id = users.id
    ORDER BY logins.login_time DESC
");
$logins = $loginsStmt->fetchAll();

// Fetch all borrowed books
$borrowedStmt = $pdo->query("
    SELECT books.id, books.title, books.author, users.email, borrowed_books.borrow_date
    FROM borrowed_books
    JOIN books ON borrowed_books.book_id = books.id
    JOIN users ON borrowed_books.user_id = users.id
    WHERE borrowed_books.return_date IS NULL
");
$borrowedBooks = $borrowedStmt->fetchAll();

// Handle user logout (force log out unauthorized users)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['logout_user_id'])) {
    $logoutUserId = $_POST['logout_user_id'];
    session_destroy();
    $deleteSession = $pdo->prepare("DELETE FROM logins WHERE user_id = ?");
    $deleteSession->execute([$logoutUserId]);
    header("Location: admin_dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        body { text-align: center; font-family: Arial, sans-serif; }
        table { width: 80%; margin: auto; border-collapse: collapse; }
        th, td { padding: 10px; border: 1px solid #007bff; }
        th { background-color: #007bff; color: white; }
        .button { padding: 8px 12px; background-color: #dc3545; color: white; border: none; cursor: pointer; }
        .button:hover { background-color: #c82333; }
    </style>
</head>
<body>

    <h2>Admin Dashboard</h2>
    <a href="admin_logout.php" class="button">Logout</a>

    <h3>All Users</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Email</th>
            <th>Department</th>
        </tr>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?= $user['id'] ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><?= htmlspecialchars($user['department']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <h3>Login Activity</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Email</th>
            <th>Login Time</th>
            <th>IP Address</th>
            <th>Action</th>
        </tr>
        <?php foreach ($logins as $login): ?>
            <tr>
                <td><?= $login['id'] ?></td>
                <td><?= htmlspecialchars($login['email']) ?></td>
                <td><?= $login['login_time'] ?></td>
                <td><?= $login['ip_address'] ?></td>
                <td>
                    <form method="post">
                        <input type="hidden" name="logout_user_id" value="<?= $login['id'] ?>">
                        <button type="submit" class="button">Logout User</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <h3>Borrowed Books</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Author</th>
            <th>Borrowed By</th>
            <th>Borrow Date</th>
        </tr>
        <?php foreach ($borrowedBooks as $book): ?>
            <tr>
                <td><?= $book['id'] ?></td>
                <td><?= htmlspecialchars($book['title']) ?></td>
                <td><?= htmlspecialchars($book['author']) ?></td>
                <td><?= htmlspecialchars($book['email']) ?></td>
                <td><?= $book['borrow_date'] ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

</body>
</html>
