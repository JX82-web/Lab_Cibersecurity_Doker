<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit;
}

$is_admin = ($_SESSION['role'] ?? '') === 'admin';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Operations Dashboard</title>
</head>
<body>
    <h1>Operations Dashboard</h1>
    <p>Signed in as <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>.</p>

    <?php if ($is_admin): ?>
        <h2>Administrative tools</h2>
        <ul>
            <li><a href="admin.php">Migration archive</a></li>
            <li><a href="logout.php">Log out</a></li>
        </ul>
    <?php else: ?>
        <p>Your account has no administrative tools.</p>
        <p><a href="logout.php">Log out</a></p>
    <?php endif; ?>
</body>
</html>
