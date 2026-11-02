<?php
// config.php - Configurazione del Database Aziendale
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', 'SuperSecureRootPassword2026!');
define('DB_NAME', 'ecommerce');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Errore di connessione al database critico di sistema.");
}
?>
