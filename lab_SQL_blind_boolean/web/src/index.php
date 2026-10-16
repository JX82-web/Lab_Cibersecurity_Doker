<?php
require_once __DIR__.'/config.php';

$code = $_GET['code'] ?? '';
$ok = false;

if (isset($_GET['code'])) {
    $result = $conn->query("SELECT id FROM tracking WHERE status_code = '$code' LIMIT 1");
    if ($result && $result->num_rows === 1) {
        $ok = true;
    }
}
?><!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>System Portal</title>
    <style>
        body { font-family: monospace; background: #111; color: #eee; padding: 50px; text-align: center; }
        .container { border: 1px solid #333; padding: 30px; display: inline-block; background: #1a1a1a; }
        input, button { background: #222; color: #fff; border: 1px solid #444; padding: 10px; font-family: monospace; }
        button { cursor: pointer; background: #005500; }
        .status { margin-top: 20px; font-weight: bold; }
        .valid { color: #00ff00; }
        .invalid { color: #ff0000; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Infrastructure Tracking System</h2>
        <form method="GET">
            <input type="text" name="code" placeholder="Enter Status Code" value="<?= htmlspecialchars($code) ?>">
            <button type="submit">Verify</button>
        </form>
        <?php if (isset($_GET['code'])): ?>
            <div class="status <?= $ok ? 'valid' : 'invalid' ?>">
                <?= $ok ? 'STATUS: ACTIVE / VERIFIED' : 'STATUS: INVALID / NOT FOUND' ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
