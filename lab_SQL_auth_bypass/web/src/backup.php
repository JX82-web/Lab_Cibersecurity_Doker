<?php
session_start();

if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    die('Forbidden');
}

header('Content-Type: text/plain; charset=utf-8');
header('Content-Disposition: attachment; filename="migration-export.txt"');

echo "Northbridge legacy migration export\n";
echo "===================================\n";
echo "Migration ID: NB-MIG-2026-04\n";
echo "Operator account: michael\n";
echo "Temporary migration password: Migration_Archive_2026!\n";
echo "Note: remove this export after migration validation.\n";
