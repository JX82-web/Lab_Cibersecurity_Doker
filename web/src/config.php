<?php

$db_host = 'db';
$db_name = 'ecommerce';
$db_user = 'user';
$db_password = 'user!123';

$dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Lancia eccezioni se c'è un errore SQL
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Restituisce array associativi di default
    PDO::ATTR_EMULATE_PREPARES => false,                  // Usa veri prepared statements sul server MySQL
];

try {

    $pdo = new PDO($dsn, $db_user, $db_password, $options);
}

catch (PDOException $e) {

    die("Impossibile contattare il database.");
}

?>
