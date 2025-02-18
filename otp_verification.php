<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Simulate sending OTP to the user's registered mobile number
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Let's assume the user has entered the correct OTP (for demo purposes)
    $otp = $_POST['otp'];

    // Here, you would check the entered OTP against the one sent to the user's mobile number
    if ($otp == '1234') { // Assume OTP is '1234' for demo
        $_SESSION['verified'] = true; // Mark the user as verified
        header("Location: dashboard.php"); // Redirect to the dashboard
        exit();
    } else {
        $message = "Invalid OTP. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification</title>
</head>
<body>
    <div class="otp-container">
        <h2>OTP Verification</h2>
        <form method="post">
            <input type="text" name="otp" placeholder="Enter OTP" required><br>
            <button type="submit">Verify OTP</button>
        </form>
        <?php if (isset($message)) { echo "<p class='message'>$message</p>"; } ?>
    </div>
</body>
</html>
