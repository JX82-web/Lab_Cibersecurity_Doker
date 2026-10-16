<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('DB_HOST', 'sqli_db');
define('DB_USER', 'web_user');
define('DB_PASS', 'WebDB_Union_2026!');
define('DB_NAME', 'unionlab');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false // Permette la SQL Injection reale
    ]);
} catch (PDOException $e) {
    die("<p style='color:red; font-family:monospace;'>[!] Errore critico di connessione al database di backend.</p>");
}

$category = $_GET['category'] ?? '';
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>UnionLab - Central Gateway</title>
    <style>
        body { font-family: 'Courier New', Courier, monospace; background-color: #111; color: #0f0; padding: 20px; }
        .container { max-width: 950px; margin: auto; background: #1a1a1a; padding: 20px; border: 1px solid #333; box-shadow: 0 0 15px #0f0; }
        .navbar { display: flex; justify-content: space-between; background: #222; padding: 15px; border: 1px solid #0f0; margin-bottom: 25px; }
        .navbar .nav-links a { color: #00ffff; text-decoration: none; margin-left: 20px; font-weight: bold; }
        .navbar .nav-links a:hover { color: #fff; text-decoration: underline; }
        .brand { font-size: 1.3em; font-weight: bold; color: #fff; }
        h1 { color: #fff; border-bottom: 2px solid #0f0; padding-bottom: 10px; margin-top: 0; }
        .item-box { background: #222; padding: 15px; margin-bottom: 15px; border-left: 5px solid #0f0; border-top: 1px solid #333; }
        .cost { font-weight: bold; color: #ff00ff; }
        .filter-nav { margin-bottom: 20px; font-size: 1.1em; }
        .filter-nav a { color: #0f0; text-decoration: none; margin-right: 15px; border: 1px solid #333; padding: 5px 10px; background: #222; }
        .filter-nav a:hover { background: #0f0; color: #111; }
    </style>
</head>
<body>
<div class="container">

    <!-- Barra di Navigazione con i riferimenti alle pagine reali del Lab -->
    <div class="navbar">
        <div class="brand">⚡ UNIONLAB::PORTAL</div>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="login.php">Login Area</a>
            <a href="register.php">Registrazione</a>
            <a href="dashboard.php">Dashboard</a>
        </div>
    </div>

    <h1>Catalogo Soluzioni Pubbliche</h1>

    <!-- Filtri di Categoria -->
    <div class="filter-nav">
        <strong>Filtra:</strong>
        <a href="?category=Cybersecurity">Cybersecurity</a>
        <a href="?category=Network">Network</a>
        <a href="?category=Software">Software</a>
        <a href="?category=">Mostra Tutto</a>
    </div>

    <div class="content">
        <?php
        if (!empty($category)) {
            $query = "SELECT item_name, category, cost, notes FROM store_items WHERE category = '" . $category . "'";
        } else {
            $query = "SELECT item_name, category, cost, notes FROM store_items WHERE category != 'UserAccount'";
        }

        try {
            $stmt = $pdo->query($query);
            $results = $stmt->fetchAll();

            if ($results) {
                foreach ($results as $row) {
                    echo "<div class='item-box'>";
                    echo "<h3>" . htmlspecialchars($row['item_name'] ?? '') . " <span class='cost'>[" . htmlspecialchars($row['cost'] ?? '') . " €]</span></h3>";
                    echo "<p><strong>Categoria:</strong> " . htmlspecialchars($row['category'] ?? '') . "</p>";
                    echo "<p><strong>Note interne:</strong> " . htmlspecialchars($row['notes'] ?? '') . "</p>";
                    echo "</div>";
                }
            } else {
                echo "<p>Nessun elemento trovato per i parametri specificati.</p>";
            }
        } catch (Exception $e) {
            echo "<div style='background:#300; border:1px solid red; padding:15px; color:#f33; margin-top:20px;'>";
            echo "<strong>⚠️ DATABASE ERROR:</strong><br>" . htmlspecialchars($e->getMessage());
            echo "</div>";
        }
        ?>
    </div>
</div>
</body>
</html>
