<?php
$db_host = 'sqli_db';
$db_user = 'web_user';
$db_pass = 'WebDB_AuthLab_2026!';
$db_name = 'authlab';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_errno) {
    http_response_code(500);
    die("Database connection failed.");
}

$conn->set_charset("utf8mb4");
?>
