<?php
// index.php - Portale E-Commerce Enterprise (Staging Lab)
ob_start();
require_once 'config.php';
session_start();

// Catalogo prodotti aziendali mappati direttamente sui percorsi dei file delle specifiche
$prodotti = [
    1 => [
        'nome' => 'Server Blade Pro v4', 
        'prezzo' => '1299.00€', 
        'desc' => 'Hardware aziendale ad alte prestazioni per nodi di calcolo privati.', 
        'path' => 'pages/prod_1.html'
    ],
    2 => [
        'nome' => 'Router Firewall HW-2026', 
        'prezzo' => '450.00€', 
        'desc' => 'Protezione perimetrale avanzata con supporto nativo VPN e ispezione dei pacchetti.', 
        'path' => 'pages/prod_2.html'
    ],
    3 => [
        'nome' => 'Licenza Enterprise OS', 
        'prezzo' => '899.99€', 
        'desc' => 'Supporto core enterprise 24/7 con aggiornamenti di sicurezza prioritari.', 
        'path' => 'pages/prod_3.html'
    ]
];
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShopCore Enterprise - Portale Forniture IT</title>
    <style>
        body { background-color: #121212; color: #e0e0e0; font-family: 'Segoe UI', sans-serif; margin: 0; padding: 0; }
        .navbar { background-color: #1a1a1a; padding: 15px 30px; border-bottom: 1px solid #2d2d2d; display: flex; justify-content: space-between; align-items: center; }
        .navbar-brand { color: #9fef00; text-decoration: none; font-weight: bold; font-size: 1.3rem; }
        .nav-links a { color: #b0b0b0; text-decoration: none; margin-right: 20px; font-size: 0.95rem; }
        .nav-links a:hover { color: #fff; }
        .container { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 25px; margin-top: 30px; }
        .card { background-color: #1e1e1e; border: 1px solid #2d2d2d; border-radius: 6px; padding: 20px; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 4px 6px rgba(0,0,0,0.3); }
        .card-title { color: #fff; margin-top: 0; font-size: 1.2rem; }
        .card-desc { color: #a0a0a0; font-size: 0.9rem; line-height: 1.4; margin-bottom: 20px; }
        .card-footer { display: flex; justify-content: space-between; align-items: center; }
        .price { font-size: 1.3rem; font-weight: bold; color: #fff; }
        .btn { background-color: #242424; color: #9fef00; border: 1px solid #9fef00; padding: 8px 14px; border-radius: 4px; text-decoration: none; font-size: 0.85rem; font-weight: 600; }
        .btn:hover { background-color: #9fef00; color: #121212; }
        .alert { padding: 15px; border-radius: 4px; margin-bottom: 25px; font-size: 0.95rem; }
        .alert-danger { background-color: #3a1a1a; border: 1px solid #cc3333; color: #ff9999; }
        .form-control { width: 100%; padding: 10px; background-color: #121212; border: 1px solid #2d2d2d; border-radius: 4px; color: #fff; box-sizing: border-box; }
        footer { text-align: center; color: #666; font-size: 0.85rem; margin-top: 60px; padding: 20px 0; border-top: 1px solid #2d2d2d; }
        .badge { background-color: #2d2d2d; color: #9fef00; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="index.php" class="navbar-brand">🛒 ShopCore Enterprise</a>
    <div class="nav-links">
        <a href="index.php">Catalogo</a>
        <a href="index.php?page=documentazione">Documentazione</a>
        <?php if (isset($_SESSION['user'])): ?>
            <a href="index.php?page=dashboard" style="color: #9fef00;">Dashboard</a>
            <a href="logout.php" style="color: #ff4444;">Logout (<?= htmlspecialchars($_SESSION['user']) ?>)</a>
        <?php else: ?>
            <a href="index.php?page=login">Accedi</a>
            <a href="index.php?page=register">Registrati</a>
        <?php endif; ?>
    </div>
</nav>

<div class="container">
    <?php
    // UNICO ROUTER CENTRALIZZATO CON PARAMETRO page=
    if (isset($_GET['page'])) {
        $page = $_GET['page'];
        
        // Elenco delle parole chiave delle sottopagine interne standard
        $standard_pages = ['login', 'register', 'dashboard', 'documentazione'];
        
        if (in_array($page, $standard_pages)) {
            include("pages/" . $page . ".php");
        } 
        // VETTORE LFI: Se non è una pagina standard, l'applicazione controlla se il file esiste direttamente
        // Questo permette di caricare i file HTML dei prodotti (es. pages/prod_1.html) o file di sistema ed Apache log
        elseif (file_exists($page)) {
            include($page);
        } else {
            echo "<div class='alert alert-danger'>Errore 404: La risorsa specificata [ " . htmlspecialchars($page) . " ] non esiste nel sistema.</div>";
        }
    } 
    // VIEW DI DEFAULT: Catalogo Prodotti Enterprise
    else {
        echo '<h2 style="margin-top:0; color:#fff; border-bottom:1px solid #2d2d2d; padding-bottom:10px;">Infrastrutture Hardware Disponibili</h2>';
        echo '<div class="grid">';
        foreach ($prodotti as $id => $p) {
            echo '
            <div class="card">
                <div>
                    <h3 class="card-title">' . htmlspecialchars($p['nome']) . '</h3>
                    <p class="card-desc">' . htmlspecialchars($p['desc']) . '</p>
                </div>
                <div class="card-footer">
                    <span class="price">' . $p['prezzo'] . '</span>
                    <!-- I bottoni del catalogo puntano ora correttamente al parametro page= -->
                    <a href="index.php?page=' . $p['path'] . '" class="btn">Specifiche Tecniche</a>
                </div>
            </div>';
        }
        echo '</div>';
    }
    ?>
</div>

<footer>
    &copy; 2026 ShopCore IT Infrastructure Solutions. Staging Environment Server Portal v1.2.9
</footer>

</body>
</html>
