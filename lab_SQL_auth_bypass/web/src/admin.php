<?php
session_start();

if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    die('Forbidden');
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Migration Archive</title>
</head>
<body>
    <h1>Legacy Migration Archive</h1>

    <p>A legacy server migration package is available for administrators.</p>

    <pre>
migration_id: NB-MIG-2026-04
old_host: northbridge-app01
operator: michael
archive_status: retained for compatibility
    </pre>

    <p><a href="backup.php">Download migration export</a></p>
    <p><a href="logout.php">Log out</a></p>
</body>
</html>
