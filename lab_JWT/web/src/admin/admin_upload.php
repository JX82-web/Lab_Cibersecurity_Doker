<?php
session_start();
function verify_jwt($jwt, $secret) {
    $parts = explode('.', $jwt);
    if (count($parts) !== 3) return false;
    [$headers_encoded, $payload_encoded, $signature_encoded] = $parts;
    $signature = base64_decode(strtr($signature_encoded, '-_', '+/'));
    $expected_signature = hash_hmac('sha256', "$headers_encoded.$payload_encoded", $secret, true);
    if (!hash_equals($expected_signature, $signature)) return false;
    return json_decode(base64_decode(strtr($payload_encoded, '-_', '+/')), true);
}

$payload = verify_jwt($_COOKIE['auth_token'] ?? '', 'secret123');
if (!$payload || ($payload['role'] ?? '') !== 'admin') {
    header("Location: index.php");
    exit;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['zipfile'])) {
        $file = $_FILES['zipfile'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        // Rabbit hole: requires .zip extension, but it's safely handled (extracted or stored without RCE)
        if ($ext === 'zip') {
            $upload_dir = 'uploads/';
            $target = $upload_dir . basename($file['name']);
            if (move_uploaded_file($file['tmp_name'], $target)) {
                $message = "ZIP archive successfully uploaded and stored for review.";
            } else {
                $error = "Failed to move uploaded file.";
            }
        } else {
            $error = "Invalid file extension! Only .zip archives are allowed.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CorpPortal - Admin Upload </title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="container">
        <div style="float: right;">
            <a href="dashboard.php"><button style="background-color: #3498db; padding: 6px 12px; font-size: 14px;">Dashboard</button></a>
        </div>
        <h1>Administrative File Upload</h1>
        <p>Upload project templates and backup archives (Restricted to <code>.zip</code> files only).</p>

        <?php if ($error): ?>
            <div class="alert"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($message): ?>
            <div class="info-box" style="border-left-color: #27ae60; background: #eafaf1;"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="zipfile">Select ZIP Archive:</label>
                <input type="file" id="zipfile" name="zipfile" accept=".zip" required>
            </div>
            <button type="submit">Upload Archive</button>
        </form>

        <div class="info-box" style="margin-top: 30px;">
            <p><strong>Note:</strong> File uploads are strictly validated and restricted to ZIP format. Look elsewhere in the admin panel if you are seeking code execution capabilities.</p>
        </div>
    </div>
</body>
</html>
