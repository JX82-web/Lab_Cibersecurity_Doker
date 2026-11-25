<?php
session_start();
if (isset($_GET['logout'])) {
    setcookie('auth_token', '', time() - 3600, "/");
    header("Location: index.php");
    exit;
}

function verify_jwt($jwt, $secret) {
    $parts = explode('.', $jwt);
    if (count($parts) !== 3) return false;
    [$headers_encoded, $payload_encoded, $signature_encoded] = $parts;
    
    $signature = base64_decode(strtr($signature_encoded, '-_', '+/'));
    $expected_signature = hash_hmac('sha256', "$headers_encoded.$payload_encoded", $secret, true);
    
    if (!hash_equals($expected_signature, $signature)) {
        return false;
    }
    
    $payload = json_decode(base64_decode(strtr($payload_encoded, '-_', '+/')), true);
    return $payload;
}

$auth_token = $_COOKIE['auth_token'] ?? '';
$secret = 'secret123';
$payload = verify_jwt($auth_token, $secret);

if (!$payload) {
    header("Location: index.php");
    exit;
}

$is_admin = ($payload['role'] ?? '') === 'admin';
$conn = new mysqli('127.0.0.1', 'root', 'cybersecurity_pass_2026', 'lab_jwt_db');

$notes = [];
$res = $conn->query("SELECT title, content FROM notes");
while ($row = $res->fetch_assoc()) {
    $notes[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CorpPortal - Dashboard</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <div style="float: right;">
            <a href="logout.php?logout=1"><button style="background-color: #e74c3c; padding: 6px 12px; font-size: 14px;">Log Out</button></a>
        </div>
        <h1>Welcome, <?php echo htmlspecialchars($payload['username']); ?>!</h1>
        <p>Role: <strong><?php echo htmlspecialchars($payload['role']); ?></strong></p>

        <?php if ($is_admin): ?>
            <div class="info-box" style="border-left-color: #27ae60; background: #eafaf1;">
                <h2>🛡️ Restricted Admin Panel</h2>
                <p>Welcome to the administrative control center. Access to sensitive tools and deployment files is granted.</p>
                <ul>
                    <li><a href="admin/admin_upload.php">📁 Administrative File Upload (ZIP Archives)</a></li>
                    <li><a href="admin/admin_python.php">🐍 Python Interactive Code Editor (Admin Tool)</a></li>
                </ul>
            </div>
        <?php else: ?>
            <div class="info-box">
                <h3>Standard User Dashboard</h3>
                <p>You are logged in as a standard user. Administrative privileges are required to access management utilities and deployment tools.</p>
            </div>
        <?php endif; ?>

        <h2>Company Announcements & Notes</h2>
        <?php foreach ($notes as $note): ?>
            <div style="background: #f8f9fa; border: 1px solid #e1e4e8; padding: 10px; margin-bottom: 10px; border-radius: 4px;">
                <strong><?php echo htmlspecialchars($note['title']); ?></strong>
                <p><?php echo htmlspecialchars($note['content']); ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
<?php $conn->close(); ?>

