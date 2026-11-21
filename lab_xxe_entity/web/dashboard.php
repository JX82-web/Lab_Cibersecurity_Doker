<?php
session_start();
if (!isset($_SESSION['user_id'])) header('Location: index.php');
?>
<h1>Dashboard</h1>
<p>Logged as <?=$_SESSION['role']?></p>
<a href="products.php">Products</a>
<a href="admin.php">Admin</a>
<a href="logout.php">Logout</a>
