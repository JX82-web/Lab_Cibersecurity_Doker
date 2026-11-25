<?php
session_start();
if (isset($_GET['logout'])) {
    setcookie('auth_token', '', time() - 3600, "/");
    header("Location: index.php");
    exit;
}
?>
