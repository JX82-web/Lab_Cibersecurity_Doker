<?php
session_start();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $conn = new mysqli('127.0.0.1', 'root', 'cybersecurity_pass_2026', 'lab_jwt_db');
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("SELECT role FROM users WHERE username = ? AND password = MD5(?)");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        function generate_jwt($headers, $payload, $secret) {
            $headers_encoded = rtrim(strtr(base64_encode(json_encode($headers)), '+/', '-_'), '=');
            $payload_encoded = rtrim(strtr(base64_encode(json_encode($payload)), '+/', '-_'), '=');
            $signature = hash_hmac('sha256', "$headers_encoded.$payload_encoded", $secret, true);
            $signature_encoded = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');
            return "$headers_encoded.$payload_encoded.$signature_encoded";
        }

        $headers = ['alg' => 'HS256', 'typ' => 'JWT'];
        $payload = [
            'username' => $username,
            'role' => $row['role'],
            'exp' => time() + 3600
        ];
        
        $secret = 'secret123';
        $jwt = generate_jwt($headers, $payload, $secret);

        setcookie('auth_token', $jwt, time() + 3600, "/");
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Invalid credentials provided.";
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Enterprise Portal - Login</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="hero-overlay"></div>
    <div class="container login-container">
        <div class="top-nav">
            <a href="register.php" class="btn-secondary">Create Account</a>
        </div>
        
        <div class="brand-header">
            <h1>Enterprise Cloud Portal</h1>
            <p>Secure Corporate Infrastructure & Services</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required placeholder="Enter username">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="Enter password">
            </div>
            <button type="submit" class="btn-primary">Sign In</button>
        </form>
    </div>
</body>
</html>


