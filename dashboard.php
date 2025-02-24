<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Fetch user information
$userStmt = $pdo->prepare("SELECT email, department FROM users WHERE id = ?");
$userStmt->execute([$_SESSION['user_id']]);
$user = $userStmt->fetch();

// Fetch borrowed books for the logged-in user
$borrowedStmt = $pdo->prepare("
    SELECT books.id, books.title, books.author, borrowed_books.borrow_date 
    FROM books
    JOIN borrowed_books ON books.id = borrowed_books.book_id
    WHERE borrowed_books.user_id = ? AND borrowed_books.return_date IS NULL
");
$borrowedStmt->execute([$_SESSION['user_id']]);
$borrowedBooks = $borrowedStmt->fetchAll();

// Fetch available books (not currently borrowed)
$availableBooksStmt = $pdo->prepare("
    SELECT * FROM books
    WHERE id NOT IN (SELECT book_id FROM borrowed_books WHERE return_date IS NULL)
");
$availableBooksStmt->execute();
$availableBooks = $availableBooksStmt->fetchAll();

// Fetch books borrowed by others
$tempUnavailableStmt = $pdo->prepare("
    SELECT books.id, books.title, books.author, users.email AS borrowed_by
    FROM books
    JOIN borrowed_books ON books.id = borrowed_books.book_id
    JOIN users ON borrowed_books.user_id = users.id
    WHERE borrowed_books.return_date IS NULL AND borrowed_books.user_id != ?
");
$tempUnavailableStmt->execute([$_SESSION['user_id']]);
$tempUnavailableBooks = $tempUnavailableStmt->fetchAll();

// Handle borrowing and returning books
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['borrow_book_id'])) {
        $bookId = $_POST['borrow_book_id'];
        $userId = $_SESSION['user_id'];

        // Borrow a book
        $insertStmt = $pdo->prepare("INSERT INTO borrowed_books (user_id, book_id, borrow_date) VALUES (?, ?, NOW())");
        if ($insertStmt->execute([$userId, $bookId])) {
            header("Location: dashboard.php"); // Refresh page
            exit();
        }
    } elseif (isset($_POST['return_book_id'])) {
        $returnBookId = $_POST['return_book_id'];
        $returnStmt = $pdo->prepare("UPDATE borrowed_books SET return_date = NOW() WHERE user_id = ? AND book_id = ? AND return_date IS NULL");
        if ($returnStmt->execute([$_SESSION['user_id'], $returnBookId])) {
            header("Location: dashboard.php"); // Refresh page
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        body {
            background-color: #f9f9f9;
            text-align: center;
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        h2, h3 { color: #333; }
        table {
            width: 80%;
            margin: 20px auto;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            border: 1px solid #007bff;
        }
        th { background-color: #007bff; color: white; }
        .button {
            padding: 8px 12px;
            color: white;
            background-color: #28a745;
            border: none;
            cursor: pointer;
        }
        .button:hover { background-color: #218838; }
        .logout-button {
            background-color: #dc3545;
            padding: 8px 12px;
            text-decoration: none;
            color: white;
            margin-bottom: 20px;
        }
        .logout-button:hover { background-color: #c82333; }
    </style>
</head>
<body>

    <h2>Welcome, <?= htmlspecialchars($user['email']) ?></h2>
    <p>Department: <?= htmlspecialchars($user['department']) ?></p>
    <a href="logout.php" class="logout-button">Logout</a>

    <h3>Borrowed Books</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Author</th>
            <th>Borrow Date</th>
            <th>Action</th>
        </tr>
        <?php foreach ($borrowedBooks as $book): ?>
            <tr>
                <td><?= $book['id'] ?></td>
                <td><?= htmlspecialchars($book['title']) ?></td>
                <td><?= htmlspecialchars($book['author']) ?></td>
                <td><?= $book['borrow_date'] ?></td>
                <td>
                    <form method="post">
                        <input type="hidden" name="return_book_id" value="<?= $book['id'] ?>">
                        <button type="submit" class="button">Return</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <h3>Available Books</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Author</th>
            <th>Action</th>
        </tr>
        <?php foreach ($availableBooks as $book): ?>
            <tr>
                <td><?= $book['id'] ?></td>
                <td><?= htmlspecialchars($book['title']) ?></td>
                <td><?= htmlspecialchars($book['author']) ?></td>
                <td>
                    <form method="post">
                        <input type="hidden" name="borrow_book_id" value="<?= $book['id'] ?>">
                        <button type="submit" class="button">Borrow</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <h3>Borrowed by Others</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Author</th>
            <th>Borrowed By</th>
        </tr>
        <?php foreach ($tempUnavailableBooks as $book): ?>
            <tr>
                <td><?= $book['id'] ?></td>
                <td><?= htmlspecialchars($book['title']) ?></td>
                <td><?= htmlspecialchars($book['author']) ?></td>
                <td><?= htmlspecialchars($book['borrowed_by']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

</body>
</html>
