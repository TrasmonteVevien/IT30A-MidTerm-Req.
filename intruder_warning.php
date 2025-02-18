<?php
session_start();
if (isset($_SESSION['intruder_warning'])) {
    echo "<script>alert('" . $_SESSION['intruder_warning'] . "'); window.location.href = 'login.php';</script>";
    unset($_SESSION['intruder_warning']);
}
?>
