<?php
session_start();
session_destroy(); // Clear the session

// Instead of redirecting immediately, display the logout message
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logged Out</title>
    <style>
        body {
            background-color: #e9ecef;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
        }

        h1 {
            color: #333;
        }

        .button-container {
            margin-top: 20px;
        }

        .button {
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
            text-decoration: none;
        }

        .button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <h1>You have been logged out</h1>
    <div class="button-container">
        <a href="index.php" class="button">Login Again</a>
        <a href="exit.php" class="button" onclick="window.close(); return false;">Exit</a> <!-- Optional exit functionality -->
    </div>
</body>
</html>









