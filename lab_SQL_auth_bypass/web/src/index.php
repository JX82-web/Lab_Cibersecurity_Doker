<?php
session_start();
require_once __DIR__ . '/config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Intentionally vulnerable training target:
    // user input is concatenated into the authentication query.
    $sql = "SELECT id, username, password, role
            FROM users
            WHERE username = '$username'
              AND password = '$password'
            LIMIT 1";

    $result = $conn->query($sql);

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        header('Location: dashboard.php');
        exit;
    }

    $error = 'Invalid credentials.';
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Northbridge Operations Portal</title>
    <style>
        body { font-family: sans-serif; max-width: 720px; margin: 60px auto; }
        input { display:block; width:100%; padding:10px; margin:8px 0 16px; box-sizing:border-box; }
        button { padding:10px 18px; }
        .error { color:#a00; }
        .hint { color:#666; }
    </style>
</head>
<body>
    <h1>Northbridge Operations Portal</h1>
    <p class="hint">Internal migration dashboard.</p>

    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="post">
        <label>Username</label>
        <input name="username" autocomplete="off" required>

        <label>Password</label>
        <input name="password" type="password" required>

        <button type="submit">Sign in</button>
    </form>
</body>
</html>
