<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Fetch user information to display the username
$userStmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$userStmt->execute([$_SESSION['user_id']]);
$user = $userStmt->fetch();

// Fetch borrowed books for the logged-in user
$stmt = $pdo->prepare("
    SELECT books.id, books.title, books.author, borrowed_books.borrow_date
    FROM books
    JOIN borrowed_books ON books.id = borrowed_books.book_id
    WHERE borrowed_books.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$borrowedBooks = $stmt->fetchAll();

// Fetch available books (not borrowed)
$availableBooksStmt = $pdo->prepare("
    SELECT * FROM books
    WHERE id NOT IN (SELECT book_id FROM borrowed_books)
");
$availableBooksStmt->execute();
$availableBooks = $availableBooksStmt->fetchAll();

// Fetch temporarily unavailable books (borrowed by others)
$tempUnavailableStmt = $pdo->prepare("
    SELECT books.id, books.title, users.username
    FROM books
    JOIN borrowed_books ON books.id = borrowed_books.book_id
    JOIN users ON borrowed_books.user_id = users.id
    WHERE borrowed_books.user_id != ?
");
$tempUnavailableStmt->execute([$_SESSION['user_id']]);
$tempUnavailableBooks = $tempUnavailableStmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['borrow_book_id'])) {
        // Borrow a book
        $bookId = $_POST['borrow_book_id'];
        $userId = $_SESSION['user_id'];

        // Insert into borrowed_books table
        $insertStmt = $pdo->prepare("INSERT INTO borrowed_books (user_id, book_id, borrow_date) VALUES (?, ?, NOW())");
        if ($insertStmt->execute([$userId, $bookId])) {
            echo "<p style='color: green; text-align: center;'>Book borrowed successfully!</p>";
            header("Refresh:0"); // Refresh the page to update the list
            exit();
        } else {
            echo "<p style='color: red; text-align: center;'>Failed to borrow the book. Please try again.</p>";
        }
    } elseif (isset($_POST['delete_borrowed_book_id'])) {
        // Delete borrowed book
        $deleteBookId = $_POST['delete_borrowed_book_id'];
        $deleteStmt = $pdo->prepare("DELETE FROM borrowed_books WHERE user_id = ? AND book_id = ?");
        if ($deleteStmt->execute([$_SESSION['user_id'], $deleteBookId])) {
            echo "<p style='color: green; text-align: center;'>Book returned successfully!</p>";
            header("Refresh:0"); // Refresh the page to update the list
            exit();
        } else {
            echo "<p style='color: red; text-align: center;'>Failed to return the book. Please try again.</p>";
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
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 0;
            font-family: Arial, sans-serif;
            padding: 20px;
        }

        h2, h3 {
            color: #333;
            margin-bottom: 10px;
        }

        .tab {
            display: none;
            width: 90%;
            max-width: 800px;
            margin-bottom: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            background-color: #fff;
            padding: 20px;
            border-radius: 6px;
        }

        .tab-header {
            cursor: pointer;
            padding: 10px;
            background-color: #007bff;
            color: white;
            width: 90%;
            max-width: 800px;
            border: none;
            text-align: center;
            font-size: 16px;
            border-radius: 4px;
            margin-bottom: 10px;
        }

        .active {
            display: block;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 20px;
        }

        th, td {
            border: 1px solid #007bff;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #007bff;
            color: white;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .button-container {
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            width: 100%;
            max-width: 800px;
        }

        .button {
            padding: 8px 12px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
            text-align: center;
            text-decoration: none;
        }

        .button:hover {
            background-color: #218838;
        }

        footer {
            margin-top: 20px;
            font-size: 14px;
            color: #666666;
        }

        .back-button {
            background-color: #007bff;
            padding: 8px 12px;
            font-size: 14px;
        }

        .back-button:hover {
            background-color: #0056b3;
        }
    </style>
    <script>
        function showTab(tabId) {
            const tabs = document.querySelectorAll('.tab');
            tabs.forEach(tab => {
                tab.classList.remove('active');
            });
            document.getElementById(tabId).classList.add('active');
        }
    </script>
</head>
<body>
    <h2>Dashboard - <?= htmlspecialchars($user['username']) ?>'s Profile</h2>

    <div class="button-container">
        <a href="logout.php" class="button">Logout</a>
    </div>

    <button class="tab-header" onclick="showTab('userProfileTab')">User Profile</button>
    <div id="userProfileTab" class="tab active">
        <h3>User Profile</h3>
        <p><strong>Login Time:</strong> <?= date("Y-m-d H:i:s") ?></p>
        <h4>Borrowed Books</h4>
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
                    <td><?= htmlspecialchars($book['id']) ?></td>
                    <td><?= htmlspecialchars($book['title']) ?></td>
                    <td><?= htmlspecialchars($book['author']) ?></td>
                    <td><?= htmlspecialchars($book['borrow_date']) ?></td>
                    <td>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="delete_borrowed_book_id" value="<?= $book['id'] ?>">
                            <button type="submit" class="button">Return</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <h4>Temporarily Unavailable Books</h4>
        <table>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Borrowed By</th>
            </tr>
            <?php foreach ($tempUnavailableBooks as $book): ?>
                <tr>
                    <td><?= htmlspecialchars($book['id']) ?></td>
                    <td><?= htmlspecialchars($book['title']) ?></td>
                    <td><?= htmlspecialchars($book['username']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <button class="tab-header" onclick="showTab('availableBooksTab')">Available Books</button>
    <div id="availableBooksTab" class="tab">
        <h3>Available Books</h3>
        <div class="button-container">
            <button class="button back-button" onclick="showTab('userProfileTab')">Back to Profile</button>
        </div>
        <table>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Author</th>
                <th>Action</th>
            </tr>
            <?php foreach ($availableBooks as $book): ?>
                <tr>
                    <td><?= htmlspecialchars($book['id']) ?></td>
                    <td><?= htmlspecialchars($book['title']) ?></td>
                    <td><?= htmlspecialchars($book['author']) ?></td>
                    <td>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="borrow_book_id" value="<?= $book['id'] ?>">
                            <button type="submit" class="button">Borrow</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <footer>
        &copy; 2024 Library Management System. All rights reserved.
    </footer>
</body>
</html>
