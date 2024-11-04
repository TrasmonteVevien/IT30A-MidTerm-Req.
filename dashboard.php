<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        body {
            background-color: #f7f9fb;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 0;
            font-family: Arial, sans-serif;
            padding: 20px;
            color: #333;
        }

        h2, h3 {
            color: #333;
            margin-bottom: 10px;
        }

        .tab-header {
            cursor: pointer;
            padding: 15px;
            background-color: #007bff;
            color: white;
            width: 80%;
            border: none;
            text-align: center;
            border-radius: 4px;
            margin-top: 10px;
            transition: background-color 0.3s;
        }

        .tab-header:hover {
            background-color: #0056b3;
        }

        .tab {
            display: none;
            width: 80%;
            margin: 20px 0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
        }

        .active {
            display: block;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid #007bff;
            padding: 12px;
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
            display: flex;
            justify-content: space-between;
            width: 100%;
            margin-bottom: 20px;
        }

        .button {
            padding: 10px 15px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            text-align: center;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .button:hover {
            background-color: #218838;
        }

        .back-button {
            background-color: #007bff;
        }

        .back-button:hover {
            background-color: #0056b3;
        }

        footer {
            margin-top: 20px;
            font-size: 14px;
            color: #666666;
        }
    </style>
    <script>
        function showTab(tabId) {
            document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
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
