<?php
session_start();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = "All fields are required.";
    } else {
        $conn = new mysqli('127.0.0.1', 'root', 'cybersecurity_pass_2026', 'lab_jwt_db');
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Username already exists.";
        } else {
            $stmt_insert = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, MD5(?), 'user')");
            $stmt_insert->bind_param("ss", $username, $password);
            if ($stmt_insert->execute()) {
                $success = "Registration successful! You can now <a href='index.php'>log in</a>.";
            } else {
                $error = "Error during registration.";
            }
        }
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CorpPortal - Register</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <div style="float: right;">
            <a href="index.php"><button style="background-color: #3498db; padding: 6px 12px; font-size: 14px;">Login</button></a>
        </div>
        <h1>Create Account</h1>
        <p>Register a new user account on CorpPortal.</p>
        
        <?php if ($error): ?>
            <div class="alert"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="info-box" style="border-left-color: #27ae60; background: #eafaf1;"><?php echo $success; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Register</button>
        </form>
    </div>
</body>
</html>
