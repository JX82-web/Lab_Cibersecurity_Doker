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
    header("Location: ../index.php");
    exit;
}

$output = '';
$code = $_POST['code'] ?? 'console.log("Hello Welcome");';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['code'])) {
    $ch = curl_init('http://127.0.0.1:5555/exec');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['code' => $code]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    
    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    
    if ($err) {
        $output = "Error connecting to internal root service on port 5555: " . $err;
    } else {
        $resObj = json_decode($response, true);
        $output = $resObj['output'] ?? $response;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CorpPortal - JS Interactive Editor</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="container" style="max-width: 900px;">
        <h1>JavaScript Interactive Code Editor (Port 5555)</h1>
        <p>Internal administrative tool running as local Node.js service on <code>127.0.0.1:5555</code>.</p>
        <form method="POST">
            <div class="form-group">
                <label for="code">JavaScript Code </label>
                <textarea id="code" name="code" rows="10" style="width: 100%; font-family: monospace; padding: 10px; border: 1px solid #ccc; border-radius: 4px;"><?php echo htmlspecialchars($code); ?></textarea>
            </div>
            <button type="submit">Run </button>
        </form>

        <?php if ($output !== ''): ?>
            <div style="margin-top: 20px;">
                <label><strong>Execution Output:</strong></label>
                <pre style="background: #1e1e1e; color: #dcdcdc; padding: 15px; border-radius: 4px; overflow-x: auto; font-family: monospace;"><?php echo htmlspecialchars($output); ?></pre>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
