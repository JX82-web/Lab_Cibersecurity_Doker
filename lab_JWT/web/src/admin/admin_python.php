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

$output = '';
$code = $_POST['code'] ?? 'print("Hello Admin Python Environment")';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['code'])) {
    // Execute Python script securely/insecurely as requested (vulnerable to os/sys execution)
    $descriptorspec = [
        0 => ["pipe", "r"],  // stdin
        1 => ["pipe", "w"],  // stdout
        2 => ["pipe", "w"]   // stderr
    ];
    
    // Write code to temp file and execute python3
    $tmp_file = tempnam(sys_get_temp_dir(), 'py_exec_');
    file_put_contents($tmp_file, $code);
    
    $process = proc_open("python3 " . escapeshellarg($tmp_file), $descriptorspec, $pipes);
    if (is_resource($process)) {
        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process);
        
        $output = $stdout . $stderr;
    } else {
        $output = "Error executing Python process.";
    }
    @unlink($tmp_file);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CorpPortal - Python Interactive Editor</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="container" style="max-width: 900px;">
        <div style="float: right;">
            <a href="dashboard.php"><button style="background-color: #3498db; padding: 6px 12px; font-size: 14px;">Dashboard</button></a>
        </div>
        <h1>Python Interactive Code Editor</h1>
        <p>Administrative tool for automated maintenance scripts and diagnostics (Supports standard library imports like <code>os</code> and <code>sys</code>).</p>

        <form method="POST">
            <div class="form-group">
                <label for="code">Python Code:</label>
                <textarea id="code" name="code" rows="10" style="width: 100%; font-family: monospace; padding: 10px; border: 1px solid #ccc; border-radius: 4px;"><?php echo htmlspecialchars($code); ?></textarea>
            </div>
            <button type="submit">Run Code</button>
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
