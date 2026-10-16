<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$host = 'sqli_db';
$db   = 'unionlab';
$user = 'web_user';
$pass = 'WebDB_Union_2026!';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("<div style='color:#ff5555; font-family:monospace; padding:20px;'>Errore critico di connessione al database: " . htmlspecialchars($e->getMessage()) . "</div>");
}
